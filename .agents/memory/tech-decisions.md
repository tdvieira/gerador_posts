# Registro de Decisões Técnicas (ADRs)

Este arquivo registra o diário cronológico de Decisões de Arquitetura de Software (Architecture Decision Records - ADRs) adotadas no desenvolvimento e evolução do plugin **Gerador de Posts (IA)**.

---

## 🏛️ Histórico Cronológico de ADRs

### ADR 01: Separação de Conceitos (SoC)
*   **Status:** Aprovado
*   **Contexto:** O painel administrativo do WordPress pode sofrer conflitos de scripts se estilos ou javascripts globais forem injetados em todas as telas de administração.
*   **Decisão:** Isolar estritamente os estilos administrativos em `assets/css/admin.css` e o comportamento dinâmico em `assets/js/admin.js`, registrando e enfileirando-os (`wp_enqueue_script`/`wp_enqueue_style`) apenas quando a página ativa no admin do CMS for a tela de configuração do plugin.
*   **Impacto:** Zero interferência de scripts no core do WordPress e tempo de carregamento otimizado.

### ADR 02: Responsabilidade Única (SRP) no Backend
*   **Status:** Aprovado
*   **Contexto:** O controlador principal PHP do plugin tendia a inflar com funções mistas de API, metaboxes, AJAX e renderização HTML, dificultando manutenções.
*   **Decisão:** Modularizar o código backend. O arquivo principal `gerador-posts-gemini.php` atua como controlador central de rotas e hooks, delegando a renderização visual para `admin-ui.php` e a lógica específica de persistência para helpers focados.
*   **Impacto:** Aumento da legibilidade e manutenibilidade do código.

### ADR 03: Cache por Transients do WordPress
*   **Status:** Aprovado
*   **Contexto:** Chamadas repetidas a APIs externas de IAs geram latência de rede e aumentam os custos de execução de tokens.
*   **Decisão:** Utilizar a API de Transients do WordPress para cachear as respostas estruturadas de posts gerados por 12 horas, implementando hooks ativos de invalidação no banco de dados quando as opções globais do plugin forem salvas.
*   **Impacto:** Economia de tokens de API e carregamento instantâneo de posts na fila.

### ADR 04: Proteção contra SSRF e SSL Verify Dinâmico
*   **Status:** Aprovado
*   **Contexto:** O download de imagens geradas por APIs externas pode expor o servidor a ataques de SSRF (Server-Side Request Forgery) se as URLs de origem não forem inspecionadas.
*   **Decisão:** Validar toda URL de imagem externa via helper `wp_http_validate_url` antes de iniciar o download. Adicionar parâmetro dinâmico para controle de verificação de SSL (`sslverify`) dependendo do ambiente local para permitir testes locais homologados.
*   **Impacto:** Proteção robusta contra requisições maliciosas internas e portabilidade de testes locais.

### ADR 05: Proteção de Nonces e Capabilities
*   **Status:** Aprovado
*   **Contexto:** Endpoints AJAX do WordPress sem validação de permissões podem ser invocados por usuários sem privilégios administrativos.
*   **Decisão:** Proteger todos os endpoints AJAX verificando explicitamente as permissões do usuário via `current_user_can('manage_options')` e validando a autenticidade da requisição por nonces estritos (`check_ajax_referer`).
*   **Impacto:** Segurança contra CSRF e escalonamento de privilégios.

### ADR 06: Spoofing de User-Agent
*   **Status:** Aprovado
*   **Contexto:** CDNs de provedores de IA protegidas por Cloudflare costumam rejeitar requisições de download com o User-Agent padrão do PHP, retornando erro HTTP 403.
*   **Decisão:** Injetar o User-Agent correspondente ao navegador Chrome moderno no cabeçalho das requisições de download do WordPress (`wp_remote_get`).
*   **Impacto:** Download contínuo de mídias de provedores externos sem falha de requisição.

### ADR 07: Pipeline Assíncrono no Cliente para Lote
*   **Status:** Aprovado
*   **Contexto:** A geração sequencial de posts em lote no backend estoura o limite de tempo de execução do PHP (*max_execution_time*).
*   **Decisão:** Controlar a esteira de processamento de lote no frontend via requisições AJAX assíncronas ordenadas gerenciadas por estado em Javascript.
*   **Impacto:** Geração ilimitada de posts sem travamentos no servidor Web.

### ADR 08: Centralização Física da Governança `.agents`
*   **Status:** Aprovado
*   **Contexto:** Pasta externa de agentes (`public/.agents/`) é volátil a resets locais de workspace do programador e dificulta o empacotamento.
*   **Decisão:** Migrar as regras normativas e a memória técnica permanente para dentro do repositório Git do plugin sob `.agents/`.
*   **Impacto:** Autossuficiência e versionamento da arquitetura assistida por IA.
