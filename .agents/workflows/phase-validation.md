# Fluxo Padrão de Validação de Integridade (Phase Validation Workflow)

Este workflow estabelece as checagens e auditorias obrigatórias a serem executadas ao término de cada fase para atestar a conformidade física e lógica da arquitetura.

---

## 🚦 Roteiro de Auditoria de QA

1.  **Auditoria de Escopo e Mínima Intervenção:**
    *   Verificar se apenas arquivos pertencentes ao escopo aprovado para a fase foram modificados ou criados.
    *   Confirmar se arquivos de outros domínios (código-fonte, documentação, metadados de memória) não sofreram alterações acidentais.
2.  **Auditoria de Limpeza Arquitetural:**
    *   Certificar que nenhum arquivo temporário de depuração, log local ou rascunho de desenvolvimento restou na árvore de trabalho do Git.
    *   Verificar que pastas preenchidas com arquivos permanentes não possuam mais arquivos marcadores `.gitkeep`.
3.  **Auditoria de Links e Portabilidade:**
    *   Confirmar que todas as referências internas de navegação adicionadas utilizam caminhos relativos e portáveis, sendo vedada a inserção de links absolutos.
4.  **Auditoria de Redundância e SRP:**
    *   Garantir que novos conhecimentos tenham sido armazenados no seu local permanente correspondente, evitando a proliferação de explicações duplicadas.
5.  **Aprovação:**
    *   Registrar o resultado consolidado e as eventuais ressalvas justificadas no relatório de encerramento da fase.
