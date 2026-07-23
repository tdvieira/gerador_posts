# Guia de Resolução de Problemas (Troubleshooting Guide) — v1.0.0

Este guia atua como a base de conhecimento consolidada para diagnóstico, contenção e resolução de falhas operacionais ou inconsistências encontradas no plugin **Gerador de Posts (IA)** em ambiente de desenvolvimento local ou produção.

---

## ❓ Quando consultar este documento?

> [!IMPORTANT]
> Consulte este documento nas seguintes situações:
> *   Quando uma requisição AJAX do plugin no painel do WordPress falhar com mensagens de erro ou códigos de resposta HTTP inesperados (ex: 403, 500, 503).
> *   Se a geração de imagens ou download de mídias externas falhar repetidamente ou retornar imagens quebradas.
> *   Ao encontrar erros de conexão de APIs, problemas de cache não atualizado ou avisos de segurança/certificados SSL no LocalWP.

---

## 📖 Índice

1. [Ambiente LocalWP e Banco de Dados](#-ambiente-localwp-e-banco-de-dados)
2. [Integração e Timeouts de APIs de IA](#-integração-e-timeouts-de-apis-de-ia)
3. [Segurança (Nonces, Capabilities e SSRF)](#-segurança-nonces-capabilities-e-ssrf)
4. [Processamento de Mídia e Imagens (Crops, WebP, 403 Forbidden)](#-processamento-de-mídia-e-imagens-crops-webp-403-forbidden)
5. [SEO e Metadados do Rank Math](#-seo-e-metadados-do-rank-math)
6. [Sistema de Cache por Transients](#-sistema-de-cache-por-transients)
7. [Documentos relacionados](#-documentos-relacionados)

---

## 💻 Ambiente LocalWP e Banco de Dados

### 1. Erros de Conexão com Banco de Dados ou Prefixo Incorreto
*   **Sintoma:** Ao ativar o plugin ou restaurar o dump, ocorrem erros fatais do PHP indicando que tabelas do WordPress (ex: `wpgj_options`) não foram encontradas.
*   **Causa:** O prefixo das tabelas no banco de dados importado é `wpgj_`, mas a instalação do LocalWP foi inicializada com o prefixo padrão do WordPress (`wp_`).
*   **Diagnóstico:** Inspecione o banco de dados no Adminer do LocalWP e verifique se as tabelas possuem o prefixo `wpgj_`.
*   **Solução:** Abra o arquivo [wp-config.php](../../../../wp-config.php) localizado na raiz pública do LocalWP e altere a variável de prefixo:
    ```php
    $table_prefix = 'wpgj_';
    ```
*   **Prevenção:** Sempre revise a correspondência do prefixo no `wp-config.php` após importar o arquivo `backup.sql`.

---

## 🔌 Integração e Timeouts de APIs de IA

### 1. Falha de SSL Verify em Ambiente Local
*   **Sintoma:** Ocorre erro de rede HTTP indicando certificado autoassinado inválido ao tentar chamar as APIs do Gemini, OpenAI ou Groq a partir do LocalWP.
*   **Causa:** O LocalWP utiliza certificados locais autoassinados, fazendo com que o core do WordPress rejeite a requisição externa caso a propriedade `sslverify` esteja configurada como `true`.
*   **Diagnóstico:** Verifique nos logs de depuração do PHP se constam erros como `cURL error 60: SSL certificate problem: self signed certificate`.
*   **Solução:** Certifique-se de que a constante `WP_ENV` esteja definida como `'local'` ou `'development'` no arquivo `wp-config.php` para que o plugin desative condicionalmente a verificação SSL no ambiente de homologação:
    ```php
    define('WP_ENV', 'local');
    ```
*   **Prevenção:** Nunca defina `WP_ENV` como `'production'` em máquinas físicas de desenvolvimento local.
*   **Referências Cruzadas:** Consulte o [tech-decisions.md](../../.agents/memory/tech-decisions.md) (ADR 05) para detalhes sobre a decisão de validação flexível de SSL.

### 2. Erros de Limite de Cota (HTTP 429) ou Timeout (HTTP 503 / 504)
*   **Sintoma:** O painel exibe um alerta de erro de timeout ou lentidão excessiva durante a geração assíncrona do post.
*   **Causa:** Chaves de API locais estouraram a cota de requisições de teste gratuita (Rate Limits) ou a API do provedor (ex: Google Gemini) está congestionada.
*   **Diagnóstico:** Inspecione a resposta da chamada AJAX na aba Network do navegador (F12) e avalie o código de retorno da API.
*   **Solução:** O plugin possui tratamento de resiliência nativo. Se ocorrer um timeout de rede, o painel exibirá uma mensagem amigável solicitando que o usuário clique em gerar novamente (Retry). Em caso de limite de cota (429), insira uma nova chave de API com saldo ativo na aba **Configurações**.
*   **Prevenção:** Desenvolvedores devem monitorar o painel das APIs dos provedores para garantir saldo ativo nas contas de teste.

---

## 🔒 Segurança (Nonces, Capabilities e SSRF)

### 1. Falhas de Token Expirado (HTTP 403 Forbidden nas Chamadas AJAX)
*   **Sintoma:** Ao clicar em gerar artigo, salvar post ou gerar imagem, a interface exibe erro de autenticação ou erro genérico 403.
*   **Causa:** O Nonce de segurança gerado pelo WordPress expirou devido a um longo tempo de inatividade da página do plugin aberta no navegador (o tempo de vida padrão do Nonce do WP é de 24 horas).
*   **Diagnóstico:** Verifique se o console do navegador exibe erro HTTP 403 para endpoints como `gpg_handle_generate_post`.
*   **Solução:** Recarregue a página do plugin no navegador (`F5` ou `Ctrl+F5`) para forçar o WordPress a gerar e injetar um novo Nonce de segurança ativo.
*   **Prevenção:** Evite manter a aba administrativa do plugin aberta por mais de 24 horas consecutivas sem recarregá-la.

### 2. URL de Imagem Rejeitada com Mensagem de SSRF
*   **Sintoma:** A imagem de IA é gerada com sucesso pela API externa, mas o plugin exibe um erro de segurança e não realiza o download para a biblioteca de mídias.
*   **Causa:** A URL fornecida pelo provedor de IA aponta para um IP da rede local interna (intranet) ou contém caracteres de controle suspeitos, sendo barrada pelo filtro de proteção contra SSRF.
*   **Diagnóstico:** Inspecione a URL no log do PHP e veja se ela falha na validação de `wp_http_validate_url()`.
*   **Solução:** Certifique-se de que a API de imagem selecionada (como Pollinations ou OpenAI) esteja retornando links públicos válidos e que o servidor local possua acesso à internet externa para resolver as conexões DNS das CDNs de imagem.
*   **Prevenção:** Não utilize provedores locais improvisados de imagens que operem sob IPs privados na rede interna.

---

## 🖼️ Processamento de Mídia e Imagens (Crops, WebP, 403 Forbidden)

### 1. Erro de Download HTTP 403 Forbidden no Sideload de Imagens
*   **Sintoma:** A IA gera o link da imagem, mas o plugin falha em baixá-la, gerando placeholders vazios ou mantendo o post sem thumbnail.
*   **Causa:** A CDN de imagens do provedor de IA (ex: Cloudflare ou OpenAI CDNs) bloqueia requisições HTTP que utilizam o User-Agent padrão do WordPress (`WordPress/X.Y.Z`).
*   **Diagnóstico:** Inspecione o log do PHP e procure por falhas em `download_url()` com retorno HTTP 403.
*   **Solução:** O plugin implementa nativamente um spoofing de User-Agent via filtro `http_request_args` no método `gpg_upload_media_source()`. Garanta que a função helper esteja recebendo o filtro ativo e que nenhuma outra função no WordPress local sobrescreva o User-Agent global para valores bloqueáveis.
*   **Prevenção:** Manter o controlador PHP com o filtro de User-Agent intacto e ativo.

### 2. Imagens Sem Crop Retina (Distorções ou Tamanhos Incorretos)
*   **Sintoma:** Imagens salvas no post ficam quadradas ou distorcidas no layout do frontend do blog.
*   **Causa:** O servidor local não possui as extensões PHP de processamento de imagem (`gd` ou `imagick`) ativas, impedindo a execução dos crops widescreen definidos pelo plugin (`1408x474` e `1408x792`).
*   **Diagnóstico:** Verifique se as funções de imagem do PHP estão disponíveis executando `phpinfo()` no LocalWP ou inspecionando erros de funções indefinidas de imagem no log do PHP.
*   **Solução:** No painel do LocalWP, certifique-se de que o site esteja rodando sob a configuração correta de PHP e que a biblioteca de imagens esteja ativa no servidor. Caso contrário, reinstale ou atualize o ambiente PHP no LocalWP.

---

## 🔍 SEO e Metadados do Rank Math

### 1. Palavras-chave e Descrições Não Injetadas no Rank Math
*   **Sintoma:** O post é gerado e publicado, mas o painel lateral do Rank Math SEO na edição do post aparece sem a palavra-chave de foco preenchida ou sem a meta descrição.
*   **Causa:** O plugin **Rank Math SEO** não está instalado ou ativado no WordPress de homologação, impedindo a escrita de metadados correspondentes nas tabelas meta do banco.
*   **Diagnóstico:** Vá em **Plugins > Plugins Instalados** e verifique se o Rank Math SEO está ativo.
*   **Solução:** Instale e ative o plugin oficial Rank Math SEO. O plugin do Gerador de Posts orquestra as chaves de metadados de forma automática assim que as tabelas de destino estiverem disponíveis no banco.
*   **Prevenção:** Certifique-se de que o Rank Math SEO faça parte do checklist de ativação de plugins do bootstrap do ambiente.

---

## 💾 Sistema de Cache por Transients

### 1. Posts Recentes ou Caixa "Veja Também" Desatualizados
*   **Sintoma:** Um post recém-publicado não aparece nas opções de linkagem interna de geração da IA ou a caixa visual "Veja Também" do artigo exibe dados obsoletos de posts excluídos.
*   **Causa:** O cache por transients do WordPress expirou ou a invalidação de cache falhou por bloqueio de hooks de banco.
*   **Diagnóstico:** Verifique se os transients `gpg_recent_posts_links_context` e `gpg_veja_tambem_posts_pool` existem no banco de dados e confira a sua data de expiração.
*   **Solução:** Publique, atualize, lixe ou delete qualquer post de teste no painel administrativo do WordPress. O plugin interceptará essa ação por meio dos hooks associados (ex: `save_post`) e disparará o método `gpg_invalidate_posts_cache()`, limpando instantaneamente todos os transients ativos para forçar a reconstrução na próxima consulta.
*   **Prevenção:** Garantir que nenhuma rotina personalizada no `functions.php` ou em outros snippets desative os hooks padrões de salvamento de posts do WordPress.
*   **Referências Cruzadas:** Para detalhes sobre o ciclo do transient cache, consulte o [ARCHITECTURE.md](./ARCHITECTURE.md) (Seção 7 e Diagrama C).

---

## 🔗 Documentos relacionados

Para navegar e aprofundar-se nos fluxos de qualidade e engenharia do Handbook, consulte:
*   **[DEVELOPMENT_WORKFLOW.md](./DEVELOPMENT_WORKFLOW.md):** Padrões de código e controle de depuração local.
*   **[ARCHITECTURE.md](./ARCHITECTURE.md):** Comunicação de componentes e fluxos de dados detalhados.
*   **[BOOTSTRAP_LOCALWP.md](./BOOTSTRAP_LOCALWP.md):** Manual completo de preparação de ambiente LocalWP.
*   **[MAINTENANCE_GUIDE.md](./MAINTENANCE_GUIDE.md):** Fluxo de manutenções e correções futuras de bugs.
