# Correção de Defeitos no Plugin (Plugin Bugfix Prompt)

Este prompt apoia o diagnóstico e hotfixes de falhas de processamento HTTP, transients de cache e conexões SSL no plugin.

---

## 🛠️ Diretrizes de Diagnóstico do Plugin

*   **Validação de SSRF e SSL:** Garantir a validação de URLs com `wp_http_validate_url` e o controle correto do SSL verify no download de imagens.
*   **Invalidação de Cache:** Atestar se os transients expiram corretamente após o intervalo de 12 horas ou ao modificar as opções globais.
*   **Investigação de Log:** Inspecionar o arquivo de logs local `error_log` do servidor local do WordPress para rastrear a pilha de erros PHP.
