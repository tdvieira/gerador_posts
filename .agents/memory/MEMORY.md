# Índice de Memória Persistente (Memory Index)

Este arquivo atua exclusivamente como roteador e índice do diretório de memória persistente da arquitetura de agentes. Ele descreve a finalidade de cada arquivo e a sequência recomendada para o carregamento do contexto dinâmico do projeto.

---

## 🧭 Ordem de Leitura Recomendada para Agentes

Para carregar o contexto do repositório de forma eficiente, siga rigorosamente a sequência de leitura abaixo:

1.  **[project-status.md](./project-status.md) (Snapshot de Status):** Ponto de entrada obrigatório da memória. Resumo executivo de metadados, status de garantia de qualidade (QA), releases e links de documentação.
2.  **[tech-decisions.md](./tech-decisions.md) (Histórico de Decisões):** Diário cronológico de Registros de Decisões Arquiteturais (ADRs) de design e refatorações aplicadas no plugin.
3.  **[blog-architecture.md](./blog-architecture.md) (Arquitetura de Negócios):** Mapeamento do blog local WordPress, templates do Elementor, snippets PHP e regras funcionais do plugin.

---

## 📂 Descrição dos Arquivos de Memória

*   **[project-status.md](./project-status.md):** Consolidado executivo unificado da versão atual do plugin e do repositório Git.
*   **[tech-decisions.md](./tech-decisions.md):** Diário cronológico de Registros de Decisões Arquiteturais (ADRs) de separação de conceitos, princípio de responsabilidade única, transients, SSRF, SSL e governança.
*   **[blog-architecture.md](./blog-architecture.md):** Configurações do WordPress local e escopo detalhado de negócios das regras de escrita e imagens do plugin.

---

## 🗺️ Snapshot Estrutural da Arquitetura Congelada

O ecossistema de inteligência artificial do repositório é composto pelas seguintes pastas e arquivos permanentes:

*   **[AGENT.md](../../AGENT.md) (Raiz):** Inicializador técnico oficial de integração obrigatória para agentes de IA.
*   **[.agents/README.md](../../README.md):** Roteador conceitual e introdutório da pasta de agentes.
*   **[.agents/architecture-index.md](../architecture-index.md):** Mapa taxonômico e localizador de categorias de conhecimento de IA.
*   **[.agents/rules/](../rules/):** Normas permanentes de desenvolvimento e Hierarquia de Autoridade (Suprema governança em `project-governance.md`).
*   **[.agents/memory/](../memory/):** Snapshots dinâmicos de status, decisões arquiteturais (ADRs) e premissas do blog (Este diretório).
*   **[.agents/workflows/](../workflows/):** Roteiros de execução lógicos operacionais de garantia de qualidade (como o validador `audit-execution.md`).
*   **[.agents/prompts/](../prompts/):** Modelos e parâmetros de contexto operacionais reutilizáveis.
*   **[.agents/docs/](../docs/):** Histórico evolutivo e retrospectivas de design (como `ARCHITECTURE_HISTORY.md`).
*   **[.agents/reports/](../reports/):** Destinado exclusivamente a relatórios de execuções de testes do projeto.
