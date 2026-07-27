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

### ADR 09: Hardening Normativo e Congelamento (v2.2)
*   **Status:** Aprovado
*   **Contexto:** O incidente de perda de arquivos locais da infraestrutura assistida por IA devido à ausência de commits no Git local expôs a necessidade de blindar fisicamente a governança permanente e o histórico da arquitetura contra exclusões acidentais.
*   **Decisão:** Institucionalizar os Princípios Arquiteturais 13 (Persistence Validation Principle) e 14 (Incremental Validation Principle) no manual supremo de governança do projeto, e declarar oficialmente o ecossistema `.agents` v2.2 em estado **Architecture Frozen** (Congelamento Arquitetural), blindando a taxonomia de diretórios contra novas alterações estruturais.
*   **Impacto:** Segurança contra perdas físicas de metadados operacionais e estabelecimento de base permanente inalterada para evoluções lógicas subsequentes de código.

### ADR 10: Unificação e Consolidação do Pipeline de Releases
*   **Status:** Aprovado
*   **Contexto:** O pipeline de release original possuía processos manuais redundantes, whitelists rígidas de arquivos estáticos, riscos de quebra de codificação regional sob PowerShell 5.1 e falta de padronização na extração de Release Notes.
*   **Decisão:** Consolidar o processo de deploy em duas etapas operacionais exclusivas (`prepare_release.ps1` e `publish_release.ps1`), mantendo `build_release.ps1` como utilitário complementar interno. Estabelecer o `CHANGELOG.md` como Single Source of Truth para Release Notes extraídas de forma automática da pasta `docs/releases/`. Utilizar codificação UTF-8 explícita de ponta a ponta com validação round-trip, e implementar a auditoria de Working Tree por categorias arquiteturais dinâmicas (eliminando listas estáticas).
*   **Impacto:** Pipeline de deploy 100% robusto, escalável, livre de manutenções manuais a cada evolução do projeto e blindado contra corrupções de caracteres.

### ADR 11: Centralização de Arquivos Raiz do Empacotamento de Releases
*   **Status:** Aprovado
*   **Contexto:** No script original de build (`build_release.ps1`), a cópia dos arquivos produtivos da raiz para a pasta temporária de empacotamento era feita por múltiplas instruções `Copy-Item` manuais e isoladas. Isso provocou o esquecimento da inclusão de `readme.txt` no pacote ZIP de release gerado.
*   **Decisão:** Centralizar os arquivos da raiz permitidos para o ZIP sob uma coleção `$root_files` no `build_release.ps1`, realizando a cópia via loop síncrono `foreach`. Incluir explicitamente o `readme.txt` nessa lista.
*   **Impacto:** Garantia de inclusão permanente de `readme.txt` no pacote compactado WordPress distribuído, eliminando duplicação de instruções de cópia e riscos de divergência com o repositório.

### ADR 12: Compatibilidade e Resiliência na API do Plugin Update Checker (PUC)
*   **Status:** Aprovado
*   **Contexto:** Ocorreu um erro fatal de carregamento de classe do WordPress devido à chamada direta do método `setReadmeFilename()` no objeto do `GitHubApi` retornado pelo updater. Esse método não pertence à API pública suportada pela versão embarcada do PUC (v5.7).
*   **Decisão:** Remover a invocação direta do método `setReadmeFilename()` e encapsular quaisquer chamadas de recursos opcionais ou de versões futuras da biblioteca externa utilizando verificações ativas de compatibilidade do PHP (`method_exists()`).
*   **Impacto:** Eliminação definitiva do erro fatal na inicialização, preservando a portabilidade, a verificação automática de updates do plugin e o carregamento correto da janela "Ver detalhes" do WordPress.

### ADR 13: Desacoplamento de Categorias de Release via Configuração Externa (v2.0.5)
*   **Status:** Aprovado
*   **Contexto:** Originalmente, a validação de arquivos permitidos na Working Tree antes do deploy (função `Test-IsFileAllowed` em `publish_release.ps1`) baseava-se em Whitelists e categorias codificadas no próprio script procedural. Isso exigia alterações no código do pipeline sempre que novas pastas de documentação, novos relatórios ou novas configurações arquiteturais do projeto fossem criados, sob o risco de travar releases (como no bloqueio ocorrido na versão 2.0.5).
*   **Decisão:** Migrar as listas de arquivos aceitos e categorias funcionais da raiz e subdiretórios para um arquivo centralizado de configuração arquitetural em JSON (`.agents/config/pipeline-categories.json`). Modificar o script `publish_release.ps1` para ler dinamicamente e aplicar esta configuração por meio de correspondências exatas e wildcards, de acordo com o princípio *Configuration over Code*.
*   **Impacto:** Desacoplamento total do script de publicação das futuras evoluções estruturais do projeto. Toda nova categoria será adicionada exclusivamente no JSON, eliminando manutenções lógicas e evitando falhas na esteira de deploy.





