# Fluxo Padrão de Execução de Fases (Phase Execution Workflow)

Este workflow define as etapas lógicas obrigatórias para a execução sequencial de fases ou tarefas de projetos sob a arquitetura de suporte a agentes.

---

## 🔄 Roteiro Sequencial de Execução

1.  **Carregamento de Contexto (Fase Inicial):**
    *   O agente deve ler o snapshot de status executivo do projeto antes de qualquer ação.
    *   O agente deve carregar as normas permanentes descritas nas regras do domínio associado.
2.  **Desenvolvimento do Escopo Isolado:**
    *   Executar as alterações lógicas, sintáticas ou documentais restringindo-se estritamente ao escopo delimitado para a fase em andamento.
    *   Em caso de identificação de mais de uma alternativa técnica válida ou dúvidas arquiteturais, abrir imediatamente o Socratic Gate.
3.  **Higienização (Limpeza Arquitetural):**
    *   Antes de finalizar a fase, varrer a área de trabalho para identificar e remover arquivos temporários, rascunhos, scripts descartáveis e placeholders obsoletos.
    *   Se algum diretório criado passar a contar com arquivos definitivos, remover imediatamente o marcador temporário `.gitkeep`.
4.  **Esteira de Validação:**
    *   Acionar a validação de integridade conforme roteiro do workflow de validação.
5.  **Elaboração de Relatório:**
    *   Gerar o relatório de conformidade da fase correspondente sob o modelo do workflow de relatórios.
