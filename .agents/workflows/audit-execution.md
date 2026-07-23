# Workflow de Auditoria Arquitetural (Audit Execution Workflow)

Este workflow normativo estabelece o processo oficial portátil para planejar, executar e reportar auditorias de integridade arquitetural na infraestrutura `.agents` do repositório do plugin.

---

## 🎯 1. Objetivo e Escopo

### Objetivo
Padronizar a verificação sistemática de conformidade regulatória, integridade estrutural, portabilidade de referências e limpeza de artefatos de IA na infraestrutura física do plugin.

### Escopo
*   **Auditável:** Todos os diretórios, subpastas e arquivos sob `.agents/` (`memory`, `rules`, `workflows`, `prompts`, `reports`) e manuais técnicos de engenharia contidos na pasta `/docs`.
*   **Não Auditável (Excluído):** O código-fonte operacional (PHP, JS, CSS), dependências do Composer/NPM e a pasta de execução local externa (`public/.agents/` do AG Kit).

---

## 🚦 2. Pré-condições e Entradas

### Pré-condições
1.  **Estado Estável do Repositório:** A fase ou tarefa a ser auditada deve ter sido declarada concluída pelo agente executor correspondente.
2.  **Rastreabilidade Ativa:** Todos os arquivos da fase devem estar rastreados no controle de versão Git.

### Entradas Obrigatórias
*   A relação exata de modificações físicas e arquivos criados na iteração em andamento.
*   O relatório de encerramento de fase ou de tarefa gerado pelo agente executor.

---

## 📖 3. Documentos Obrigatórios de Consulta

A auditoria deve consultar as fontes oficiais pertinentes ao objeto de análise, obedecendo rigorosamente à Hierarchy of Authority estabelecida em [project-governance.md](../rules/project-governance.md):

1.  **Nível Suprema Autoridade:** [project-governance.md](../rules/project-governance.md) (Normas gerais e princípios permanentes).
2.  **Nível Governança de Domínio:** Regras específicas pertinentes à área do objeto auditado sob [.agents/rules/](../rules/) (`git.md`, `documentation.md`, `memory.md`, `workflows.md` ou `prompts.md`).
3.  **Nível Operacional e Técnico:**
    *   Workflows e prompts específicos associados ao escopo do desenvolvimento.
    *   Manuais e handbooks em [/docs/](../../docs/) em caso de dependência documental explícita de conceitos técnicos.

---

## 📊 4. Checklist de Conformidade

O auditor deve validar as seguintes diretrizes estruturais durante a auditoria:

*   [ ] **SRP de Conhecimento:** Validar que cada arquivo atende exclusivamente ao seu domínio (regras contêm apenas normas, memória contém apenas metadados dinâmicos e workflows contêm apenas orquestração lógica).
*   [ ] **Portabilidade de Referências:** Confirmar que todos os links internos utilizam caminhos relativos e compatíveis com a estrutura do Git, sendo vedada a inclusão de links locais absolutos (`file:///`).
*   [ ] **Validade Física de Links:** Certificar que as referências markdown relativos ativas de fato apontam para arquivos físicos existentes no repositório.
*   [ ] **Limpeza Arquitetural:** Verificar se todos os arquivos temporários de teste, dumps de banco, artefatos de depuração e marcadores `.gitkeep` em pastas preenchidas foram devidamente removidos.
*   [ ] **Consistência de Nomenclatura:** Checar a ausência de termos obsoletos na memória permanente ativa (ex: referências residuais a "Milestones" em favor de "Releases").

---

## 🛠️ 5. Ferramentas e Scripts de Validação

*   **Portabilidade Independente:** A auditoria arquitetural é estruturalmente portátil. A análise documental e a validação analítica manual são as ferramentas soberanas e obrigatórias de verificação.
*   **Recurso Opcional:** Caso existam scripts locais de auditoria automatizados (ex: script de checklist global `checklist.py` do AG Kit ou scripts estáticos de varredura de links), eles poderão ser executados exclusivamente para fins de geração de evidências adicionais.
*   **Restrição de Modificação:** Nenhum script automatizado está autorizado a modificar arquivos da arquitetura do plugin de forma ativa; seu uso limita-se ao escaneamento e reporte estático.

