# Relatório Técnico: Arquitetura de Configuração Dinâmica da Working Tree
**Pipeline Oficial de Release — v2.2.3**

---

## 1. Contexto e Resolução de Bloqueio Histórico (v2.0.5)

Durante a publicação da versão estável `2.0.5` do plugin, a esteira de deploy encontrou uma inconformidade que interrompeu o pipeline de publicação. 

**Causa Raiz:**
O script `publish_release.ps1` executava a validação estrutural da Working Tree através de Whitelists e categorias codificadas de forma rígida em seu código procedural (`Test-IsFileAllowed`). Ao introduzir novos manuais técnicos, relatórios de deploy ou novos arquivos de infraestrutura, a esteira classificou-os como ruído estranho e bloqueou a release por segurança.

Para sanar esse gargalo operacional em definitivo, eliminamos as listas estáticas internas e migramos para uma validação orientada a dados e baseada em configuração externa centralizada.

---

## 2. Configuração Externa sob o Princípio "Configuration over Code"

Criamos o arquivo oficial de configuração arquitetural **`.agents/config/pipeline-categories.json`** na raiz do repositório, que atua como centralizador exclusivo de classificação estrutural.

Esse JSON define dois vetores funcionais:
-   `exact_matches`: Lista de arquivos produtivos exatos (README.md, manifesto, bootstrap, updater, ZIP).
-   `wildcard_matches`: Padrões e subpastas operacionais e de relatórios (scripts de pipeline, documentações markdown na pasta `docs/`, memória permanente sob `.agents/memory/`, etc.).

---

## 3. Funcionamento do publish_release.ps1

O script `publish_release.ps1` lê e decodifica esse arquivo na inicialização utilizando `ConvertFrom-Json` com decodificação UTF-8 explícita.
-   **Segurança no Inicializador:** Caso o arquivo de configuração esteja ausente, com estrutura JSON inválida ou corrompido, a esteira interrompe o deploy imediatamente antes de efetuar qualquer comando do Git, instruindo o operador e prevenindo comportamentos indefinidos.
-   **Desacoplamento de Baixo Nível:** Qualquer novo manual ou relatório Markdown criado sob as pastas autorizadas do projeto passa a ser reconhecido e validado automaticamente sem exigir edições lógicas do script de publicação.

---

## 4. Confirmação de Integridade das Lógicas

Confirmamos que esta alteração alterou exclusivamente a forma como a Working Tree é validada:
-   **Estabilidade de Empacotamento:** O script `build_release.ps1` mantém sua lista `$root_files` centralizada para empacotar o ZIP.
-   **Operações Git e CLI:** Os comandos de tagging local SemVer, comissões de release, verificação remota com GitHub CLI e uploads de ZIP operam sob a mesma lógica original homologada.

---

## Resumo para Release
### Melhorias
- Migração definitiva da validação da Working Tree para configuração externa em JSON (.agents/config/pipeline-categories.json) sob o princípio "Configuration over Code".
- Desacoplamento completo do script publish_release.ps1 de evoluções taxonômicas e novas documentações do repositório.
- Prevenção ativa de falsos bloqueios de deploy (como ocorrido na v2.0.5) decorrentes de atualizações manuais ausentes.
