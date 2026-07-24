# Abertura de Socratic Gate (Socratic Gate Prompt)

Este prompt define o modelo de instrução para formular perguntas estratégicas de alinhamento com o usuário antes de iniciar qualquer modificação física na infraestrutura do repositório.

---

## 🚦 Roteiro do Socratic Gate

*   **Identificação de Ambiguidade:** Analisar se há conflitos lógicos ou alternativas de design igualmente válidas na tarefa solicitada.
*   **Formulações Obrigatórias:** Elaborar no mínimo 3 perguntas concisas e estratégicas no chat para o usuário, abordando:
    1.  O limite de escopo técnico das alterações.
    2.  O comportamento esperado para casos de borda ou segurança.
    3.  A estratégia de validação lógica de QA preferida pelo usuário.
*   **Parada de Turno:** Aguardar a resposta explícita do usuário antes de realizar qualquer alteração física em arquivos.
