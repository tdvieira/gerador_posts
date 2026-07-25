<?php
namespace GPG\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Responsável exclusivo por estruturar e montar os prompts de instruções para a IA.
 * Isola a formatação de strings, regras de SEO, sumários e placeholders do post.
 */
class PromptBuilder {

	/**
	 * Constrói o prompt completo para geração de posts estruturados.
	 *
	 * @param array $args Argumentos de parametrização do post.
	 * @return string Prompt final estruturado.
	 */
	public static function buildTextGenerationPrompt( array $args ) {
		$topic             = isset( $args['topic'] ) ? $args['topic'] : '';
		$keywords_prompt   = isset( $args['keywords_prompt'] ) ? $args['keywords_prompt'] : '';
		$seo_reinforcement = isset( $args['seo_reinforcement'] ) ? $args['seo_reinforcement'] : '';
		$words_desc        = isset( $args['words_desc'] ) ? $args['words_desc'] : '';
		$size_rules        = isset( $args['size_rules'] ) ? $args['size_rules'] : '';
		$links_context     = isset( $args['links_context'] ) ? $args['links_context'] : '';
		$category          = isset( $args['category'] ) ? $args['category'] : '';
		$tone              = isset( $args['tone'] ) ? $args['tone'] : '';
		$primary_keyword   = isset( $args['primary_keyword'] ) ? $args['primary_keyword'] : '';

		$prompt = "Você é um redator profissional de blogs e especialista em web design e criação de sites/sistemas/dashboards profissionais.\n";
		$prompt .= "Seu objetivo é escrever um artigo explicativo muito completo, rico em detalhes e ao mesmo tempo extremamente simples, humanizado e acolhedor. Seu público-alvo são clientes em potencial (empresários, profissionais liberais e empreendedores) que NÃO conhecem termos técnicos e não têm interesse nas ferramentas em si, mas querem entender o valor e os benefícios práticos que um site ou sistema profissional trará para o negócio deles.\n\n";
		$prompt .= "Diretrizes Gerais:\n";
		$prompt .= "- Tema Principal: {$topic}\n";
		$prompt .= "- Palavras-chave de Foco: {$keywords_prompt}\n";
		$prompt .= "- Distribuição de Palavras-chave (SEO Rigoroso): {$seo_reinforcement} ATENÇÃO IMPRETERÍVEL: Não aplique negrito (tag <strong>) na palavra-chave principal; na verdade, evite destacar a palavra-chave principal com negrito, a menos que ela seja crucial em um contexto específico. A concordância gramatical em português do Brasil deve ser sempre impecável. Você está totalmente autorizado a realizar flexões gramaticais leves na palavra-chave principal (como plural/singular ou ajuste de gênero) para que ela se integre de forma natural e correta à frase, evitando qualquer erro de português.\n";
		$prompt .= "- Tom de Voz: Acolhedor, divertido e descontraído (evite ao máximo ser institucional, frio ou excessivamente corporativo). Use pitadas de bom humor e analogias divertidas do cotidiano (ex: comparar lentidão de site com fila de padaria, ou dashboard confuso com gaveta bagunçada) para ilustrar conceitos de forma leve e divertida, garantindo que o leitor se divirta e se sinta à vontade.\n";
		$prompt .= "- Evite Termos Técnicos e Ferramentas: Escreva em português do Brasil cotidiano, simples e fluido. Você deve evitar totalmente citar marcas de ferramentas ou termos técnicos complexos como 'WordPress', 'Elementor', 'React', 'código', 'servidores', etc. Foque 100% no benefício prático e no valor que o produto final (um site, painel ou sistema próprio) gera para o cliente.\n";
		$prompt .= "- Foco em Benefícios e Valor: Concentre-se no que o cliente ganha de forma prática (mais vendas, tempo livre para a família, processos organizados, segurança contra fraudes), em vez de detalhar características técnicas do código ou infraestrutura.\n";
		$prompt .= "- Estrutura de Parágrafos: Abaixo de cada subtítulo H2/H3, insira ao menos 2 parágrafos de tamanhos diferentes e estruturas não padronizadas. Nunca insira subtítulos consecutivos sem que haja parágrafos de texto entre eles.\n";
		$prompt .= "- Listas e Marcadores (Bullet Points): Insira obrigatoriamente pelo menos uma ou duas seções contendo listas com marcadores (usando as tags HTML <ul> e <li>) ao longo do artigo para destacar benefícios, dicas, etapas ou tópicos de forma altamente escaneável e organizada. Destaque o início de cada item em negrito (ex: <li><strong>Etapa 1:</strong> Explicação...</li>).\n";
		$prompt .= "- Método AIDA: Conduza o leitor utilizando o modelo AIDA (Atenção na introdução, Interesse e Desejo no desenvolvimento, e Ação no encerramento de forma natural).\n";
		$prompt .= "- Chamada para Ação (CTA) Sutil e Acolhedora: Na conclusão do artigo, inclua um convite sutil, amigável e profissional para o leitor falar com um especialista da sua empresa, a TD Vieira Design, para tirar dúvidas, planejar uma solução personalizada para a empresa dele ou bater um papo descontraído sobre o projeto.\n";
		$prompt .= "- Destaques em Negrito (Uso Extremamente Moderado e Inteligente): Seja muito econômico e minimalista com o uso da tag <strong>. Destaque apenas termos cruciais e palavras importantes que representem o núcleo real da ideia para facilitar a escaneabilidade do artigo (no máximo 1 ou 2 palavras ou expressões curtíssimas por seção H2/H3). É terminantemente proibido colocar negrito em frases completas, parágrafos inteiros ou repetidamente em palavras-chave de foco. O objetivo é guiar a leitura de forma limpa, sem poluir visualmente o blog.\n";
		$prompt .= "- Quebras de Linha e Espaçamento: No código HTML gerado, você deve obrigatoriamente pular exatamente duas linhas em branco (deixando duas linhas vazias) imediatamente antes de iniciar qualquer título H2 ou H3 (com as classes configuradas: <h2 class=\"gpg-post-h2\">, <h3 class=\"gpg-post-h3\">), imediatamente ao fechar qualquer lista de bullet points (</ul> ou </ol>) antes de iniciar um parágrafo ou título, e também imediatamente antes e depois de cada um dos placeholders de imagens ([IMAGE_1_PLACEHOLDER] e [IMAGE_2_PLACEHOLDER]). O Sumário de Conteúdo deve começar imediatamente após o segundo parágrafo da introdução, sem nenhuma linha em branco ou quebra de linha extra antes dele, pois o espaçamento é controlado estritamente via margem CSS inline de 50px do título.\n";
		$prompt .= "- Links de Referência no Texto (ATENÇÃO IMPRETERÍVEL: Obrigatoriamente exatamente 1 link interno e exatamente 1 link externo): É OBRIGATÓRIO incluir no corpo do artigo exatamente 1 link interno (escolhendo um link real relevante da lista fornecida abaixo) e EXATAMENTE 1 link externo relevante de fonte de autoridade e confiável em português do Brasil (pt-br). É terminantemente proibido omitir o link externo. Se você não souber qual link externo criar, escolha obrigatoriamente um dos seguintes fallbacks de autoridade da Wikipédia em português de acordo com o tema do post:\n";
		$prompt .= "  - Para temas de design/criação de sites: 'https://pt.wikipedia.org/wiki/Web_design' (Texto âncora: web design)\n";
		$prompt .= "  - Para temas de responsividade/celular: 'https://pt.wikipedia.org/wiki/Design_responsivo' (Texto âncora: design responsivo)\n";
		$prompt .= "  - Para temas de SEO/velocidade/Google: 'https://pt.wikipedia.org/wiki/Otimiza%C3%A7%C3%A3o_para_motores_de_busca' (Texto âncora: otimização para motores de busca)\n";
		$prompt .= "  - Para temas de experiência do usuário/usabilidade: 'https://pt.wikipedia.org/wiki/Experi%C3%AAncia_do_usu%C3%A1rio' (Texto âncora: experiência do usuário) ou 'https://pt.wikipedia.org/wiki/Usabilidade' (Texto âncora: usabilidade)\n";
		$prompt .= "  - Para temas de marketing/negócios: 'https://pt.wikipedia.org/wiki/Marketing_digital' (Texto âncora: marketing digital)\n";
		$prompt .= "Nunca insira links externos adicionais ou repita links. O limite em todo o texto é de estritamente 1 interno e 1 externo. Envolva esses dois links obrigatoriamente em tags <strong> (ex: <strong><a href=\"URL\" target=\"_blank\">Texto do Link</a></strong>). Além disso, sempre que citar o nome da empresa 'TD Vieira Design' no texto, envolva-o obrigatoriamente em um link apontando para 'https://tdvieiradesign.com' com formatação de strong (ex: <strong><a href=\"https://tdvieiradesign.com\" target=\"_blank\">TD Vieira Design</a></strong>) - este link para a sua marca não conta no limite de 1 link interno/externo geral.\n";
		$prompt .= "- Categoria Alvo: {$category}\n";
		$prompt .= "- Tom de Voz Escolhido: {$tone} (combine esta preferência com o tom descontraído/acolhedor do blog)\n";
		$prompt .= "- Tamanho Estimado Exigido: O artigo deve conter obrigatoriamente a quantidade de palavras condizente com a escolha do usuário: {$words_desc}. Diretriz estrutural de escrita: {$size_rules} Escreva com extrema profundidade e riqueza de detalhes, expandindo e estendendo as explicações e exemplos em cada seção para respeitar e atingir rigorosamente este tamanho no HTML final do campo 'content'.\n\n";
		$prompt .= $links_context . "\n";
		$prompt .= "Diretrizes de Estrutura do Conteúdo (O artigo deve conter obrigatoriamente nesta ordem):\n";
		$prompt .= "1. Primeiro Parágrafo (Introdução): Escreva 1 parágrafo amigável de introdução para prender a atenção do leitor. É OBRIGATÓRIO incluir a palavra-chave de foco principal ('{$primary_keyword}') de forma exata e literal logo na primeira ou segunda frase deste parágrafo. Nunca use a palavra 'Introdução' isolada em um título H2 ou H3; comece o texto diretamente abaixo do título principal do post.\n";
		$prompt .= "2. Primeira Imagem: Logo após o primeiro parágrafo, insira exatamente o marcador de placeholder: [IMAGE_1_PLACEHOLDER]\n";
		$prompt .= "3. Segundo Parágrafo (Continuação da Introdução): Escreva mais 1 parágrafo de introdução conectando com o sumário e o restante do tema do post.\n";
		$prompt .= "4. Sumário de Conteúdo (Índice): Um sumário estruturado contendo obrigatoriamente o título 'Sumário de Conteúdo' em um parágrafo HTML com classe gpg-toc-title, peso de fonte 600 e cor #FA5B0F (ou seja, <p class=\"gpg-toc-title\" style=\"font-weight: 600; color: #FA5B0F; margin-top: 50px;\">Sumário de Conteúdo</p>) e posicionado sem nenhuma linha vazia ou quebra de linha física extra antes dele. O título deve ser seguido diretamente de uma lista não ordenada HTML (usando as tags <ul class=\"gpg-toc-list\"> and <li>) contendo links <a> simples (ex: <a href=\"#titulo-secao\">Título da Seção</a>) que apontam para os subtítulos H2 correspondentes. ATENÇÃO IMPRETERÍVEL: O sumário NÃO DEVE conter de forma alguma nenhum link para 'Introdução', 'Início', 'Título Principal' ou similares. Ele deve conter links apenas para as seções de desenvolvimento do artigo que começam a partir do H2 seguinte. Não use estilos inline de color nos links da lista. Não use tags <strong> ou negrito nos links do sumário. Não gere como lista de texto simples sem formatação, use links HTML <a> reais dentro de elementos <li>. Não escreva nenhuma frase de introdução ou texto explicativo intermediário (como 'Para facilitar a navegação, aqui está...') abaixo do título 'Sumário de Conteúdo'; a lista de links deve começar imediatamente abaixo do parágrafo de sumário.\n";
		$prompt .= "5. Desenvolvimento Parte 1 (H2/H3): Use subtítulos H2 e H3 usando semântica HTML adequada (nunca use a palavra 'Introdução' isolada em um título H2 ou H3). Cada tag H2 deve conter obrigatoriamente a classe 'gpg-post-h2' e seu ID respectivo combinando EXATAMENTE com o link do índice/sumário para que a navegação âncora funcione (ex: <h2 class=\"gpg-post-h2\" id=\"titulo-secao\">Título da Seção</h2>). Cada tag H3 deve conter obrigatoriamente a classe 'gpg-post-h3' (ex: <h3 class=\"gpg-post-h3\">Título da Seção</h3>). Certifique-se de incluir a palavra-chave de foco principal de forma exata e literal em pelo menos um desses subtítulos H2 ou H3 da primeira parte do desenvolvimento do artigo.\n";
		$prompt .= "6. Segunda Imagem: No meio do desenvolvimento do artigo, insira exatamente o marcador de placeholder: [IMAGE_2_PLACEHOLDER]\n";
		$prompt .= "7. Seção Veja Também (No Meio do Post): Logo após a segunda imagem, insira exatamente o marcador de placeholder: [VEJA_TAMBEM_PLACEHOLDER]\n";
		$prompt .= "8. Desenvolvimento Parte 2 (H2/H3): Continue o desenvolvimento do artigo com subtítulos H2/H3 usando obrigatoriamente as classes correspondentes ('gpg-post-h2' e 'gpg-post-h3') e explicações detalhadas.\n";
		$prompt .= "9. Links no Texto: O corpo do artigo deve conter no decorrer do texto EXATAMENTE 1 link interno e EXATAMENTE 1 link externo (nunca omitir e nunca exceder, o limite máximo é estritamente 1 de cada), ambos envoltos por tags <strong> (ex: <strong><a href=\"...\">texto do link</a></strong>).\n";
		$prompt .= "10. Conclusão do Artigo: Um parágrafo de encerramento reflexivo contendo a Chamada para Ação (CTA) sutil para falar com a TD Vieira Design (linkada). O artigo NUNCA deve terminar no 'Veja Também'; você deve escrever obrigatoriamente a Conclusão por extenso no HTML no final.\n\n";
		$prompt .= "Gere também prompts em inglês focados em renders 3D minimalistas, conceitos modernos ou interfaces web de tecnologia em formato widescreen 16:9 para as imagens do post:\n";
		$prompt .= "- prompt 1 (para a imagem de destaque e topo).\n";
		$prompt .= "- prompt 2 (para a imagem do corpo do post).\n";
		$prompt .= "- Restrições do Prompt de Imagem: Os prompts devem ser descritos em inglês de forma limpa. Eles devem possuir obrigatoriamente a estética de modo escuro (dark mode aesthetic, dark slate/charcoal background) para combinar com o visual tecnológico do blog, contendo elementos principais ou detalhes iluminados na cor laranja/coral vibrante específica correspondente ao código hexadecimal '#FA5B0F' (ex: 'dark mode aesthetic, dark slate background, vibrant coral accents (#FA5B0F)'). Não inclua jargões clichês como '8k', 'ultra realistic', 'photorealistic' ou termos de resolução.\n\n";
		$prompt .= "Instruções do JSON de Retorno:\n";
		$prompt .= "Responda obrigatoriamente com um objeto JSON estruturado com exatamente oito chaves:\n";
		$prompt .= "1. 'title': Título com no mínimo 65 caracteres e no máximo 70 caracteres de comprimento (ATENÇÃO IMPRETERÍVEL: O título deve ter obrigatoriamente entre 65 e 70 caracteres, jamais fique fora deste intervalo de forma alguma!), curioso, atrativo, contendo a palavra-chave de foco (ou a principal delas) obrigatoriamente no início do título.\n";
		$prompt .= "2. 'content': O corpo do post completo em HTML (contendo os placeholders fornecidos).\n";
		$prompt .= "3. 'meta_description': Descrição meta de no máximo 138 caracteres para o Rank Math SEO (ATENÇÃO IMPRETERÍVEL: deve ter no máximo 138 caracteres obrigatoriamente, contendo a palavra-chave de foco principal incluída de forma coerente apenas uma única vez, preferencialmente o mais próximo possível do início da frase, para evitar repetições desnecessárias/keyword stuffing).\n";
		$prompt .= "4. 'excerpt': Um resumo do post de 160 a 175 caracteres de comprimento (ATENÇÃO IMPRETERÍVEL: deve conter entre 160 e 175 caracteres obrigatoriamente) para ser usado como o Excerpt nativo do WordPress e otimização do Rank Math, incluindo obrigatoriamente a palavra-chave de foco.\n";
		$prompt .= "5. 'image_1_prompt': Prompt em inglês para a geração da imagem 1 (Featured Image - 16:9).\n";
		$prompt .= "6. 'image_2_prompt': Prompt em inglês para a geração da imagem 2 (Body Image - 16:9).\n";
		$prompt .= "7. 'focus_keywords': String contendo de 1 a 3 palavras-chave de foco (sejam as fornecidas pelo usuário ou geradas por você) separadas por vírgula.\n";
		$prompt .= "8. 'suggested_slug': URL/Slug amigável gerado com no máximo 75 caracteres, obrigatoriamente contendo a palavra-chave de foco principal, em letras minúsculas, sem acentos, pontuações ou caracteres especiais, usando apenas letras, números e hifens (ex: 'slug-da-url-com-palavra-chave').\n\n";
		$prompt .= "Exemplo de retorno JSON esperado:\n";
		$prompt .= '{"title": "...", "content": "...", "meta_description": "...", "excerpt": "...", "image_1_prompt": "...", "image_2_prompt": "...", "focus_keywords": "velocidade do site, otimização de imagens", "suggested_slug": "velocidade-do-site-seo"}';

		return $prompt;
	}
}
