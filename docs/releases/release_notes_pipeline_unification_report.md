# Relatório Técnico: Arquitetura Unificada de Release Notes e Simplificação Operacional
**Pipeline Oficial de Release — v2.0.5**

---

## 1. Arquitetura Anterior e Problemas de Duplicidade

Na arquitetura documental legada do projeto, a geração e a gestão de notas de lançamento (Release Notes) sofriam de severos problemas de consistência e duplicação manual:
1.  **Múltiplas Fontes de Entrada:** As informações sobre o que foi desenvolvido ou corrigido eram documentadas dispersamente em relatórios técnicos na pasta `docs/releases/`, escritas manualmente na seção do `CHANGELOG.md` e reinseridas manualmente (ou sob a forma de texto estático genérico) no corpo das Releases do GitHub.
2.  **Conteúdo Fixo no Script de Preparação:** O script `scripts/prepare_release.ps1` injetava no `CHANGELOG.md` uma mensagem estática e genérica (`- Atualizacao e consolidacao da Release vX.Y.Z`), que exigia edição manual subsequente.
3.  **Desalinhamento entre Local e Remoto:** A descrição presente na página de Releases do GitHub frequentemente divergia do `CHANGELOG.md` local, seja por esquecimento de atualização ou por erros de formatação textual na digitação via painel do navegador ou parâmetros de linha de comando.

---

## 2. A Nova Arquitetura Baseada em Single Source of Truth

A refatoração unificou o fluxo sob o conceito de **Single Source of Truth (Fonte Única de Verdade)**:

```
Relatórios Técnicos docs/releases/*.md (Com seção "## Resumo para Release")
           ↓ (Coleta Automática por prepare_release.ps1)
CHANGELOG.md (Consolidado e Única Fonte de Verdade)
           ↓ (Extração Automática por publish_release.ps1)
GitHub Release (Corpo gerado dinamicamente via gh release create)
```

-   **CHANGELOG.md** passa a ser a única fonte oficial de verdade para todas as Release Notes públicas.
-   Toda a geração de conteúdo do `CHANGELOG.md` e do GitHub Release é **100% automatizada** e baseada na agregação de conteúdos existentes nos relatórios operacionais gerados durante a fase de desenvolvimento da release.

---

## 3. Funcionamento da Coleta Automática

A extração dinâmica e consolidação das notas de lançamento opera da seguinte forma:

1.  **Identificação Dinâmica dos Relatórios Correntes:** O script `prepare_release.ps1` lê recursivamente todos os arquivos Markdown (`docs/releases/*.md`) e filtra apenas aqueles que mencionam o número da versão em preparação (ex: `v2.0.5` ou `2.0.5`).
2.  **Extração da Seção "Resumo para Release":** Para cada relatório identificado, o script busca pela seção Markdown denominada `## Resumo para Release`. A captura isola apenas a lista Markdown de alterações contidas sob este cabeçalho.
3.  **Agregação e Desduplicação por Categoria:** As informações são agrupadas por categoria funcional (`### Novidades`, `### Melhorias`, `### Correções`, `### Segurança`, `### Documentação`, `### Arquitetura`). O script elimina itens duplicados idênticos para evitar redundâncias quando múltiplos relatórios descrevem a mesma alteração.
4.  **Geração e Injeção no CHANGELOG.md:** O bloco consolidado é inserido de forma automática na seção da nova versão no `CHANGELOG.md`. Caso determinada categoria não possua itens, ela simplesmente não é gerada no documento. O script atua estritamente como agregador, sem resumir, reescrever ou inferir informações.

---

## 4. Sincronização e Publicação no GitHub Releases

Durante a etapa de publicação (`scripts/publish_release.ps1`), a integração atinge o seu fechamento automatizado:
1.  **Extração do Bloco de Notas:** O script de publicação lê o arquivo `CHANGELOG.md` e localiza o bloco específico da versão correspondente à tag que está sendo publicada.
2.  **Escrita em Arquivo Temporário:** O bloco é isolado e gravado em um arquivo temporário com codificação de texto UTF-8 (garantindo compatibilidade nativa de acentos e caracteres especiais com o GitHub).
3.  **Publicação via CLI:** O comando `gh release create` consome esse arquivo temporário através da flag `--notes-file`, fazendo com que a GitHub Release no repositório remoto contenha exatamente a mesma formatação, listas e títulos do `CHANGELOG.md` local, garantindo sincronização permanente.

---

## 5. Oficialização do Fluxo de Duas Etapas

O manual do desenvolvedor e o pipeline operacional foram simplificados para apenas **duas etapas ativas obrigatórias** executadas pelo operador:

```
[Desenvolvimento] ──> [Prepare Release] ──> [Publish Release] ──> [GitHub Release]
```

-   **Etapa 1: Prepare Release (`scripts/prepare_release.ps1 -Version X.Y.Z`):** Responsável por atualizar as strings de versão no plugin e manuais, realizar varreduras de consistência, consolidar as Release Notes no `CHANGELOG.md` a partir dos relatórios técnicos e disparar de forma automática o build e a auditoria do ZIP.
-   **Etapa 2: Publish Release (`scripts/publish_release.ps1`):** Responsável por rodar auditoria final na Working Tree, fazer o commit administrativo, criar a tag semântica local, sincronizar com o origin remoto, extrair a Release Note do CHANGELOG.md e realizar a publicação oficial e upload do ZIP no GitHub.
-   **Build Autônomo:** O script `scripts/build_release.ps1` foi retirado da trilha direta de execução do operador, sendo documentado como ferramenta técnica complementar exclusiva para fins de manutenção e reconstruções manuais pontuais do ZIP.

---

## 6. Confirmação de Preservação Arquitetural

Toda a infraestrutura lógica e de segurança previamente homologada foi integralmente preservada:
- A validação estrutural obrigatória do pacote ZIP do WordPress permanece ativa.
- As validações de autenticação e integridade do GitHub CLI (`gh auth status` e `gh repo view`) permanecem inalteradas.
- O rigor da validação da Working Tree continua ativo, abortando a publicação em caso de modificações indevidas no código.
- As regras de versionamento SemVer e tagging semântico do Git permanecem intocadas.
- Toda a lógica da função auxiliar de subprocessos `Execute-ExternalCommand` foi preservada.

---

## Resumo para Release
### Novidades
- Unificação e automação da arquitetura documental de Release Notes sob o princípio de Single Source of Truth (CHANGELOG.md).
- Oficialização do fluxo operacional do pipeline de deploy em apenas duas etapas ativas (Prepare e Publish).
- Extração dinâmica e consolidação das seções "Resumo para Release" de relatórios técnicos correntes no CHANGELOG.md.
- Sincronização automática entre o CHANGELOG.md local e as notas da GitHub Release remota via --notes-file do GitHub CLI.
- Redirecionamento da ferramenta build_release.ps1 como utilitário técnico complementar e autônomo para manutenção de builds.
