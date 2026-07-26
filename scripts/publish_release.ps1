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

# 1. Obter o diretório raiz dinamicamente
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir

# 2. Validar que o diretório é um repositório Git válido
if (!(git rev-parse --is-inside-work-tree 2>$null)) {
    Write-Output "Erro: O diretório atual não é um repositório Git válido."
    exit 1
}

# 3. Validar que o branch atual é main
$branch = (git rev-parse --abbrev-ref HEAD).Trim()
if ($branch -ne "main") {
    Write-Output "Erro: O branch atual é '$branch'. A publicação do Pipeline Oficial de Release só é permitida no branch 'main'."
    exit 1
}

# 4. Validar que o arquivo build/gerador-posts-gemini.zip existe
$zip_path = Join-Path $source_dir "build\gerador-posts-gemini.zip"
if (!(Test-Path $zip_path)) {
    Write-Output "Erro: O pacote de distribuição build/gerador-posts-gemini.zip nao foi gerado. Execute scripts/prepare_release.ps1 primeiro."
    exit 1
}

# 5. Identificar a versão ativa a partir do cabeçalho do arquivo principal
$main_file = Join-Path $source_dir "gerador-posts-gemini.php"
if (!(Test-Path $main_file)) {
    Write-Output "Erro: Arquivo principal do plugin nao encontrado em: $main_file"
    exit 1
}

$main_content = Get-Content $main_file -Raw
if ($main_content -match "\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)") {
    $Version = $Matches[1]
} else {
    Write-Output "Erro: Nao foi possivel ler a versao ativa no cabeçalho do plugin."
    exit 1
}

# 6. Validar a working tree (arquivos permitidos de release vs arquivos soltos de desenvolvimento)
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
    Write-Output "Por favor, comite ou descarte essas alterações antes de publicar."
    exit 1
}

# 7. Commitar modificações pendentes de preparação se houver
if ($status) {
    Write-Output "Fazendo commit das alterações preparatórias de release..."
    git add -A
    git commit -m "Release v$Version"
    if ($LASTEXITCODE -ne 0) {
        Write-Output "Erro ao criar o commit da release."
        exit 1
    }
} else {
    Write-Output "Working tree limpa. Nenhuma alteração pendente a commitar."
}

# 8. Verificar e criar tag Git local
$tag_name = "v$Version"
$tag_exists = (git tag -l $tag_name).Trim()
if ($tag_exists) {
    Write-Output "Aviso: A tag Git '$tag_name' já existe localmente."
} else {
    Write-Output "Criando a tag Git '$tag_name'..."
    git tag -a $tag_name -m "Release oficial $tag_name"
    if ($LASTEXITCODE -ne 0) {
        Write-Output "Erro ao criar a tag Git local."
        exit 1
    }
}

# 9. Push das alterações e tags para o repositório remoto
Write-Output "Sincronizando commits com a branch remota main..."
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Output "Erro ao enviar commits para o origin remoto."
    exit 1
}

Write-Output "Sincronizando tags com o repositório remoto..."
git push origin --tags
if ($LASTEXITCODE -ne 0) {
    Write-Output "Erro ao enviar tags para o origin remoto."
    exit 1
}

# 10. Verificar e publicar no GitHub usando o GitHub CLI
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

# Verificar se a release remota já existe
$release_check = (gh release view $tag_name 2>&1 | Out-String)
if ($release_check -match "release v$Version" -or $release_check -match "title: v$Version") {
    Write-Output "Aviso: A release remota no GitHub para a tag '$tag_name' já existe."
    $release_url = (gh release view $tag_name --json url -q .url 2>$null).Trim()
} else {
    Write-Output "Criando GitHub Release oficial para a tag '$tag_name'..."
    $release_url = (gh release create $tag_name $zip_path --title $tag_name --notes "Release oficial v$Version" 2>&1).Trim()
    if ($LASTEXITCODE -ne 0) {
        Write-Output "Erro ao criar a GitHub Release: $release_url"
        exit 1
    }
}

# Obter HASH do commit atual
$commit_hash = (git rev-parse HEAD).Trim()

Write-Output "`n=================================================="
Write-Output "RESUMO DE PUBLICACAO DA RELEASE"
Write-Output "=================================================="
Write-Output "Versao Publicada: v$Version"
Write-Output "Commit Hash: $commit_hash"
Write-Output "Tag Criada: $tag_name"
Write-Output "ZIP Enviado: build/gerador-posts-gemini.zip"
if ($release_url) {
    Write-Output "URL da Release: $release_url"
}
Write-Output "=================================================="
