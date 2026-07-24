# Regras do Domínio de Documentação (Documentation Domain Rules)

Este documento estabelece as regras normativas permanentes para redação, estruturação e portabilidade do ecossistema documental técnico do plugin.

---

## 🏛️ Organização e Localização

1.  **Centralização da Documentação:** Toda a documentação técnica operacional e de engenharia (Developer Handbook) deve residir na subpasta oficial `/docs` na raiz do repositório do plugin.
2.  **Responsabilidade Única (SRP documental):** Cada manual técnico deve tratar de um escopo operacional bem definido, sendo proibido duplicar conteúdos permanentes entre arquivos.
3.  **Uso de Referências Cruzadas:** Informações já documentadas em outros manuais devem ser indicadas através de resumos concisos combinados com links markdown de navegação direta.

---

## 🔗 Portabilidade e Links Markdown

1.  **Proibição de Links Absolutos:** É terminantemente proibido utilizar links absolutos de arquivos locais (`file:///...`) em documentos permanentes.
2.  **Uso Obrigatório de Caminhos Relativos:** Todas as referências cruzadas entre documentos técnicos, metadados de memória e caminhos do código do plugin devem ser formatadas como caminhos relativos compatíveis com GitHub e independentes de sistema operacional (ex: `./DEVELOPMENT_WORKFLOW.md`, `../gerador-posts-gemini.php`).

---

## 📂 Requisitos Estruturais de Manuais

Guias operacionais e manuais de engenharia devem conter obrigatoriamente:
*   A seção **"Quando consultar este documento?"** no topo, definindo as condições práticas em que a leitura do arquivo é recomendada.
*   A seção **"Documentos relacionados"** no rodapé, contendo links de navegação para as demais referências técnicas pertinentes do Handbook.
