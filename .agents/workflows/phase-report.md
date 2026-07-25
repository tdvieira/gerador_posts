# Fluxo Padrão de Relatórios de Conformidade (Fluxo de Trabalho de Relatórios de Fases)

Este fluxo de trabalho orienta a estrutura mínima e as responsabilidades necessárias para a elaboração de relatórios técnicos de encerramento de fases de migração ou desenvolvimento.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras de documentação em [documentation.md](../rules/documentation.md).
*   **Carregamento de Memória sob Demanda:** O agente deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) e o histórico de ADRs em [tech-decisions.md](../memory/tech-decisions.md) sob demanda para reunir as evidências necessárias que comporão o relatório.

---

## 📋 2. Estrutura Requisitada de Relatório

Todo relatório de conformidade gerado ao fim de uma fase ou correção crítica deve conter exclusivamente as seguintes seções estruturadas:

### Passo 1: Cabeçalho da Fase
*   Identificar claramente o nome do relatório com a fase correspondente e a versão estável atual do projeto.

### Passo 2: Relação de Ações Executadas
*   Inventário exaustivo de diretórios e arquivos criados.
*   Inventário exaustivo de arquivos modificados ou deletados.

### Passo 3: Demonstrativo de Regras e Governança
*   Detalhar quais normas, princípios e diretrizes foram aplicados durante a fase.

### Passo 4: Validação de Limpeza Arquitetural
*   Confirmar a exclusão de arquivos marcadores `.gitkeep` em pastas preenchidas.
*   Registrar a justificativa de permanência de quaisquer marcadores ou artefatos temporários remanescentes por dependência técnica de fases futuras.

### Passo 5: Atestado de Mínima Intervenção
*   Declaração formal e verificação de que nenhum código-fonte, manual externo ou metadado de memória sofreu alteração indevida fora do escopo.

### Passo 6: Pendências e Recomendações
*   Listar as atividades previstas a serem executadas na próxima etapa cronológica.
