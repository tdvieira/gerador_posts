# Histórico Evolutivo da Arquitetura (Architecture History) — v2.2

Este documento registra a evolução cronológica institucional, as motivações de design, os incidentes e as lições aprendidas que moldaram o desenvolvimento da infraestrutura de suporte a agentes (`.agents`) do plugin **Gerador de Posts (IA)** até a sua estabilização definitiva na versão v2.2.

---

## 🏛️ 1. Motivação da Criação da Arquitetura v2

Nas versões legadas v1.x do plugin, a governança conceitual e a memória técnica residiam em uma pasta temporária externa localizada na raiz pública do servidor WordPress (`public/.agents/`). Esse acoplamento fraco apresentava graves problemas operacionais:
*   **Volatilidade Física:** A pasta legada não era rastreada pelo Git do plugin, sendo frequentemente limpa ou deletada durante resets de ambientes locais do LocalWP.
*   **Perda de Contexto:** A perda de arquivos normativos causava comportamentos inconsistentes de agentes de IA em novas sessões de pair programming, forçando reanalises manuais de código.
*   **Falta de Portabilidade:** A infraestrutura de IA não era empacotada no ZIP comercial do plugin, impedindo que desenvolvedores ou IAs em ambientes de produção carregassem as regras vigentes do projeto.

A arquitetura v2 resolveu esses problemas acoplando fisicamente a pasta `.agents/` diretamente à raiz do repositório Git do plugin, tornando-a autossuficiente e portable.

---

## ⏳ 2. Cronograma de Evolução da Arquitetura

### Fase 1 a 6: Migração e Consolidação da v2.0
*   **Fase 1 (Estrutura Inicial):** Inicialização das pastas de suporte `.agents/memory/`, `.agents/rules/`, `.agents/workflows/`, `.agents/prompts/` e `.agents/reports/`.
*   **Fase 2 (Governança Oficial):** Criação das 6 regras de domínio (`rules/`) e estabelecimento formal da **Hierarchy of Authority** sob a liderança do [project-governance.md](../rules/project-governance.md).
*   **Fase 2.1 (Limpeza Arquitetural):** Introdução do *Princípio 12 (Princípio da Limpeza Arquitetural)*, exigindo o saneamento de arquivos marcadores `.gitkeep` e de arquivos temporários ao final de cada iteração.
*   **Fase 3A/B (Portabilidade de Memória):** Inventário da memória legada e transporte de snapshots essenciais para a pasta `.agents/memory/`, recalculando caminhos relativos e eliminando premissas de marcos (Milestones) em favor de Releases do Git.
*   **Fase 4A/B (Modelos de Orquestração):** Desenvolvimento de workflows e prompts reutilizáveis genéricos de suporte, seguidos da instanciação de roteiros operacionais específicos de hooks, de transientes e de QA do plugin.
*   **Fase 5 (Integração do Handbook):** Atualização dos manuais de engenharia na pasta `/docs/` para sincronizar os links com a nova infraestrutura.
*   **Fase 6 (Auditoria Final):** Varredura estática de links e atestado de prontidão para merge da branch `feature/agents-v2`.

### Evolutions da Versão v2.1
*   **Evolution 1 (Workflow Permanente):** Recomendado no relatório final da Fase 6, foi implementado o workflow definitivo [audit-execution.md](../workflows/audit-execution.md), permitindo a execução de auditorias arquiteturais manuais independentes do AG Kit.
*   **Evolution 2 (Bootstrap Soberano):** Criação do arquivo permanente [AGENT.md](../../AGENT.md) na raiz do plugin como ponto de entrada obrigatório e soberano de onboarding para qualquer agente de IA que opere no projeto.

---

## 🚨 3. O Incidente de Perda Física e a Recovery Sprint v2.2

Durante a transição de branches locais, uma falha de sincronização do sandbox realizou um checkout forçado no Git local. Como os arquivos das Fases 1 a 6 (regras, memória migrada e a maior parte dos prompts/workflows) **não haviam sido gravados em commits no histórico do Git** (estavam apenas como arquivos locais *untracked*), o Git apagou completamente o diretório `.agents/` do disco.

Para conter o incidente, foi executada a **Recovery Sprint v2.2**:
1.  **Diagnóstico e Inventário:** Cruzamento de logs e identificação de que apenas o workflow de auditoria havia sobrevivido por estar versionado no commit `03ed675`. Emissão do parecer técnico de recuperação.
2.  **Reconstrução por Blocos:** Recriação incremental de toda a governança normativa, de metadados, workflows e dos 11 prompts operacionais com base nas evidências de transcripts históricos.
3.  **Validação Incremental e Saneamento de Links:** Correção sintática em manuais ativos (mudando conexões para exatamente 1 nível de subida) e preparação de toda a árvore física no index do Git (`git add`), garantindo a rastreabilidade segura.

---

## 💡 4. Lições Aprendidas e Fortalecimento da Governança

O incidente da perda de arquivos evidenciou a fragilidade de manter arquivos de suporte arquiteturais em estado *untracked* por longos períodos no Git. Para blindar a infraestrutura física contra perdas futuras e padronizar alterações arquiteturais, foram introduzidos dois novos princípios na governança suprema:

1.  **Princípio de Validação de Persistência (Princípio 13):** Estabelece que nenhuma iteração ou fase de desenvolvimento assistido por IA pode ser considerada homologada se os artefatos previstos não estiverem persistidos fisicamente no disco e adicionados de forma rastreável no index do Git (`git add`).
2.  **Princípio de Validação Incremental (Princípio 14):** Determina que refatorações ou reconstruções físicas de grande porte na governança devem ser executadas em blocos funcionais independentes e validadas lógica e sintaticamente antes do avanço para a etapa seguinte.

---

## ❄️ 5. Declaração de Adequação e Congelamento (Architecture Frozen)

Com a incorporação desta memória evolutiva institucional em seu diretório de documentação de IA, a infraestrutura da arquitetura `.agents` v2.2 encontra-se oficialmente homologada e em estado **Architecture Frozen** (Congelamento Arquitetural). 

A taxonomia física de regras, memória e roteiros operacionais está consolidada e estabilizada, servindo de base normativa definitiva para todas as iterações e desenvolvimentos futuros de código e features do plugin WordPress.
