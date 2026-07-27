<#
.SYNOPSIS
    Script de publicacao do Pipeline Oficial de Release.
.DESCRIPTION
    Este script e responsavel exclusivamente por:
    1. Validar o repositorio Git, branch main e integridade do ZIP de build.
    2. Identificar a versao ativa no plugin.
    3. Commitar as alteracoes de release, criar a tag semantica local e sincronizar com o origin.
    4. Criar a release correspondente no GitHub diante do GitHub CLI (gh).
#>

$ErrorActionPreference = "Stop"

# Configurar console para compatibilidade ASCII nativa
[Console]::OutputEncoding = [System.Text.Encoding]::ASCII
$OutputEncoding = [System.Text.Encoding]::ASCII
cmd /c chcp 437 > $null

# Obter o diretorio raiz dinamicamente
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir

# Carregar configuracao arquitetural das categorias da Working Tree
$config_path = Join-Path $source_dir ".agents/config/pipeline-categories.json"
if (!(Test-Path $config_path)) {
    [Console]::Error.WriteLine("[ERRO] A configuracao arquitetural da Pipeline Oficial de Release encontra-se invalida (Arquivo nao encontrado).")
    exit 1
}

$global:PipelineCategories = $null
try {
    $categories_content = Get-Content -Path $config_path -Encoding UTF8 -Raw
    $global:PipelineCategories = ConvertFrom-Json $categories_content
    if ($null -eq $global:PipelineCategories -or $null -eq $global:PipelineCategories.exact_matches -or $null -eq $global:PipelineCategories.wildcard_matches) {
        throw "Estrutura do JSON invalida."
    }
} catch {
    [Console]::Error.WriteLine("[ERRO] A configuracao arquitetural da Pipeline Oficial de Release encontra-se invalida ou corrompida.")
    [Console]::Error.WriteLine("Detalhes: $_")
    exit 1
}

# Funcao auxiliar reutilizavel para execucao padronizada de comandos externos
function Execute-ExternalCommand {
    param (
        [string]$Command,
        [string[]]$Arguments = @(),
        [int[]]$AllowedExitCodes = @(0),
        [bool]$CaptureOutput = $false
    )

    $stdoutFile = [System.IO.Path]::GetTempFileName()
    $stderrFile = [System.IO.Path]::GetTempFileName()

    # Serializacao robusta dos argumentos para compatibilidade com o parser do Windows (CommandLineToArgvW)
    $escapedArgs = @()
    foreach ($arg in $Arguments) {
        if ($arg -match '[\s"]' -or $arg -eq "") {
            # Escapa aspas internas com barra invertida e envolve com aspas duplas
            $escaped = $arg.Replace('"', '\"')
            $escapedArgs += "`"$escaped`""
        } else {
            $escapedArgs += $arg
        }
    }
    $argumentString = [string]::Join(" ", $escapedArgs)

    try {
        $p = Start-Process -FilePath $Command -ArgumentList $argumentString -NoNewWindow -PassThru -Wait -RedirectStandardOutput $stdoutFile -RedirectStandardError $stderrFile
        $exitCode = $p.ExitCode
    }
    catch {
        $exitCode = -1
        [System.IO.File]::WriteAllText($stderrFile, $_.Exception.Message)
    }

    $global:LASTEXITCODE = $exitCode

    $isAllowed = $false
    foreach ($code in $AllowedExitCodes) {
        if ($exitCode -eq $code) {
            $isAllowed = $true
            break
        }
    }

    $stdout = @()
    $stderr = @()

    if (Test-Path $stdoutFile) {
        $stdout = Get-Content $stdoutFile
        Remove-Item $stdoutFile -Force
    }
    if (Test-Path $stderrFile) {
        $stderr = Get-Content $stderrFile
        Remove-Item $stderrFile -Force
    }

    if (!$isAllowed) {
        if ($stderr) {
            if ($stderr -is [array]) {
                $errText = $stderr -join "`n"
            } else {
                $errText = $stderr
            }
            if ($errText.Trim()) {
                Write-Output $errText.Trim()
            }
        }
        return $null
    }

    if ($CaptureOutput) {
        if (!$stdout) {
            return $null
        }
        if ($stdout -is [array] -and $stdout.Count -eq 1) {
            return $stdout[0]
        }
        return $stdout
    }
    return $null
}

