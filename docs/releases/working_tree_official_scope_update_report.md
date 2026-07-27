# Relatório Técnico: Evolução do Escopo de Validação da Working Tree
**Pipeline Oficial de Release — v2.0.8**

---

## 1. Evolução da Whitelist e a Causa dos Falsos Bloqueios

No início da concepção do Pipeline Oficial de Release, a segurança e a consistência da árvore de trabalho (Working Tree) do Git eram regidas por uma whitelist estática extremamente rígida. Essa política inicial bloqueava qualquer deploy caso houvesse modificações em arquivos que não fizessem parte explícita da lista restrita de documentações Markdown (`docs/releases/*.md`) ou de arquivos administrativos do repositório.

Com a evolução estrutural do projeto e a adequação do plugin às conformidades do ecossistema do WordPress, foram introduzidos componentes permanentes essenciais:
1.  **`readme.txt`:** O arquivo padrão WordPress.org para leitura de metadados de instalação e changelogs.
2.  **`includes/updater.php`:** Arquivo do mecanismo de atualização automática através do Plugin Update Checker.

Como esses arquivos de código e metadados operacionais sofrem modificações ativas legítimas durante a preparação de releases (como a injeção da nova tag de versão e limpezas de depuração), a sua ausência na whitelist original do script de publicação disparava falsos bloqueios. Isso impedia que a publicação prosseguisse mesmo em estados completamente seguros, forçando operadores a contornar a validação manualmente.

---

## 2. Novos Componentes Oficiais e Atualização do Escopo

Para eliminar os falsos positivos de forma permanente e sem comprometer a blindagem de segurança, atualizamos a whitelist interna da função `Test-IsFileAllowed` em `scripts/publish_release.ps1` para incluir oficialmente os seguintes arquivos:

-   **`readme.txt`:** Reconhecido como parte legítima e oficial do ecossistema documental da release.
-   **`includes/updater.php`:** Autorizado como parte do escopo técnico oficial que pode sofrer alterações de preparação da versão no mecanismo de autoupdate.

Além disso, a lista de staging do Git (`$static_to_add`), responsável por fazer o `git add` no commit de publicação automática da versão, foi atualizada para indexar e salvar ambos os arquivos de forma automática no commit de release.

---

## 3. Preservação Integral da Segurança do Pipeline

A atualização do escopo da whitelist foi implementada sob critérios estritos de segurança, garantindo que a blindagem da esteira de deploy permaneça inquebrável:

1.  **Bloqueio de Arquivos Alheios:** Qualquer modificação em arquivos de desenvolvimento que não constem na whitelist (como arquivos de código PHP fora do updater, scripts em JavaScript de painéis administrativos, folhas de estilo CSS, backups `.bak`, arquivos de log `.log`, dumps de banco de dados `.sql` ou arquivos de uso pessoal) continua sendo barrada de forma rígida, provocando o cancelamento imediato da publicação.
2.  **Imutabilidade de Lógicas Funcionais:** A lógica de validação do arquivo ZIP (auditoria estrutural .NET), a assinatura criptográfica, a criação de tags anotadas e o push remoto para a branch `main` no GitHub permanecem operando de forma integral e sem alterações.

---

## Resumo para Release
### Melhorias
- Atualização da whitelist da Working Tree para incluir oficialmente os arquivos readme.txt e includes/updater.php nas validações de release.
- Eliminação de falsos bloqueios operacionais ao publicar novas releases com metadados do ecossistema WordPress modificados.
- Sincronização automática dos arquivos readme.txt e includes/updater.php no commit final da release.
- Preservação integral do bloqueio de segurança contra modificações indevidas em arquivos alheios ao processo de deploy.
