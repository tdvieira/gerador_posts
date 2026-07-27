# Relatório de Automação de Validação do Pacote WordPress — v1.0.0

Este relatório documenta a integração e homologação da etapa obrigatória de **Validação Estrutural Automatizada** no script oficial de build do plugin **Gerador de Posts (IA)**, mitigando definitivamente falhas de descompactação e ativação em produção.

---

## 📁 1. Arquivos Modificados e Criados

As seguintes atualizações foram adicionadas ao projeto de engenharia:

1.  **[scripts/build_release.ps1](scripts/build_release.ps1) (Modificado):** Incorporada a lógica de compactação manual baseada em `ZipArchive` e `FileStream` (forçando barras normais `/` nos caminhos das entradas do ZIP) e a rotina automática de 8 validações estruturais (.NET) pós-build.
2.  **[README.md](README.md) (Modificado):** Atualizada a seção "Release" para registrar a nova validação estrutural automática sob o Passo 2 do Pipeline Oficial.
3.  **[docs/releases/RELEASE_PROCESS.md](docs/releases/RELEASE_PROCESS.md) (Modificado):** Atualizado o manual de release detalhando o funcionamento da barreira estrutural e o checklist de regras atestadas.
4.  **[.agents/rules/project-governance.md](.agents/rules/project-governance.md) (Modificado):** Adicionado o Princípio 18 (Validação Estrutural Obrigatória de Builds) exigindo aprovação absoluta da estrutura do ZIP antes de qualquer submissão de release.

---

## 🛠️ 2. Validações e Regras Estruturais Verificadas

O validador pós-build abre o arquivo ZIP via API `.NET System.IO.Compression.ZipFile` e executa as seguintes auditorias obrigatórias:

1.  **Integridade do Pacote:** Garante que o arquivo ZIP não está corrompido e pode ser lido sem exceções de I/O.
2.  **Pasta Raiz Única:** Verifica que todas as entradas estão encapsuladas dentro da pasta raiz correspondente ao slug do plugin (`gerador-posts-gemini/`), impedindo a presença de arquivos avulsos soltos na raiz do ZIP.
3.  **Arquivo de Entrada WordPress:** Confirma a existência do arquivo principal `gerador-posts-gemini/gerador-posts-gemini.php` diretamente sob a pasta raiz do plugin.
4.  **Conformidade de Separadores:** Varre a estrutura binária física de caminhos do ZIP no disco para certificar que todos os separadores utilizam exclusivamente barras normais `/`, banindo barras invertidas `\` que inviabilizam deploys em sistemas Linux/Apache.
5.  **Subdiretórios Obrigatórios:** Atesta a presença e consistência das pastas internas críticas: `assets/`, `includes/` e `vendor/`.

---

## 🚦 3. Comportamento e Resiliência do Pipeline

### Em Caso de Falha de Validação
*   O script de build interrompe o fluxo imediatamente.
*   Remove fisicamente do disco o arquivo ZIP inválido ou corrompido para impedir publicações acidentais de pacotes inconsistentes.
*   Retorna no console de erro do PowerShell uma mensagem clara e em português detalhando a regra violada.
*   O código de erro (`exit 1`) trava o `prepare_release.ps1` e suspende a release.

### Em Caso de Sucesso de Validação
*   O script fecha com segurança todos os handles de arquivos.
*   Exibe no terminal a mensagem homologada:
    `Validação estrutural do pacote WordPress: APROVADA. ZIP íntegro. Estrutura compatível com WordPress. Arquivo principal localizado corretamente. Separadores internos validados. Pacote liberado para publicação.`
*   Salva o pacote final liberado em `build/gerador-posts-gemini.zip` e retorna código de saída `0`.

Esta barreira de segurança impede **100%** de chances de pacotes incompletos ou incompatíveis com o WordPress avançarem para a etapa de deploy (`publish_release.ps1`).

---

## 🚦 Bloco de Status do Relatório
*   **Status:** Concluído
*   **Fase:** Automação de Validação Estrutural de Releases
*   **Resultado:** Aprovado (Compactação Manual, Varredura Binária de Barras e 8 Validações WordPress Homologadas)
*   **Validação:** Execução do scripts/prepare_release.ps1 (v2.0.1) com Sucesso Absoluto, Checagem do ZIP de Produção e Auditoria de Governança no project-governance.md
