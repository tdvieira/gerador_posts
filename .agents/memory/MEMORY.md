# Índice de Memória Persistente (Memory Index)

Este arquivo atua exclusivamente como roteador e índice do diretório de memória persistente da arquitetura v2. Ele descreve a finalidade de cada arquivo e a sequência recomendada para o carregamento do contexto.

---

## 🧭 Ordem de Leitura Recomendada para Agentes

Para carregar o contexto do repositório de forma eficiente, siga rigorosamente a sequência de leitura abaixo:

1.  **[project-status.md](./project-status.md) (Snapshot de Status):** Ponto de entrada obrigatório. Resumo executivo de metadados, status de QA, releases e links de documentação.
2.  **[tech-decisions.md](./tech-decisions.md) (Histórico de Decisões):** Diário cronológico de ADRs de design e refatorações aplicadas no plugin.
3.  **[blog-architecture.md](./blog-architecture.md) (Arquitetura de Negócios):** Mapeamento do blog local WordPress, templates do Elementor, snippets PHP e regras funcionais do plugin.

---

## 📂 Descrição dos Arquivos de Memória

*   **[project-status.md](./project-status.md):** Consolidado executivo unificado da versão atual do plugin e do repositório Git.
*   **[tech-decisions.md](./tech-decisions.md):** Diário cronológico de ADRs de Separation of Concerns (SoC), SRP, transients, SSRF e SSL.
*   **[blog-architecture.md](./blog-architecture.md):** Configurações do WordPress local e escopo detalhado de negócios das regras de escrita e imagens do plugin.
