<#
.SYNOPSIS
    Script de publicação do Pipeline Oficial de Release.
.DESCRIPTION
    Este script é responsável exclusivamente por:
    1. Validar o repositório Git, branch main e integridade do ZIP de build.
    2. Identificar a versão ativa no plugin.
    3. Commitar as alterações de release, criar a tag semântica local e sincronizar com o origin.
    4. Criar a release correspondente no GitHub anexando o ZIP através do GitHub CLI (gh).
#>

$ErrorActionPreference = "Stop"

# 1. Configurar console do PowerShell para UTF-8 de forma nativa e silenciosa
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8
cmd /c chcp 65001 > $null

# 2. Obter o diretório raiz dinamicamente
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir

Write-Output "=================================================="
Write-Output "INICIANDO PUBLICACAO DA RELEASE"
Write-Output "=================================================="

# 3. Validar que o diretório é um repositório Git válido
if (!(git rev-parse --is-inside-work-tree 2>$null)) {
    Write-Output "Erro: O diretório atual não é um repositório Git válido."
    exit 1
}
Write-Output "[OK] Repositório Git ativo detectado."

# 4. Validar que o branch atual é main
$branch = (git rev-parse --abbrev-ref HEAD).Trim()
if ($branch -ne "main") {
    Write-Output "Erro: O branch atual é '$branch'. A publicação do Pipeline Oficial de Release só é permitida no branch 'main'."
    exit 1
}
Write-Output "[OK] Branch main ativa e selecionada."

# 5. Identificar a versão ativa a partir do cabeçalho do arquivo principal
$main_file = Join-Path $source_dir "gerador-posts-gemini.php"
if (!(Test-Path $main_file)) {
    Write-Output "Erro: Arquivo principal do plugin não encontrado em: $main_file"
    exit 1
}

$main_content = Get-Content $main_file -Raw
if ($main_content -match "\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)") {
    $Version = $Matches[1]
} else {
    Write-Output "Erro: Não foi possível ler a versão ativa no cabeçalho do plugin."
    exit 1
}
Write-Output "[OK] Versão do plugin identificada: v$Version"

# 6. Validar consistência de versão cruzada com o CHANGELOG.md
$changelog_file = Join-Path $source_dir "CHANGELOG.md"
if (!(Test-Path $changelog_file)) {
    Write-Output "Erro: Arquivo CHANGELOG.md não encontrado na raiz do projeto."
    exit 1
}
$changelog_content = Get-Content $changelog_file -Raw
if ($changelog_content -notmatch "## $Version") {
    Write-Output "Erro: A versão lida 'v$Version' não coincide com nenhuma seção no CHANGELOG.md."
    exit 1
}
Write-Output "[OK] Sincronização do CHANGELOG validada com sucesso."

# 7. Confirmar que o pacote ZIP existe e foi aprovado pela validação estrutural do build_release.ps1
$zip_path = Join-Path $source_dir "build\gerador-posts-gemini.zip"
if (!(Test-Path $zip_path)) {
    Write-Output "Erro: O pacote de distribuição build/gerador-posts-gemini.zip não existe."
    Write-Output "A validação estrutural obrigatória do build_release.ps1 deve aprovar o build antes do deploy."
    exit 1
}
Write-Output "[OK] Pacote ZIP localizado e pré-validado."

# 8. Validar a working tree (arquivos permitidos de release vs arquivos soltos de desenvolvimento)
$status = git status --porcelain
$allowed_files = @(
    "gerador-posts-gemini.php",
    "includes/Core/PluginBootstrap.php",
    "README.md",
    "CHANGELOG.md",
    "docs/releases/RELEASE_PROCESS.md",
    "docs/releases/release_pipeline_consolidation_report.md",
    "docs/releases/release_preparation_script_report.md",
    "docs/releases/release_publish_script_report.md",
    "docs/releases/wordpress_package_validation_automation_report.md",
    "docs/releases/release_publish_pipeline_hardening_report.md",
    "build/gerador-posts-gemini.zip",
    "build/.gitkeep",
    ".gitignore",
    ".agents/rules/project-governance.md",
    ".agents/rules/documentation.md",
    "scripts/prepare_release.ps1",
    "scripts/build_release.ps1",
    "scripts/publish_release.ps1"
)

