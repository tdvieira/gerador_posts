# Gerador de Posts (IA) — Plugin WordPress

O **Gerador de Posts (IA)** é um plugin WordPress de nível profissional projetado para automatizar a criação, otimização e publicação de artigos de alta fidelidade visual e estrutural utilizando múltiplos provedores de Inteligência Artificial. Ele gera artigos estruturados com sumários (TOC), imagens em resoluções Retina e WebP, links contextuais dinâmicos e integração nativa com o Rank Math SEO.

---

## 🎯 Objetivo

O objetivo principal deste plugin é reduzir drasticamente o tempo necessário para criar e formatar conteúdos otimizados para blogs WordPress, integrando a escrita de artigos por inteligência artificial diretamente à biblioteca de mídias e painel do CMS, mantendo um alto padrão editorial e de SEO.

---

## 💎 Principais Benefícios

*   **Economia de Tempo:** Reduz o fluxo de escrita, formatação e publicação de um post completo de horas para menos de 2 minutos.
*   **SEO On-page Impecável:** Metadados estruturados, links limpos e integração direta com o Rank Math que auxiliam no ranqueamento no Google.
*   **Qualidade Visual Superior:** Garante que todas as imagens fiquem consistentes, no formato moderno WebP e com alta definição para telas Retina.
*   **Segurança e Resiliência:** Proteção contra downloads maliciosos e tratamento robusto de timeouts caso as APIs externas oscilem.

---

## 🔄 Fluxo de Geração de Conteúdo

O fluxo abaixo ilustra como o plugin orquestra a inteligência artificial, validações de segurança e gravação de mídias:

```mermaid
graph TD
    A[Input do Usuário: Tema & Palavras-chave] --> B[Geração do Artigo via IA: Texto, Prompts, Slug, Excerpt]
    B --> C[Validação Automática de Links: Remoção de URLs 404]
    C --> D[Geração de Imagens via IA/Puter]
    D --> E[Processamento de Mídia: Conversão para WebP & Crop Retina 1408px]
    E --> F[Publicação/Gravação: Inserção de Post, Imagens & Rank Math SEO]
```

---

## 🚀 Principais Funcionalidades

*   **Orquestração de Múltiplas APIs de IA:** Suporte nativo para **Google Gemini** (Gemini 3.5 Flash, Gemini 3.1 Pro/Flash-Lite), **OpenAI** (GPT-4o, GPT-4o-mini, o1-mini) e **Groq Cloud** (Llama 3.3 70B, Llama 3.1 8B, Gemma 2).
*   **Geração Inteligente de Imagens:** Criação de imagens widescreen (16:9) utilizando **OpenAI DALL-E 3**, **Google Imagen 4** (via predict) e **Flux/Flux-Anime** (via Pollinations.ai ou SDK frontend Puter.js).
*   **Processamento Otimizado de Mídia:**
    *   **Conversão para WebP:** Conversão automática de imagens externas ou payloads Base64 para o formato `.webp` com nível de qualidade em 90%.
    *   **Suporte a Monitores Retina:** Redimensionamento e crop de imagens gerando dimensões de alta definição (Destaque em `1408x474px` e imagens do corpo do post em `1408x792px`).
*   **Otimização SEO de Alta Performance:** Injeção automática das palavras-chave de foco e metadados de descrição na tabela `wp_postmeta` integrada nativamente com as chaves oficiais do **Rank Math SEO**.
*   **Limpeza & Sanitização de Conteúdo:** Algoritmos dedicados via expressões regulares para remoção de quebras extras de parágrafos, ajuste de placeholders, sumários de conteúdo sem cabeçalhos órfãos e remoção automática de links com resposta HTTP 404 (páginas inexistentes).
*   **Sistema de Cache por Transients:** Redução de consultas repetidas ao banco de dados MySQL via armazenamento temporário por 12 horas dos links contextuais e do pool de posts recomendados (caixa "Veja Também" com array aleatório no PHP).
*   **Segurança Avançada (Zero Trust):**
    *   **Prevenção contra SSRF:** Validação de URLs externas de download de imagens via `wp_http_validate_url()` para evitar varredura de intranet.
    *   **Validação Condicional de SSL:** Verificação de segurança SSL (`sslverify`) obrigatória em ambientes de homologação e produção, desativada apenas em hosts de desenvolvimento local (`local` e `development`).
    *   **Controle de Acesso:** Proteção estrita contra CSRF por meio de Nonces e Capabilities (`manage_options`) em todos os endpoints AJAX.

---

## 📐 Arquitetura e Estrutura de Diretórios

O plugin segue uma arquitetura modularizada, separando as camadas de estilos, lógica interativa de frontend, templates HTML da view e controladores PHP do backend:

