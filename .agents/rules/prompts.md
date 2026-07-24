# Regras do Domínio de Prompts (Prompt Domain Rules)

Este documento estabelece as diretrizes normativas para redação, modularidade e aplicação de prompts operacionais e estruturados de suporte.

---

## 🎭 1. Modularidade e Abstração

*   **Responsabilidade de Prompts:** Prompts operacionais funcionam exclusivamente como templates de suporte sistêmicos e modelos de instrução temporária. É terminantemente proibido inserir regras normativas permanentes de desenvolvimento de código ou Git dentro de arquivos de prompts.
*   **Separação Conceitual:** Os prompts devem atuar de forma desacoplada da governança, instruindo o agente de IA a consultar os arquivos permanentes de regras antes de executar a lógica parametrizada.
*   **Genéricos vs. Específicos:** Prompts genéricos de fase devem ser abstratos. Prompts do plugin podem carregar parâmetros técnicos necessários para a execução sintática da tarefa (ex: validação de Capability ou Nonce do WordPress).
