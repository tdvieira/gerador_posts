# Relatório de Atualização de Nomenclatura da Interface (UI Label Update Report) — v1.0.0

Este relatório documenta a alteração de nomenclatura de label executada na interface administrativa do plugin **Gerador de Posts (IA)**, visando simplificar a exibição visual do provedor Groq.

---

## 📖 Índice

1. [Resumo da Alteração](#-resumo-da-alteração)
2. [Arquivo Modificado](#-arquivo-modificado)
3. [Detalhamento Técnico das Modificações](#-detalhamento-técnico-das-modificações)
4. [Garantia de Integridade do Sistema](#-garantia-de-integridade-do-sistema)

---

## 👔 Resumo da Alteração

A correção realizada é de caráter estritamente visual (label/interface), substituindo o texto **"Groq (Llama / Grátis)"** por **"Groq (Grátis)"** na interface do painel. A alteração elimina redundâncias visuais na seleção de provedores de IA sem introduzir modificações funcionais de processamento ou chamadas de API.

---

## 📂 Arquivo Modificado

A alteração foi aplicada unicamente no arquivo de visualização (View) administrativa do plugin:
*   **[admin-ui.php](../admin-ui.php):** Estrutura HTML da tela do plugin no painel de administração do WordPress.

---

## 🎯 Detalhamento Técnico das Modificações

Três ocorrências do termo foram ajustadas em [admin-ui.php](../admin-ui.php):

1.  **Linha 115 (Painel de Geração de Texto Individual):**
    *   *Antes:* `<option value="groq">Groq (Llama / Grátis)</option>`
    *   *Depois:* `<option value="groq">Groq (Grátis)</option>`
2.  **Linha 426 (Painel do Agendador em Lote):**
    *   *Antes:* `<option value="groq">Groq (Llama / Grátis)</option>`
    *   *Depois:* `<option value="groq">Groq (Grátis)</option>`
3.  **Linha 580 (Aba de Configurações de API Keys):**
    *   *Antes:* `<span class="provider-label">Groq API (Llama / Grátis)</span>`
    *   *Depois:* `<span class="provider-label">Groq API (Grátis)</span>`

---

## 🏆 Garantia de Integridade do Sistema

A engenharia técnica assegura que:
*   **Valores e IDs Preservados:** O valor de envio do formulário (`value="groq"`) permaneceu inalterado. Dessa forma, as chaves de banco de dados (`gpg_groq_api_key`) e as validações AJAX do PHP continuam funcionando de forma idêntica.
*   **Zero Impacto Lógico:** Nenhuma regra de negócio, chamada HTTP de API, manipulação de modelo de texto, crop de imagem ou proteção de segurança sofreu modificações no controlador [gerador-posts-gemini.php](../gerador-posts-gemini.php).
*   **Alinhamento Estável:** A alteração permanece em total conformidade com a versão comercial estável **v1.0.0** de produção do plugin.
