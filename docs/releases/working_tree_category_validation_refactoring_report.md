# Relatório Técnico: Validação Arquitetural da Working Tree por Categorias
**Pipeline Oficial de Release — v2.0.9**

---

## 1. Migração da Whitelist Estática para Validação por Categorias

Anteriormente, o Pipeline Oficial de Release utilizava uma whitelist baseada em nomes estáticos de arquivos específicos na função `Test-IsFileAllowed`. Embora segura no início do projeto, essa abordagem gerava severas desvantagens operacionais:
1.  **Alto Custo de Manutenção:** Toda introdução de um novo arquivo legítimo (como relatórios técnicos adicionais, novas documentações sob regras ou novos arquivos auxiliares no updater) forçava os engenheiros a editarem o script de deploy `publish_release.ps1` para atualizar a lista estática.
2.  **Duplicidade de Listas de Controle:** O script mantinha uma lista duplicada para auditoria inicial da Working Tree e para o `git add` no comissionamento final.

A migração substituiu essa whitelist estática por uma **validação arquitetural baseada em categorias funcionais do projeto**. Isso significa que o script agora identifica o propósito e a pasta de destino de cada arquivo modificado, determinando a sua aceitação de forma dinâmica.

---

## 2. Categorias Oficiais Adotadas e Critérios de Validação

A árvore de trabalho é avaliada sob cinco categorias oficiais de release, utilizando regras de caminhos, extensões de arquivos e padrões (wildcards):

1.  **Categoria 1: Documentação Oficial:**
    *   Arquivos específicos de documentação raiz: `README.md`, `PIPELINE.md`, `CHANGELOG.md` e `readme.txt`.
    *   Todos os relatórios operacionais sob o diretório `docs/releases/*.md`.
    *   Regras e políticas sob o diretório `.agents/rules/*.md`.
2.  **Categoria 2: Scripts Oficiais de Pipeline:**
    *   Todos os scripts PowerShell sob o diretório `scripts/*.ps1` (ou em subpastas).
3.  **Categoria 3: Manifesto Principal e Bootstrap:**
    *   O arquivo principal de assinatura do WordPress: `gerador-posts-gemini.php`.
    *   A classe central de inicialização e ciclo de vida: `includes/Core/PluginBootstrap.php`.
4.  **Categoria 4: Subsistema de Atualização:**
    *   O controlador de updates: `includes/updater.php`.
    *   Quaisquer novos arquivos no subsistema de atualização mapeados sob as subpastas `includes/updater/*.php`.
5.  **Categoria 5: Configurações de Infraestrutura e Build:**
    *   Manifesto de ignorados do Git: `.gitignore`.
    *   O placeholder de pastas de build: `build/.gitkeep`.
    *   O pacote ZIP gerado automaticamente: `build/gerador-posts-gemini.zip`.

---

## 3. Indexação e Commit Dinâmico do Git

Graças à nova lógica baseada em categorias, o processo de staging e commit no final da publicação tornou-se totalmente dinâmico. Em vez de percorrer um array fixo de arquivos estáticos, o script:
1. Consulta os arquivos modificados na Working Tree através do comando `git status --porcelain`.
2. Avalia cada arquivo modificado contra a função `Test-IsFileAllowed`.
3. Adiciona automaticamente com `git add` apenas os arquivos que pertencem a uma das categorias legítimas permitidas.

---

## 4. Preservação Absoluta da Segurança e Escalabilidade

A segurança da esteira de deploy permaneça inquebrável, atingindo escalabilidade perpétua:
- **Segurança Antirruído:** Modificações em arquivos críticos de lógica do negócio (como os conectores de IA sob `includes/AI/`, controllers funcionais em `includes/Controllers/` ou scripts de interface do usuário sob `assets/`) continuam estritamente proibidas no deploy de release, abortando a publicação de forma imediata.
- **Escalabilidade Perpétua:** Novos relatórios técnicos criados em `docs/releases/` ou atualizações nos scripts PowerShell de preparação não exigirão mais nenhuma alteração na whitelist do deploy.

---

## Resumo para Release
### Melhorias
- Refatoração da validação da Working Tree de whitelist estática para mapeamento por categorias arquiteturais dinâmicas.
- Automatização da indexação do Git no final do deploy utilizando checagem dinâmica de arquivos alterados contra a função Test-IsFileAllowed.
- Eliminação permanente da necessidade de manutenção manual do script de deploy a cada introdução de novos relatórios ou scripts.
- Garantia contínua de segurança contra commit indevido de códigos funcionais ou experimentais de desenvolvimento.
