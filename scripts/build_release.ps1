$ErrorActionPreference = "Stop"

# Determinar os diretorios raiz dinamicamente para garantir a portabilidade do build
$script_dir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source_dir = Split-Path -Parent $script_dir
$temp_dir = Join-Path $env:TEMP "gerador-posts-gemini-temp"

# Limpar diretorio temporario anterior
if (Test-Path $temp_dir) {
    Remove-Item $temp_dir -Recurse -Force
}
New-Item -ItemType Directory -Path $temp_dir | Out-Null

# Criar pasta do plugin dentro do temp para que ao extrair o zip ele crie a pasta do plugin
$plugin_dir = Join-Path $temp_dir "gerador-posts-gemini"
New-Item -ItemType Directory -Path $plugin_dir | Out-Null

# Copiar arquivos raízes necessários
Copy-Item (Join-Path $source_dir "gerador-posts-gemini.php") $plugin_dir
Copy-Item (Join-Path $source_dir "admin-ui.php") $plugin_dir
Copy-Item (Join-Path $source_dir "LICENSE") $plugin_dir
Copy-Item (Join-Path $source_dir "README.md") $plugin_dir
Copy-Item (Join-Path $source_dir "CHANGELOG.md") $plugin_dir
Copy-Item (Join-Path $source_dir "SECURITY.md") $plugin_dir

# Copiar diretórios necessários
Copy-Item (Join-Path $source_dir "assets") $plugin_dir -Recurse
Copy-Item (Join-Path $source_dir "includes") $plugin_dir -Recurse
Copy-Item (Join-Path $source_dir "vendor") $plugin_dir -Recurse

# Remover arquivos de desenvolvimento ou desnecessários adicionais dentro das subpastas se houver
Get-ChildItem -Path $plugin_dir -Filter ".gitkeep" -Recurse | Remove-Item -Force

# Garantir a existência do diretório build
$build_dir = Join-Path $source_dir "build"
if (!(Test-Path $build_dir)) {
    New-Item -ItemType Directory -Path $build_dir | Out-Null
}

$zip_dest = Join-Path $build_dir "gerador-posts-gemini.zip"

# Forçar coleta de handles pendentes do .NET na sessão antes de remover o ZIP
[System.GC]::Collect()
[System.GC]::WaitForPendingFinalizers()

if (Test-Path $zip_dest) {
    try {
        Remove-Item $zip_dest -Force
    } catch {
        [System.GC]::Collect()
        [System.GC]::WaitForPendingFinalizers()
        try {
            Remove-Item $zip_dest -Force
        } catch {
            # Caso continue travado por outra sessão, tenta gerar com nome alternativo e substituir
            Write-Output "Aviso: ZIP principal travado. Tentando liberação alternativa..."
        }
    }
}

# Carregar as assemblies .NET de compressão
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