$invalid_changes = @()
if ($status) {
    foreach ($line in $status) {
        $file_path = $line.Substring(3).Trim()
        $file_path_norm = $file_path.Replace("\", "/")
        
        $is_allowed = $false
        foreach ($allowed in $allowed_files) {
            if ($file_path_norm -eq $allowed) {
                $is_allowed = $true
                break
            }
        }
        if (!$is_allowed) {
            $invalid_changes += $file_path_norm
        }
    }
}

if ($invalid_changes.Count -gt 0) {
    Write-Output "Erro: A working tree contém alterações pendentes de desenvolvimento alheias à release:"
    foreach ($inv in $invalid_changes) {
        Write-Output " - $inv"
    }
    Write-Output "A publicação foi cancelada por motivos de segurança. Por favor, descarte ou faça commit manual das alterações."
    exit 1
}
Write-Output "[OK] Árvore de trabalho limpa e em conformidade."

# 9. Commitar modificações pendentes de preparação se houver
if ($status) {
    Write-Output "Fazendo commit das alterações preparatórias de release..."
    git add -A
    git commit -m "Release v$Version"
    if ($LASTEXITCODE -ne 0) {
        Write-Output "Erro ao criar o commit da release."
        exit 1
    }
    Write-Output "[OK] Commit da release efetuado com sucesso."
} else {
    Write-Output "[OK] Working tree totalmente limpa. Nenhuma alteração a commitar."
}

# 10. Verificar e criar tag Git local
$tag_name = "v$Version"
$tag_exists = (git tag -l $tag_name)
if ($tag_exists -and $tag_exists.Trim()) {
    Write-Output "[OK] A tag Git '$tag_name' já existe localmente."
} else {
    Write-Output "Criando a tag Git '$tag_name'..."
    git tag -a $tag_name -m "Release oficial $tag_name"
    if ($LASTEXITCODE -ne 0) {
        Write-Output "Erro ao criar a tag Git local."
        exit 1
    }
    Write-Output "[OK] Tag Git '$tag_name' criada com sucesso."
}

# 11. Push das alterações e tags para o repositório remoto
Write-Output "Sincronizando commits com a branch remota main..."
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Output "Erro ao enviar commits para o origin remoto."
    exit 1
}
Write-Output "[OK] Commits enviados para a branch remota main."

Write-Output "Sincronizando tags com o repositório remoto..."
git push origin --tags
if ($LASTEXITCODE -ne 0) {
    Write-Output "Erro ao enviar tags para o origin remoto."
    exit 1
}
Write-Output "[OK] Tags Git sincronizadas com o repositório remoto."

# 12. Verificar e publicar no GitHub usando o GitHub CLI
$gh_installed = $false
try {
    $gh_test = (gh --version 2>&1)
    if ($LASTEXITCODE -eq 0) {
        $gh_installed = $true
    }
} catch {}

if (!$gh_installed) {
    Write-Output "Erro: O utilitário GitHub CLI (gh) não está instalado ou disponível no PATH."
    Write-Output "Apenas a publicação remota da release depende do gh. O processo foi interrompido."
    exit 1
}

Write-Output "GitHub CLI detectado. Validando autenticacao..."
$auth_check = (gh auth status 2>&1 | Out-String)
if ($auth_check -match "Logged in to github.com as" -eq $false) {
    Write-Output "Aviso: GitHub CLI está instalado, mas não autenticado."
    Write-Output "Erro: Apenas a publicação remota da release depende do gh. Processo interrompido."
    exit 1
}
Write-Output "[OK] Autenticação com GitHub CLI validada."

# Verificar se a release remota já existe
$release_check = (gh release view $tag_name 2>&1 | Out-String)
if ($release_check -match "release v$Version" -or $release_check -match "title: v$Version") {
    Write-Output "[OK] A release remota no GitHub para a tag '$tag_name' já existe."
    $release_url = (gh release view $tag_name --json url -q .url 2>$null).Trim()
} else {
    Write-Output "Criando GitHub Release oficial para a tag '$tag_name'..."
    $release_url = (gh release create $tag_name $zip_path --title $tag_name --notes "Release oficial v$Version" 2>&1).Trim()
    if ($LASTEXITCODE -ne 0) {
        Write-Output "Erro ao criar a GitHub Release: $release_url"
        exit 1
    }
    Write-Output "[OK] GitHub Release criada com sucesso."
}

# Obter HASH do commit atual
$commit_hash = (git rev-parse HEAD).Trim()

Write-Output "`n=================================================="
Write-Output "RESUMO DE PUBLICACAO DA RELEASE"
Write-Output "=================================================="
Write-Output "Versao Publicada : v$Version"
Write-Output "Commit           : $commit_hash"
Write-Output "Tag              : $tag_name"
Write-Output "Push             : Aprovado [OK]"
Write-Output "GitHub Release   : Aprovada [OK]"
Write-Output "Upload do ZIP    : Concluido [OK]"
Write-Output "Status Final     : Publicado com Sucesso"
if ($release_url) {
    Write-Output "URL da Release   : $release_url"
}
Write-Output "=================================================="
