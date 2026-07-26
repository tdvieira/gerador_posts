<#
.SYNOPSIS
    Script de preparação do Pipeline Oficial de Release.
.DESCRIPTION
    Este script é responsável exclusivamente por:
    1. Preparação da Release
    2. Sincronização automática do versionamento
    3. Atualização da documentação oficial
    4. Validações de consistência
    5. Coordenação da execução do scripts/build_release.ps1
    6. Encerramento do processo de preparação antes da etapa de publicação
#>
param (
    [Parameter(Mandatory=$true)]
    [string]$Version
)

# 1. Validar formato da versão informada (MAJOR.MINOR.PATCH)
if ($Version -notmatch "^[0-9]+\.[0-9]+\.[0-9]+$") {
    Write-Output "Erro: A versão '$Version' é inválida! O formato correto é MAJOR.MINOR.PATCH (ex: 2.0.1)."
    exit 1
}

# 2. Determinar os diretórios raiz do projeto dinamicamente
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir

# 3. Detectar a versão atual (lendo o cabeçalho do arquivo principal)
$main_file = Join-Path $source_dir "gerador-posts-gemini.php"
if (!(Test-Path $main_file)) {
    Write-Output "Erro: Arquivo de entrada principal do plugin nao encontrado em: $main_file"
    exit 1
}

$main_content = Get-Content $main_file -Raw
if ($main_content -match "\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)") {
    $old_version = $Matches[1]
} else {
    Write-Output "Erro: Nao foi possivel identificar a versao anterior no cabeçalho do plugin."
    exit 1
}

# Se a versão informada for igual à atual, pular atualização de arquivos
if ($Version -eq $old_version) {
    Write-Output "Aviso: A versão informada '$Version' já é a versão corrente do plugin."
} else {
    Write-Output "Iniciando preparação da Release: v$old_version -> v$Version"

    # Lista de arquivos para atualizar
    $files_to_update = @(
        "gerador-posts-gemini.php",
        "includes/Core/PluginBootstrap.php",
        "README.md",
        "CHANGELOG.md",
        "docs/releases/RELEASE_PROCESS.md"
    )

    $updated_files = @()

    foreach ($file_rel in $files_to_update) {
        $file_abs = Join-Path $source_dir ($file_rel.Replace("/", [System.IO.Path]::DirectorySeparatorChar))
        if (Test-Path $file_abs) {
            $content = Get-Content $file_abs -Raw
            
            # Tratar atualização específica para o CHANGELOG.md (inserir nova seção)
            if ($file_rel -eq "CHANGELOG.md") {
                if ($content -notmatch "## $Version") {
                    $today = Get-Date -Format "yyyy-MM-dd"
                    $target_section = "## $old_version"
                    $new_section = "## $Version - $today`r`n`r`n### Adicionado`r`n- Atualização e consolidação da Release v$Version.`r`n`r`n## $old_version"
                    $content = $content.Replace($target_section, $new_section)
                }
            } else {
                # Substituição simples da string da versão anterior pela nova versão
                $content = $content.Replace($old_version, $Version)
            }

            Set-Content $file_abs $content -NoNewline
            $updated_files += $file_rel
            Write-Output "Arquivo atualizado: $file_rel"
        }
    }

    # 4. Validar se não sobraram referências à versão anterior nos arquivos operacionais e manuais primários
    Write-Output "Executando varreduras de consistência pós-atualização..."
    
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
                $inconsistencies += "$file_rel (Contém referência antiga: $old_version)"
            }
        }
    }

    if ($inconsistencies.Count -gt 0) {
        Write-Output "Erro: Inconsistência de versão detectada! O processo de build foi abortado."
        Write-Output "Arquivos pendentes de correção manual:"
        foreach ($inc in $inconsistencies) {
            Write-Output " - $inc"
        }
        exit 1
    }

    Write-Output "Consistência de versionamento validada com sucesso! Nenhuma referência órfã encontrada."
}

# 5. Executar o script de build build_release.ps1 para gerar o ZIP de produção
$build_script = Join-Path $script_dir "build_release.ps1"
if (Test-Path $build_script) {
    Write-Output "Disparando o empacotamento oficial build_release.ps1..."
    & $build_script
} else {
    Write-Output "Erro: Script de build $build_script nao encontrado."
    exit 1
}

Write-Output "`n=================================================="
Write-Output "RESUMO DE PREPARAÇÃO DA RELEASE"
Write-Output "=================================================="
Write-Output "Versão Preparada: v$Version"
Write-Output "Arquivos Sincronizados: $($updated_files -join ', ')"
Write-Output "Validações Concluídas: Lint de Versão, Varredura de Consistência e Build"
Write-Output "Localização do Pacote: build/gerador-posts-gemini.zip"
Write-Output "=================================================="
