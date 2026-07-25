# Registro de Decisões de Arquitetura (Architecture Decision Records - ADR) — v1.0.0

Este documento atua como o diário cronológico de decisões técnicas e de design do plugin **Gerador de Posts (IA)**. Ele detalha o contexto, os problemas identificados, as alternativas avaliadas, as soluções implementadas e seus respectivos impactos arquiteturais.

---

## 📖 Índice de Decisões (ADRs)

1. [ADR 01: Separação de CSS e JS (Separation of Concerns - SoC)](#adr-01-separação-de-css-e-js-separation-of-concerns---soc)
2. [ADR 02: Refatoração SRP para Decomposição de Controladores PHP](#adr-02-refatoração-srp-para-decomposição-de-controladores-php)
3. [ADR 03: Cache por Transients para Otimização de Performance e Queries](#adr-03-cache-por-transients-para-otimização-de-performance-e-queries)
4. [ADR 04: Proteção contra Server-Side Request Forgery (SSRF)](#adr-04-proteção-contra-server-side-request-forgery-ssrf)
5. [ADR 05: Validação Flexível de SSL em Requisições HTTP Externas](#adr-05-validação-flexível-de-ssl-em-requisições-http-externas)
6. [ADR 06: Enfileiramento Seletivo de Assets Administrativos](#adr-06-enfileiramento-seletivo-de-assets-administrativos)
7. [ADR 07: Centralização da Documentação Técnica (Developer Handbook)](#adr-07-centralização-da-documentação-técnica-developer-handbook)
8. [ADR 08: Padronização das Notas de Versionamento Histórico (v1.0.0)](#adr-08-padronização-das-notas-de-versionamento-histórico-v100)

---

## ADR 01: Separação de CSS e JS (Separation of Concerns - SoC)

*   **Status:** Aprovado
*   **Data:** 2026-07-22

### Contexto
O arquivo `admin-ui.php` continha a estrutura HTML de visualização do plugin misturada a blocos extensos de folhas de estilo CSS inline e scripts de comportamento JavaScript (jQuery) e chamadas AJAX de geração.

### Problema
O acoplamento dificultava a manutenção sintática, impedia a aplicação de linters nos assets front-end e violava as melhores práticas recomendadas de segurança do WordPress e de escaneabilidade do código.

### Alternativas Consideradas
1.  **Manter Inline (Rejeitado):** Facilitava alterações rápidas, mas impossibilitava o reaproveitamento de cache do navegador e a auditoria de código de forma limpa.
2.  **Mover para Arquivos Externos e Injetar Dados Nativos via PHP (Adotado):** Isolamento completo dos assets e carregamento via hook oficial, alimentando o JS com dados do PHP usando `wp_localize_script()`.

### Decisão Adotada
Toda a estilização foi extraída para o arquivo [admin.css](../assets/css/admin.css), o JS de comportamento foi isolado em [admin.js](../assets/js/admin.js) e ambos foram enfileirados utilizando `wp_enqueue_style()` e `wp_enqueue_script()`. Parâmetros do PHP (como URLs de endpoints, nonces e dados globais) foram mapeados utilizando a função nativa do WordPress `wp_localize_script()`.

### Justificativa
Alinhamento completo à filosofia de Separação de Preocupações (SoC), melhora na performance de carregamento da página por cache local dos arquivos de assets e maior facilidade de manutenção futura.

### Impacto
*   **Positivo:** Código de visualização `admin-ui.php` limpo e focado em marcação HTML. Assets otimizados e cacheados.
*   **Neutro:** Necessidade de referenciar variáveis de backend no JS através do objeto global de contexto gerado por `wp_localize_script()`.

---

## ADR 02: Refatoração SRP para Decomposição de Controladores PHP

*   **Status:** Aprovado
*   **Data:** 2026-07-22

### Contexto
O arquivo controlador `gerador-posts-gemini.php` concentrava toda a lógica de negócios em duas funções gigantes ("god functions"): `gpg_handle_generate_post()` e `gpg_handle_save_post()`.

### Problema
A legibilidade do código era baixa, dificultando a identificação de pontos de falha no pipeline (desde a chamada às APIs de IA até a persistência de mídias e metadados de SEO) e impedindo testes funcionais e unitários de escopo isolado.

### Alternativas Consideradas
1.  **Manter a Estrutura Monolítica (Rejeitado):** Alto risco de regressão em manutenções simples de provedores de API.
2.  **Decomposição em Funções Utilitárias com Responsabilidade Única (Adotado):** Quebrar o pipeline em subfunções menores e encapsuladas.

### Decisão Adotada
Refatorar as funções monolíticas em helpers de escopo cirúrgico e isolado:
*   `gpg_prepare_generation_data()`: Coleta, higieniza e valida as variáveis HTTP e inputs.
*   `gpg_build_generation_prompt()`: Centraliza a formatação e instruções estritas do prompt para IAs.
*   `gpg_call_[gemini/openai/groq]_api()`: Isola as chamadas assíncronas HTTP (`wp_remote_post`) específicas de cada provedor.
*   `gpg_download_and_process_images()`: Trata o download, WebP conversion, crops widescreen/retina e vinculação das mídias.
*   `gpg_sanitize_and_clean_content()`: Higieniza o HTML final, removendo quebras de linha redundantes do editor.
*   `gpg_save_rank_math_metadata()`: Responsável por persistir metadados Rank Math.

### Justificativa
Aderência ao Princípio de Responsabilidade Única (SRP), melhoria na legibilidade global e isolamento de erros (ex: falhas em APIs externas de imagem não corrompem o fluxo principal do post).

### Impacto
*   **Positivo:** Manutenibilidade e modularidade excelentes. Rastreamento facilitado de bugs operacionais no backend.

---

## ADR 03: Cache por Transients para Otimização de Performance e Queries

*   **Status:** Aprovado
*   **Data:** 2026-07-22

### Contexto
O prompt contextual das IAs requer a injeção da lista de links dos posts publicados recentemente para geração do link interno obrigatório. Além disso, o bloco "Veja Também" do post seleciona aleatoriamente artigos para linkagem no frontend.

### Problema
Fazer consultas SQL pesadas via `WP_Query` repetidas vezes no banco de dados para recuperar esses posts degradava a performance do servidor web durante requisições AJAX concorrentes, além de expor o site a gargalos de concorrência.

### Alternativas Consideradas
1.  **Queries Diretas Sem Cache (Rejeitado):** Impacto negativo na performance do servidor WordPress.
2.  **Utilização de Cache por Transients do WordPress (Adotado):** Persistência em banco/memória temporária por 12 horas.

### Decisão Adotada
Implementar transients com tempo de expiração padrão de **12 horas**:
*   `gpg_recent_posts_links_context` (links de posts para prompt de IA).
*   `gpg_veja_tambem_posts_pool` (posts válidos para renderização no Veja Também).
Implementar a invalidação ativa do cache atrelando a função `gpg_invalidate_posts_cache()` aos hooks nativos `save_post`, `deleted_post` e `trash_post`.

### Justificativa
Reduzir queries repetitivas no banco de dados a zero durante o ciclo de expiração, com a garantia de atualização imediata caso um novo post seja cadastrado ou excluído pelo administrador.

### Impacto
*   **Positivo:** Redução de queries SQL de links contextuais a uma única consulta por ciclo de 12 horas. Carregamento do painel mais ágil.

---

## ADR 04: Proteção contra Server-Side Request Forgery (SSRF)

*   **Status:** Aprovado
*   **Data:** 2026-07-22

### Contexto
Para processar as imagens widescreen e retina, o backend faz o download de arquivos temporários a partir das URLs retornadas pelas APIs de geração de IA (como OpenAI e Pollinations.ai) e realiza o sideload para a biblioteca de mídias.

### Problema
Aceitar URLs externas arbítrias para download HTTP no servidor pode permitir que um atacante aponte para servidores internos confidenciais do provedor de hospedagem (localhost ou IPs de redes locais), configurando uma falha grave de Server-Side Request Forgery (SSRF).

### Alternativas Consideradas
1.  **Download Direto sem Validação (Rejeitado):** Vulnerabilidade de segurança severa de nível crítico.
2.  **Validação de URL via Função de Core do WordPress (Adotado):** Passar qualquer URL externa por filtros estritos de segurança do WordPress antes de iniciar a conexão.

### Decisão Adotada
Toda URL externa de imagem capturada para sideload no método `gpg_upload_media_source()` passa obrigatoriamente pela validação do método nativo do core do WordPress `wp_http_validate_url()`. Se a URL for inválida ou apontar para escopos de rede locais restritos, o download é imediatamente abortado.

### Justificativa
Garantir conformidade com os padrões de segurança de desenvolvimento do WordPress.org e mitigar invasões laterais via SSRF.

### Impacto
*   **Positivo:** Proteção integral do ecossistema de hospedagem do cliente contra requisições maliciosas internas.

---

## ADR 05: Validação Flexível de SSL em Requisições HTTP Externas

*   **Status:** Aprovado
*   **Data:** 2026-07-22

### Contexto
Para manter a confidencialidade e integridade dos dados trafegados com as chaves de API, todas as chamadas externas a APIs de terceiros devem verificar e certificar o certificado SSL (`sslverify => true`).

### Problema
Ambientes locais de desenvolvimento (como LocalWP ou Docker locais) frequentemente não possuem certificados SSL instalados ou utilizam certificados autoassinados. Isso fazia com que o plugin falhasse e bloqueasse a comunicação com as APIs de IA com erro de SSL em computadores locais de homologação.

### Alternativas Consideradas
1.  **Desligar sslverify Permanentemente (Rejeitado):** Altíssimo risco em produção, abrindo brecha para ataques Man-in-the-Middle (MITM).
2.  **Desligamento Dinâmico de sslverify Baseado no Ambiente Local (Adotado):** Desligar SSL Verify somente em ambientes comprovadamente de teste local.

### Decisão Adotada
A propriedade `sslverify` nas chamadas `wp_remote_post` de texto e imagem é configurada dinamicamente:
```php
$sslverify = true;
$env = defined('WP_ENV') ? WP_ENV : 'production';
if ($env === 'local' || $env === 'development') {
    $sslverify = false;
}
```
Isso permite que desenvolvedores testem em máquinas locais sem contornar as validações e mantendo a verificação 100% ativa em servidores de produção públicos.

### Justificativa
Equilibrar a segurança operacional máxima de dados com a flexibilidade e agilidade em estações de desenvolvimento locais.

### Impacto
*   **Positivo:** Zero erros de certificado SSL autoassinado localmente e segurança rígida em servidores de produção.

---

## ADR 06: Enfileiramento Seletivo de Assets Administrativos

*   **Status:** Aprovado
*   **Data:** 2026-07-22

### Contexto
O plugin registra scripts e folhas de estilo CSS customizadas para modelar o painel administrativo. 

### Problema
Enfileirar arquivos de estilo e scripts JS globalmente em todas as telas da administração do WordPress pode gerar conflitos visuais com outros plugins ativos, além de degradar a performance global do painel de administração para o usuário.

### Alternativas Consideradas
1.  **Enqueue Global (Rejeitado):** Prática desencorajada pelo WordPress.org por poluir telas de terceiros.
2.  **Filtro Seletivo de Tela (Adotado):** Enfileirar apenas quando a tela ativa for a tela do plugin.

### Decisão Adotada
A função de enfileiramento `gpg_enqueue_admin_styles( $hook )` foi programada para inspecionar a variável de hook de tela administrativa. O enfileiramento prossegue apenas se `$hook` corresponder exatamente a `'toplevel_page_gerador-posts-gemini'`. Caso contrário, a execução é abortada de imediato.

### Justificativa
Garantir o isolamento estrito dos assets do plugin, mantendo o restante da área administrativa do WordPress limpa, performática e livre de colisões.

### Impacto
*   **Positivo:** Performance aprimorada e ausência de bugs visuais em páginas como "Posts", "Mídias" ou "Configurações Globais".

---

## ADR 07: Centralização da Documentação Técnica (Developer Handbook)

*   **Status:** Aprovado
*   **Data:** 2026-07-23

### Contexto
O ecossistema contava apenas com manuais simplificados orientados a usuários finais nos arquivos `README.md` e `README_EN.md` dentro da pasta do plugin.

### Problema
Engenheiros de software e agentes inteligentes integrados ao repositório não possuíam referências técnicas sobre a arquitetura de dados, fluxo de builds de release, decisões passadas (ADRs), fluxos de agentes inteligentes ou processos de desenvolvimento.

### Alternativas Consideradas
1.  **Injetar manuais técnicos no README.md (Rejeitado):** Poluiria a interface do usuário final, além de tornar o arquivo excessivamente longo.
2.  **Criar uma pasta de governança técnica exclusiva `/docs` (Adotado):** Estruturar uma wiki técnica interna focada estritamente no desenvolvedor.

### Decisão Adotada
Criar a pasta física `/docs` na raiz do repositório contendo cinco manuais com responsabilidades bem definidas:
*   `DEVELOPMENT_WORKFLOW.md`: Regras de desenvolvimento e QA.
*   `ARCHITECTURE.md`: Detalhes de infraestrutura e diagramas Mermaid.
*   `RELEASE_PROCESS.md`: Processos de empacotamento e publicação.
*   `AGENTS.md`: Catálogo de workflows de IA e auditorias de qualidade.
*   `DECISIONS.md`: Registro cronológico de ADRs.
*   `technical_documentation_report.md`: Sumário e índice consolidado da wiki.

### Justificativa
Separar a documentação comercial/operacional da documentação arquitetural de engenharia, promovendo manutenibilidade sustentável de longo prazo.

### Impacto
*   **Positivo:** Facilidade de onboarding para novos engenheiros e total clareza arquitetural no controle de versionamento Git.

---

## ADR 08: Padronização das Notas de Versionamento Histórico (v1.0.0)

*   **Status:** Aprovado
*   **Data:** 2026-07-23

### Contexto
Durante ciclos de homologação local e testes de estresse efetuados pela equipe de controle de qualidade, parte da documentação local e logs referenciou a versão `1.2.0` de forma provisória.

### Problema
Manter inconsistências de versão no Handbook oficial de lançamento poderia confundir auditores, clientes e novos engenheiros quanto à verdadeira versão de produção publicada, criando uma inconsistência de histórico no Git.

### Alternativas Consideradas
1.  **Manter referências mistas de v1.2.0 e v1.0.0 (Rejeitado):** Prejudicava a transparência histórica e governança de releases.
2.  **Padronização Absoluta sob a v1.0.0 (Adotado):** Tratar a versão lançada no Git e nos manuais exclusivamente como `v1.0.0`.

### Decisão Adotada
Ficou decidido (após aprovação no Socratic Gate) que todas as documentações e referências técnicas internas do Handbook serão padronizadas estritamente como a primeira versão oficial **v1.0.0**, refletindo o commit e a tag enviados com sucesso para o GitHub. Referências pretéritas de homologação local de QA a ramificações v1.2.0 são consideradas experimentais e não integram a governança.

### Justificativa
Manter consistência absoluta e conformidade total com o repositório remoto Git do GitHub e o ZIP de distribuição oficial fornecido.

### Impacto
*   **Positivo:** Histórico de desenvolvimento, tags e handbooks unificados sob a versão comercial real do produto (`v1.0.0`).
