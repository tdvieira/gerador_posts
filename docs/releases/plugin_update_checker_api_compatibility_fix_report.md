# Relatório Técnico: Resiliência e Compatibilidade na API do Plugin Update Checker
**Pipeline Oficial de Release — v2.2.2**

---

## 1. Causa Raiz do Erro

Durante a inicialização do plugin, ocorria o seguinte erro fatal:
`Fatal error: Call to undefined method YahnisElsts\PluginUpdateChecker\v5p7\Vcs\GitHubApi::setReadmeFilename()`

**Análise Técnica:**
O método `setReadmeFilename()` foi introduzido em forks secundários ou versões experimentais e **não** faz parte da API pública estável oficial do **Plugin Update Checker (PUC v5.7)** que encontra-se ativamente embarcada no diretório `vendor/` do plugin.

Como a chamada ao método no arquivo `includes/updater.php` era direta e incondicional, ela resultava em erro fatal do interpretador PHP no WordPress, travando o carregamento administrativo do plugin e impedindo sua ativação.

---

## 2. Solução Adotada e Restauração de Compatibilidade

1.  **Isolamento de Chamadas:** Refatoramos a chamada no arquivo `includes/updater.php`, isolando a requisição do objeto da API de VCS do GitHub:
    ```php
    $vcsApi = $updateChecker->getVcsApi();
    ```
2.  **Remoção de Invocação Direta:** Removemos a dependência incondicional do método inexistente `setReadmeFilename()`.
3.  **Encapsulamento por Checagem Ativa (Compatibilidade Retroativa):** Para garantir compatibilidade com possíveis variações ou evoluções futuras da biblioteca, encapsulamos o recurso opcional dentro de uma checagem `method_exists()` do interpretador PHP:
    ```php
    if ($vcsApi) {
        $vcsApi->enableReleaseAssets('/\.zip$/i');
        
        if (method_exists($vcsApi, 'setReadmeFilename')) {
            $vcsApi->setReadmeFilename('readme.txt');
        }
    }
    ```

---

## 3. Preservação de Lógicas de Release

Confirmamos que esta alteração restringiu-se exclusivamente à inicialização lógica da biblioteca de atualização do plugin:
-   **Pipeline de Deploy:** Nenhuma lógica de versionamento nos scripts `prepare_release.ps1`, `build_release.ps1` ou `publish_release.ps1` foi modificada.
-   **Integridade do ZIP:** Os arquivos coletados na raiz para compactação (incluindo a lista centralizada `$root_files`) e os testes estruturais do WordPress permanecem inalterados.
-   **Sincronização de Notas:** A publicação no GitHub por código de retorno do CLI e a Fonte Única de Verdade das Release Notes no `CHANGELOG.md` continuam funcionando plenamente.

---

## Resumo para Release
### Correções de Bugs
- Eliminação de erro fatal de inicialização no WordPress provocado por chamada a método inexistente setReadmeFilename() na biblioteca Plugin Update Checker v5.7.
- Implementação de checagem condicional method_exists() para recursos opcionais de APIs externas no arquivo includes/updater.php, conferindo total resiliência técnica e compatibilidade retroativa.
