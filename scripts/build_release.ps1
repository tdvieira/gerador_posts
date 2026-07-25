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

# Gerar arquivo zip na pasta build do projeto usando .NET ZipFile para consistência de caminhos relativos
$zip_dest = Join-Path $build_dir "gerador-posts-gemini.zip"
if (Test-Path $zip_dest) {
    Remove-Item $zip_dest -Force
}

# Carregar a biblioteca .NET de compressão de arquivos
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($temp_dir, $zip_dest)

# Limpar temporário
Remove-Item $temp_dir -Recurse -Force

Write-Output "Release ZIP construído com sucesso em: $zip_dest"