```
gerador-posts-gemini/
├── assets/
│   ├── css/
│   │   ├── admin.css       # Estilos visuais Outfit da interface administrativa
│   │   └── frontend.css    # Estilizações aplicadas aos artigos (Veja Também/TOC)
│   └── js/
│       └── admin.js        # Lógica de controle AJAX, abas e pipeline de progresso
├── admin-ui.php            # View/Interface HTML do painel administrativo
└── gerador-posts-gemini.php # Controlador principal do plugin (Hooks, AJAX, APIs)
```

---

## 🛠️ Requisitos de Instalação

*   **WordPress:** 5.8 ou superior (Testado até WordPress 7.0.2).
*   **PHP:** 8.0 ou superior (Testado no PHP 8.2.29) com extensões `gd` ou `imagick` ativas para processamento de imagem.
*   **Dependências:** Plugin **Rank Math SEO** ativo (opcional, para injeção automática de metadados).

---

## ⚙️ Configuração

1.  Acesse o painel do WordPress, vá em **Plugins > Adicionar Novo** e faça o upload do arquivo ZIP do plugin.
2.  Ative o plugin. Ao ativar, as 8 categorias oficiais do blog serão criadas automaticamente se não existirem.
3.  Vá em **Posts > Gerador de Posts** no menu lateral.
4.  Acesse a aba **Configurações** e insira as suas chaves de API correspondentes (Gemini, OpenAI, Groq ou Puter Token). As chaves serão salvas de forma segura e mascaradas na interface.

---

## 🚀 Utilização

### Geração Individual
1.  Insira o tema do artigo no campo principal.
2.  Selecione o Provedor de Texto e o Modelo desejado.
3.  Forneça as palavras-chave (separadas por vírgula).
4.  Defina o tom de escrita, comprimento do artigo e categoria de publicação.
5.  Clique em **Gerar Artigo**.
6.  Acompanhe a geração assíncrona. Ao final, a interface de pré-visualização carregará o título, slug sugerido, corpo formatado, excerpt e prompts sugeridos de imagem.
7.  Clique em **Gerar Imagem 1** (Destaque) e **Gerar Imagem 2** (Corpo).
8.  Configure a data de publicação e o status (Rascunho, Agendado ou Publicado) e clique em **Salvar Post**.

### Geração em Lote
1.  Acesse a aba **Agendador em Lote**.
2.  Selecione os provedores de texto/imagem e insira uma lista de temas (um por linha).
3.  Clique em **Iniciar Lote**. Acompanhe visualmente o pipeline sequencial de progresso por tema (Interpretação, Segurança, Escrita, Revisão, Imagens, Publicação e Conclusão).

## 📂 Estrutura do Projeto

Para manter a organização do código-fonte e do acervo documental de desenvolvimento, o repositório é segmentado da seguinte forma:

*   **`build/`**: Pasta reservada para conter os artefatos de build consolidados (como o pacote ZIP de distribuição oficial do plugin). Nenhum arquivo ZIP de distribuição deve residir na raiz.
*   **`docs/`**: Subpasta oficial contendo toda a documentação técnica, manuais operacionais e relatórios de conformidade e qualidade do plugin (como arquitetura, governança, testes de QA, relatórios de releases e históricos). É o repositório documental público e final do projeto.
*   **`.agents/`**: Pasta privada contendo regras internas, esquemas de memória persistente, ganchos de automação de tarefas e prompts de uso exclusivo do framework **Antigravity** e dos agentes de IA durante a fase de desenvolvimento técnico do plugin. Não compõe a distribuição de produção.
*   **`assets/`**: Contém folhas de estilo css e JavaScript administrativo do painel.
*   **`includes/`**: Armazena as classes PHP divididas de forma modular e seguindo o autoloader PSR-4 (`Core`, `AI`, `Controllers`, `Services`, `Providers`).
*   **`vendor/`**: Guarda dependências necessárias ao plugin em produção, como a biblioteca `plugin-update-checker` para atualizações automáticas via GitHub.

## 📄 Dualidade de Documentação (README.md vs readme.txt)

O projeto adota uma arquitetura de documentação com propósitos distintos para otimizar o consumo pelo repositório GitHub e pelo ecossistema do CMS:
*   **`README.md` (Destinado ao GitHub):** Focado nos desenvolvedores e operadores. Contém guias de infraestrutura, arquitetura de pipeline, diagramas de fluxo de geração, instruções de dependência e governança do projeto.
*   **`readme.txt` (Destinado ao WordPress):** Formatado no padrão oficial do WordPress.org e utilizado pelo mecanismo do Plugin Update Checker (PUC). Serve como fonte oficial para fornecer as informações e descrições na modal "Ver detalhes" de atualizações do painel administrativo do WordPress.

