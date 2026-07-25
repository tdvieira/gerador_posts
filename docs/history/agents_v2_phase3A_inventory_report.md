# Relatório de Inventário e Plano de Migração de Memória — Arquitetura .agents v2 (Fase 3A)

Este relatório consolida o inventário completo da memória legada em `public/.agents`, a classificação técnica de cada arquivo e o plano de migração planejado para a Fase 3B, mantendo o repositório em modo somente leitura nesta fase.

---

## 📖 Índice

1. [Resumo do Inventário](#-resumo-do-inventário)
2. [Matriz de Classificação e Destinos de Migração](#-matriz-de-classificação-e-destinos-de-migração)
3. [Comparação Conceitual de Convenções (Divergências)](#-comparação-conceitual-de-convenções-divergências)
4. [Mapeamento de Dependências e Links Relativos](#-mapeamento-de-dependências-e-links-relativos)
5. [Riscos da Migração (Fase 3B)](#-riscos-da-migração-fase-3b)
6. [Validação de Integridade e Mínima Intervenção](#-validação-de-integridade-e-mínima-intervenção)
7. [Recomendações para a Fase 3B](#-recomendações-para-a-fase-3b)

---

## 👔 Resumo do Inventário

A inspeção cobriu a estrutura legada externa em `public/.agents`. Foram identificados 5 arquivos sob o diretório `memory/`, 1 arquivo sob `rules/`, além das pastas de apoio operacionais de workflows e skills. O plano de migração desenhado visa portar a memória essencial para o diretório interno do plugin em `wp-content/plugins/gerador-posts-gemini/.agents/`, descartando redundâncias sob o Princípio da Limpeza Arquitetural e preservando a infraestrutura global no workspace externo.

---

## 📊 Matriz de Classificação e Destinos de Migração

| Arquivo Legado Atual | Categoria de Conteúdo | Status | Destino Recomendado na v2 | Justificativa e Rationale Técnico | Revisão Humana |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`public/.agents/rules/GEMINI.md`** | Infraestrutura Externa (AG Kit) | Ativo (Externo) | Não migrar. Permanecer intacto. | Regula o comportamento geral do workspace IDE e pertence ao AG Kit local. | No |
| **`public/.agents/memory/MEMORY.md`** | Memória Permanente (Índice) | Ativo | `.agents/memory/MEMORY.md` | Índice e roteador de contexto de sessões dos agentes. | Sim (recalcular links) |
| **`public/.agents/memory/project-status.md`** | Memória Permanente (Snapshot) | Ativo | `.agents/memory/project-status.md` | Snapshot oficial contendo os metadados estáveis de QA e release. | Sim (remover milestones) |
| **`public/.agents/memory/project-conventions.md`** | Obsoleto/Redundante | Obsoleto | Não migrar. Descartar. | Regras de Git e commit já incorporadas em `.agents/rules/git.md`. Evita redundâncias permanentes. | No |
| **`public/.agents/memory/tech-decisions.md`** | Memória Permanente (Decisões) | Ativo | `.agents/memory/tech-decisions.md` | Histórico cronológico de decisões arquiteturais (ADRs). | No |
| **`public/.agents/memory/blog-architecture.md`** | Memória Permanente (Arquitetura) | Ativo | `.agents/memory/blog-architecture.md` | Detalhamento das regras de negócio de IAs, crop e blog local. | No |
| **`public/.agents/workflows/`** (diretório) | Dependência Externa (AG Kit) | Ativo (Externo) | Não migrar. Permanecer intacto. | Suporta slash commands locais do framework do agente. O plugin criará workflows independentes futuramente. | No |
| **`public/.agents/skills/`** (diretório) | Dependência Externa (AG Kit) | Ativo (Externo) | Não migrar. Permanecer intacto. | Extensões locais da IDE. Sem utilidade física direta no empacotamento do plugin. | No |

---

## 🔎 Comparação Conceitual de Convenções (Divergências)

Realizamos o confronto conceitual prévio entre o arquivo obsoleto [project-conventions.md](file:///C:/Users/tdvie/Local%20Sites/blog/app/public/.agents/memory/project-conventions.md) (legado) e o arquivo de governança permanente [.agents/rules/git.md](file:///C:/Users/tdvie/Local%20Sites/blog/app/public/wp-content/plugins/gerador-posts-gemini/.agents/rules/git.md) (v2):

*   **Convenções Git e Conventional Commits:** Alinhamento de 100% de consistência. Ramificações `feature/` / `fix/` e prefixos de commits semânticos estão totalmente documentados em `git.md`.
*   **Divergência de Milestones:** O arquivo legado estabelecia o ciclo de desenvolvimento dependente de "Milestone v1.1.0" e "Nova Milestone". A nova governança estabelecida removeu as milestones, reorientando o fluxo sob **Issues do GitHub** e **Releases**.
*   **Divergência de Segurança:** O arquivo permanente `git.md` introduziu regras estritas de proibição de versionamento de segredos que não constavam no manual de convenções legada.
*   *Recomendação de Revisão:* A governança estabelecida na Fase 2 está completa. Não há regras legítimas pendentes de incorporação. O descarte do arquivo `project-conventions.md` na Fase 3B está formalmente seguro.

---

## ⛓️ Mapeamento de Dependências e Links Relativos

A migração física da memória para a raiz do repositório do plugin altera os níveis de diretórios em relação à raiz pública de homologação:

*   *Estrutura Legada:* `public/.agents/memory/` (3 níveis relativos de subida para a raiz pública `public/` → `../../../`).
*   *Estrutura da v2:* `wp-content/plugins/gerador-posts-gemini/.agents/memory/` (4 níveis relativos de subida para a raiz pública `public/` → `../../../../`).

Todos os links que referenciam arquivos na raiz pública (como [backup.sql](../../../../backup.sql) ou o controlador principal [gerador-posts-gemini.php](../../gerador-posts-gemini.php)) devem ser recalculados em `MEMORY.md`, `project-status.md`, `tech-decisions.md` e `blog-architecture.md` na Fase 3B para manter a rastreabilidade e evitar links quebrados no GitHub.

---

## 🚦 Riscos da Migração (Fase 3B)

1.  **Links Quebrados:** Risco de manter caminhos relativos legados de 3 níveis, corrompendo a navegação do Handbook.
2.  **Vazamento de Termo Obsoleto:** Risco de migrar metadados de status e de decisões contendo a premissa de uso de Milestones. Ambas as ocorrências devem ser limpas durante a gravação na Fase 3B.

---

## 🧹 Validação de Integridade e Mínima Intervenção

*   **Preservação Total:** Nenhum arquivo do plugin, da documentação em `/docs`, ou das pastas `.agents` de governança e legadas foi movido, copiado, alterado ou excluído.
*   **Modo Somente Leitura:** A Fase 3A limitou-se à análise técnica e geração deste parecer.
*   **Conformidade de Roteador:** O arquivo `project-status.md` permaneceu intocado na memória legada, aguardando execução física em 3B.

---

## 🚀 Recomendações para a Fase 3B

1.  Executar a cópia incremental e gravação da memória permanente activa (`MEMORY.md`, `project-status.md`, `tech-decisions.md`, `blog-architecture.md`) para o novo diretório `.agents/memory/` do plugin.
2.  Adequar os caminhos relativos em 1 nível extra (`../` adicional) em todos os links e referências dos manuais portados.
3.  Remover as menções de "milestone" em `project-status.md` e `MEMORY.md` migrados, substituindo-as sintaticamente por "Releases".
4.  Remover os arquivos temporários marcadores `.agents/memory/.gitkeep` do plugin de forma automática, uma vez que a pasta deixará de estar vazia, em conformidade com o Princípio da Limpeza Arquitetural.
