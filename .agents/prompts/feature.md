# Desenvolvimento de Funcionalidades do Plugin (Plugin Feature Prompt)

Este prompt orienta o desenvolvimento de novas features do plugin **Gerador de Posts (IA)** em conformidade com as boas práticas do WordPress.

---

## 💻 Parâmetros Técnicos de Features

*   **Separação de Assets (SoC):** Estilos administrativos sob `assets/css/admin.css` e Javascript dinâmico sob `assets/js/admin.js`.
*   **Segurança nos Endpoints:** Inserir a validação `current_user_can('manage_options')` no backend e checagem de nonce `check_ajax_referer` em todos os endpoints de chamadas AJAX.
*   **Leitura de Contexto:** Iniciar o desenvolvimento lendo a arquitetura de negócios no manual de especificações [blog-architecture.md](../memory/blog-architecture.md).
