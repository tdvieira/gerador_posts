# Desenvolvimento de Features do Plugin (Plugin Development Workflow)

Este workflow operacional orienta o ciclo prático de engenharia para criar e evoluir funcionalidades do plugin **Gerador de Posts (IA)** em conformidade com as diretrizes do WordPress.

---

## 💻 Roteiro de Engenharia de Features

### 1. Carregamento de Onboarding
*   O agente de IA inicia a tarefa consultando o bootstrap `AGENT.md` na raiz do plugin.
*   Ler as regras de domínio sob `.agents/rules/` (especialmente `git.md` e `documentation.md`).

### 2. Padrões Técnicos Obrigatórios (WordPress SoC/SRP)
*   **Separação de Assets (SoC):** Qualquer código CSS ou JS administrativo deve ser inserido sob `assets/css/admin.css` ou `assets/js/admin.js`, respectivamente. O enfileiramento deve ser restrito seletivamente à tela do plugin por hook `admin_enqueue_scripts` com verificação de handle.
*   **Modularidade PHP (SRP):** Funções e classes utilitárias de backend devem ser criadas em helpers focados, delegando apenas o roteamento de hooks para o controlador principal `gerador-posts-gemini.php` e a tela administrativa para `admin-ui.php`.
*   **Proteção de Endpoints:** Todas as chamadas AJAX do plugin devem validar capacidades de acesso `manage_options` via `current_user_can()` e autenticidade por `check_ajax_referer()`.

### 3. Validação Local
*   Finalizado o desenvolvimento, o desenvolvedor ou o agente deve acionar os checklists locais descritos no workflow de QA ([qa-validation.md](./qa-validation.md)).
