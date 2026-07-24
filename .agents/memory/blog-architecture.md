---
type: blog
created: 2026-06-05
updated: 2026-07-23
version: v1.0.0
---

# Memória de Configurações e Funcionalidades do Blog

Este arquivo contém o levantamento consolidado e atualizado das funções, comportamentos, lógica e arquitetura do **Blog TD Vieira Design** e de seu plugin proprietário **Gerador de Posts (IA)** após a homologação e publicação de sua versão oficial estável **v1.0.0**.

---

## 1. Informações Gerais do Site

- **Nome do Blog:** Blog TD Vieira Design
- **Descrição (Tagline):** *No Blog TD Vieira Design, você aprende de forma simples e leve como um site profissional pode transformar o seu negócio.*
- **URL Principal (Site/Home):** `https://blog.tdvieiradesign.com` (Nota: Algumas mídias e referências apontam para o domínio principal `https://tdvieiradesign.com`).
- **E-mail do Administrador:** `contato@tdvieiradesign.com`
- **Administrador Principal:** Thiago Vieira (Login: `admin`, E-mail: `thiagodvr@gmail.com`)

---

## 2. Tema e Construtor Visual

O blog utiliza uma estrutura baseada em design dinâmico controlado por construtor visual:

- **Tema Ativo:** Hello Elementor (Versão `3.4.4`). É um tema extremamente leve e minimalista ("starter theme"), projetado para delegar todo o design visual ao Elementor.
- **Construtores:** 
  - **Elementor:** Plugin base para edição e criação de páginas.
  - **Pro Elements:** Plugin alternativo que desbloqueia os recursos e widgets do *Elementor Pro* gratuitamente (GPL).

### Templates Criados no Elementor (Theme Builder)
Os layouts estruturais do blog não dependem de arquivos PHP do tema, mas de templates guardados no banco de dados (tipo de post `elementor_library`):
1. **Kit Padrão:** Armazena as configurações globais de design (paleta de cores, tipografia, espaçamento).
2. **Single Posts:** Template dinâmico para a exibição de artigos individuais do blog.
3. **Loop Posts:** Grade ou listagem personalizada para exibição dos posts em páginas de arquivo.
4. **Pesquisa:** Template para a exibição da página de resultados de busca.
5. **Loop Busca:** Grade de exibição específica para os resultados da pesquisa.
6. **Categorias & Tag:** Templates para as páginas de arquivos de categorias e tags.
7. **Elementor Error 404 #2237:** Template de página não encontrada (Erro 404).

---

## 3. Arquitetura de Plugins Instalados

Os plugins ativos dividem-se em pilares estratégicos de segurança, performance, SEO e customização:

### SEO (Otimização para Mecanismos de Busca)
- **Rank Math SEO (`seo-by-rank-math`):** Plugin principal utilizado para otimização de metadados, sitemaps e SEO on-page. (Nota: Há tabelas antigas do *Yoast SEO* no banco de dados, indicando uma migração no passado).

### Performance e Cache
- **WP Rocket (`wp-rocket`):** Plugin premium de cache de página, otimização de arquivos CSS/JS, lazy load e pré-carregamento.
- **Endurance Page Cache:** Sistema de cache a nível de servidor (comum em hospedagens Hostgator/Bluehost), ativo via arquivo `endurance-page-cache.php` na pasta `wp-content/mu-plugins`.

### Segurança e Antispam
- **WPS Hide Login (`wps-hide-login`):** Altera a URL padrão de login `/wp-admin` ou `/wp-login.php` para uma URL personalizada, protegendo contra ataques de força bruta.
- **Google Authenticator (`google-authenticator`):** Adiciona autenticação de dois fatores (2FA) para reforçar o login do administrador.
- **Advanced Google reCAPTCHA (`advanced-google-recaptcha`):** Proteção antispam usando reCAPTCHA do Google.
- **Antispam Bee (`antispam-bee`):** Filtro antispam eficiente e sem anúncios para a área de comentários do blog.

### Ferramentas de Desenvolvimento e Administração
- **Code Snippets (`code-snippets`):** Permite adicionar e rodar códigos PHP/JS/CSS diretamente pelo painel do WordPress, eliminando a necessidade de editar o arquivo `functions.php` do tema diretamente.
- **Loco Translate (`loco-translate`):** Facilita a tradução de strings e termos de temas e plugins diretamente pelo painel.
- **UpdraftPlus (`updraftplus`):** Utilizado para automação de backups (arquivos e banco de dados) e envio para nuvem.

---

