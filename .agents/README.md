# Infraestrutura de Agentes (.agents) — v2

Este diretório centraliza a governança normativa, memória persistente, rotinas de testes, fluxos de trabalho de garantia de qualidade (QA) e prompts do ecossistema assistido por inteligência artificial (IA) do plugin **Gerador de Posts (IA)**.

---

## 🚦 Ponto de Entrada Oficial da Arquitetura

Para compreender a taxonomia física deste diretório e localizar as informações e relatórios do projeto:
*   Acesse o **[Índice de Arquitetura (architecture-index.md)](./architecture-index.md)**.

---

## 📜 Governança e Regras do Projeto

As normas permanentes, a hierarquia de autoridade e as regras sintáticas de cada domínio estão localizadas no diretório [rules/](./rules/) nos seguintes documentos:

*   **Governança Geral:** [project-governance.md](./rules/project-governance.md) (Contém os princípios fundamentais e a Hierarquia de Autoridade).
*   **Domínio Git:** [git.md](./rules/git.md) (Regras de ramificação, commits semânticos e exclusão de segredos).
*   **Domínio de Documentação:** [documentation.md](./rules/documentation.md) (Regras de centralização de manuais, portabilidade e responsabilidade única documental).
*   **Domínio de Memória:** [memory.md](./rules/memory.md) (Regras de carregamento de contexto e gravação de snapshots).
*   **Domínio de Fluxos de Trabalho:** [workflows.md](./rules/workflows.md) (Normas de execução de scripts de validação e isolamento de registros de execução).
*   **Domínio de Prompts:** [prompts.md](./rules/prompts.md) (Regras de modularidade e reusabilidade de prompts).

---

## 📄 Histórico e Documentação de IA

*   **Documentação Institucional:** [.agents/docs/](./docs/) (Diretório contendo o diário de evolução histórica da arquitetura).
*   **Diário de Evolução da Arquitetura:** [ARCHITECTURE_HISTORY.md](./docs/ARCHITECTURE_HISTORY.md) (Registro cronológico de fases, incidentes e lições aprendidas).
