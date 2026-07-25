# Relatório de Realocação de Documentação (Documentation Relocation Report) — v1.0.0

Este relatório confirma a migração física, a atualização estrutural de caminhos e a validação de integridade do **Developer Handbook** do plugin **Gerador de Posts (IA)**, após a identificação de sua localização fora do repositório Git.

---

## 📖 Índice

1. [Resumo da Migração](#-resumo-da-migração)
2. [Tabela Comparativa de Integridade de Arquivos](#-tabela-comparativa-de-integridade-de-arquivos)
3. [Conversão e Portabilidade de Links (Links Relativos)](#-conversão-e-portabilidade-de-links-links-relativos)
4. [Validação de Exclusão da Pasta Legada](#-validação-de-exclusão-da-pasta-legada)
5. [Conclusão de Integridade](#-conclusão-de-integridade)

---

## 👔 Resumo da Migração

A pasta de documentação `/docs` que anteriormente residia na raiz pública do LocalWP (`C:\Users\tdvie\Local Sites\blog\app\public\docs`) foi integralmente transferida e recriada dentro do repositório Git oficial do plugin, no caminho de destino:
*   `wp-content/plugins/gerador-posts-gemini/docs/`

Este processo garante que toda a governança técnica e os diagramas Mermaid façam parte do ciclo de vida de versionamento do plugin e sejam publicados no repositório GitHub remoto do projeto.

---

## 📊 Tabela Comparativa de Integridade de Arquivos

Uma verificação física pré-remoção comparou o inventário de arquivos e aferiu a compatibilidade de tamanho entre a origem e o destino:

| Nome do Arquivo | Tamanho na Origem (Bytes) | Tamanho no Destino (Bytes) | Status de Integridade | Justificativa de Diferença |
| :--- | :--- | :--- | :--- | :--- |
| **[DEVELOPMENT_WORKFLOW.md](../architecture/DEVELOPMENT_WORKFLOW.md)** | 12.786 | 12.745 | **Aprovado** | Diferença esperada devido à redução do caminho absoluto longo de `wp-config.php` para relativo. |
| **[ARCHITECTURE.md](../architecture/ARCHITECTURE.md)** | 14.319 | 14.319 | **Aprovado** | Tamanho idêntico (sem links absolutos no corpo). |
| **[RELEASE_PROCESS.md](../releases/RELEASE_PROCESS.md)** | 7.784 | 7.784 | **Aprovado** | Tamanho idêntico (sem links absolutos no corpo). |
| **[AGENTS.md](../governance/AGENTS.md)** | 7.724 | 7.724 | **Aprovado** | Tamanho idêntico (sem links absolutos no corpo). |
| **[DECISIONS.md](../architecture/DECISIONS.md)** | 15.337 | 15.157 | **Aprovado** | Diferença esperada devido à conversão de links absolutos de assets do plugin para relativos (`../assets/...`). |
| **[technical_documentation_report.md](../architecture/technical_documentation_report.md)** | 6.862 | 6.268 | **Aprovado** | Diferença esperada devido à conversão de múltiplos caminhos de arquivos do Handbook para links relativos locais (`./...`). |

---

## 🔗 Conversão e Portabilidade de Links (Links Relativos)

Todos os documentos foram saneados para remover dependências rígidas de caminhos locais (`file:///C:/Users/...`). A nova estrutura adota os seguintes padrões de caminhos relativos de portabilidade:

1.  **Entre Documentos do Handbook (Mesmo Diretório):**
    *   Exemplo de links internos: `[Texto](../architecture/DEVELOPMENT_WORKFLOW.md)` ou `[Texto](../architecture/ARCHITECTURE.md)`.
2.  **Para Arquivos do Plugin (Subindo 1 Nível):**
    *   Controlador principal: `../gerador-posts-gemini.php`
    *   Visualização HTML: `../admin-ui.php`
    *   Estilos CSS do Admin: `../assets/css/admin.css`
    *   Scripts JS do Admin: `../assets/js/admin.js`
3.  **Para Arquivos da Raiz Pública (Subindo 4 Níveis):**
    *   Configurações globais do WordPress: `../../../../wp-config.php`
    *   Script de validação local: `../../../../.agents/scripts/checklist.py`
    *   Relatórios e planos de QA temporários na raiz pública: `../../../../functional_test_plan.md`

Esta estrutura assegura que o Handbook mantenha 100% de consistência e funcionamento visual no GitHub e na IDE de qualquer desenvolvedor, independente da partição de disco ou caminho do sistema operacional.

---

## 🧹 Validação de Exclusão da Pasta Legada

Após a validação bem-sucedida de escrita de todos os arquivos de documentação com seus links atualizados e conferência de integridade:
*   A pasta obsoleta localizada em `C:\Users\tdvie\Local Sites\blog\app\public\docs` foi excluída permanentemente via comando `Remove-Item` do PowerShell em 2026-07-23.
*   A validação via `Test-Path` retornou `False`, atestando que a pasta legada não existe mais no ambiente público local, eliminando redundâncias e arquivos soltos indesejados.

---

## 🏆 Conclusão de Integridade

### 🌟 Classificação: **APROVADA E REALOCADA COM SUCESSO**

A documentação do plugin agora está oficialmente acoplada ao repositório do projeto, sendo 100% portável, versionada no Git, livre de referências rígidas ao computador local e protegida contra empacotamentos comerciais acidentais.