## 4. Snippets de Código Personalizados (Lógica PHP)

Os seguintes snippets de código PHP estão cadastrados no sistema (via plugin *Code Snippets*):

### Snippets Ativos (Funcionamento Atual)

#### A. Remover o campo URL dos comentários (ID 5)
- **Objetivo:** Diminuir consideravelmente o spam nos comentários, retirando o campo opcional de preenchimento do site do usuário.
- **Gatilho (Hook):** Filtro `comment_form_default_fields`
- **Código:**
  ```php
  add_filter('comment_form_default_fields', 'unset_url_field');
  function unset_url_field($fields){
      if(isset($fields['url']))
         unset($fields['url']);
         return $fields;
  }
  ```

#### B. Limitação da busca para posts (ID 6)
- **Objetivo:** Garante que a busca interna no site (frontend) traga apenas artigos do blog (`post`), excluindo páginas institucionais, templates ou mídias.
- **Gatilho (Hook):** Filtro `pre_get_posts` (apenas quando não é a área administrativa).
- **Código:**
  ```php
  if (!is_admin()) {
      function wpb_search_filter($query) {
          if ($query->is_search) {
              $query->set('post_type', 'post');
          }
          return $query;
      }
      add_filter('pre_get_posts','wpb_search_filter');
  }
  ```

#### C. Font Display (ID 7)
- **Objetivo:** Melhora a pontuação de performance no Google PageSpeed ao forçar o comportamento `font-display: swap` em fontes customizadas do Elementor. Isso faz com que o texto seja legível usando fontes padrão do sistema enquanto as fontes personalizadas carregam.
- **Gatilho (Hook):** Filtro `elementor_pro/custom_fonts/font_display`
- **Código:**
  ```php
  add_filter( 'elementor_pro/custom_fonts/font_display', function( $current_value, $font_family, $data ) {
      return 'swap';
  }, 10, 3 );
  ```

---

## 5. Plugin Personalizado: Gerador de Posts (IA) v1.0.0

O ecossistema conta com o plugin customizado [gerador-posts-gemini](../../), desenvolvido para automatizar a criação de artigos de blog otimizados para SEO e com estética visual avançada baseada em inteligência artificial.

### 5.1 Visão Geral da Arquitetura e Assets
O plugin está modularizado em camadas físicas em conformidade com as diretrizes do WordPress:
1.  **[gerador-posts-gemini.php](../../gerador-posts-gemini.php):** Controlador principal (Controller) do backend PHP que registra hooks do WordPress, orquestra endpoints de chamadas AJAX, valida permissões, interage com APIs externas e com transients de cache.
2.  **[admin-ui.php](../../admin-ui.php):** Estrutura HTML/Visual (View) da tela de administração do plugin no WordPress.
3.  **[admin.css](../../assets/css/admin.css) e [admin.js](../../assets/js/admin.js):** Assets de estilos e comportamentos operacionais enfileirados de forma seletiva apenas na tela administrativa do plugin para evitar conflitos no core do CMS.

### 5.2 Provedores e Modelos de IA Suportados

#### Provedores de Texto (Escrita e Metadados)
- **Google Gemini (Padrão):** Modelos `gemini-3.5-flash` (Padrão), `gemini-3.1-pro` e `gemini-3.1-flash-lite`. (Retornos estruturados em JSON via `responseSchema` com limite de `8192` tokens de saída).
- **OpenAI:** Modelos `gpt-4o`, `gpt-4o-mini` (Padrão) e `o1-mini`. (Retornos em JSON via `response_format` -> `json_object` com limite de `4096` tokens de saída).
- **Groq:** Modelos `llama-3.3-70b-versatile` (Padrão), `llama-3.1-8b-instant` e `gemma2-9b-it`. (Retornos estruturados com limite de `4096` tokens de saída).

#### Provedores de Imagem (Widescreen 16:9)
- **OpenAI:** Modelos `dall-e-3` (resolução widescreen `1792x1024` pixels) e `dall-e-2` (resolução quadrada `1024x1024`).
- **Google Imagen:** Modelo `imagen-4` (resolução widescreen 16:9).
- **Flux (Pollinations.ai / Grátis):** Modelos `flux` (Foto Realista) e `flux-anime` (Estilo Ilustrativo/3D). Exige que a chave da API esteja cadastrada nas Configurações.
- **Puter (Flux via Puter.js / Grátis):** Renderiza imagens base64 no frontend via integração com `puter.js` em Javascript.

---

### 5.3 Regras de Conteúdo, SEO e Formatação (Lógica de Negócios)

