# Relatório de Hardening da Validação do GitHub CLI — v1.0.0

Este relatório detalha as melhorias de engenharia integradas no script de publicação do plugin **Gerador de Posts (IA)**, destinadas a tornar a validação do GitHub CLI (`gh`) 100% robusta e independente de idioma.

---

## 📁 1. Arquivos Modificados e Criados

Os seguintes recursos foram atualizados ou adicionados ao repositório:

1.  **[scripts/publish_release.ps1](scripts/publish_release.ps1) (Modificado):** Refatorada a validação do GitHub CLI para utilizar checagem de código de retorno do terminal (`$LASTEXITCODE`), removido o parsing textual e adicionado o painel de verificação e tratamento de releases existentes.
2.  **[README.md](README.md) (Modificado):** Atualizada a seção correspondente com as especificações do setup do GitHub CLI por código de retorno.
3.  **[PIPELINE.md](PIPELINE.md) (Modificado):** Incluída a documentação técnica da validação em duas etapas (autenticação e repositório) sem dependências de Unicode ou formato textual.
4.  **[docs/releases/RELEASE_PROCESS.md](docs/releases/RELEASE_PROCESS.md) (Modificado):** Detalhado o procedimento operacional e o comportamento ante falhas de CLI.
5.  **[docs/releases/github_cli_validation_hardening_report.md](docs/releases/github_cli_validation_hardening_report.md) (Criado):** Este relatório registrando as validações e comportamentos.

---

## 🛠️ 2. Motivo da Mudança e Solução Implementada

### O Problema do Parsing de Texto (Fragilidade)
A validação anterior do GitHub CLI baseava-se em varrer a saída textual do comando `gh auth status` procurando por strings fixas em inglês como `Logged in to github.com as`. Esse padrão de validação falha em ambientes onde a saída do console está internacionalizada (ex: português, espanhol) ou quando novas versões do utilitário GitHub CLI modificam o layout de texto da saída.

### A Solução por Código de Retorno
A nova implementação torna a validação universal e imune a formatações ou idiomas de console, utilizando estritamente a variável de código de saída do console do PowerShell (`$LASTEXITCODE`). Um código igual a `0` indica sucesso e qualquer outro valor indica falha. A verificação ocorre em duas etapas complementares:
1.  **Autenticação Local:** Executa `gh auth status 2>&1` direcionando a saída e valida se o usuário possui credenciais locais configuradas (`$LASTEXITCODE -eq 0`).
2.  **Acesso ao Repositório:** Executa `gh repo view 2>&1` e valida se o repositório do projeto no origin remoto está acessível e possui permissão de leitura/gravação ativa para a conta autenticada.

---

## 🚦 3. Comportamentos de Sucesso e Falha

### Comportamento com Sucesso
Quando todos os critérios são aprovados, o terminal exibe o seguinte painel visual em ASCII:
```
==================================================
VALIDACAO DO GITHUB CLI
==================================================
[OK] GitHub CLI localizado.
[OK] Usuario autenticado.
[OK] Repositorio acessivel.
==================================================
```
A publicação prossegue. Caso a release já exista no GitHub, o script captura a URL via `gh release view`, exibe `[INFO] Release vX.Y.Z ja existe.` acompanhada de sua URL oficial e finaliza sem erros. Caso não exista, cria e exibe `[OK] Release publicada com sucesso.` com a respectiva URL.

### Comportamentos com Falha (Interrupção do Pipeline)
1.  **Ausência do Executável:** Caso `gh` não esteja no PATH, exibe `[ERRO] GitHub CLI nao encontrado.` e interrompe.
2.  **Não Autenticado:** Caso o status de autenticação falhe, exibe `[ERRO] GitHub CLI nao autenticado.` seguido da instrução `Execute: gh auth login` e interrompe.
3.  **Repositório Inacessível:** Caso o comando de leitura do repositório falhe, exibe `[ERRO] Repositorio GitHub inacessivel.` e interrompe.
4.  **Falha de Publicação/Upload:** Se o comando de criação da release ou upload do ZIP falhar, exibe `[ERRO] Falha ao publicar Release.` e interrompe.

---

## 🏁 4. Confirmação de Preservação Arquitetural

Toda a lógica técnica, versionamento, lints de versão dinâmicos, compactação manual por streams e barramentos de segurança foram preservados em sua totalidade. A mudança atuou exclusivamente na validação e usabilidade da integração com o GitHub CLI.

---

## 🚦 Bloco de Status do Relatório
*   **Status:** Concluído
*   **Fase:** Hardening da Integração de Releases com GitHub CLI
*   **Resultado:** Aprovado (Validação por Códigos de Saída de Duas Etapas e Tratamento Visual de Sucesso/Falha Homologados)
*   **Validação:** Execução do scripts/publish_release.ps1, Logs de Erro Controlados no Terminal e Auditoria de Regras no project-governance.md
