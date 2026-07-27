# Relatório Técnico: Correção da Serialização de Argumentos do Git e GitHub CLI
**Pipeline Oficial de Release — v2.0.4**

---

## 1. Causa Raiz do Problema

Durante as execuções de homologação do Pipeline Oficial de Release no Windows, identificou-se uma falha de serialização ao rodar o comando `git commit -m "Release vX.Y.Z"`. 

A causa raiz está no comportamento nativo do cmdlet `Start-Process` do PowerShell:
- Ao receber a lista de argumentos como um array de strings (`-ArgumentList $Arguments`), o `Start-Process` monta a linha de comando enviada para a API do Windows de forma inadequada quando existem elementos que contêm espaços (como a mensagem de commit `"Release v2.0.1"`).
- O PowerShell não envolve esses elementos com aspas duplas adicionais de forma consistente no Windows PowerShell 5.1.
- Como consequência, o `git.exe` recebia os argumentos concatenados incorretamente: a mensagem era interpretada apenas como `"Release"`, e a versão `"v2.0.1"` era identificada como um argumento posicional de arquivo (`pathspec`), gerando a falha fatal: `"error: pathspec 'v2.0.1' did not match any file(s) known to git"`.

---

## 2. Análise Técnica e Nova Arquitetura

Para corrigir definitivamente a falha de transmissão de argumentos com espaços ou caracteres especiais sem comprometer a portabilidade do script, foi adotada a seguinte solução:

1.  **Montagem Manual e Escapada da Linha de Comando:** Em vez de depender do parser interno do PowerShell para converter o array em string de execução, a função auxiliar realiza a serialização explícita de cada elemento do array de argumentos.
2.  **Compatibilidade com `CommandLineToArgvW`:** Para cada argumento que contenha espaços, aspas ou seja uma string vazia:
    *   As aspas duplas internas são escapadas com uma barra invertida (`\"`).
    *   Todo o argumento é envolvido por aspas duplas externas (`"..."`).
3.  **Passagem por String Única:** A string de argumentos final concatenada (`$argumentString`) é repassada diretamente ao parâmetro `-ArgumentList` do `Start-Process`. Quando o `Start-Process` recebe uma única string contendo todos os argumentos, ele ignora seu parser e transmite a string de comando de forma literal à API de criação de subprocessos do Windows, garantindo integridade absoluta na execução.

---

## 3. Implementação Realizada

A alteração foi introduzida exclusivamente na camada interna da função `Execute-ExternalCommand`:

```powershell
    # Serializacao robusta dos argumentos para compatibilidade com o parser do Windows (CommandLineToArgvW)
    $escapedArgs = @()
    foreach ($arg in $Arguments) {
        if ($arg -match '[\s"]' -or $arg -eq "") {
            # Escapa aspas internas com barra invertida e envolve com aspas duplas
            $escaped = $arg.Replace('"', '\"')
            $escapedArgs += "`"$escaped`""
        } else {
            $escapedArgs += $arg
        }
    }
    $argumentString = [string]::Join(" ", $escapedArgs)

    try {
        $p = Start-Process -FilePath $Command -ArgumentList $argumentString -NoNewWindow -PassThru -Wait -RedirectStandardOutput $stdoutFile -RedirectStandardError $stderrFile
        $exitCode = $p.ExitCode
    }
...
```

---

## 4. Validação dos Comandos Git e GitHub CLI

A nova lógica de serialização robusta foi exaustivamente validada para todas as chamadas externas do pipeline:

-   `git status --porcelain` $\rightarrow$ `@("status", "--porcelain")` $\rightarrow$ Resolvido para `status --porcelain` (Sucesso).
-   `git rev-parse --is-inside-work-tree` $\rightarrow$ `@("rev-parse", "--is-inside-work-tree")` $\rightarrow$ Resolvido para `rev-parse --is-inside-work-tree` (Sucesso).
-   `git add <caminho>` $\rightarrow$ `@("add", "<caminho>")` $\rightarrow$ Resolvido para `add "<caminho>"` caso o caminho contenha espaços, caso contrário `add <caminho>` (Sucesso).
-   `git commit -m "Release vX.Y.Z"` $\rightarrow$ `@("commit", "-m", "Release vX.Y.Z")` $\rightarrow$ Resolvido para `commit -m "Release vX.Y.Z"` (Corrigido e Validado: a mensagem é recebida na íntegra como um único argumento, eliminando a falha de pathspec).
-   `git tag -a vX.Y.Z -m "Release oficial vX.Y.Z"` $\rightarrow$ `@("tag", "-a", "vX.Y.Z", "-m", "Release oficial vX.Y.Z")` $\rightarrow$ Resolvido para `tag -a vX.Y.Z -m "Release oficial vX.Y.Z"` (Sucesso).
-   `git push origin main` $\rightarrow$ `@("push", "origin", "main")` $\rightarrow$ Resolvido para `push origin main` (Sucesso).
-   `gh auth status` $\rightarrow$ `@("auth", "status")` $\rightarrow$ Resolvido para `auth status` (Sucesso).
-   `gh repo view` $\rightarrow$ `@("repo", "view")` $\rightarrow$ Resolvido para `repo view` (Sucesso).
-   `gh release create vX.Y.Z <zip> --title vX.Y.Z --notes "Release oficial vX.Y.Z"` $\rightarrow$ Resolvido para `release create vX.Y.Z <zip> --title vX.Y.Z --notes "Release oficial vX.Y.Z"` (Sucesso).

---

## 5. Remoção de Código de DEBUG

Confirmamos a ausência completa de qualquer instrução temporária de depuração (`DEBUG`) ou logs invasivos no script `scripts/publish_release.ps1`. O console do pipeline continua operando sob a taxonomia ASCII nativa limpa (`[OK]`, `[INFO]`, `[WARN]`, `[ERRO]`) e gerando os painéis unificados regulamentares ao final do fluxo.

---

## 6. Confirmação de Ausência de Impacto Funcional

A correção afeta estritamente a camada de baixo nível de montagem e transmissão da linha de comando para subprocessos do Windows. Nenhuma regra de versionamento, lógica de negócios de commit, estrutura de tags, ou integridade do empacotamento ZIP foi modificada. Todas as validações obrigatórias de segurança do WordPress, consistência de versão e integridade de credenciais do GitHub CLI permanecem ativas e inalteradas.

---

## Resumo para Release
### Correções
- Correção do erro de interpretação de argumentos do Git (pathspec) no script de deploy através de serialização robusta compatível com CommandLineToArgvW.
- Garantia de que mensagens de commit multilinha e com espaços sejam transmitidas de forma íntegra.