---

## 🛑 6. Limites de Correção Automática e Socratic Gate

### Limites para Correções Automáticas (Baixo Impacto)
O auditor está autorizado a efetuar correções corretivas imediatas e automáticas, sem consulta prévia, restritas estritamente a:
1.  Ajustes de caminhos relativos de navegação incorretos (ex: níveis incorretos de subida `../`).
2.  Atualização ou remoção de links markdown quebrados direcionados a arquivos excluídos de governança.
3.  Remoção de marcadores `.gitkeep` obsoletos em pastas que já contêm arquivos permanentes.
4.  Pequenos alinhamentos tipográficos ou gramaticais em cabeçalhos de navegação.

### Critérios Obrigatórios para Abertura de Socratic Gate
O auditor deve **interromper imediatamente** a tarefa de auditoria e abrir um Socratic Gate com o usuário se for detectada qualquer necessidade de:
1.  Alteração estrutural de diretórios da infraestrutura do plugin.
2.  Modificação de princípios normativos e regras permanentes sob `.agents/rules/`.
3.  Edição de diários de ADRs ou alteração de decisões de design já consolidadas na memória.
4.  Inserção ou deleção de manuais operacionais definitivos em `/docs`.

---

## 🚦 7. Critérios de Aprovação e Reprovação (DoD)

### Critérios de Reprovação (Blockers de Merge)
A auditoria será **reprovada**, impedindo qualquer merge, se for constatada qualquer uma das seguintes não conformidades:
1.  **Links Quebrados Ativos:** Presença de links relativos corrompidos em arquivos de memória, governança ou manuais ativos.
2.  **Duplicação de Autoridade:** Presença de regras normativas permanentes ou convenções escritas fora do diretório `.agents/rules/`.
3.  **Conflito Normativo:** Workflows ou prompts operacionais contendo regras de desenvolvimento divergentes das estipuladas no `project-governance.md`.
4.  **Desvio de SRP:** Presença de explicações, decisões ou dados técnicos estruturais dentro de arquivos de workflows ou prompts (devem residir em `memory/` ou `/docs/`).
5.  **Acúmulo de Resíduos:** Presença de arquivos temporários, rascunhos de testes, ou marcadores `.gitkeep` obsoletos em diretórios ativos.
6.  **Falha de Sincronização:** Inconsistência evidente entre os metadados de status (`project-status.md`) e o estado atualizado do código ou das releases.

### Critérios de Aprovação (DoD)
A auditoria será considerada **aprovada** quando:
1.  Todos os itens do Checklist de Conformidade forem verificados e marcados como conformes.
2.  Nenhum critério de reprovação estiver presente na árvore de arquivos.
3.  As eventuais inconsistências de baixo impacto forem resolvidas em sua totalidade.
4.  O Relatório Final de Auditoria for registrado na raiz do repositório.

---

## 📝 8. Estrutura do Relatório Final de Auditoria

Todo relatório de encerramento de auditoria deve ser gerado na raiz do plugin com o nome `agents_v2_1_evolutionX_report.md` (ou correspondente ao ciclo) e conter exclusivamente as seguintes seções:

1.  **Cabeçalho da Auditoria:** Identificação da versão do projeto, branch auditada, nome do auditor e data.
2.  **Parecer de Conformidade (DoD):** Declaração clara de Aprovação ou Reprovação.
3.  **Checklist Consolidado:** Apresentação em formato de tabela do checklist de conformidade contendo o status de cada item verificado.
4.  **Relação de Correções de Baixo Impacto:** Inventário de todas as referências relativas ou `.gitkeep` ajustados automaticamente durante a execução.
5.  **Registro de Ocorrências Históricas:** Relação detalhada de links legados de auditorias passadas que foram mantidos intactos intencionalmente.
6.  **Declaração de Prontidão para Merge:** Declaração de conformidade da branch e indicação de eventuais alterações locais remanescentes na working tree (que não constituem blockers, mas requerem registro para commit final).