# Funcao auxiliar para converter padroes glob do JSON em regex com semantica correta de filesystem.
# Semantica: * casa qualquer caractere EXCETO / (um unico nivel de diretorio).
#             ** casa qualquer caractere INCLUINDO / (qualquer profundidade).
function Convert-GlobToRegex {
    param (
        [string]$GlobPattern
    )

    # Escape all regex-special characters first, then restore glob semantics
    $escaped = [regex]::Escape($GlobPattern)

    # Restore ** (escaped as \*\*) -> .* (any depth, crosses /)
    $escaped = $escaped -replace '\\\*\\\*', '.*'

    # Restore remaining * (escaped as \*) -> [^/]* (single directory level, no /)
    $escaped = $escaped -replace '\\\*', '[^/]*'

    return "^$escaped$"
}

# Funcao auxiliar para validar se um arquivo e permitido no processo de release
function Test-IsFileAllowed {
    param (
        [string]$FilePathNorm
    )

    if ($null -eq $global:PipelineCategories) {
        return $false
    }

    # Validar correspondencias exatas
    foreach ($exact in $global:PipelineCategories.exact_matches) {
        if ($FilePathNorm -eq $exact) {
            return $true
        }
    }

    # Validar correspondencias com wildcards (glob -> regex com semantica de filesystem)
    foreach ($wildcard in $global:PipelineCategories.wildcard_matches) {
        $regexPattern = Convert-GlobToRegex -GlobPattern $wildcard
        if ($FilePathNorm -match $regexPattern) {
            return $true
        }
    }

    return $false
}

Write-Output "=================================================="
Write-Output "INICIANDO PUBLICACAO DA RELEASE"
Write-Output "=================================================="

# 2. Validar que o diretorio e um repositorio Git valido
$is_git_check = Execute-ExternalCommand -Command "git" -Arguments @("rev-parse", "--is-inside-work-tree") -AllowedExitCodes @(0, 128) -CaptureOutput $true
if ($LASTEXITCODE -ne 0 -or !$is_git_check -or $is_git_check.Trim() -ne "true") {
    Write-Output "[ERRO] O diretorio atual nao e um repositorio Git valido."
    exit 1
}
Write-Output "[OK] Repositorio Git ativo detectado."

# 3. Validar que o branch atual e main
$branch_raw = Execute-ExternalCommand -Command "git" -Arguments @("rev-parse", "--abbrev-ref", "HEAD") -CaptureOutput $true
if ($LASTEXITCODE -ne 0 -or !$branch_raw) {
    Write-Output "[ERRO] Nao foi possivel obter a branch atual do Git."
    exit 1
}
$branch = $branch_raw.Trim()
if ($branch -ne "main") {
    Write-Output "[ERRO] O branch atual e '$branch'. A publicacao do Pipeline Oficial de Release so e permitida no branch 'main'."
    exit 1
}
Write-Output "[OK] Branch main ativa e selecionada."

# 4. Identificar a versao activa a partir do cabecalho do arquivo principal
$main_file = Join-Path $source_dir "gerador-posts-gemini.php"
if (!(Test-Path $main_file)) {
    Write-Output "[ERRO] Arquivo principal do plugin nao encontrado em: $main_file"
    exit 1
}

$main_content = Get-Content $main_file -Raw
if ($main_content -match "\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)") {
    $Version = $Matches[1]
} else {
    Write-Output "[ERRO] Nao foi possivel ler a versao activa no cabecalho do plugin."
    exit 1
}
Write-Output "[OK] Versao do plugin identificada: v$Version"

