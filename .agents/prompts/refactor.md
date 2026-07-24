# Refatoração e Simplificação Lógica (Refactor Prompt)

Este prompt genérico orienta a instrução de agente para refatorar trechos de códigos-fonte obsoletos ou complexos sob o Princípio de Responsabilidade Única (SRP) e Clean Code.

---

## 🧼 Diretrizes de Refatoração

*   **Identificação de Complexidade:** Mapear classes muito grandes ou métodos que acumulem múltiplas responsabilidades na mesma estrutura.
*   **Decomposição Incremental:** Separar a lógica do arquivo de forma gradual, extraindo métodos auxiliares e respeitando o isolamento do ecossistema.
*   **Preservação de Comportamento:** A refatoração não deve alterar as funcionalidades, assinaturas de endpoints ou retornos de sistema esperados pela esteira de testes.
