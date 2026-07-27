# Relatório Técnico: Centralização de Arquivos Raiz no Empacotamento
**Pipeline Oficial de Release — v2.2.1**

---

## 1. Contexto e Problema de Empacotamento

Na modelagem inicial de empacotamento do script de build (`scripts/build_release.ps1`), a transferência de arquivos da raiz do projeto para o diretório de compilação temporário era feita por meio de comandos manuais repetidos:
```powershell
Copy-Item (Join-Path $source_dir "gerador-posts-gemini.php") $plugin_dir
Copy-Item (Join-Path $source_dir "admin-ui.php") $plugin_dir
...
```

Essa duplicação difusa causava dois sérios problemas:
1.  **Suscetibilidade a Falhas:** Modificações e novos arquivos na raiz exigiam que os mantenedores lembrassem de editar o script de build, aumentando o risco de novos artefatos serem esquecidos.
2.  **Inconsistência de Deploy:** O arquivo de metadados obrigatório do ecossistema WordPress, `readme.txt`, embora presente na raiz do repositório, não era copiado para o ZIP de produção. Isso resultava em um pacote distribuído incompleto e inconsistente com a governança.

---

## 2. Refatoração Arquitetural e Centralização

A etapa de cópia de arquivos da raiz no script de build foi completamente refatorada para adotar uma coleção centralizada `$root_files`, que lista explicitamente todos os arquivos produtivos autorizados:

```powershell
$root_files = @(
    "gerador-posts-gemini.php",
    "admin-ui.php",
    "LICENSE",
    "README.md",
    "readme.txt",
    "CHANGELOG.md",
    "SECURITY.md"
)
```

O processamento e cópia ocorrem em um único loop síncrono `foreach`:
```powershell
foreach ($file in $root_files) {
    $src_file = Join-Path $source_dir $file
    if (Test-Path $src_file) {
        Copy-Item $src_file $plugin_dir
    }
}
```

Essa mudança garante que o `readme.txt` de metadados WordPress seja incluído de forma permanente no ZIP final gerado.

---

## 3. Preservação de Lógicas Funcionais e Validações

Confirmamos que esta alteração restringiu-se estritamente à forma física como os arquivos da raiz são copiados:
-   **Estrutura de Subpastas:** A lógica de cópia recursiva de subdiretórios produtivos (`assets/`, `includes/` e `vendor/`) permanece intocada.
-   **Validação Rígida:** Os 8 critérios obrigatórios da validação estrutural .NET aplicados após a compactação do ZIP permanecem ativos e inalterados.
-   **Políticas de Release:** As regras de versionamento, tagging Git SemVer, decodificação UTF-8 round-trip, exit codes de subprocessos e o fluxo operacional de duas etapas operam sem qualquer interferência lógica.

---

## Resumo para Release
### Melhorias
- Refatoração da cópia de arquivos raiz no script de build, adotando uma coleção centralizada $root_files e eliminando chamadas repetidas de Copy-Item.
- Correção definitiva da ausência de readme.txt no pacote compactado gerado para distribuição WordPress.
- Sincronização e consistência permanente entre o conteúdo da raiz do repositório Git e do ZIP de release comercial.
