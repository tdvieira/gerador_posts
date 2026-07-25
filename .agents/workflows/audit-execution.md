# Fluxo de Trabalho de Auditoria Arquitetural (Execução de Auditoria)

Este fluxo de trabalho normativo estabelece o processo oficial e portátil para planejar, executar e reportar auditorias de integridade arquitetural na infraestrutura de agentes do repositório do plugin.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras gerais de governança em [project-governance.md](../rules/project-governance.md) antes de analisar arquivos de código ou relatórios.
*   Carregar as regras de domínio específicas sob [.agents/rules/](../rules/) (como `git.md`, `memory.md`, `documentation.md`, `workflows.md`, `prompts.md` e o manual de engenharia `engineering.md`).
*   **Carregamento de Memória sob Demanda:** Para auditoria de encerramento de fases ou publicações, o auditor deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) e o índice de memória em [MEMORY.md](../memory/MEMORY.md).

---

## 🎯 2. Objetivo e Escopo

### Objetivo
Padronizar a verificação sistemática de conformidade regulatória, integridade estrutural, portabilidade de referências e limpeza de artefatos de IA na infraestrutura física do plugin.

### Escopo
*   **Auditável:** Todos os diretórios, subpastas e arquivos sob `.agents/` (`memory`, `rules`, `workflows`, `prompts`, `reports`) e manuais técnicos de engenharia contidos na pasta `docs/`.
*   **Não Auditável (Excluído):** O código-fonte operacional (PHP, JS, CSS), dependências de terceiros e pastas locais de execução temporárias do desenvolvedor.

---

## 🚦 3. Pré-condições e Entradas

### Pré-condições
1.  **Estado Estável do Repositório:** A fase ou tarefa a ser auditada deve ter sido declarada concluída pelo agente executor correspondente.
2.  **Rastreabilidade Ativa:** Todos os arquivos da fase devem estar rastreados no controle de versão Git.

### Entradas Obrigatórias
*   A relação exata de modificações físicas e arquivos criados na iteração em andamento.
*   O relatório de encerramento de fase ou de tarefa gerado pelo agente executor.

---

## 📊 4. Checklist de Conformidade

O auditor deve validar as seguintes diretrizes estruturais durante a auditoria:

*   [ ] **Responsabilidade Única de Conhecimento (SRP):** Validar que cada arquivo atende exclusivamente ao seu domínio (regras contêm apenas normas, memória contém apenas metadados dinâmicos e fluxos de trabalho contêm apenas orquestração lógica).
*   [ ] **Portabilidade de Referências:** Confirmar que todos os links internos utilizam caminhos relativos e compatíveis com a estrutura do Git, sendo vedada a inclusão de links locais absolutos (`file:///`).
*   [ ] **Validade Física de Links:** Certificar que as referências markdown relativas ativas de fato apontam para arquivos físicos existentes no repositório.
*   [ ] **Limpeza Arquitetural:** Verificar se todos os arquivos temporários de teste, dumps de banco, artefatos de depuração e marcadores `.gitkeep` em pastas preenchidas foram devidamente removidos.
*   [ ] **Consistência de Nomenclatura:** Checar a ausência de termos obsoletos na memória permanente ativa (ex: referências residuais a marcos temporários em favor de "Releases").

---

## 🛠️ 5. Ferramentas e Scripts de Validação

*   **Portabilidade Independente:** A auditoria arquitetural é estruturalmente portátil. A análise documental e a validação analítica manual são as ferramentas soberanas e obrigatórias de verificação.
*   **Recurso Opcional:** Caso existam scripts locais de auditoria automatizados (ex: script de checklist global `checklist.py` ou scripts de varredura de links), eles poderão ser executados exclusivamente para fins de geração de evidências adicionais.
*   **Restrição de Modificação:** Nenhum script automatizado está autorizado a modificar arquivos da arquitetura do plugin de forma ativa; seu uso limita-se ao escaneamento e reporte estático.

---

## 🛑 6. Limites de Correção Automática e Portal Socrático

### Limites para Correções Automáticas (Baixo Impacto)
O auditor está autorizado a efetuar correções corretivas imediatas e automáticas, sem consulta prévia, restritas estritamente a:
1.  Ajustes de caminhos relativos de navegação incorretos.
2.  Atualização ou remoção de links markdown quebrados direcionados a arquivos excluídos de governança.
3.  Remoção de marcadores `.gitkeep` obsoletos em pastas que já contêm arquivos permanentes.
4.  Pequenos alinhamentos tipográficos ou gramaticais em cabeçalhos de navegação.

### Critérios Obrigatórios para Abertura de Portal Socrático
O auditor deve **interromper imediatamente** a tarefa de auditoria e abrir um Portal Socrático com o usuário se for detectada qualquer necessidade de:
1.  Alteração estrutural de diretórios da infraestrutura do plugin.
2.  Modificação de princípios normativos e regras permanentes sob `.agents/rules/`.
3.  Edição de diários de ADRs ou alteração de decisões de design já consolidadas na memória.
4.  Inserção ou deleção de manuais operacionais definitivos em `docs/`.

---

## 🚦 7. Critérios de Aprovação e Reprovação (Definição de Concluído)

### Critérios de Reprovação (Bloqueios de Mesclagem)
A auditoria será **reprovada**, impedindo qualquer mesclagem no repositório, se for constatada qualquer uma das seguintes não conformidades:
1.  **Links Quebrados Ativos:** Presença de links relativos corrompidos em arquivos de memória, governança ou manuais ativos.
2.  **Duplicação de Autoridade:** Presença de regras normativas permanentes ou convenções escritas fora do diretório `.agents/rules/`.
3.  **Conflito Normativo:** Fluxos de trabalho ou prompts operacionais contendo regras de desenvolvimento divergentes das estipuladas no `project-governance.md`.
4.  **Desvio de Responsabilidade Única (SRP):** Presença de explicações, decisões ou dados técnicos estruturais dentro de arquivos de fluxos de trabalho ou prompts (devem residir em `memory/` ou `docs/`).
5.  **Acúmulo de Resíduos:** Presença de arquivos temporários, rascunhos de testes ou marcadores `.gitkeep` obsoletos em diretórios ativos.
6.  **Falha de Sincronização:** Inconsistência evidente entre os metadados de status (`project-status.md`) e o estado atualizado do código ou das releases.

### Critérios de Aprovação
A auditoria será considerada **aprovada** quando:
1.  Todos os itens do Checklist de Conformidade forem verificados e marcados como conformes.
2.  Nenhum critério de reprovação estiver presente na árvore de arquivos.
3.  As eventuais inconsistências de baixo impacto forem resolvidas em sua totalidade.
4.  O Relatório Final de Auditoria for registrado na raiz do repositório.

---

## 🚦 Bloco de Status do Relatório
*   **Status:** Concluído
*   **Fase:** Fluxo de Auditoria
*   **Resultado:** Aprovado (Roteiro Normativo Estabelecido)
*   **Validação:** Validação da Estrutura de Fluxos de Trabalho
