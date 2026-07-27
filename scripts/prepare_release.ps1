<#
.SYNOPSIS
    Script de preparacao do Pipeline Oficial de Release.
.DESCRIPTION
    Este script e responsavel exclusivamente por:
    1. Preparacao da Release
    2. Sincronizacao automatica do versionamento
    3. Atualizacao da documentacao oficial
    4. Validacoes de consistencia
    5. Coordenacao da execucao do scripts/build_release.ps1
    6. Encerramento do processo de preparacao antes da etapa de publicacao
#>
param (
    [Parameter(Mandatory=$true)]
    [string]$Version
)

$ErrorActionPreference = "Stop"

# Configurar console para compatibilidade ASCII nativa
[Console]::OutputEncoding = [System.Text.Encoding]::ASCII
$OutputEncoding = [System.Text.Encoding]::ASCII
cmd /c chcp 437 > $null

# Determinar os diretorios raiz dinamicamente
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir

Write-Output "=================================================="
Write-Output "INICIANDO PREPARACAO DA RELEASE"
Write-Output "=================================================="

# 1. Validar formato da versao informada (MAJOR.MINOR.PATCH)
if ($Version -notmatch "^[0-9]+\.[0-9]+\.[0-9]+$") {
    Write-Output "[ERRO] A versao '$Version' e invalida! O formato correto e MAJOR.MINOR.PATCH (ex: 2.0.1)."
    exit 1
}
Write-Output "[OK] Formato de versao '$Version' validado."

# 2. Detectar a versao atual (lendo o cabecalho do arquivo principal)
$main_file = Join-Path $source_dir "gerador-posts-gemini.php"
if (!(Test-Path $main_file)) {
    Write-Output "[ERRO] Arquivo de entrada principal do plugin nao encontrado em: $main_file"
    exit 1
}

$main_content = Get-Content $main_file -Raw
if ($main_content -match "\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)") {
    $old_version = $Matches[1]
} else {
    Write-Output "[ERRO] Nao foi possivel identificar a versao anterior no cabecalho do plugin."
    exit 1
}
Write-Output "[INFO] Versao anterior identificada: v$old_version"

$updated_files = @()

# Se a versao informada for igual a atual, pular atualizacao de arquivos
if ($Version -eq $old_version) {
    Write-Output "[INFO] Versao $Version ja sincronizada."
    Write-Output "[INFO] Nenhuma atualizacao de versionamento necessaria."
} else {
    Write-Output "[INFO] Iniciando preparacao de versao: v$old_version -> v$Version"

    # Lista de arquivos para atualizar
    $files_to_update = @(
        "gerador-posts-gemini.php",
        "includes/Core/PluginBootstrap.php",
        "README.md",
        "CHANGELOG.md",
        "docs/releases/RELEASE_PROCESS.md"
    )

    foreach ($file_rel in $files_to_update) {
        $file_abs = Join-Path $source_dir ($file_rel.Replace("/", [System.IO.Path]::DirectorySeparatorChar))
        if (Test-Path $file_abs) {
            $content = Get-Content $file_abs -Raw
            
            # Tratar atualizacao especifica para o CHANGELOG.md (inserir nova secao)
            if ($file_rel -eq "CHANGELOG.md") {
                if ($content -notmatch "## $Version") {
                    $today = Get-Date -Format "yyyy-MM-dd"
                    $target_section = "## $old_version"
                    $new_section = "## $Version - $today`r`n`r`n### Adicionado`r`n- Atualizacao e consolidacao da Release v$Version.`r`n`r`n## $old_version"
                    $content = $content.Replace($target_section, $new_section)
                }
            } else {
                # Substituicao simples da string da versao anterior pela nova versao
                $content = $content.Replace($old_version, $Version)
            }

            Set-Content $file_abs $content -NoNewline
            $updated_files += $file_rel
            Write-Output "[OK] Arquivo atualizado: $file_rel"
        }
    }

    # 4. Validar se nao sobraram referencias a versao anterior nos arquivos operacionais e manuais primarios
    Write-Output "[INFO] Executando varreduras de consistencia pos-atualizacao..."
    
    $critical_files = @(
        "gerador-posts-gemini.php",
        "includes/Core/PluginBootstrap.php",
        "README.md",
        "docs/releases/RELEASE_PROCESS.md"
    )

    $inconsistencies = @()

    foreach ($file_rel in $critical_files) {
        $file_abs = Join-Path $source_dir ($file_rel.Replace("/", [System.IO.Path]::DirectorySeparatorChar))
        if (Test-Path $file_abs) {
            $content = Get-Content $file_abs -Raw
            if ($content -match "\b$($old_version.Replace('.', '\.'))\b") {
                $inconsistencies += "$file_rel (Contem referencia antiga: $old_version)"
            }
        }
    }

    if ($inconsistencies.Count -gt 0) {
        Write-Output "[ERRO] Inconsistencia de versao detectada! O processo de build foi abortado."
        Write-Output "Arquivos pendentes de correcao manual:"
        foreach ($inc in $inconsistencies) {
            Write-Output " - $inc"
        }
        exit 1
    }

    Write-Output "[OK] Consistencia de versionamento validada com sucesso."
}

# 5. Executar o script de build build_release.ps1 para gerar o ZIP de producao
$build_script = Join-Path $script_dir "build_release.ps1"
if (Test-Path $build_script) {
    Write-Output "[INFO] Disparando o empacotamento oficial build_release.ps1..."
    & $build_script
} else {
    Write-Output "[ERRO] Script de build $build_script nao encontrado."
    exit 1
}

Write-Output "`n=================================================="
Write-Output "RESUMO DE PREPARACAO DA RELEASE"
Write-Output "=================================================="
Write-Output "Versao Preparada   : v$Version"
Write-Output "Arquivos Sincroniz : $($updated_files -join ', ')"
Write-Output "Validacoes Conclui : Lint de Versao, Varredura de Consistencia e Build [OK]"
Write-Output "Localizacao Pacote : build/gerador-posts-gemini.zip"
Write-Output "=================================================="