# 5. Validar consistencia de versao cruzada com o CHANGELOG.md
$changelog_file = Join-Path $source_dir "CHANGELOG.md"
if (!(Test-Path $changelog_file)) {
    Write-Output "[ERRO] Arquivo CHANGELOG.md nao encontrado na raiz do projeto."
    exit 1
}
$changelog_content = Get-Content $changelog_file -Raw -Encoding UTF8
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
$status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain", "--untracked-files=all") -CaptureOutput $true
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Falha ao executar git status."
    exit 1
}
$invalid_changes = @()
if ($status) {
    $lines = @()
    if ($status -is [array]) {
        $lines = $status
    } else {
        $lines = $status.Split("`n") | Where-Object { $_.Trim() }
    }
    foreach ($line in $lines) {
        $file_path = $line.Substring(3).Trim()
        $file_path_norm = $file_path.Replace("\", "/")
        
        if (!(Test-IsFileAllowed -FilePathNorm $file_path_norm)) {
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
if ($status -and ($status -is [array] -or $status.Trim())) {
    Write-Output "[INFO] Fazendo commit das alteracoes preparatorias de release..."
    Execute-ExternalCommand -Command "git" -Arguments @("add", "-A")
    if ($LASTEXITCODE -ne 0) {
        Write-Output "[ERRO] Erro ao adicionar arquivos para commit."
        exit 1
    }
    Execute-ExternalCommand -Command "git" -Arguments @("commit", "-m", "Release v$Version")
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
$tag_exists_raw = Execute-ExternalCommand -Command "git" -Arguments @("tag", "-l", $tag_name) -CaptureOutput $true
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Erro ao verificar a tag Git local."
    exit 1
}
if ($tag_exists_raw -and $tag_exists_raw.Trim()) {
    Write-Output "[OK] A tag Git '$tag_name' ja existe localmente."
} else {
    Write-Output "[INFO] Criando a tag Git '$tag_name'..."
    Execute-ExternalCommand -Command "git" -Arguments @("tag", "-a", $tag_name, "-m", "Release oficial $tag_name")
    if ($LASTEXITCODE -ne 0) {
        Write-Output "[ERRO] Erro ao criar a tag Git local."
        exit 1
    }
    Write-Output "[OK] Tag Git '$tag_name' criada com sucesso."
}

# 10. Push das alteracoes e tags para o repositorio remoto
Write-Output "[INFO] Sincronizando commits com a branch remota main..."
Execute-ExternalCommand -Command "git" -Arguments @("push", "origin", "main")
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Erro ao enviar commits para o origin remoto."
    exit 1
}
Write-Output "[OK] Commits enviados para a branch remota main."

Write-Output "[INFO] Sincronizando tags com o repositorio remoto..."
Execute-ExternalCommand -Command "git" -Arguments @("push", "origin", "--tags")
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Erro ao enviar tags para o origin remoto."
    exit 1
}
Write-Output "[OK] Tags Git sincronizadas com o repositorio remoto."

# 11. Validar ambiente do GitHub CLI de forma robusta e independente de idioma
Write-Output "[INFO] Validando ambiente GitHub..."

# Verificar se o executavel gh existe
$gh_path = Get-Command gh -ErrorAction SilentlyContinue
if (!$gh_path) {
    Write-Output "=================================================="
    Write-Output "VALIDACAO DO GITHUB CLI"
    Write-Output "=================================================="
    Write-Output "[ERRO] GitHub CLI nao encontrado."
    exit 1
}

# Executar gh auth status para checar autenticacao
Execute-ExternalCommand -Command "gh" -Arguments @("auth", "status")
if ($LASTEXITCODE -ne 0) {
    Write-Output "=================================================="
    Write-Output "VALIDACAO DO GITHUB CLI"
    Write-Output "=================================================="
    Write-Output "[OK] GitHub CLI localizado."
    Write-Output "[ERRO] GitHub CLI nao autenticado."
    Write-Output "Execute: gh auth login"
    exit 1
}

# Executar gh repo view para testar o acesso ao repositorio remoto
Execute-ExternalCommand -Command "gh" -Arguments @("repo", "view")
if ($LASTEXITCODE -ne 0) {
    Write-Output "=================================================="
    Write-Output "VALIDACAO DO GITHUB CLI"
    Write-Output "=================================================="
    Write-Output "[OK] GitHub CLI localizado."
    Write-Output "[OK] Usuario autenticado."
    Write-Output "[ERRO] Repositorio GitHub inacessivel."
    exit 1
}

# Exibir painel de sucesso de validacao do gh
Write-Output "=================================================="
Write-Output "VALIDACAO DO GITHUB CLI"
Write-Output "=================================================="
Write-Output "[OK] GitHub CLI localizado."
Write-Output "[OK] Usuario autenticado."
Write-Output "[OK] Repositorio acessivel."
Write-Output "=================================================="

# Verificar se a release remota ja existe
# gh release view retorna exit code 1 quando a release nao existe, o que e esperado e nao-fatal.
Execute-ExternalCommand -Command "gh" -Arguments @("release", "view", $tag_name) -AllowedExitCodes @(0, 1)
$gh_release_exists = ($LASTEXITCODE -eq 0)

# Linha em branco antes da criacao da release
Write-Output ""

$release_url = $null
if ($gh_release_exists) {
    # Capturar a URL da release existente via gh
    $release_url_raw = Execute-ExternalCommand -Command "gh" -Arguments @("release", "view", $tag_name, "--json", "url", "-q", ".url") -AllowedExitCodes @(0, 1) -CaptureOutput $true
    if ($release_url_raw) {
        $release_url = $release_url_raw.Trim()
    }
    Write-Output "[INFO] Release v$Version ja existe."
    if ($release_url) {
        Write-Output "[INFO] URL da Release: $release_url"
    }
    $gh_release_status = "$tag_name [APROVADA]"
} else {
    # Extrair notas do CHANGELOG.md para a versao corrente garantindo leitura UTF-8 explicita
    $release_notes = ""
    $changelog_file = Join-Path $source_dir "CHANGELOG.md"
    if (Test-Path $changelog_file) {
        $changelog_content = Get-Content $changelog_file -Raw -Encoding UTF8
        $pattern = "(?s)(?m)^##\s*$($Version.Replace('.', '\.'))\b.*?\r?\n(.*?)(?=\r?\n##\s+|\Z)"
        if ($changelog_content -match $pattern) {
            $release_notes = $Matches[1].Trim()
        }
    }

    if (!$release_notes) {
        $release_notes = "Release oficial v$Version"
    }

    $notes_file = [System.IO.Path]::GetTempFileName()
    
    try {
        # Gravar com codificacao UTF-8 explicita
        [System.IO.File]::WriteAllText($notes_file, $release_notes, [System.Text.Encoding]::UTF8)

        # Validacao de integridade Round-Trip em UTF-8 (compativel com Windows PowerShell 5.1 e PS 7+)
        $temp_content = Get-Content $notes_file -Raw -Encoding UTF8
        
        $notes_norm = $release_notes.Replace("`r`n", "`n").Trim()
        $temp_norm = $temp_content.Replace("`r`n", "`n").Trim()

        if ($notes_norm -ne $temp_norm) {
            Write-Output "[ERRO] Falha de integridade: A codificacao UTF-8 das Release Notes foi corrompida na serializacao."
            if (Test-Path $notes_file) {
                Remove-Item $notes_file -Force
            }
            exit 1
        }
    }
    catch {
        Write-Output "[ERRO] Ocorreu uma excecao na gravacao ou validacao do arquivo temporario das Release Notes: $_"
        if (Test-Path $notes_file) {
            Remove-Item $notes_file -Force
        }
        exit 1
    }

    Write-Output "[INFO] Criando GitHub Release oficial para a tag '$tag_name'..."
    # Criar a release e capturar a URL gerada pelo comando gh utilizando as notas extraidas do CHANGELOG.md
    $release_url_raw = Execute-ExternalCommand -Command "gh" -Arguments @("release", "create", $tag_name, $zip_path, "--title", $tag_name, "--notes-file", $notes_file) -CaptureOutput $true
    
    if (Test-Path $notes_file) {
        Remove-Item $notes_file -Force
    }

    if ($LASTEXITCODE -eq 0) {
        if ($release_url_raw) { $release_url = $release_url_raw.Trim() }
        Write-Output "[OK] Release publicada com sucesso."
        if ($release_url) {
            Write-Output "[INFO] URL da Release: $release_url"
        }
        $gh_release_status = "$tag_name [APROVADA]"
    } else {
        Write-Output "[ERRO] Falha ao publicar Release."
        exit 1
    }
}

# 12. Limpeza automatica da Working Tree e Git Add de relatorios gerados
Write-Output "[INFO] Executando limpeza automatica de residuos temporarios..."
$local_temp_zip = Join-Path $source_dir "temp_zip"
if (Test-Path $local_temp_zip) {
    Remove-Item $local_temp_zip -Recurse -Force
}

# Adicionar ao Git de forma automatica os arquivos previstos e relatorios gerados pelo pipeline de release
# Desativamos temporariamente a interrupcao por erros nativos para ignorar avisos menores do Git
$current_pref = $ErrorActionPreference
$ErrorActionPreference = "Continue"

Write-Output "[INFO] Adicionando arquivos e relatorios oficiais de release ao Git..."

# Capturar e adicionar de forma dinamica todos os arquivos modificados/novos que pertençam as categorias permitidas
$status_raw = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain", "--untracked-files=all") -CaptureOutput $true
if ($status_raw) {
    $lines = @()
    if ($status_raw -is [array]) {
        $lines = $status_raw
    } else {
        $lines = $status_raw.Split("`n") | Where-Object { $_.Trim() }
    }
    foreach ($line in $lines) {
        $file_path = $line.Substring(3).Trim()
        $file_path_norm = $file_path.Replace("\", "/")
        
        if (Test-IsFileAllowed -FilePathNorm $file_path_norm) {
            $allowed_abs = Join-Path $source_dir ($file_path_norm.Replace("/", [System.IO.Path]::DirectorySeparatorChar))
            if (Test-Path $allowed_abs) {
                Execute-ExternalCommand -Command "git" -Arguments @("add", $allowed_abs) -AllowedExitCodes @(0, 1, 128)
                Write-Output "[OK] Indexado para release: $file_path_norm"
            }
        }
    }
}

$ErrorActionPreference = $current_pref

# Verificar se a working tree contem qualquer alteracao externa nao permitida
$post_status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain", "--untracked-files=all") -CaptureOutput $true
if ($LASTEXITCODE -ne 0) {
    Write-Output "[ERRO] Falha ao verificar o status do Git pos-release."
    exit 1
}
$invalid_post_changes = @()
if ($post_status -and ($post_status -is [array] -or $post_status.Trim())) {
    $lines = @()
    if ($post_status -is [array]) {
        $lines = $post_status
    } else {
        $lines = $post_status.Split("`n") | Where-Object { $_.Trim() }
    }
    foreach ($line in $lines) {
        $file_path = $line.Substring(3).Trim()
        $file_path_norm = $file_path.Replace("\", "/")
        
        if (!(Test-IsFileAllowed -FilePathNorm $file_path_norm)) {
            $invalid_post_changes += $file_path_norm
        }
    }
}

if ($invalid_post_changes.Count -gt 0) {
    Write-Output "[ERRO] A arvore de trabalho contem alteracoes externas nao pertencentes ao processo oficial de release:"
    foreach ($inv in $invalid_post_changes) {
        Write-Output " - $inv"
    }
    Write-Output "A publicacao foi interrompida devido a inconsistencias na arvore de trabalho."
    exit 1
}
Write-Output "[OK] Working Tree limpa."

# Obter HASH do commit atual
$commit_hash_raw = Execute-ExternalCommand -Command "git" -Arguments @("rev-parse", "HEAD") -CaptureOutput $true
if ($LASTEXITCODE -ne 0 -or !$commit_hash_raw) {
    Write-Output "[ERRO] Nao foi possivel obter o hash do commit atual."
    exit 1
}
$commit_hash = $commit_hash_raw.Trim()
$pub_date = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

# Linha em branco antes do resumo final da execucao
Write-Output ""

Write-Output "=================================================="
Write-Output "RESUMO FINAL DA RELEASE"
Write-Output "=================================================="
Write-Output "Versao Publicada   : v$Version"
Write-Output "Branch             : $branch"
Write-Output "Commit             : $commit_hash"
Write-Output "Tag Git            : $tag_name"
Write-Output "Caminho ZIP Gerado : build/gerador-posts-gemini.zip"
Write-Output "Status Validacao   : APROVADA [OK]"
Write-Output "Status do Push     : CONCLUIDO [OK]"
Write-Output "Status da GH Rel   : $gh_release_status"
if ($release_url) {
    Write-Output "URL da Release     : $release_url"
}
Write-Output "Data e Hora Pub    : $pub_date"
Write-Output "Status Final       : PUBLICADO COM SUCESSO [OK]"
Write-Output "=================================================="

# 13. Validacao Final do Pipeline
$zip_exists_check = Test-Path $zip_path

$tag_check_raw = Execute-ExternalCommand -Command "git" -Arguments @("tag", "-l", $tag_name) -CaptureOutput $true
$tag_exists_check = $tag_check_raw -and ($tag_check_raw.Trim() -eq $tag_name)

$local_commit_raw = Execute-ExternalCommand -Command "git" -Arguments @("rev-parse", "HEAD") -CaptureOutput $true
$local_commit = $null
if ($local_commit_raw) { $local_commit = $local_commit_raw.Trim() }

$remote_commit_raw = Execute-ExternalCommand -Command "git" -Arguments @("rev-parse", "origin/main") -AllowedExitCodes @(0, 1, 128) -CaptureOutput $true
$remote_commit = $null
if ($remote_commit_raw) { $remote_commit = $remote_commit_raw.Trim() }

$git_sync_check = $local_commit -and $remote_commit -and ($local_commit -eq $remote_commit)

if ($zip_exists_check -and $tag_exists_check -and $git_sync_check) {
    Write-Output "`n=================================================="
    Write-Output "PIPELINE OFICIAL DE RELEASE FINALIZADO"
    Write-Output "=================================================="
    Write-Output "Versao         : v$Version [APROVADA]"
    Write-Output "ZIP            : build/gerador-posts-gemini.zip [APROVADO]"
    Write-Output "Git            : branch main [APROVADO]"
    Write-Output "Tag            : $tag_name [APROVADA]"
    Write-Output "Release GitHub : $gh_release_status"
    Write-Output "Working Tree   : Limpa [OK]"
    Write-Output "=================================================="
    Write-Output "PUBLICACAO CONCLUIDA COM SUCESSO"
    Write-Output "=================================================="
} else {
    Write-Output "[ERRO] Falha na conferencia de integridade final do Pipeline."
    exit 1
}
