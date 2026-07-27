# Relatório de Refatoração: Identificação Dinâmica da Documentação Oficial
**Pipeline Oficial de Release — v2.0.3**

---

## 1. Motivação da Alteração

Anteriormente, o script de publicação do Pipeline Oficial de Release (`scripts/publish_release.ps1`) utilizava uma lista estática e rígida de arquivos permitidos (whitelist) para validar a integridade da Working Tree do Git. 

Essa abordagem trazia dois problemas operacionais graves:
1.  **Gargalo de Manutenção:** Toda vez que um novo relatório técnico, manual operacional ou documento explicativo era criado pelo time de engenharia ou de garantia de qualidade (QA) durante o processo de release, o desenvolvedor era obrigado a editar o script de publicação para inserir o caminho completo do novo arquivo na whitelist.
2.  **Risco de Deploy Bloqueado:** O esquecimento de atualizar a whitelist estática fazia com que a publicação da release abortasse prematuramente na etapa de auditoria da Working Tree (`git status --porcelain`), indicando a presença de arquivos "não permitidos".

A refatoração migra este fluxo para um modelo dinâmico baseado em padrões de arquivos (wildcards).

---

## 2. Arquitetura Anterior vs. Nova Estratéria Baseada em Padrões

### Arquitetura Anterior (Whitelist Estática)
Os arquivos de relatórios eram listados individualmente em um array fixo:
```powershell
$allowed_files = @(
    "gerador-posts-gemini.php",
    ...
    "docs/releases/release_pipeline_consolidation_report.md",
    "docs/releases/release_preparation_script_report.md",
    "docs/releases/release_publish_script_report.md",
    ...
)
```
Qualquer novo arquivo `.md` fora desta lista gerava uma violação na Working Tree.

### Nova Estratégia Baseada em Padrões (Dynamic Whitelist)
O script de publicação passa a utilizar a função auxiliar encapsulada `Test-IsFileAllowed`. Essa função valida os arquivos contra uma lista simplificada de arquivos de código/configuração estáticos, combinando-a com uma regra baseada em padrões curinga (`wildcards`) para documentos da release:

```powershell
function Test-IsFileAllowed {
    param (
        [string]$FilePathNorm
    )

    $static_allowed = @(
        "gerador-posts-gemini.php",
        "includes/Core/PluginBootstrap.php",
        "README.md",
        "CHANGELOG.md",
        "PIPELINE.md",
        "build/gerador-posts-gemini.zip",
        "build/.gitkeep",
        ".gitignore",
        "scripts/prepare_release.ps1",
        "scripts/build_release.ps1",
        "scripts/publish_release.ps1",
        ".agents/rules/project-governance.md",
        ".agents/rules/documentation.md"
    )

    # 1. Checagem contra arquivos estaticos
    foreach ($static in $static_allowed) {
        if ($FilePathNorm -eq $static) { return $true }
    }

    # 2. Identificacao dinamica de documentacao por padrao
    if ($FilePathNorm -like "docs/releases/*.md") {
        return $true
    }

    return $false
}
```

E para a etapa de inclusão automática no Git, o script varre dinamicamente a pasta `docs/releases/`:
```powershell
$releases_dir = Join-Path $source_dir "docs\releases"
if (Test-Path $releases_dir) {
    $md_files = Get-ChildItem -Path $releases_dir -Filter "*.md"
    foreach ($file in $md_files) {
        Execute-ExternalCommand -Command "git" -Arguments @("add", $file.FullName) -AllowedExitCodes @(0, 1, 128)
    }
}
```

---

## 3. Benefícios Obtidos

- **Manutenção Zero na Pipeline:** Não é mais necessário editar o script `publish_release.ps1` ao criar novos relatórios ou manuais técnicos.
- **Evolução Contínua da Documentação:** O time de engenharia pode expandir o acervo documental de releases sob `docs/releases/` livremente.
- **Commit Automático e Dinâmico:** O comando `git add` do pipeline adiciona automaticamente todos os relatórios novos e antigos que residam em `docs/releases/` e correspondam ao padrão.
- **Simplificação de Código:** Remoção de mais de 15 linhas de código duplicado e caminhos estáticos que poluíam o script de publicação.

---

## 4. Exemplos de Arquivos Aceitos Dinamicamente

Os seguintes arquivos são aceitos e comitados automaticamente pelo novo padrão:
- `docs/releases/RELEASE_PROCESS.md` (Manual oficial de release)
- `docs/releases/external_commands_standardization_report.md` (Relatório técnico de comandos externos)
- `docs/releases/release_documentation_whitelist_refactoring_report.md` (Este relatório)
- Qualquer futuro relatório técnico no formato `docs/releases/novo_relatorio_qa.md`.

---

## 5. Preservação das Regras de Segurança e Ausência de Impacto Funcional

A segurança e a rigidez do pipeline foram integralmente mantidas:
- **Proteção Contra Arquivos Soltos:** Caso existam alterações em arquivos PHP, JS, CSS, arquivos de desenvolvimento soltos ou qualquer arquivo de código fora do diretório `docs/releases/` que não esteja na lista estática explícita, a validação da Working Tree continua abortando o deploy e cancelando a publicação.
- **Validações Preservadas:** A validação estrutural do arquivo ZIP do WordPress, a checagem em duas etapas do GitHub CLI (`gh auth status` e `gh repo view`), a consistência do `CHANGELOG.md` com a versão do plugin e a validação de tags locais e remotas permanecem ativas e inalteradas.
- **Sem Alteração de Versionamento:** Nenhuma regra de versionamento do repositório ou tagging semântico foi modificada.
- **Sem Alteração nos Demais Scripts:** Os scripts `prepare_release.ps1` e `build_release.ps1` não sofreram nenhuma alteração.

---

## Resumo para Release
### Melhorias
- Substituição da whitelist estática de documentações de release por uma identificação dinâmica baseada em wildcards (docs/releases/*.md).
- Implementação de rastreamento e commit automático para todos os relatórios de release, reduzindo o custo de manutenção futura do pipeline.
