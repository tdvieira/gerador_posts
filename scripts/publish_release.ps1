<#
.SYNOPSIS
    Script de publicacao do Pipeline Oficial de Release.
.DESCRIPTION
    Este script e responsavel exclusivamente por:
    1. Validar o repositorio Git, branch main e integridade do ZIP de build.
    2. Identificar a versao ativa no plugin.
    3. Commitar as alteracoes de release, criar a tag semantica local e sincronizar com o origin.
    4. Criar a release correspondente no GitHub anexando o ZIP atraves do GitHub CLI (gh).
#>

$ErrorActionPreference = "Stop"

# Configurar console para compatibilidade ASCII nativa
[Console]::OutputEncoding = [System.Text.Encoding]::ASCII
$OutputEncoding = [System.Text.Encoding]::ASCII
cmd /c chcp 437 > $null

# Obter o diretorio raiz dinamicamente
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir

Write-Output "=================================================="
Write-Output "INICIANDO PUBLICACAO DA RELEASE"
Write-Output "=================================================="

# 2. Validar que o diretorio e um repositorio Git valido
if (!(git rev-parse --is-inside-work-tree 2>$null)) {
    Write-Output "[ERRO] O diretorio atual nao e um repositorio Git valido."
    exit 1
}
Write-Output "[OK] Repositorio Git ativo detectado."

# 3. Validar que o branch atual e main
$branch = (git rev-parse --abbrev-ref HEAD).Trim()
if ($branch -ne "main") {
    Write-Output "[ERRO] O branch atual e '$branch'. A publicacao do Pipeline Oficial de Release so e permitida no branch 'main'."
    exit 1
}
Write-Output "[OK] Branch main ativa e selecionada."

# 4. Identificar a versao ativa a partir do cabecalho do arquivo principal
$main_file = Join-Path $source_dir "gerador-posts-gemini.php"
if (!(Test-Path $main_file)) {
    Write-Output "[ERRO] Arquivo principal do plugin nao encontrado em: $main_file"
    exit 1
}

$main_content = Get-Content $main_file -Raw
if ($main_content -match "\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)") {
    $Version = $Matches[1]
} else {
    Write-Output "[ERRO] Nao foi possivel ler a versao ativa no cabecalho do plugin."
    exit 1
}
Write-Output "[OK] Versao do plugin identificada: v$Version"

# 5. Validar consistencia de versao cruzada com o CHANGELOG.md
$changelog_file = Join-Path $source_dir "CHANGELOG.md"
if (!(Test-Path $changelog_file)) {
    Write-Output "[ERRO] Arquivo CHANGELOG.md nao encontrado na raiz do projeto."
    exit 1
}
$changelog_content = Get-Content $changelog_file -Raw
if ($changelog_content -notmatch "## $Version") {
    Write-Output "[ERRO] A versao lida 'v$Version' nao coincide com nenhuma secao no CHANGELOG.md."
    exit 1
}
Write-Output "[OK] Sincronizacao do CHANGELOG validada com sucesso."

# 6. Confirmar que o pacote ZIP existe e foi aprovado pela validacao estrutural do build_release.ps1
$zip_path = Join-Path $source_dir "build\gerador-posts-gemini.zip"
if (!(Test-Path $zip_path)) {
    Write-Output "[ERRO] O pacote de distribuicao build/gerador-posts-gemini.zip nao existe."
    Write-Output "A validacao estrutural obrigatoria do build_release.ps1 deve aprovar o build antes do deploy."
    exit 1
}
Write-Output "[OK] Pacote ZIP localizado e pre-validado."

# 7. Validar a working tree (arquivos permitidos de release vs arquivos soltos de desenvolvimento)
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
    "docs/releases/release_pipeline_console_standardization_v2_report.md",
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
    Write-Output "[ERRO] A working tree contem alteracoes pendentes de desenvolvimento alheias a release:"
    foreach ($inv in $invalid_changes) {
        Write-Output " - $inv"
    }
    Write-Output "A publicacao foi cancelada por motivos de seguranca. Por favor, descarte ou faca commit manual das alteracoes."
    exit 1
}
Write-Output "[OK] Arvore de trabalho limpa e em conformidade."

