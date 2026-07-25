# Guia de Inicialização do Ambiente LocalWP (LocalWP Bootstrap Guide) — v1.0.0

Este guia orienta o desenvolvedor no processo passo a passo de preparação, configuração e validação de um ambiente de desenvolvimento local idêntico para o plugin **Gerador de Posts (IA)** utilizando a ferramenta **LocalWP**.

---

## ❓ Quando consultar este documento?

> [!IMPORTANT]
> Consulte este documento nas seguintes situações:
> *   Sempre que um novo engenheiro de software ingressar no projeto e precisar configurar a sua estação local de desenvolvimento.
> *   Quando for necessário reinstalar ou recriar a base de dados local do blog do zero a partir do backup estruturado.
> *   Ao validar ou migrar a estrutura de desenvolvimento para uma nova máquina física.

---

## 📖 Índice

1. [Pré-requisitos do Sistema](#-pré-requisitos-do-sistema)
2. [Criação do Site no LocalWP](#-criação-do-site-no-localwp)
3. [Clonagem do Repositório Git](#-clonagem-do-repositório-git)
4. [Restauração do Banco de Dados (backup.sql)](#-restauração-do-banco-de-dados-backupsql)
5. [Configuração Manual de Chaves e APIs](#-configuração-manual-de-chaves-e-apis)
6. [Boas Práticas de Segurança de Credenciais](#-boas-práticas-de-segurança-de-credenciais)
7. [Testes Rápidos de Validação e Checklist](#-testes-rápidos-de-validação-e-checklist)
8. [Documentos relacionados](#-documentos-relacionados)

---

## 💻 Pré-requisitos do Sistema

Antes de iniciar, certifique-se de ter instalado e configurado em seu sistema operacional:
*   [LocalWP](https://localwp.com/) (ferramenta padrão de virtualização local WordPress).
*   Cliente [Git](https://git-scm.com/) configurado globalmente.
*   Editor de código (recomenda-se VS Code).

---

## 🛠️ Criação do Site no LocalWP

1.  Abra o **LocalWP** em sua máquina e clique no botão **"+" (Add Local Site)** no canto inferior esquerdo.
2.  Selecione a opção **Create a new site** e clique em **Continue**.
3.  Defina o nome do site como **`Blog TD Vieira Design`** (ou nome de sua preferência) e clique em **Continue**.
4.  Na tela de configurações de ambiente, selecione **Preferred** (ou configure manualmente as versões estáveis homologadas):
    *   **PHP Version:** `8.2.x` (mínimo `8.0.x`).
    *   **Web Server:** `nginx` ou `Apache`.
    *   **Database:** `MySQL 8.0.x`.
5.  Defina um usuário administrativo (ex: `admin`), senha administrativa forte e o e-mail correspondente. Clique em **Add Site**.
6.  Aguarde o LocalWP concluir a instalação física do core do WordPress.

---

## 📂 Clonagem do Repositório Git

O código-fonte do plugin deve ser clonado diretamente sob o diretório de plugins do site WordPress virtualizado pelo LocalWP:

1.  Abra o terminal de sua máquina.
2.  Navegue até a pasta de plugins do site recém-criado (o caminho padrão geralmente segue a estrutura do LocalWP):
    *   **Windows:** `cd "C:\Users\<usuario>\Local Sites\blog\app\public\wp-content\plugins"`
    *   **macOS/Linux:** `cd ~/Local\ Sites/blog/app/public/wp-content/plugins`
3.  Clone o repositório oficial do plugin criando a pasta específica `gerador-posts-gemini`:
    ```bash
    git clone https://github.com/tdvieira/gerador_posts.git gerador-posts-gemini
    ```
4.  Navegue para a pasta clonada e garanta que você está na branch principal:
    ```bash
    cd gerador-posts-gemini
    git checkout main
    ```

---

## 💾 Restauração do Banco de Dados (backup.sql)

Para carregar a base de dados oficial do blog contendo as categorias cadastradas, posts publicados e as tabelas meta estruturadas:

1.  O repositório fornece o arquivo de dump de banco de dados na raiz pública de homologação em `C:\Users\<usuario>\Local Sites\blog\app\public\backup.sql`.
2.  No painel do LocalWP, com o site selecionado e rodando, clique na aba **Database** e depois em **Open Adminer** (ou use uma ferramenta como DBeaver se preferir).
3.  No Adminer, selecione a opção **Importar** (Import).
4.  Selecione o arquivo [backup.sql](../../../../backup.sql) localizado na raiz pública e execute a importação das tabelas.
5.  *Nota:* Certifique-se de que o prefixo de tabelas definido no arquivo [wp-config.php](../../../../wp-config.php) local corresponda a `wpgj_`. Se necessário, ajuste o arquivo de configuração para refletir `$table_prefix = 'wpgj_';`.

---

## ⚙️ Configuração Manual de Chaves e APIs

O plugin utiliza integradores de inteligência artificial de terceiros. Cada desenvolvedor deve configurar manualmente as suas próprias chaves de API:

1.  Acesse o painel do WordPress em seu navegador: `http://blog.local/wp-admin` (ou URL definida pelo LocalWP).
2.  Insira suas credenciais administrativas de teste.
3.  No menu lateral esquerdo, navegue até **Posts > Gerador de Posts**.
4.  Acesse a aba **Configurações**.
5.  Cadastre as suas chaves e tokens de desenvolvimento descartáveis nos campos correspondentes:
    *   **Google Gemini API Key:** (Para geração de texto Gemini).
    *   **OpenAI API Key:** (Para GPT-4o-mini e DALL-E 3).
    *   **Groq Cloud API Key:** (Para Llama 3.3).
    *   **Puter.js Token (Opcional):** (Para Flux via SDK no navegador).
    *   **Chave do Pollinations (Opcional):** (Para Flux grátis no backend).
6.  Clique em **Salvar Configurações**. O plugin salvará os dados criptografados/mascarados no banco local `wpgj_options`.

---

## 🔒 Boas Práticas de Segurança de Credenciais

Para manter a integridade dos servidores de produção e evitar vazamento acidental de segredos comerciais no repositório GitHub público do projeto:

> [!CAUTION]
> *   **NUNCA versionar segredos:** É terminantemente proibido commitar chaves de API reais, logins, senhas, chaves privadas ou tokens no repositório Git do projeto.
> *   **Isolamento Estrito:** Credenciais de produção do blog real jamais devem ser inseridas em ambientes locais de desenvolvimento. Utilize chaves de teste descartáveis e com limites de cota rigorosos nas estações de homologação.
> *   **Uso Correto do `.gitignore`:** O arquivo `.gitignore` do plugin está configurado para excluir arquivos `.env`, backups locais, zips temporários e dumps de dados. Nunca edite o `.gitignore` para forçar a adição de segredos.
> *   **Configuração Manual e Individual:** Cada novo desenvolvedor ou agente deve registrar manualmente suas chaves locais pelo painel administrativo do WordPress no LocalWP. Nenhuma chave deve ser embutida de forma hardcoded no código PHP ou JS.
> *   **Isolamento do `wp-config.php`:** O arquivo `wp-config.php` do LocalWP reside fora da pasta física do plugin, garantindo que suas credenciais do MySQL local (`local`/`root`/`root`) não sejam adicionadas ao controle de versão Git.

---

## 🚦 Testes Rápidos de Validação e Checklist

Para certificar que o seu bootstrap local foi concluído com sucesso e o ambiente está pronto para evoluções:

1.  **Checklist de Validação Inicial:**
    *   [ ] O site WordPress está rodando sem erros no LocalWP.
    *   [ ] O prefixo de banco de dados em `wp-config.php` está mapeado como `wpgj_`.
    *   [ ] A pasta física do plugin está em `wp-content/plugins/gerador-posts-gemini`.
    *   [ ] O painel administrativo do plugin em **Posts > Gerador de Posts** abre sem erros de script.
    *   [ ] Assets enfileirados (`admin.css` e `admin.js`) estão carregando via rede local (verifique no console `F12` se não há erros 404).
2.  **Validação Rápida de Escrita (IA):**
    *   Tente gerar um post curto selecionando o provedor **Google Gemini** (`gemini-3.5-flash`). Se retornar a resposta formatada na tela de pré-visualização, a comunicação assíncrona AJAX e o wrapper HTTP estão em pleno funcionamento.

---

## 🔗 Documentos relacionados

Para navegar e aprofundar-se nos fluxos de qualidade e engenharia do Handbook, consulte:
*   **[DEVELOPMENT_WORKFLOW.md](DEVELOPMENT_WORKFLOW.md):** Aprenda o fluxo operacional de 13 fases para criar novas funcionalidades.
*   **[ARCHITECTURE.md](ARCHITECTURE.md):** Entenda a comunicação interna de componentes e diagramas Mermaid.
*   **[TROUBLESHOOTING.md](TROUBLESHOOTING.md):** Consulte se encontrar erros de banco, APIs ou imagens durante a inicialização.
*   **[MAINTENANCE_GUIDE.md](MAINTENANCE_GUIDE.md):** Manual de manutenção para evoluções futuras.
