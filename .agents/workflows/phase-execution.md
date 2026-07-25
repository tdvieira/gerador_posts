# Fluxo Padrão de Execução de Fases (Fluxo de Trabalho de Execução de Fases)

Este fluxo de trabalho define as etapas lógicas obrigatórias para a execução sequencial de fases ou tarefas de projetos sob a arquitetura de suporte a agentes.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras de governança e prioridades em [project-governance.md](../rules/project-governance.md).
*   **Carregamento de Memória sob Demanda:** O agente deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) no início deste fluxo para identificar a fase atual ativa e o histórico de alterações pendentes.

---

## 🔄 2. Roteiro Sequencial de Execução

### Passo 1: Desenvolvimento do Escopo Isolado
*   Executar as alterações lógicas, sintáticas ou documentais restringindo-se estritamente ao escopo delimitado para a fase em andamento.
*   Em caso de identificação de mais de uma alternativa técnica válida ou dúvidas arquiteturais, abrir imediatamente o Portal Socrático.

### Passo 2: Higienização (Limpeza Arquitetural)
*   Antes de finalizar a fase, varrer a área de trabalho para identificar e remover arquivos temporários, rascunhos, scripts descartáveis e marcadores de substituição obsoletos.
*   Se algum diretório criado passar a contar com arquivos definitivos, remover imediatamente o marcador temporário `.gitkeep`.

### Passo 3: Esteira de Validação
*   Acionar a validação de integridade conforme roteiro do fluxo de trabalho de validação de fases.

### Passo 4: Elaboração de Relatório
*   Gerar o relatório de conformidade da fase correspondente sob o modelo do fluxo de trabalho de relatórios.
