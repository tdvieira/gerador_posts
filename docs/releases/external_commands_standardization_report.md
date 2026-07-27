# Relatório Técnico: Padronização Definitiva da Execução de Comandos Externos
**Pipeline Oficial de Release — v2.0.2**

---

## 1. Descrição das Alterações Realizadas

Foi efetuado o refinamento final do script `scripts/publish_release.ps1` com o objetivo de centralizar e padronizar toda a execução de processos e utilitários externos (Git e GitHub CLI). As seguintes modificações foram implementadas com sucesso:

- **Criação da Função Auxiliar Reutilizável `Execute-ExternalCommand`:** Declarada no próprio escopo do script, a função encapsula a execução de processos externos usando a API nativa `.NET` do Windows via `Start-Process`, garantindo o isolamento completo de fluxos de entrada e saída.
- **Remoção de Redirecionamentos Complexos (`2>&1` e `2>$null`):** O uso de operadores de redirecionamento no nível do script para comandos externos foi totalmente eliminado.
- **Tomada de Decisão baseada Exclusivamente em Códigos de Retorno (`$LASTEXITCODE`):** Todo o fluxo de tomada de decisão, validações condicionais e loops de integridade utilizam de forma estrita o código de saída numérico das ferramentas externas.
- **Tratamento de Saída e Diagnóstico em Falhas:** Em execuções com sucesso, o pipeline descarta a saída se ela não for necessária (agindo como `*> $null` ou `Out-Null`). Caso ocorra uma falha não-esperada, a saída de erro original (`StandardError`) é integralmente impressa no console antes de abortar o processo, fornecendo riqueza de diagnóstico para o operador.
- **Tabela de Códigos Esperados:** Implementada a parametrização de códigos permitidos (`AllowedExitCodes`). O comando `gh release view` foi configurado para aceitar tanto `0` (sucesso) quanto `1` (release inexistente) como códigos válidos, permitindo que a ausência de uma release remota prévia seja tratada como fluxo não-fatal operacional normal para seguir com a criação da release.

---

## 2. Motivação Técnica

- **Portabilidade Linguística e Regionalização:** O parsing textual de saídas como `"Successfully created release"` ou `"Authentication failed"` torna os scripts frágeis diante de computadores configurados com idiomas diferentes (ex: português, inglês, espanhol), onde a tradução do terminal altera as strings retornadas. Basear as decisões no `$LASTEXITCODE` garante imunidade linguística absoluta.
- **Compatibilidade com Novas Versões:** Alterações na formatação textual, ordem de parâmetros ou layout visual de saída promovidos por novas versões do Git ou do GitHub CLI (`gh`) poderiam quebrar validações baseadas em expressões regulares ou strings fixas. O código de retorno é uma especificação padrão e estável em nível do sistema operacional.
- **Estabilidade no Controle de Exceções do PowerShell:** Quando `$ErrorActionPreference = "Stop"` está ativo, a gravação direta no stream de erro por executáveis externos que usam redirecionamentos como `2>&1` ou `2>` pode disparar exceções de fluxo do PowerShell no pipeline, interrompendo a execução indevidamente. O encapsulamento em `Start-Process` com redirecionamento de arquivo temporário isola e estabiliza esse comportamento.

---

## 3. Comandos Padronizados

Todas as chamadas externas do script foram migradas e utilizam exclusivamente a função auxiliar `Execute-ExternalCommand`:

1.  **Status e Conformidade do Git:**
    *   `git rev-parse --is-inside-work-tree` (validação de repositório ativo).
    *   `git rev-parse --abbrev-ref HEAD` (obtenção da branch atual).
    *   `git status --porcelain` (validação de integridade da árvore de trabalho e detecção de arquivos de desenvolvimento pendentes).
2.  **Operações de Versionamento Git:**
    *   `git add -A` e `git add $allowed_abs` (estágio de arquivos).
    *   `git commit -m "Release v$Version"` (gravação do commit da release).
    *   `git tag -l $tag_name` (validação de tag local).
    *   `git tag -a $tag_name -m "Release oficial $tag_name"` (criação de tag).
3.  **Sincronização Remota Git:**
    *   `git push origin main` (envio de alterações).
    *   `git push origin --tags` (envio de tags).
4.  **Validação de Ambiente GitHub CLI (gh):**
    *   `gh auth status` (checagem de autenticação local).
    *   `gh repo view` (validação de acesso ao repositório remoto).
5.  **Gerenciamento de Release no GitHub CLI (gh):**
    *   `gh release view $tag_name` (verificação de existência da release).
    *   `gh release view $tag_name --json url -q .url` (obtenção segura da URL da release existente).
    *   `gh release create $tag_name $zip_path --title $tag_name --notes "Release oficial v$Version"` (criação e upload do ZIP).

---

## 4. Benefícios Obtidos

- **Zero Dependência Textual:** Eliminação total de regex ou verificações de strings literais sobre a saída de comandos para controle de fluxo.
- **Robustez Operacional:** Detecção imediata de comandos indisponíveis ou falhas silenciosas de rede e credenciais.
- **Preservação de Logs Úteis:** Operador visualiza a mensagem de erro literal emitida pelas ferramentas externas apenas quando um erro real interrompe a execução, mantendo o console limpo em situações de sucesso.
- **Portabilidade:** Scripts 100% autocontidos, executáveis em qualquer console PowerShell Windows sem dependência de dependências extras ou arquivos de utilitários externos.

---

## 5. Confirmação de Ausência de Impacto Funcional

A padronização de comandos externos não alterou nenhuma funcionalidade do pipeline. Os inputs esperados, os arquivos de destino, as lógicas de staging, tagging, envio de tags, criação de release e upload do arquivo ZIP permanecem idênticos aos homologados anteriormente pelo Pipeline Oficial de Release. A alteração restringe-se exclusivamente à camada de invocação de subprocessos e tratamento de seus retornos.

---

## 6. Confirmação de Preservação Integral da Arquitetura

A arquitetura geral do ecossistema de release permanece estritamente intacta. As seguintes regras do projeto foram rigorosamente atendidas:
- **NÃO** houve alteração em regras de versionamento ou tags SemVer.
- **NÃO** houve alteração nos scripts `prepare_release.ps1` e `build_release.ps1`.
- **NÃO** houve alteração na lógica de negócios ou restrição estrutural de geração do arquivo ZIP.
- A função auxiliar está inteiramente contida no escopo isolado de `scripts/publish_release.ps1`, preservando o princípio da responsabilidade única.

---

## 7. Conclusão da Homologação

Todas as edições operacionais foram integradas ao script principal e as documentações técnicas correspondentes ([README.md](../../README.md), [PIPELINE.md](../../PIPELINE.md) e [RELEASE_PROCESS.md](./RELEASE_PROCESS.md)) foram atualizadas com sucesso para refletir este novo padrão. As simulações lógicas comprovam a estabilidade das checagens baseadas em exit codes e a adequada recuperação de diagnósticos em cenários de erro simulados.

O Pipeline Oficial de Release atinge, portanto, o seu estado definitivo de refinamento de comandos externos.
