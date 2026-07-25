# Regras do Domínio de Documentação (Regras do Domínio de Documentação)

Este documento estabelece as regras normativas permanentes para redação, estruturação e portabilidade do ecossistema documental técnico do plugin.

---

## 🏛️ Organização e Localização

1.  **Centralização da Documentação:** Toda a documentação técnica operacional e de engenharia (Manual do Desenvolvedor) deve residir na subpasta oficial `docs/` na raiz do repositório do plugin.
2.  **Responsabilidade Única (Responsabilidade Única documental):** Cada manual técnico deve tratar de um escopo operacional bem definido, sendo proibido duplicar conteúdos permanentes entre arquivos.
3.  **Uso de Referências Cruzadas:** Informações já documentadas em outros manuais devem ser indicadas através de resumos concisos combinados com links markdown de navegação direta.
4.  **Estrutura de Categorias docs/:** O ecossistema de documentação técnica e relatórios de conformidade deve ser obrigatoriamente segmentado nas seguintes subpastas oficiais:
    *   `docs/architecture/` para planos, decisões de design e relatórios da arquitetura de software.
    *   `docs/governance/` para políticas normativas, responsabilidades e ganchos de controle do projeto.
    *   `docs/migration/` para manuais e relatórios técnicos de implantação de fases de refatoração ou migrações incrementais.
    *   `docs/qa/` para inventários, checklists de testes funcionais, relatórios de prontidão de release e auditorias de qualidade.
    *   `docs/releases/` para checklists, processos e relatórios específicos de empacotamento e preparação final de releases de produção.
    *   `docs/history/` para relatórios históricos obsoletos, evoluções de agentes e registros passados de marcos descontinuados.
5.  **Proibição de Relatórios na Raiz:** É terminantemente proibida a criação de novos arquivos de relatórios (incluindo qualquer arquivo com sufixo `_report.md` ou `_plan.md`) diretamente na raiz do repositório. Todo novo artefato documental ou relatório de fase deve ser obrigatoriamente salvo no diretório temático correspondente em `docs/`.
6.  **Pasta de Build build/:** Todo arquivo compactado (ZIP) gerado para distribuição oficial e instalação do plugin no WordPress deve residir obrigatoriamente dentro do diretório `build/` na raiz do projeto (ex: `build/gerador-posts-gemini.zip`). É terminantemente proibido gerar, armazenar ou manter arquivos compactados ZIP diretamente na raiz do repositório, garantindo que o arquivo principal de entrada e cabeçalho do plugin permaneçam isolados e desimpedidos de arquivos binários pesados de build.

---

## 🔗 Portabilidade e Links Markdown

1.  **Proibição de Links Absolutos:** É terminantemente proibido utilizar links absolutos de arquivos locais (`file:///...`) em documentos permanentes.
2.  **Uso Obrigatório de Caminhos Relativos:** Todas as referências cruzadas entre documentos técnicos, metadados de memória e caminhos do código do plugin devem ser formatadas como caminhos relativos compatíveis com GitHub e independentes de sistema operacional (ex: `./DEVELOPMENT_WORKFLOW.md`, `../gerador-posts-gemini.php`).

---

## 📂 Requisitos Estruturais de Manuais

Guias operacionais e manuais de engenharia devem conter obrigatoriamente:
*   A seção **"Quando consultar este documento?"** no topo, definindo as condições práticas em que a leitura do arquivo é recomendada.
*   A seção **"Documentos relacionados"** no rodapé, contendo links de navegação para as demais referências técnicas pertinentes do Manual do Desenvolvedor.
