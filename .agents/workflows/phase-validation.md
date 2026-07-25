# Fluxo Padrão de Validação de Integridade (Fluxo de Trabalho de Validação de Fases)

Este fluxo de trabalho estabelece as checagens e auditorias obrigatórias a serem executadas ao término de cada fase para atestar a conformidade física e lógica da arquitetura.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras gerais de governança em [project-governance.md](../rules/project-governance.md).
*   **Carregamento de Memória sob Demanda:** O agente deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) e o índice em [MEMORY.md](../memory/MEMORY.md) para realizar as devidas auditorias e checagens físicas da fase contra o planejado.

---

## 🚦 2. Roteiro de Auditoria de Garantia de Qualidade

### Passo 1: Auditoria de Escopo e Mínima Intervenção
*   Verificar se apenas arquivos pertencentes ao escopo aprovado para a fase foram modificados ou criados.
*   Confirmar se arquivos de outros domínios (código-fonte, documentação, metadados de memória) não sofreram alterações acidentais.

### Passo 2: Auditoria de Limpeza Arquitetural
*   Certificar que nenhum arquivo temporário de depuração, log local ou rascunho de desenvolvimento restou na árvore de trabalho do Git.
*   Verificar que pastas preenchidas com arquivos permanentes não possuam mais arquivos marcadores `.gitkeep`.

### Passo 3: Auditoria de Links e Portabilidade
*   Confirmar que todas as referências internas de navegação adicionadas utilizam caminhos relativos e portáveis, sendo vedada a inserção de links absolutos.

### Passo 4: Auditoria de Redundância e Responsabilidade Única (SRP)
*   Garantir que novos conhecimentos tenham sido armazenados no seu local permanente correspondente, evitando a proliferação de explicações duplicadas.

### Passo 5: Aprovação
*   Registrar o resultado consolidado e as eventuais ressalvas justificadas no relatório de encerramento da fase.