#### Título do Artigo
- **Tamanho:** Limite estrito de no mínimo **65** e no máximo **70** caracteres.
- **Reescrita Inteligente por IA:** Caso o título gerado exceda 70 caracteres, o backend executa uma chamada de reescrita inteligente para encurtá-lo. Se persistir, o helper PHP `gpg_limit_title_length()` executa um corte no último espaço em branco antes do limite de caracteres.

#### URL Amigável (Slug)
- **Tamanho:** Máximo de **75** caracteres. Em minúsculas, sem caracteres especiais, contendo obrigatoriamente a palavra-chave de foco (verificada em tempo real pelo JS no front-end).

#### Meta Descrição (Rank Math) e Excerpt
- **Meta Descrição:** Máximo estrito de **138** caracteres contendo a palavra-chave. Persistida diretamente na tabela do Rank Math SEO.
- **Resumo do Artigo (Excerpt):** Entre **160** e **175** caracteres de comprimento contendo a palavra-chave.

#### Inserção Adaptável de Palavra-chave e Destaques
- A palavra-chave é injetada naturalmente na introdução, corpo (3 a 5 vezes) e subtítulos H2/H3, sendo permitidas flexões gramaticais sem forçar negritos sistemáticos.
- **Negritos Econômicos:** Devem ser destacados com `<strong>` apenas termos cruciais que representem o núcleo da ideia (limite de 1 ou 2 expressões curtíssimas por seção), sendo proibido negritar frases ou parágrafos inteiros.

#### Links de Referência no Texto (Estrutura de Links Rígida)
- **Obrigatoriedade e Limites:** É obrigatório incluir exatamente **1 link interno** (fornecido na lista de recentes do blog) e exatamente **1 link externo** relevante de autoridade em português do Brasil (fallback Wikipédia).
- **Validação contra Erro 404:** O backend executa uma requisição rápida (HEAD) para cada link. Se retornar erro 404, a tag `<a>` é convertida em texto simples.
- **Linkagem de Marca:** Menções à "TD Vieira Design" são convertidas em link strong para `https://tdvieiradesign.com` (não entra na contagem limite).

#### Estrutura de Imagens e Placeholders
- Placeholders `[IMAGE_1_PLACEHOLDER]` e `[IMAGE_2_PLACEHOLDER]` são substituídos por figuras WebP (qualidade 90) processadas no WordPress:
  - **Imagem de Destaque (Thumbnail):** Crop de `1408x474` pixels.
  - **Imagem do Corpo 1 e 2:** Crop de `1408x792` pixels (Retina widescreen).
- **Spoofing de User-Agent:** O plugin injeta dinamicamente o User-Agent do Chrome no download de mídias externas para evitar bloqueio 403 das CDNs protegidas pelo Cloudflare.

#### TOC e Bloco "Veja Também"
- **Sumário (TOC):** Posicionado após o segundo parágrafo. Higieniza subtítulos físicos redundantes que façam menção a "Introdução".
- **Veja Também:** Busca aleatoriamente 3 posts ativos publicados do blog, estruturando uma lista com imagens e títulos. Controla o espaçamento estritamente via margem CSS de `85px` superior para evitar sobreposição.

---

### 5.4 Processamento em Lote (Pipeline Visual)
A aba **Agendador em Lote** permite enfileirar posts a serem gerados sequencialmente in um pipeline assíncrono controlado pelo Javascript do admin, cobrindo visualmente 9 etapas ordenadas:
`Interpretação (INTERP) → Segurança (SEG) → Escrita (ESCR) → Revisão (REV) → Prompts de Imagens (PROMPT) → Geração/Crop de Imagens (IMG) → Gravação no WP (PUB) → SEO Final (INLINE) → Concluído 100%`.

---

### 5.5 Banco de Dados Local
- **Prefixo das Tabelas:** `wpgj_` (Ex: `wpgj_posts`, `wpgj_options`, `wpgj_snippets`, `wpgj_postmeta`).
- **Banco de Dados Local:** Nome `local`, usuário `root`, senha `root`, rodando em `localhost` (configurado em [wp-config.php](../../../../../wp-config.php)).
- **Arquivo de Backup:** Backup SQL completo disponível na raiz pública do LocalWP em [backup.sql](../../../../../backup.sql).

---

### 5.6 Governança Documental Técnica (Developer Handbook)
Toda a documentação técnica consolidada e os relatórios de QA residem estritamente sob o repositório Git na pasta de documentação do plugin:
*   [Developer Handbook Docs](../../docs/)