---

## 🚀 Release

O pacote oficial de distribuição e instalação do plugin é gerado em **`build/gerador-posts-gemini.zip`**.

### Pipeline Oficial de Release
A preparação, empacotamento e publicação de novas versões são estruturados por meio do Pipeline Oficial de Release, composto por apenas **duas etapas operacionais ativas** conduzidas pelo operador. O script de build (`build_release.ps1`) permanece exclusivamente como uma ferramenta técnica complementar de manutenção e reconstrução manual do ZIP, sem compor o fluxo ativo direto. Toda a interface de console e blocos de status utilizam estritamente codificação de texto ASCII sem dependência de caracteres Unicode (usando marcadores `[OK]`, `[INFO]`, `[WARN]` e `[ERRO]`), finalizando o fluxo com o painel unificado "RESUMO FINAL DA RELEASE" de 10 chaves de status alinhadas:

*   **Etapa 1: Prepare Release (Preparação e Build):** Sincronização automatizada de metadados de versão, varredura de consistência, extração dinâmica das seções `## Resumo para Release` de todos os relatórios técnicos correntes em `docs/releases/*.md` para consolidar o `CHANGELOG.md` sem textos fixos, seguido pelo empacotamento automático e validação estrutural obrigatória do arquivo ZIP:
    ```powershell
    powershell -ExecutionPolicy Bypass -File scripts/prepare_release.ps1 -Version X.Y.Z
    ```
*   **Etapa 2: Publish Release (Publicação e Sincronização):** Publicação automatizada de commits e tags Git, envio para o repositório remoto, criação da release no GitHub com upload do ZIP de produção e publicação automática das Release Notes (extraídas do `CHANGELOG.md` para a versão corrente como única fonte de verdade). Adiciona automaticamente ao Git os arquivos alterados através de um mapeamento dinâmico baseado em categorias arquiteturais (documentações, scripts do pipeline, manifesto principal, bootstrap e o subsistema de updater), eliminando manutenções manuais em listas estáticas de arquivos, enquanto barra rigidamente qualquer modificação estranha ao escopo de release por segurança:
    ```powershell
    powershell -ExecutionPolicy Bypass -File scripts/publish_release.ps1
    ```

### 📚 Guia de Referência Documental de Releases

O ecossistema documental do Pipeline Oficial de Release é segmentado de acordo com a finalidade prática de cada documento técnico:
1.  **[RELEASE_CHEATSHEET.md](file:///C:/Users/tdvie/Local%20Sites/blog/app/public/wp-content/plugins/gerador-posts-gemini/docs/RELEASE_CHEATSHEET.md) (Referência Rápida):** Destinado ao operador da release. Guia de bolso de apenas 1 página contendo os dois comandos oficiais de publicação de forma direta.
2.  **[PIPELINE.md](file:///C:/Users/tdvie/Local%20Sites/blog/app/public/wp-content/plugins/gerador-posts-gemini/PIPELINE.md) (Manual de Operação):** Destinado à equipe de DevOps e engenharia. Apresenta o fluxo operacional detalhado, logs de console ASCII, interfaces visuais de progresso e guia de instalação do GitHub CLI (`gh`).
3.  **[RELEASE_PROCESS.md](file:///C:/Users/tdvie/Local%20Sites/blog/app/public/wp-content/plugins/gerador-posts-gemini/docs/releases/RELEASE_PROCESS.md) (Normativa e Governança):** Destinado a auditorias de conformidade. Descreve as políticas de versionamento SemVer, critérios lógicos de decisões GO/NO-GO de deploy e governança de commits.
4.  **[RELEASE_ARCHITECTURE.md](file:///C:/Users/tdvie/Local%20Sites/blog/app/public/wp-content/plugins/gerador-posts-gemini/docs/architecture/RELEASE_ARCHITECTURE.md) (Manual de Engenharia):** Destinado a refatorações e evoluções do pipeline. Consolida os princípios arquiteturais (Single Source of Truth, codificação UTF-8 round-trip, exit codes, validações estruturais e categorias funcionais).

---

### Instalação e Validação do GitHub CLI (gh)
Para Windows, o método oficial principal recomendado para instalar o GitHub CLI é:
```powershell
winget install --id GitHub.cli
```
Como alternativa manual secundária, baixe o instalador oficial diretamente em: https://cli.github.com/

---

## 📄 Licença

Este software é um produto comercial e possui **Licença Proprietária**. É proibida a redistribuição, cópia ou modificação não autorizada do código fora do escopo estabelecido pelo proprietário dos direitos autorais.

---

## ✍️ Autor

*   **Thiago Vieira** - *Criação e Arquitetura* - contato@tdvieiradesign.com
