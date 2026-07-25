# Regras Permanentes de Engenharia (Engineering Rules)

Este documento estabelece as diretrizes normativas técnicas permanentes de desenvolvimento de software, arquitetura de código, segurança e empacotamento para o plugin **Gerador de Posts (IA)**. Estas regras visam garantir a manutenibilidade, a compatibilidade multiplataforma e a segurança contra vulnerabilidades comuns no ambiente WordPress.

---

## 🔒 1. Segurança e Proteção de Endpoints

*   **Validação de Capabilities:** Todo endpoint ou callback AJAX deve validar se o usuário possui a capacidade administrativa correta antes de realizar qualquer processamento:
    *   Utilizar obrigatoriamente a verificação `current_user_can('manage_options')` antes de executar qualquer lógica ou persistência.
*   **Verificação de Autenticidade (Nonces):**
    *   Toda requisição AJAX de backend deve validar o token de nonce correspondente através de `check_ajax_referer('gpg_admin_nonce', 'nonce')` ou respectiva action cadastrada no bootstrap.
    *   Nenhum dado externo deve ser processado sem a validação do nonce correspondente no início do callback.

---

## 🏛️ 2. Modularidade, Separação de Conceitos (SoC) e Padrões WordPress

*   **Isolamento de Assets (CSS/JS):**
    *   Todos os estilos e comportamentos interativos do painel devem residir em arquivos independentes sob `assets/css/` e `assets/js/`.
    *   O enfileiramento de estilos e scripts administrativos deve ser restrito seletivamente à tela específica do plugin no WordPress, validando o parâmetro `$hook` no hook `admin_enqueue_scripts`.
*   **Separação de Views (HTML):**
    *   O arquivo principal do plugin não deve renderizar blocos extensos de HTML. A interface do painel do usuário deve residir no arquivo `admin-ui.php`.
*   **Responsabilidade Única no PHP (SRP):**
    *   Lógicas utilitárias de backend (como processamento de mídias ou conexões externas de IAs) devem ser desenvolvidas em classes ou helpers dedicados no diretório `includes/`, restando ao controlador principal apenas a orquestração e registro de hooks do WordPress.

---

## 🌐 3. Regra de Caminhos, Compatibilidade Multiplataforma e Empacotamento

*   **Links Relativos na Documentação:**
    *   Todos os caminhos e referências internas de documentação markdown sob o diretório `.agents/` ou na raiz devem utilizar obrigatoriamente caminhos e links relativos (ex: `[NOME](./CAMINHO)`), sendo terminantemente proibido o uso de caminhos locais absolutos do sistema operacional (`file:///`).
*   **Padronização do Separador de Diretório:**
    *   Todos os artefatos de governança, documentação, manuais, rotinas de empacotamento e referências devem utilizar **exclusivamente** o separador "/" (forward slash) em caminhos de arquivos e pastas.
    *   É terminantemente proibido o uso do separador "\" (backslash) em qualquer arquivo de documentação, instruções de compilação ou rotinas de zip do repositório.
*   **Compatibilidade Multiplataforma:**
    *   O tratamento e a persistência de caminhos físicos devem ser programados de forma compatível com ambientes Linux, macOS, Windows e o ecossistema interno de subpastas do WordPress.
*   **Diretrizes de Empacotamento (Arquivos ZIP):**
    *   Os scripts e comandos de empacotamento do plugin devem preservar exatamente a árvore de diretórios do repositório.
    *   As rotinas de geração do arquivo ZIP final do plugin devem gerar uma estrutura portável que evite a criação de caminhos incompatíveis com sistemas operacionais de servidores de produção (Linux/Unix).