# Criar o ZIP manualmente escrevendo as entradas com barras normais "/" para conformidade estrita do WordPress
$stream = $null
$zip_archive = $null
try {
    $stream = [System.IO.File]::Open($zip_dest, [System.IO.FileMode]::Create)
    $zip_archive = New-Object System.IO.Compression.ZipArchive($stream, [System.IO.Compression.ZipArchiveMode]::Create)

    $files = Get-ChildItem -Path $temp_dir -Recurse -File
    foreach ($file in $files) {
        # Obter caminho relativo em relação ao temp_dir
        $relative_path = $file.FullName.Substring($temp_dir.Length + 1)
        $entry_name = $relative_path.Replace("\", "/")
        
        # Criar entrada no ZIP
        $entry = $zip_archive.CreateEntry($entry_name)
        $entry_stream = $entry.Open()
        $file_stream = [System.IO.File]::OpenRead($file.FullName)
        $file_stream.CopyTo($entry_stream)
        $file_stream.Close()
        $entry_stream.Close()
    }
} finally {
    if ($zip_archive -ne $null) { $zip_archive.Dispose() }
    if ($stream -ne $null) { $stream.Close() }
    [System.GC]::Collect()
    [System.GC]::WaitForPendingFinalizers()
}

# Limpar temporário
Remove-Item $temp_dir -Recurse -Force

# Iniciar validação estrutural do ZIP gerado para WordPress (Segurança de Release)
Write-Output "Iniciando validação estrutural do pacote ZIP gerado..."
$zip = $null
try {
    # 1 e 2. Abrir o arquivo ZIP utilizando exclusivamente a API System.IO.Compression.ZipFile
    $zip = [System.IO.Compression.ZipFile]::OpenRead($zip_dest)
} catch {
    # Falha na abertura do ZIP
    [System.GC]::Collect()
    [System.GC]::WaitForPendingFinalizers()
    if (Test-Path $zip_dest) {
        Remove-Item $zip_dest -Force
    }
    Write-Error "Erro de Validação Estrutural: O pacote ZIP gerado está corrompido ou é inválido."
    exit 1
}

$root_folder = "gerador-posts-gemini/"
$has_main_file = $false
$has_assets = $false
$has_includes = $false
$has_vendor = $false

foreach ($entry in $zip.Entries) {
    # Normalizar caminhos temporariamente para validação de estrutura lógica
    $entry_path = $entry.FullName.Replace("\", "/")
    
    # 3 e 5. Validar que todas as entradas pertencem à pasta raiz do plugin e não há arquivos soltos na raiz
    if (!$entry_path.StartsWith($root_folder)) {
        $zip.Dispose()
        [System.GC]::Collect()
        [System.GC]::WaitForPendingFinalizers()
        if (Test-Path $zip_dest) { Remove-Item $zip_dest -Force }
        Write-Error "Erro de Validação Estrutural: O ZIP contém o arquivo solto '$entry_path' fora da pasta raiz '$root_folder'."
        exit 1
    }
    
    # 4. Confirmar que o arquivo principal está localizado no caminho exato
    if ($entry_path -eq "gerador-posts-gemini/gerador-posts-gemini.php") {
        $has_main_file = $true
    }
    
    # 7. Identificar presença dos diretórios obrigatórios
    if ($entry_path.StartsWith("gerador-posts-gemini/assets/")) { $has_assets = $true }
    if ($entry_path.StartsWith("gerador-posts-gemini/includes/")) { $has_includes = $true }
    if ($entry_path.StartsWith("gerador-posts-gemini/vendor/")) { $has_vendor = $true }
}

# Fechar arquivo zip para liberar o handle antes de ler os bytes crus
$zip.Dispose()
[System.GC]::Collect()
[System.GC]::WaitForPendingFinalizers()

# 6. Confirmar que todos os caminhos internos do ZIP utilizam exclusivamente barras normais "/" como separador, nunca "\"
# Lemos os bytes do arquivo ZIP diretamente do disco para evitar normalizações de plataforma do .NET Framework no Windows
$bytes = [System.IO.File]::ReadAllBytes($zip_dest)
for ($i = 0; $i -lt $bytes.Length - 4; $i++) {
    if ($bytes[$i] -eq 0x50 -and $bytes[$i+1] -eq 0x4B -and $bytes[$i+2] -eq 0x03 -and $bytes[$i+3] -eq 0x04) {
        $name_len = [System.BitConverter]::ToUInt16($bytes, $i + 26)
        if ($name_len -gt 0 -and $i + 30 + $name_len -le $bytes.Length) {
            $name_bytes = $bytes[($i + 30)..($i + 30 + $name_len - 1)]
            $name = [System.Text.Encoding]::UTF8.GetString($name_bytes)
            if ($name.Contains("\")) {
                if (Test-Path $zip_dest) { Remove-Item $zip_dest -Force }
                Write-Error "Erro de Validação Estrutural: O arquivo '$name' gravado fisicamente no ZIP contém barras invertidas '\'."
                exit 1
            }
        }
    }
}

# Verificar resultados de buscas das pastas e arquivos essenciais
if (!$has_main_file) {
    if (Test-Path $zip_dest) { Remove-Item $zip_dest -Force }
    Write-Error "Erro de Validação Estrutural: O arquivo principal 'gerador-posts-gemini/gerador-posts-gemini.php' não foi encontrado no ZIP."
    exit 1
}

if (!$has_assets) {
    if (Test-Path $zip_dest) { Remove-Item $zip_dest -Force }
    Write-Error "Erro de Validação Estrutural: O diretório obrigatório 'assets/' não está presente sob a pasta do plugin."
    exit 1
}

if (!$has_includes) {
    if (Test-Path $zip_dest) { Remove-Item $zip_dest -Force }
    Write-Error "Erro de Validação Estrutural: O diretório obrigatório 'includes/' não está presente sob a pasta do plugin."
    exit 1
}

if (!$has_vendor) {
    if (Test-Path $zip_dest) { Remove-Item $zip_dest -Force }
    Write-Error "Erro de Validação Estrutural: O diretório obrigatório 'vendor/' não está presente sob a pasta do plugin."
    exit 1
}

# Exibir resumo em caso de sucesso
Write-Output "Validação estrutural do pacote WordPress: APROVADA. ZIP íntegro. Estrutura compatível com WordPress. Arquivo principal localizado corretamente. Separadores internos validados. Pacote liberado para publicação."
Write-Output "Release ZIP construído com sucesso em: $zip_dest"
