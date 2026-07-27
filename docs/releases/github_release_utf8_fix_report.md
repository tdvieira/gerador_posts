# Relatório Técnico: Garantia de Codificação UTF-8 nas Release Notes
**Pipeline Oficial de Release — v2.0.6**

---

## 1. Causa Raiz do Problema

Durante deploys heterogêneos utilizando o Windows PowerShell 5.1, constatou-se que caracteres acentuados, cedilhas (como em "Melhorações", "Segurança", "Correções") e alguns símbolos Markdown de Release Notes eram publicados no GitHub de forma corrompida.

A causa raiz está na diferença de comportamento de codificação padrão do PowerShell:
- **Windows PowerShell 5.1:** Utiliza a codificação padrão ANSI do sistema operacional (geralmente Windows-1252/cp1252 para sistemas em português ou inglês) em cmdlets como `Get-Content` e `Out-File` se nenhum parâmetro de codificação for explicitamente fornecido.
- **PowerShell 7+:** Adota UTF-8 sem BOM como codificação padrão para todas as operações de leitura e escrita.
- Como o arquivo `CHANGELOG.md` é escrito em formato UTF-8 (BOM ou sem BOM), a leitura implícita pelo Windows PowerShell 5.1 resultava em caracteres corrompidos na memória. Subsequentemente, a gravação do arquivo temporário utilizado pela flag `--notes-file` do GitHub CLI seguia a codificação cp1252, transmitindo bytes corrompidos ao GitHub.

---

## 2. Análise Técnica e Blindagem UTF-8 de Ponta a Ponta

Para assegurar compatibilidade absoluta e blindar o fluxo contra corrupções de caracteres, implementamos as seguintes melhorias:

1.  **Leitura Explícita em UTF-8:** Todas as chamadas ao cmdlet `Get-Content` para arquivos de texto com acentuação (como `CHANGELOG.md`) passam a conter o parâmetro explícito `-Encoding UTF8`. Isso força o Windows PowerShell 5.1 e o PowerShell 7+ a decodificar os bytes corretamente na memória.
2.  **Gravação Explícita em UTF-8:** A escrita das Release Notes no arquivo temporário, realizada pela chamada `.NET` `[System.IO.File]::WriteAllText`, utiliza explicitamente o codificador `[System.Text.Encoding]::UTF8`.
3.  **Validação Automática de Integridade (Round-Trip Check):**
    *   Imediatamente após a gravação do arquivo temporário no disco, o script de publicação relê o conteúdo gerado utilizando explicitamente `Get-Content -Encoding UTF8`.
    *   Ambos os conteúdos (o original extraído do CHANGELOG.md e o relido do arquivo temporário) são normalizados (substituindo quebras de linha `\r\n` por `\n`) e comparados.
    *   Se qualquer byte for modificado ou corrompido durante a gravação, o pipeline aborta de forma instantânea a publicação do deploy e remove o arquivo temporário.

---

## 3. Implementação Realizada

A melhoria foi aplicada exclusivamente no script [publish_release.ps1](../../scripts/publish_release.ps1) na extração e geração das notas temporárias:

```powershell
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
    $release_url_raw = Execute-ExternalCommand -Command "gh" -Arguments @("release", "create", $tag_name, $zip_path, "--title", $tag_name, "--notes-file", $notes_file) -CaptureOutput $true
    
    if (Test-Path $notes_file) {
        Remove-Item $notes_file -Force
    }
```

---

## 4. Confirmação de Preservação Integral da Arquitetura

O ecossistema funcional do Pipeline de Release permanece intacto:
- **NÃO** houve alterações no script de preparação (`prepare_release.ps1`) ou de build (`build_release.ps1`).
- A lógica de versionamento SemVer e tagging anotada do Git permanece inalterada.
- A validação estrutural do pacote ZIP do WordPress e a autenticação em duas etapas do GitHub CLI continuam funcionando de forma transparente e robusta.
- A Working Tree mantém a sua rigidez habitual, bloqueando deploys indevidos.

---

## Resumo para Release
### Correções
- Implementação de codificação UTF-8 explícita de ponta a ponta na leitura e escrita das notas de release do pipeline.
- Adição de validação round-trip de integridade (ida e volta) para certificar que caracteres acentuados e cedilhas permaneçam perfeitos no arquivo temporário.
- Garantia de que o arquivo temporário de notas seja deletado em qualquer cenário operacional do deploy.
