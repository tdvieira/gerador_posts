# Governança do Projeto (Project Governance Rules)

Este documento estabelece as normas, princípios arquiteturais permanentes e a hierarquia de autoridade que regem o desenvolvimento e a manutenção assistida por inteligência artificial (IA) no projeto.

---

## 🏛️ Princípios Arquiteturais Permanentes

1.  **Evolução Incremental:** Alterações e melhorias devem ser feitas em passos pequenos e delimitados, evitando grandes reestruturações globais em um único turno.
2.  **Princípio de Responsabilidade Única (SRP):** Cada arquivo, script, manual ou diretório da infraestrutura de governança deve atender a uma responsabilidade exclusiva e clara.
3.  **Mínima Intervenção:** Modifique apenas o necessário para cumprir o objetivo direto da tarefa. Proibida a reescrita ou reorganização de arquivos completos quando uma alteração incremental localizada for tecnicamente suficiente.
4.  **Separação Conceitual:** Garantir isolamento estrito entre memória, documentação, regras de domínio, workflows, prompts e relatórios técnicos.
5.  **Proibição de Duplicação:** É vedada a redundância de conhecimento permanente entre arquivos. Utilize referências cruzadas e links markdown para ligar conceitos relacionados.
6.  **Preservação da Rastreabilidade Histórica:** Diffs e históricos de commits no Git devem ser mantidos limpos e rastreáveis para facilitar revisões futures de código e design.
7.  **Validação Obrigatória:** Toda fase do ciclo de desenvolvimento ou tarefa executada deve ser validada por ferramentas de teste ou scripts de conformidade antes de ser considerada concluída.
8.  **Política de Arquivos Marcadores (`.gitkeep`):**
    *   Arquivos `.gitkeep` devem ser mantidos nos subdiretórios da pasta `.agents/` apenas enquanto estes permanecerem vazios de arquivos permanentes.
    *   A remoção do `.gitkeep` é automática e obrigatória assim que qualquer arquivo permanente versionado for adicionado ao respectivo diretório.
9.  **Escopo Isolado por Fase:** Tarefas técnicas de migração ou refatoração devem restringir-se ao escopo definido de sua etapa. Elementos que extrapolem o escopo não devem ser executados.
10. **Justificativa para Reorganizações:** Qualquer alteração na estrutura física ou taxonômica do repositório exige um benefício técnico claro e justificado em relatório de conformidade.
11. **Uso Obrigatório do Socratic Gate:** É obrigatória a abertura do Socratic Gate pelo agente sempre que:
    *   Existir mais de uma decisão de design ou alternativa técnica válida para resolver uma tarefa.
    *   Houver qualquer margem de dúvida antes de modificar elementos estruturais da arquitetura ou código do repositório.
12. **Princípio da Limpeza Arquitetural (Architectural Cleanliness Principle):** Toda fase do projeto deve terminar com a arquitetura em um estado consistente, organizado e livre de artefatos temporários desnecessários. Arquivos temporários, marcadores, estruturas redundantes, placeholders e quaisquer elementos cuja finalidade tenha sido concluída devem ser removidos antes do encerramento de cada fase, preservando apenas componentes permanentes, versionáveis e relevantes para a arquitetura. Este princípio complementa os princípios de Evolução Incremental, Mínima Intervenção e Responsabilidade Única (SRP), sendo parte dos critérios obrigatórios de validação e encerramento de todas as fases futuras da arquitetura `.agents`.

---

## 👑 Hierarquia de Autoridade (Hierarchy of Authority)

Nas tomadas de decisão e em caso de conflitos normativos de contexto, a ordem de precedência estabelecida para o projeto é:

1.  **`project-governance.md`** (Este arquivo - Autoridade Suprema de Governança).
2.  **Regra específica do domínio** (`git.md`, `documentation.md`, `memory.md`, `workflows.md` ou `prompts.md`).
3.  **Workflows oficiais** (Normas e processos operacionais de tarefas).
4.  **Prompts operacionais** (Instruções temporárias e reutilizáveis de suporte).
5.  **Documentação técnica** (Manuais operacionais em `/docs`).
