# Fluxo Padrão de Relatórios de Conformidade (Phase Report Workflow)

Este workflow orienta a estrutura mínima e as responsabilidades necessárias para a elaboração de relatórios técnicos de encerramento de fases de migração ou desenvolvimento.

---

## 📋 Estrutura Requisitada de Relatório

Todo relatório de conformidade gerado ao fim de uma fase ou hotfix deve conter exclusivamente as seguintes seções estruturadas:

1.  **Cabeçalho da Fase:** Identificar claramente o nome do relatório com a fase correspondente e a versão estável atual do projeto.
2.  **Relação de Ações Executadas:**
    *   Inventário exaustivo de diretórios e arquivos criados.
    *   Inventário exaustivo de arquivos modificados ou deletados.
3.  **Demonstrativo de Regras e Governança:** Detalhar quais normas, princípios e diretrizes foram aplicados durante a fase.
4.  **Validação de Limpeza Arquitetural:**
    *   Confirmar a exclusão de arquivos marcadores `.gitkeep` em pastas preenchidas.
    *   Registrar a justificativa de permanência de quaisquer marcadores ou artefatos temporários remanescentes por dependência técnica de fases futuras.
5.  **Atestado de Mínima Intervenção:** Declaração formal e verificação de que nenhum código-fonte, manual externo ou metadado de memória sofreu alteração indevida fora do escopo.
6.  **Pendências e Recomendações:** Listar as atividades previstas a serem executadas na próxima etapa cronológica.
