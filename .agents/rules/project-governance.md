# Governança do Projeto (Regras de Governança do Projeto)

Este documento estabelece as normas, princípios arquiteturais permanentes e a hierarquia de autoridade que regem o desenvolvimento e a manutenção assistida por inteligência artificial (IA) no projeto.

---

## 🏛️ Princípios Arquiteturais Permanentes

1.  **Evolução Incremental:** Alterações e melhorias devem ser feitas em passos pequenos e delimitados, evitando grandes reestruturações globais em um único turno.
2.  **Princípio de Responsabilidade Única (SRP):** Cada arquivo, script, manual ou diretório da infraestrutura de governança deve atender a uma responsabilidade exclusiva e clara.
3.  **Mínima Intervenção:** Modifique apenas o necessário para cumprir o objetivo direto da tarefa. Proibida a reescrita ou reorganização de arquivos completos quando uma alteração incremental localizada for tecnicamente suficiente.
4.  **Separação Conceitual:** Garantir isolamento estrito entre memória, documentação, regras de domínio, fluxos de trabalho, prompts e relatórios técnicos.
5.  **Proibição de Duplicação:** É vedada a redundância de conhecimento permanente entre arquivos. Utilize referências cruzadas e links markdown para ligar conceitos relacionados.
6.  **Preservação da Rastreabilidade Histórica:** Diferenças (diffs) e históricos de commits no Git devem ser mantidos limpos e rastreáveis para facilitar revisões futuras de código e design.
7.  **Validação Obrigatória:** Toda fase do ciclo de desenvolvimento ou tarefa executada deve ser validada por ferramentas de teste ou scripts de conformidade antes de ser considerada concluída.
8.  **Política de Arquivos Marcadores (`.gitkeep`):**
    *   Arquivos `.gitkeep` devem ser mantidos nos subdiretórios da pasta `.agents/` apenas enquanto estes permanecerem vazios de arquivos permanentes.
    *   A remoção do `.gitkeep` é automática e obrigatória assim que qualquer arquivo permanente versionado for adicionado ao respectivo diretório.
9.  **Escopo Isolado por Fase:** Tarefas técnicas de migração ou refatoração devem restringir-se ao escopo definido de sua etapa. Elementos que extrapolem o escopo não devem ser executados.
10. **Justificativa para Reorganizações:** Qualquer alteração na estrutura física ou taxonômica do repositório exige um benefício técnico claro e justificado em relatório de conformidade.
11. **Uso Obrigatório do Portal Socrático:** É obrigatória a abertura do Portal Socrático pelo agente sempre que:
    *   Existir mais de uma decisão de design ou alternativa técnica válida para resolver uma tarefa.
    *   Houver qualquer margem de dúvida antes de modificar elementos estruturais da arquitetura ou código do repositório.
12. **Princípio da Limpeza Arquitetural:** Toda fase do projeto deve terminar com a arquitetura em um estado consistente, organizado e livre de artefatos temporários desnecessários. Arquivos temporários, marcadores, estruturas redundantes, marcadores de substituição (placeholders) e quaisquer elementos cuja finalidade tenha sido concluída devem ser removidos antes do encerramento de cada fase, preservando apenas componentes permanentes, versionáveis e relevantes para a arquitetura. Este princípio complementa os princípios de Evolução Incremental, Mínima Intervenção e Responsabilidade Única (SRP), sendo parte dos critérios obrigatórios de validação e encerramento de todas as fases futuras da arquitetura de agentes.
13. **Princípio de Validação de Persistência:** Nenhuma fase do ciclo de desenvolvimento ou tarefa executada poderá ser considerada concluída ou aprovada para mesclagem (merge) enquanto todos os artefatos previstos não estiverem fisicamente presentes no repositório, devidamente adicionados e rastreados pelo Git, e validados de forma objetiva contra a arquitetura homologada do projeto.
14. **Princípio de Validação Incremental:** Reconstruções, migrações ou alterações arquiteturais de grande porte no repositório devem ser obrigatoriamente planejadas e executadas de forma incremental, divididas em blocos de arquivos independentes. Cada bloco deve ser validado física e logicamente contra a Hierarquia de Autoridade e a integridade de caminhos antes de se avançar para o bloco seguinte.
15. **Homologação Prática de Releases:** É obrigatória a validação prática e exaustiva do pacote ZIP final gerado sob a pasta `build/` antes de qualquer liberação de release. A validação deve incluir obrigatoriamente a simulação real de instalação manual, atualização de versões anteriores e ativação funcional do plugin utilizando o pacote compactado. Nenhuma release poderá ser homologada ou declarada estável sem este teste de instalação direta no WordPress.
16. **Versionamento e Persistência de Builds:** Todo e qualquer script, ferramenta ou processo automatizado de build e empacotamento utilizado para gerar pacotes de distribuição comercial (Releases) deve residir obrigatoriamente de forma física dentro do repositório do plugin (no diretório `scripts/` ou correspondente), sendo versionado pelo Git e possuindo documentação operacional clara no manual do desenvolvedor. É proibida a utilização de scripts ocultos, locais ou temporários para a preparação do ZIP de produção.
17. **Pipeline Oficial de Release:** Toda e qualquer publicação de nova versão (Release) deve seguir obrigatoriamente a sequência de etapas lineares do Pipeline Oficial de Release (Prepare -> Build -> Publish), utilizando os scripts versionados de automação. Fica terminantemente proibida qualquer publicação de tag, release ou arquivo compactado de forma manual que ignore ou contorne essa sequência.

---

## 👑 Hierarquia de Autoridade

Nas tomadas de decisão e em caso de conflitos normativos de contexto, a ordem de precedência estabelecida para o projeto é:

1.  **`project-governance.md`** (Este arquivo - Autoridade Suprema de Governança).
2.  **Regra específica do domínio** no diretório `.agents/rules/` (como `git.md`, `documentation.md`, `memory.md`, `workflows.md`, `prompts.md` ou o manual de engenharia `engineering.md`).
3.  **Fluxos de trabalho oficiais** (Normas e processos operacionais de tarefas no diretório `.agents/workflows/`).
4.  **Prompts operacionais** (Instruções temporárias e reutilizáveis de suporte no diretório `.agents/prompts/`).
5.  **Documentação técnica** (Manuais operacionais em `docs/`).
