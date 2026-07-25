# Relatório de Inventário de Recuperação (Recovery Inventory Report) — v2.2

Este relatório apresenta o parecer técnico de diagnóstico e auditoria física executado durante a **Recovery Sprint v2.2** para a infraestrutura `.agents` do plugin **Gerador de Posts (IA)**. O objetivo é confrontar o estado real de persistência do repositório contra o desenho oficial homologado na Evolution 2 da arquitetura v2.1.

---

## 📖 Índice

1. [Resumo Executivo](#-resumo-executivo)
2. [Linha do Tempo de Persistência no Git](#-linha-do-tempo-de-persistência-no-git)
3. [Determinação do Ponto de Divergência](#-determinação-do-ponto-de-divergência)
4. [Matriz de Conformidade de Arquivos (Auditados vs. Esperados)](#-matriz-de-conformidade-de-arquivos-auditados-vs-esperados)
5. [Auditoria de Integridade (Links, SRP e Hierarchy)](#-auditoria-de-integridade-links-srp-e-hierarchy)
6. [Plano Técnico Preliminar de Recuperação Integral](#-plano-técnico-preliminar-de-recuperação-integral)

---

## 👔 Resumo Executivo

A auditoria física exaustiva realizada no diretório do plugin `wp-content/plugins/gerador-posts-gemini/` e no seu respectivo histórico Git identificou uma **grave inconsistência de persistência**. Quase toda a infraestrutura `.agents/` (regras de governança, memória, prompts e a maioria dos workflows), bem como os relatórios históricos das Fases 1 a 5 e da Fase 6, **não existem mais fisicamente no disco local** nem estão indexados ou gravados na branch atual do Git.

O único arquivo da arquitetura que de fato persiste e está versionado na branch é o [.agents/workflows/audit-execution.md](./.agents/workflows/audit-execution.md) (inserido no commit `03ed675`). Os arquivos [AGENT.md](../../AGENT.md) e o relatório de conformidade da Evolution 2 existem temporariamente apenas como arquivos não rastreados (*untracked*) na working tree local. A causa raiz da perda física dos arquivos foi a ausência de commits correspondentes a cada fase no histórico da branch, o que fez com que operações de checkout ou resets de ambiente apagassem os arquivos locais não versionados.

---

## ⏳ Linha do Tempo de Persistência no Git

A auditoria cronológica do histórico de commits do repositório git do plugin revelou as seguintes ocorrências desde a inicialização do projeto:

1.  **Commit `e9f2077` (release(v1.0.0): primeira versão oficial...)**
    *   *Ação:* Criação do código-fonte PHP/JS/CSS e arquivos base do plugin.
    *   *Status da Arquitetura:* Inexistente. A pasta `.agents/` do plugin não existia neste ponto.
2.  **Commit `ff3cc55` (docs: adiciona Developer Handbook...)**
    *   *Ação:* Criação dos manuais base no Handbook `/docs` (`AGENTS.md`, `ARCHITECTURE.md`, `DECISIONS.md`, `DEVELOPMENT_WORKFLOW.md`, `RELEASE_PROCESS.md`).
    *   *Status da Arquitetura:* Inexistente.
3.  **Commit `7a445c8` (docs: adiciona auditoria final...)**
    *   *Ação:* Adiciona `docs/documentation_quality_report.md`.
    *   *Status da Arquitetura:* Inexistente.
4.  **Commit `0940713` (chore: alinha versão do cabeçalho...)**
    *   *Ação:* Ajuste menor no controlador principal PHP.
    *   *Status da Arquitetura:* Inexistente.
5.  **Commit `1171a66` (docs: adiciona documentação operacional...)**
    *   *Ação:* Adiciona manuais e relatórios técnicos em `docs/`.
    *   *Status da Arquitetura:* Inexistente.
6.  **Commit `4f0f75e` (docs: consolida documentação operacional...)**
    *   *Ação:* Adiciona mais relatórios em `docs/` e ajusta classes do provedor Groq.
    *   *Status da Arquitetura:* Inexistente.
7.  **Commit `03ed675` (feat(agents): add audit execution workflow)**
    *   *Ação:* Adiciona os arquivos `.agents/workflows/audit-execution.md` e `agents_v2_1_evolution1_report.md`.
    *   *Status da Arquitetura:* **Persistência Parcial.** Este é o único commit no histórico do repositório que contém elementos do ecossistema `.agents/` v2.x.

---

## 🔎 Determinação do Ponto de Divergência

*   **O Desvio:** Os relatórios das Fases 1 a 5 e da Fase 6 detalham a criação de 34 arquivos permanentes no repositório. Porém, a verificação física e o comando `git ls-files` provam que estes arquivos **nunca foram commitados** no histórico Git do projeto.
*   **Causa Raiz:** O agente executor das fases de migração gerou as alterações e relatórios localmente na working tree, mas não realizou os commits das Fases 1 a 5 nem da Fase 6 de forma permanente. 
*   **O Gatilho da Perda:** Ao transitar da branch de migração (`feature/agents-v2` / `feature/agents-v2.1`) para a branch de trabalho local (ou por comandos de limpeza automática do ambiente), o Git removeu os arquivos *untracked* locais, deletando fisicamente a governança, a memória ativa e os workflows específicos que existiam apenas em memória de disco local volátil.

---

## 📊 Matriz de Conformidade de Arquivos (Auditados vs. Esperados)

A matriz abaixo confronta o inventário físico atual com a arquitetura definida na homologação da Evolution 2:

| Componente Arquitetural | Arquivo Esperado | Estado Físico Real | Status de Persistência | Classificação |
| :--- | :--- | :--- | :--- | :--- |
| **Bootstrap Soberano** | `AGENT.md` | Existe na raiz | **Não Persistido** | Untracked na working tree. |
| **Index da Arquitetura** | `.agents/architecture-index.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **README Conceitual** | `.agents/README.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Regras (Suprema)** | `.agents/rules/project-governance.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Regras de Git** | `.agents/rules/git.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Regras de Docs** | `.agents/rules/documentation.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Regras de Memória** | `.agents/rules/memory.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Regras de Workflows** | `.agents/rules/workflows.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Regras de Prompts** | `.agents/rules/prompts.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Índice de Memória** | `.agents/memory/MEMORY.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Snapshot de Status** | `.agents/memory/project-status.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Decisões Técnicas** | `.agents/memory/tech-decisions.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Arquitetura do Blog** | `.agents/memory/blog-architecture.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflow de Auditoria**| `.agents/workflows/audit-execution.md` | Existe na pasta | **Persistido** | Tracked e commitado em `03ed675`. |
| **Workflows (Fase)** | `.agents/workflows/phase-execution.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (Validação)**| `.agents/workflows/phase-validation.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (Relatório)**| `.agents/workflows/phase-report.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (Release)** | `.agents/workflows/release-preparation.md`| **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (Dev)** | `.agents/workflows/plugin-development.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (ZIP/Rel)** | `.agents/workflows/plugin-release.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (Memória)**| `.agents/workflows/memory-update.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (Docs)** | `.agents/workflows/documentation-update.md`| **Ausente** | **Perdido** | Deletado localmente. |
| **Workflows (QA)** | `.agents/workflows/qa-validation.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Prompts Reutilizáveis**| 11 prompts estruturados em `prompts/` | **Ausente** | **Perdido** | Deletados localmente. |
| **Relatórios Históricos**| `agents_v2_phase1_report.md` a `...phase5_report.md` | **Ausente** | **Perdido** | Deletados localmente. |
| **Relatório Fase 6** | `agents_v2_final_audit_report.md` | **Ausente** | **Perdido** | Deletado localmente. |
| **Relatório Evo 1** | `agents_v2_1_evolution1_report.md` | Existe na raiz | **Persistido** | Tracked e commitado em `03ed675`. |
| **Relatório Evo 2** | `agents_v2_1_evolution2_report.md` | Existe na raiz | **Não Persistido** | Untracked na working tree. |

---

## 🚦 Auditoria de Integridade (Links, SRP e Hierarchy)

*   **Inconsistência de Links (Links Quebrados):** Todos os 12 links relativos configurados no bootstrap [AGENT.md](../../AGENT.md) e no workflow [audit-execution.md](./.agents/workflows/audit-execution.md) que apontam para arquivos sob `.agents/rules/`, `.agents/memory/` ou workflows específicos estão **fisicamente quebrados**, pois as pastas e arquivos de destino não existem no disco.
*   **Quebra de Hierarchy of Authority:** A exclusão física do arquivo supremo [project-governance.md](./.agents/rules/project-governance.md) impede o cumprimento analítico de validação de regras sintáticas e princípios em tempo real. O único roteador normativo sobrevivente que faz referência à hierarquia é o `audit-execution.md`.
*   **Separação Conceitual (SRP documental):** O arquivo [docs/AGENTS.md](../governance/AGENTS.md) está íntegro na pasta `/docs`, mantendo sua responsabilidade voltada a programadores humanos. No entanto, o `AGENT.md` na raiz (bootstrap) está órfão de suas conexões normativas.

---

## 🚀 Plano Técnico Preliminar de Recuperação Integral

Para restaurar fisicamente o repositório à arquitetura homologada, propõe-se a execução estruturada dos seguintes passos em uma sprint futura de reconstrução:

### Passo 1: Restauração da Governança e Regras
1.  Recriar a pasta `.agents/rules/` e restaurar o arquivo supremo [project-governance.md](./.agents/rules/project-governance.md) contendo os 12 princípios fundamentais (incluindo o Princípio da Limpeza Arquitetural).
2.  Recriar e restaurar as regras de domínio sintáticas: `git.md` (regras Git e Commits), `documentation.md` (portabilidade), `memory.md`, `workflows.md` e `prompts.md`.

### Passo 2: Restauração do Contexto de Memória
1.  Recriar a pasta `.agents/memory/` e restaurar a memória permanente do plugin: `project-status.md` (v1.0.0 estável), `tech-decisions.md` (ADRs), `blog-architecture.md` (ajustado com os 5 níveis de subida relativos para `wp-config.php`) e o roteador `MEMORY.md`.

### Passo 3: Restauração de Workflows e Prompts
1.  Recriar e restaurar todos os 8 workflows operacionais genéricos e específicos ausentes na pasta `.agents/workflows/`.
2.  Recriar e restaurar os 11 prompts estruturados e reutilizáveis na pasta `.agents/prompts/`.
3.  Recriar a pasta `.agents/reports/` mantendo apenas o arquivo marcador `.gitkeep`.

### Passo 4: Restauração Histórica e Bootstrap
1.  Restaurar na raiz do plugin os relatórios históricos de migração das Fases 1 a 5 (`agents_v2_phase1_report.md` a `agents_v2_phase5_report.md`) e o relatório final da Fase 6 (`agents_v2_final_audit_report.md`).
2.  Efetuar o *staging* (git add) do bootstrap [AGENT.md](../../AGENT.md) e do relatório [agents_v2_1_evolution2_report.md](agents_v2_1_evolution2_report.md) atualmente untracked.

### Passo 5: Consolidação Histórica via Git Commits
Para evitar novos incidentes de perda por resets locais e manter a conformidade de rastreabilidade do histórico do repositório, **todos os arquivos recriados deverão ser comitados de forma permanente no Git** por meio de commits semânticos incrementais:
*   `feat(agents): phase 1 - setup basic infrastructure`
*   `feat(agents): phase 2 - establish project governance and domain rules`
*   `feat(agents): phase 3 - migrate memory files and project status`
*   `feat(agents): phase 4 - add workflows and reusable prompts`
*   `feat(agents): phase 5 - integrate documentation handbook`
*   `feat(agents): phase 6 - finalize agents v2 migration and audit`
*   `feat(agents): evolution 2 - establish agent bootstrap guide`