# 8. Commitar modificacoes pendentes de preparacao se houver
if ($status) {
    Write-Output "[INFO] Fazendo commit das alteracoes preparatorias de release..."
    git add -A
    git commit -m "Release v$Version"
    if ($LASTEXITCODE -ne 0) {
        Write-Output "[ERRO] Erro ao criar o commit da release."
        exit 1
    }
    Write-Output "[OK] Commit da release efetuado com sucesso."
} else {
    Write-Output "[OK] Working tree totalmente limpa. Nenhuma alteracao a commitar."
}

# 9. Verificar e criar tag Git local
$tag_name = "v$Version"
$tag_exists = (git tag -l $tag_name)
if ($tag_exists -and $tag_exists.Trim()) {
    Write-Output "[OK] A tag Git '$tag_name' ja existe localmente."
} else {
    Write-Output "[INFO] Criando a tag Git '$tag_name'..."
    git tag -a $tag_name -m "Release oficial $tag_name"
    if ($LASTEXITCODE -ne 0) {
        Write-Output "[ERRO] Erro ao criar a tag Git local."
        exit 1
    }
    Write-Output "[OK] Tag Git '$tag_name' criada com sucesso."
}

# 10. Push das alteracoes e tags para o repositorio remoto
Write-Output "[INFO] Sincronizando commits com a branch remota main..."
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Erro ao enviar commits para o origin remoto."
    exit 1
}
Write-Output "[OK] Commits enviados para a branch remota main."

Write-Output "[INFO] Sincronizando tags com o repositorio remoto..."
git push origin --tags
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Erro ao enviar tags para o origin remoto."
    exit 1
}
Write-Output "[OK] Tags Git sincronizadas com o repositorio remoto."

# 11. Verificar e publicar no GitHub usando o GitHub CLI
$gh_installed = $false
try {
    $gh_test = (gh --version 2>&1)
    if ($LASTEXITCODE -eq 0) {
        $gh_installed = $true
    }
} catch {}

if (!$gh_installed) {
    Write-Output "[ERRO] O utilitario GitHub CLI (gh) nao esta instalado ou disponivel no PATH."
    Write-Output "Apenas a publicacao remota da release depende do gh. O processo foi interrompido."
    exit 1
}

Write-Output "[INFO] GitHub CLI detectado. Validando autenticacao..."
$auth_check = (gh auth status 2>&1 | Out-String)
if ($auth_check -match "Logged in to github.com as" -eq $false) {
    Write-Output "[WARN] GitHub CLI esta instalado, mas nao autenticado."
    Write-Output "[ERRO] Apenas a publicacao remota da release depende do gh. Processo interrompido."
    exit 1
}
Write-Output "[OK] Autenticacao com GitHub CLI validada."

# Verificar se a release remota ja existe
$release_check = (gh release view $tag_name 2>&1 | Out-String)
if ($release_check -match "release v$Version" -or $release_check -match "title: v$Version") {
    Write-Output "[OK] A release remota no GitHub para a tag '$tag_name' ja existe."
    $release_url = (gh release view $tag_name --json url -q .url 2>$null).Trim()
} else {
    Write-Output "[INFO] Criando GitHub Release oficial para a tag '$tag_name'..."
    $release_url = (gh release create $tag_name $zip_path --title $tag_name --notes "Release oficial v$Version" 2>&1).Trim()
    if ($LASTEXITCODE -ne 0) {
        Write-Output "[ERRO] Erro ao criar a GitHub Release: $release_url"
        exit 1
    }
    Write-Output "[OK] GitHub Release criada com sucesso."
}

# Obter HASH do commit atual
$commit_hash = (git rev-parse HEAD).Trim()
$pub_date = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

Write-Output "`n=================================================="
Write-Output "RESUMO FINAL DA RELEASE"
Write-Output "=================================================="
Write-Output "Versao Publicada   : v$Version"
Write-Output "Branch             : $branch"
Write-Output "Commit             : $commit_hash"
Write-Output "Tag Git            : $tag_name"
Write-Output "Caminho ZIP Gerado : build/gerador-posts-gemini.zip"
Write-Output "Status Validacao   : APROVADA [OK]"
Write-Output "Status do Push     : CONCLUIDO [OK]"
Write-Output "Status da GH Rel   : APROVADA [OK]"
Write-Output "Data e Hora Pub    : $pub_date"
Write-Output "Status Final       : PUBLICADO COM SUCESSO [OK]"
Write-Output "=================================================="
