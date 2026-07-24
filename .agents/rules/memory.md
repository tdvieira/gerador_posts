# Regras do Domínio de Memória (Memory Domain Rules)

Este documento estabelece as diretrizes normativas para leitura, gravação e manutenção incremental dos arquivos de memória persistente do repositório.

---

## 🧭 1. Carregamento de Contexto e Leitura Ordenada

*   **Ponto de Entrada:** Toda sessão de desenvolvimento assistido por IA deve obrigatoriamente ler o snapshot de status [project-status.md](../memory/project-status.md) antes de analisar arquivos de código ou manuais.
*   **Ordem de Leitura Recomendada:**
    1.  `project-status.md` (Metadados do projeto, status de QA, versão).
    2.  `MEMORY.md` (Roteador do diretório de memória).
    3.  `tech-decisions.md` (Histórico de decisões e ADRs).
    4.  `blog-architecture.md` (Premissas de regras de negócio).

---

## 🧹 2. Manutenção e Higienização de Metadados

*   **Proibição de Termos Obsoletos:** É vedada a utilização do termo "Milestone" na memória permanente ativa. Todo o ciclo iterativo deve ser documentado sob "Releases" e "Issues".
*   **Sincronização ao Fim de Turnos:** O snapshot de status deve ser atualizado de forma incremental sempre que uma tarefa de desenvolvimento for finalizada, registrando o novo estado físico do repositório no `project-status.md`.
*   **Decisões Técnicas (ADRs):** Novas decisões de arquitetura de software de grande relevância devem ser formalizadas no diário cronológico `tech-decisions.md` para carregar o contexto conceitual das decisões do projeto.
