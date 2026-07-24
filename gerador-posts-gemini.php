<?php
/**
 * Plugin Name: Gerador de Posts
 * Description: Cria posts estruturados seguindo o padrão do blog, gera até 2 imagens 16:9, vincula SEO (Rank Math) e agenda a publicação em lote.
 * Version: 1.2.4
 * Author: Thiago Vieira
 * Text Domain: gerador-posts-gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// require_once plugin_dir_path(__FILE__) . 'includes/updater.php';
if (file_exists(plugin_dir_path(__FILE__) . 'includes/updater.php')) {
    require_once plugin_dir_path(__FILE__) . 'includes/updater.php';
} else {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>Arquivo includes/updater.php não encontrado.</p></div>';
    });
}

// Remover chave obsoleta do Pollinations do banco de dados ao carregar o plugin
if ( get_option( 'gpg_pollinations_api_key' ) !== false ) {
	delete_option( 'gpg_pollinations_api_key' );
}

// Inicializar categorias padrão ao carregar o plugin
add_action( 'init', 'gpg_ensure_categories_exist' );

function gpg_ensure_categories_exist() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$categories = array(
		'Benefícios de ter um Site',
		'Design e Experiência do Usuário',
		'Dicas e Boas Práticas',
		'Histórias de Sucesso',
		'Marketing Digital e E-commerce',
		'Segurança e Manutenção',
		'Tendências e Novidades',
		'Tutoriais Simples'
	);
	foreach ( $categories as $cat ) {
		if ( ! term_exists( $cat, 'category' ) ) {
			wp_insert_term( $cat, 'category' );
		}
	}
}

// Carregar estilos no frontend
add_action( 'wp_enqueue_scripts', 'gpg_enqueue_frontend_styles' );

function gpg_enqueue_frontend_styles() {
	wp_enqueue_style( 'gerador-posts-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css', array(), '1.2.3' );
}

// Carregar estilos no admin apenas na página do plugin
add_action( 'admin_enqueue_scripts', 'gpg_enqueue_admin_styles' );

function gpg_enqueue_admin_styles( $hook ) {
	if ( 'posts_page_gerador-posts-gemini' === $hook ) {
		wp_enqueue_style( 'gerador-posts-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), '1.2.3' );
		
		// Enfileirar biblioteca Puter.js de forma nativa
		wp_enqueue_script( 'puter-js', 'https://js.puter.com/v2/', array(), '2.0.0', false );
		
		// Enfileirar JavaScript administrativo com dependência do jQuery e Puter.js
		wp_enqueue_script( 'gerador-posts-admin-js', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array( 'jquery', 'puter-js' ), '1.2.3', true );
		
		// Gerar o HTML das categorias para passar ao JS
		$blog_categories = array(
			'Benefícios de ter um Site',
			'Design e Experiência do Usuário',
			'Dicas e Boas Práticas',
			'Histórias de Sucesso',
			'Marketing Digital e E-commerce',
			'Segurança e Manutenção',
			'Tendências e Novidades',
			'Tutoriais Simples'
		);
		$categories_options_html = '<option value="" disabled selected>Escolha a categoria...</option>';
		foreach ( $blog_categories as $cat ) {
			$categories_options_html .= '<option value="' . esc_attr( $cat ) . '">' . esc_html( $cat ) . '</option>';
		}
		
		// Injetar dados dinâmicos do PHP no script JS externo
		wp_localize_script( 'gerador-posts-admin-js', 'gpgAdminData', array(
			'nonce'             => wp_create_nonce( 'gpg_admin_nonce' ),
			'categoriesOptions' => $categories_options_html
		) );
	}
}

// Registrar o menu administrativo
add_action( 'admin_menu', 'gpg_register_menu' );

function gpg_register_menu() {
	add_posts_page(
		__( 'Gerador de Posts', 'gerador-posts-gemini' ),
		__( 'Gerador de Posts', 'gerador-posts-gemini' ),
		'manage_options',
		'gerador-posts-gemini',
		'gpg_render_admin_page'
	);
}

// Incluir a página de administração
function gpg_render_admin_page() {
	require_once plugin_dir_path( __FILE__ ) . 'admin-ui.php';
}

// Registrar ações de AJAX
add_action( 'wp_ajax_gpg_generate_post', 'gpg_handle_generate_post' );
add_action( 'wp_ajax_gpg_generate_image', 'gpg_handle_generate_image' );
add_action( 'wp_ajax_gpg_save_post', 'gpg_handle_save_post' );
add_action( 'wp_ajax_gpg_save_settings', 'gpg_handle_save_settings' );
add_action( 'wp_ajax_gpg_delete_post', 'gpg_handle_delete_post' );

/**
 * AJAX: Salvar chaves de API
 */
function gpg_handle_save_settings() {
	check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
	}

	$gemini_key = isset( $_POST['gemini_api_key'] ) ? sanitize_text_field( $_POST['gemini_api_key'] ) : '';
	$openai_key = isset( $_POST['openai_api_key'] ) ? sanitize_text_field( $_POST['openai_api_key'] ) : '';
	$groq_key   = isset( $_POST['groq_api_key'] ) ? sanitize_text_field( $_POST['groq_api_key'] ) : '';
	$puter_key  = isset( $_POST['puter_api_key'] ) ? sanitize_text_field( $_POST['puter_api_key'] ) : '';
	
	update_option( 'gpg_gemini_api_key', $gemini_key );
	update_option( 'gpg_openai_api_key', $openai_key );
	update_option( 'gpg_groq_api_key', $groq_key );
	update_option( 'gpg_puter_api_key', $puter_key );

	wp_send_json_success( array( 'message' => __( 'Configurações de chaves de API salvas com sucesso!', 'gerador-posts-gemini' ) ) );
}

/**
 * AJAX: Chamar a API de Texto (Gemini ou OpenAI)
 */
function gpg_handle_generate_post() {
	check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
	}

	$data_prepared = gpg_prepare_generation_data();

	$text_provider     = $data_prepared['text_provider'];
	$text_model        = $data_prepared['text_model'];
	$topic             = $data_prepared['topic'];
	$keywords          = $data_prepared['keywords'];
	$tone              = $data_prepared['tone'];
	$length            = $data_prepared['length'];
	$category          = $data_prepared['category'];
	$keywords_prompt   = $data_prepared['keywords_prompt'];
	$seo_reinforcement = $data_prepared['seo_reinforcement'];
	$words_desc        = $data_prepared['words_desc'];
	$size_rules        = $data_prepared['size_rules'];
	$links_context     = $data_prepared['links_context'];
	$primary_keyword   = $data_prepared['primary_keyword'];

	if ( empty( $topic ) ) {
		wp_send_json_error( array( 'message' => __( 'O tema principal é obrigatório.', 'gerador-posts-gemini' ) ) );
	}

	// Montar prompt refinado baseado no modelo estrito do blog
	$prompt = gpg_build_generation_prompt( array(
		'topic'             => $topic,
		'keywords_prompt'   => $keywords_prompt,
		'seo_reinforcement' => $seo_reinforcement,
		'words_desc'        => $words_desc,
		'size_rules'        => $size_rules,
		'links_context'     => $links_context,
		'category'          => $category,
		'tone'              => $tone,
		'primary_keyword'   => $primary_keyword,
	) );

	if ( $text_provider === 'openai' ) {
		// --- GERAÇÃO OPENAI ---
		$openai_key = get_option( 'gpg_openai_api_key' );
		if ( empty( $openai_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor, configure sua chave da OpenAI.', 'gerador-posts-gemini' ) ) );
		}

		$model = ! empty( $text_model ) ? $text_model : 'gpt-5-mini';

		$response = gpg_call_openai_api( $prompt, $model );

		if ( is_wp_error( $response ) ) {
			$err_msg = $response->get_error_message();
			if ( strpos( strtolower( $err_msg ), 'timeout' ) !== false || strpos( strtolower( $err_msg ), 'timed out' ) !== false ) {
				$msg = __( 'A requisição para a OpenAI expirou (Timeout). A geração de artigos longos pode levar mais tempo que o esperado. Por favor, tente novamente.', 'gerador-posts-gemini' );
			} else {
				$msg = __( 'Conexão falhou com a OpenAI: ', 'gerador-posts-gemini' ) . $err_msg;
			}
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Erro desconhecido na OpenAI.', 'gerador-posts-gemini' );
			wp_send_json_error( array( 'message' => sprintf( __( 'Erro na OpenAI (Status %d): %s', 'gerador-posts-gemini' ), $response_code, $error_msg ) ) );
		}

		$data = json_decode( $response_body, true );
		$json_text = isset( $data['choices'][0]['message']['content'] ) ? $data['choices'][0]['message']['content'] : '';

	} elseif ( $text_provider === 'groq' ) {
		// --- GERAÇÃO GROQ ---
		$groq_key = get_option( 'gpg_groq_api_key' );
		if ( empty( $groq_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor, configure sua chave do Groq.', 'gerador-posts-gemini' ) ) );
		}

		$model = ! empty( $text_model ) ? $text_model : 'llama-3.1-8b';

		$response = gpg_call_groq_api( $prompt, $model );

		if ( is_wp_error( $response ) ) {
			$err_msg = $response->get_error_message();
			if ( strpos( strtolower( $err_msg ), 'timeout' ) !== false || strpos( strtolower( $err_msg ), 'timed out' ) !== false ) {
				$msg = __( 'A requisição para o Groq expirou (Timeout). Por favor, tente novamente.', 'gerador-posts-gemini' );
			} else {
				$msg = __( 'Conexão falhou com o Groq: ', 'gerador-posts-gemini' ) . $err_msg;
			}
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Erro desconhecido no Groq.', 'gerador-posts-gemini' );
			wp_send_json_error( array( 'message' => sprintf( __( 'Erro no Groq (Status %d): %s', 'gerador-posts-gemini' ), $response_code, $error_msg ) ) );
		}

		$data = json_decode( $response_body, true );
		$json_text = isset( $data['choices'][0]['message']['content'] ) ? $data['choices'][0]['message']['content'] : '';

	} else {
		// --- GERAÇÃO GEMINI ---
		$gemini_key = get_option( 'gpg_gemini_api_key' );
		if ( empty( $gemini_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Por favor, configure sua chave do Gemini.', 'gerador-posts-gemini' ) ) );
		}

		$model = ! empty( $text_model ) ? $text_model : 'gemini-3.5-flash';

		$response = gpg_call_gemini_api( $prompt, $model );

		if ( is_wp_error( $response ) ) {
			$err_msg = $response->get_error_message();
			if ( strpos( strtolower( $err_msg ), 'timeout' ) !== false || strpos( strtolower( $err_msg ), 'timed out' ) !== false ) {
				$msg = __( 'A requisição para o Gemini expirou (Timeout). A geração de artigos longos pode levar mais tempo que o esperado. Por favor, tente novamente.', 'gerador-posts-gemini' );
			} else {
				$msg = __( 'Conexão falhou com o Gemini: ', 'gerador-posts-gemini' ) . $err_msg;
			}
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : '';
			if ( empty( $error_msg ) ) {
				$error_msg = sprintf( __( 'Erro na geração de texto (Status %d): %s', 'gerador-posts-gemini' ), $response_code, $response_body );
			}
			wp_send_json_error( array( 'message' => sprintf( __( 'Erro no Gemini (Status %d): %s', 'gerador-posts-gemini' ), $response_code, $error_msg ) ) );
		}

		$data = json_decode( $response_body, true );
		$json_text = isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ? $data['candidates'][0]['content']['parts'][0]['text'] : '';
	}

	$result = json_decode( $json_text, true );

	// Tratar título longo via reescrita inteligente por IA
	if ( isset( $result['title'] ) && mb_strlen( trim( $result['title'] ) ) > 70 ) {
		$focus_kw = isset( $result['focus_keywords'] ) ? $result['focus_keywords'] : $keywords;
		$rewritten = gpg_rewrite_title_concise( $result['title'], $focus_kw, $text_provider, $text_model );
		if ( $rewritten !== false && mb_strlen( $rewritten ) > 0 ) {
			$result['title'] = $rewritten;
		}
	}

	// Limitar o título estritamente a 70 caracteres no retorno da IA como segurança final
	if ( isset( $result['title'] ) ) {
		$result['title'] = gpg_limit_title_length( $result['title'], 70 );
	}

	// Limitar o resumo (excerpt) estritamente a 175 caracteres no retorno da IA como segurança final
	if ( isset( $result['excerpt'] ) ) {
		$result['excerpt'] = gpg_limit_excerpt_length( $result['excerpt'] );
	}

	// Limitar a meta descrição estritamente a 138 caracteres no retorno da IA como segurança final
	if ( isset( $result['meta_description'] ) ) {
		$result['meta_description'] = gpg_limit_excerpt_length( $result['meta_description'], 138 );
	}

	if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $result['title'] ) || ! isset( $result['content'] ) || ! isset( $result['meta_description'] ) || ! isset( $result['excerpt'] ) || ! isset( $result['image_1_prompt'] ) || ! isset( $result['image_2_prompt'] ) || ! isset( $result['focus_keywords'] ) || ! isset( $result['suggested_slug'] ) ) {
		wp_send_json_error( array( 
			'message' => __( 'A IA falhou em formatar a resposta no formato JSON de 8 chaves exigido.', 'gerador-posts-gemini' ),
			'raw' => $json_text 
		) );
	}

	// Validar e limpar links quebrados (404) no HTML gerado
	if ( ! empty( $result['content'] ) ) {
		$result['content'] = gpg_validate_and_clean_links( $result['content'] );
		
		// Forçar linkagem automática da marca TD Vieira Design para o site oficial
		$result['content'] = preg_replace_callback(
			'/<a\b[^>]*>.*?<\/a>(*SKIP)(*F)|\bTD Vieira Design\b/i',
			function( $matches ) {
				return '<strong><a href="https://tdvieiradesign.com" target="_blank">TD Vieira Design</a></strong>';
			},
			$result['content']
		);

		// Garantir que haja exatamente 2 quebras de linha após o fechamento de listas (bullet points)
		$result['content'] = preg_replace( '/<\/ul>(\s*\n)*/i', "</ul>\n\n", $result['content'] );
		$result['content'] = preg_replace( '/<\/ol>(\s*\n)*/i', "</ol>\n\n", $result['content'] );

		// Limitar quantidade de links de referência no texto (máximo 1 interno e 1 externo)
		$result['content'] = gpg_limit_article_links( $result['content'] );
	}

	wp_send_json_success( $result );
}

/**
 * Funções Auxiliares de Chamadas HTTP das APIs de Texto
 */
function gpg_call_openai_api( $prompt, $model ) {
	$openai_key = get_option( 'gpg_openai_api_key' );
	$body = array(
		'model' => $model,
		'messages' => array(
			array(
				'role' => 'user',
				'content' => $prompt
			)
		),
		'response_format' => array( 'type' => 'json_object' ),
		'max_tokens' => 4096
	);

	$url = 'https://api.openai.com/v1/chat/completions';

	return wp_remote_post( $url, array(
		'headers'   => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $openai_key
		),
		'body'      => wp_json_encode( $body ),
		'timeout'   => 90,
	) );
}

function gpg_call_groq_api( $prompt, $model ) {
	$groq_key = get_option( 'gpg_groq_api_key' );
	$body = array(
		'model' => $model,
		'messages' => array(
			array(
				'role' => 'user',
				'content' => $prompt
			)
		),
		'response_format' => array( 'type' => 'json_object' ),
		'max_tokens' => 4096
	);

	$url = 'https://api.groq.com/openai/v1/chat/completions';

	return wp_remote_post( $url, array(
		'headers'   => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $groq_key
		),
		'body'      => wp_json_encode( $body ),
		'timeout'   => 90,
	) );
}

function gpg_call_gemini_api( $prompt, $model ) {
	$gemini_key = get_option( 'gpg_gemini_api_key' );
	$body = array(
		'contents' => array(
			array(
				'parts' => array(
					array(
						'text' => $prompt
					)
				)
			)
		),
		'generationConfig' => array(
			'responseMimeType' => 'application/json',
			'responseSchema' => array(
				'type' => 'OBJECT',
				'properties' => array(
					'title' => array( 'type' => 'STRING' ),
					'content' => array( 'type' => 'STRING' ),
					'meta_description' => array( 'type' => 'STRING' ),
					'excerpt' => array( 'type' => 'STRING' ),
					'image_1_prompt' => array( 'type' => 'STRING' ),
					'image_2_prompt' => array( 'type' => 'STRING' ),
					'focus_keywords' => array( 'type' => 'STRING' ),
					'suggested_slug' => array( 'type' => 'STRING' )
				),
				'required' => array( 'title', 'content', 'meta_description', 'excerpt', 'image_1_prompt', 'image_2_prompt', 'focus_keywords', 'suggested_slug' )
			),
			'maxOutputTokens' => 8192
		)
	);

	$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $gemini_key;

	return wp_remote_post( $url, array(
		'headers'   => array( 'Content-Type' => 'application/json' ),
		'body'      => wp_json_encode( $body ),
		'timeout'   => 90,
	) );
}

/**
 * Montar Prompt de Geração de Posts Otimizados para IA
 */
function gpg_build_generation_prompt( $args ) {
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
	$prompt .= "- Foco em Benefícios e Valor: Concentre-se no que o cliente ganha de forma prática (mais vendas, tempo livre para a família, processos organizados, segurança contra fraudes), in vez de detalhar características técnicas do código ou infraestrutura.\n";
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
	$prompt .= "4. Sumário de Conteúdo (Índice): Um sumário estruturado contendo obrigatoriamente o título 'Sumário de Conteúdo' em um parágrafo HTML com classe gpg-toc-title, peso de fonte 600 e cor #FA5B0F (ou seja, <p class=\"gpg-toc-title\" style=\"font-weight: 600; color: #FA5B0F; margin-top: 50px;\">Sumário de Conteúdo</p>) e posicionado sem nenhuma linha vazia ou quebra de linha física extra antes dele. O título deve ser seguido diretamente de uma lista não ordenada HTML (usando as tags <ul class=\"gpg-toc-list\"> e <li>) contendo links <a> simples (ex: <a href=\"#titulo-secao\">Título da Seção</a>) que apontam para os subtítulos H2 correspondentes. ATENÇÃO IMPRETERÍVEL: O sumário NÃO DEVE conter de forma alguma nenhum link para 'Introdução', 'Início', 'Título Principal' ou similares. Ele deve conter links apenas para as seções de desenvolvimento do artigo que começam a partir do H2 seguinte. Não use estilos inline de color nos links da lista. Não use tags <strong> ou negrito nos links do sumário. Não gere como lista de texto simples sem formatação, use links HTML <a> reais dentro de elementos <li>. Não escreva nenhuma frase de introdução ou texto explicativo intermediário (como 'Para facilitar a navegação, aqui está...') abaixo do título 'Sumário de Conteúdo'; a lista de links deve começar imediatamente abaixo do parágrafo de sumário.\n";
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

/**
 * Coletar, Sanitizar e Organizar os Dados da Requisição para Geração de Post
 */
function gpg_prepare_generation_data() {
	$text_provider = isset( $_POST['text_provider'] ) ? sanitize_text_field( $_POST['text_provider'] ) : 'gemini';
	$text_model    = isset( $_POST['text_model'] ) ? sanitize_text_field( $_POST['text_model'] ) : '';
	$topic         = isset( $_POST['topic'] ) ? sanitize_text_field( $_POST['topic'] ) : '';
	$keywords      = isset( $_POST['keywords'] ) ? sanitize_text_field( $_POST['keywords'] ) : '';
	$tone          = isset( $_POST['tone'] ) ? sanitize_text_field( $_POST['tone'] ) : '';
	$length        = isset( $_POST['length'] ) ? sanitize_text_field( $_POST['length'] ) : '';
	$category      = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';

	// Extrair palavra-chave principal para reforço de SEO literal no prompt
	$keywords_arr = array_map( 'trim', explode( ',', $keywords ) );
	$primary_keyword = ! empty( $keywords_arr[0] ) ? $keywords_arr[0] : '';

	if ( ! empty( $primary_keyword ) ) {
		$seo_reinforcement = "A palavra-chave de foco principal é '{$primary_keyword}'. Você DEVE incluir a expressão '{$primary_keyword}' (ou uma variação gramatical leve, como singular/plural ou gênero, se estritamente necessário para manter a frase correta) logo na primeira ou segunda frase do primeiro parágrafo da introdução do artigo (no início do post). Além disso, distribua a palavra-chave principal (ou suas flexões leves) ao menos de 3 a 5 vezes de forma natural ao longo de todo o corpo do texto (nos parágrafos comuns) e também em pelo menos um dos títulos H2 e um dos títulos H3. A naturalidade e a correção gramatical da língua portuguesa são prioritárias.";
	} else {
		$seo_reinforcement = "Defina você mesmo a palavra-chave de foco ideal para o tema (uma expressão curta e comercial) e você DEVE incluir essa palavra-chave de foco principal (ou uma variação gramatical leve, como singular/plural, para manter a frase correta) logo na primeira ou segunda frase do primeiro parágrafo da introdução do artigo (no início do post). Distribua-a também (ou suas flexões leves) ao menos de 3 a 5 vezes ao longo de todo o corpo do texto (nos parágrafos comuns) e também em pelo menos um dos títulos H2 e um dos títulos H3.";
	}

	$words_desc = 'Médio (~1000 palavras)';
	$size_rules = "Para o tamanho Médio, escreva de 4 a 5 seções H2/H3 completas e detalhadas, desenvolvendo bem cada conceito de forma clara, garantindo no mínimo 1000 palavras no total.";
	if ( $length === 'short' ) {
		$words_desc = 'Curto (~500 palavras)';
		$size_rules = "Para o tamanho Curto, escreva de 2 a 3 seções H2/H3 objetivas, diretas e focadas, garantindo no mínimo 500 palavras no total.";
	} elseif ( $length === 'long' ) {
		$words_desc = 'Longo (~1500+ palavras)';
		$size_rules = "Para o tamanho Longo, escreva pelo menos 6 a 8 seções H2/H3 aprofundadas, enriquecidas com muitos exemplos práticos, analogias detalhadas e explicações estendidas em cada seção, garantindo no mínimo 1500 a 1800 palavras no total.";
	} elseif ( $length === 'extralong' ) {
		$words_desc = 'Extra Longo (~3000+ palavras)';
		$size_rules = "Para o tamanho Extra Longo, escreva pelo menos 10 a 12 seções H2/H3 exaustivas e extremamente detalhadas. Aborde o assunto sob múltiplos ângulos, inclua uma seção de perguntas frequentes (FAQ) com respostas ricas, analise prós e contras, elabore processos passo a passo e estenda cada explicação e parágrafo ao máximo, garantindo no mínimo 3000 a 3500 palavras no total.";
	}

	// Obter alguns artigos existentes para contextualizar links internos reais (usando transient)
	$links_context = get_transient( 'gpg_recent_posts_links_context' );
	if ( false === $links_context ) {
		$recent_posts = get_posts( array(
			'numberposts' => 5,
			'post_status' => 'publish'
		) );
		$links_context = '';
		if ( ! empty( $recent_posts ) ) {
			$links_context = "Aqui estão alguns links reais do blog. Insira links internos para alguns deles de forma natural ao longo do artigo se fizer sentido:\n";
			foreach ( $recent_posts as $rp ) {
				$links_context .= "- URL: " . get_permalink( $rp->ID ) . " | Texto âncora sugerido relacionado a: " . $rp->post_title . "\n";
			}
		}
		set_transient( 'gpg_recent_posts_links_context', $links_context, 12 * HOUR_IN_SECONDS );
	}

	$keywords_prompt = ! empty( $keywords ) ? $keywords : "Defina você mesmo de 1 a 3 palavras-chave de foco ideais para o tema e otimize o artigo com base nelas.";

	return array(
		'text_provider'     => $text_provider,
		'text_model'        => $text_model,
		'topic'             => $topic,
		'keywords'          => $keywords,
		'tone'              => $tone,
		'length'            => $length,
		'category'          => $category,
		'primary_keyword'   => $primary_keyword,
		'seo_reinforcement' => $seo_reinforcement,
		'words_desc'        => $words_desc,
		'size_rules'        => $size_rules,
		'links_context'     => $links_context,
		'keywords_prompt'   => $keywords_prompt,
	);
}

/**
 * AJAX: Chamar a API de Imagem (DALL-E ou Imagen 3)
 */
function gpg_handle_generate_image() {
	check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
	}
    
	$image_provider = isset( $_POST['image_provider'] ) ? sanitize_text_field( $_POST['image_provider'] ) : 'openai';
	$image_model    = isset( $_POST['image_model'] ) ? sanitize_text_field( $_POST['image_model'] ) : 'gpt-2';
	$prompt         = isset( $_POST['prompt'] ) ? sanitize_textarea_field( $_POST['prompt'] ) : '';

	if ( empty( $prompt ) ) {
		wp_send_json_error( array( 'message' => __( 'O prompt da imagem é obrigatório.', 'gerador-posts-gemini' ) ) );
	}

	if ( $image_provider === 'openai' ) {
		$openai_key = get_option( 'gpg_openai_api_key' );
		if ( empty( $openai_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Configure sua chave da OpenAI.', 'gerador-posts-gemini' ) ) );
		}

		$size = '1792x1024'; // Resolução fixa widescreen por padrão para todos os casos (GPT-2)

		$body = array(
			'model'  => $image_model,
			'prompt' => $prompt,
			'n'      => 1,
			'size'   => $size
		);

		$url = 'https://api.openai.com/v1/images/generations';

		$response = wp_remote_post( $url, array(
			'headers'   => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $openai_key
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 90,
		) );

		if ( is_wp_error( $response ) ) {
			$err_msg = $response->get_error_message();
			if ( strpos( strtolower( $err_msg ), 'timeout' ) !== false || strpos( strtolower( $err_msg ), 'timed out' ) !== false ) {
				$msg = __( 'A geração de imagem com a OpenAI expirou (Timeout). Por favor, tente novamente.', 'gerador-posts-gemini' );
			} else {
				$msg = __( 'Falha na conexão de imagem com a OpenAI: ', 'gerador-posts-gemini' ) . $err_msg;
			}
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Erro ao gerar imagem.', 'gerador-posts-gemini' );
			wp_send_json_error( array( 'message' => $error_msg ) );
		}

		$data = json_decode( $response_body, true );
		$image_url = isset( $data['data'][0]['url'] ) ? $data['data'][0]['url'] : '';

		wp_send_json_success( array(
			'type' => 'url',
			'source' => $image_url
		) );

	} elseif ( $image_provider === 'gemini' ) {
		$gemini_key = get_option( 'gpg_gemini_api_key' );
		if ( empty( $gemini_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Configure sua chave do Gemini.', 'gerador-posts-gemini' ) ) );
		}

		$body = array(
			'instances' => array(
				array(
					'prompt' => $prompt
				)
			),
			'parameters' => array(
				'sampleCount' => 1,
				'aspectRatio' => '16:9'
			)
		);

		$url = "https://generativelanguage.googleapis.com/v1beta/models/{$image_model}:predict?key=" . $gemini_key;

		$response = wp_remote_post( $url, array(
			'headers'   => array( 'Content-Type' => 'application/json' ),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 90,
		) );

		if ( is_wp_error( $response ) ) {
			$err_msg = $response->get_error_message();
			if ( strpos( strtolower( $err_msg ), 'timeout' ) !== false || strpos( strtolower( $err_msg ), 'timed out' ) !== false ) {
				$msg = __( 'A geração de imagem com o Gemini expirou (Timeout). Por favor, tente novamente.', 'gerador-posts-gemini' );
			} else {
				$msg = __( 'Falha na conexão de imagem com o Gemini: ', 'gerador-posts-gemini' ) . $err_msg;
			}
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : '';
			if ( empty( $error_msg ) ) {
				$error_msg = sprintf( __( 'Erro na geração de imagem (Status %d): %s', 'gerador-posts-gemini' ), $response_code, $response_body );
			}
			wp_send_json_error( array( 'message' => $error_msg ) );
		}

		$data = json_decode( $response_body, true );
		$base64_data = isset( $data['predictions'][0]['bytesBase64Encoded'] ) ? $data['predictions'][0]['bytesBase64Encoded'] : '';

		wp_send_json_success( array(
			'type' => 'base64',
			'source' => 'data:image/jpeg;base64,' . $base64_data
		) );
	}
}

/**
 * AJAX: Salvar post final no WordPress, anexar as imagens geradas nos placeholders e configurar Veja Também
 */
function gpg_handle_save_post() {
	check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
	}

	$title            = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
	$title            = gpg_limit_title_length( $title ); // Garante o limite estrito de 75 caracteres
	$content          = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';

	// Forçar linkagem automática de TD Vieira Design para o site oficial antes de salvar
	if ( ! empty( $content ) ) {
		$content = preg_replace_callback(
			'/<a\b[^>]*>.*?<\/a>(*SKIP)(*F)|\bTD Vieira Design\b/i',
			function( $matches ) {
				return '<strong><a href="https://tdvieiradesign.com" target="_blank">TD Vieira Design</a></strong>';
			},
			$content
		);

		// Garantir que haja exatamente 2 quebras de linha após o fechamento de listas (bullet points)
		$content = preg_replace( '/<\/ul>(\s*\n)*/i', "</ul>\n\n", $content );
		$content = preg_replace( '/<\/ol>(\s*\n)*/i', "</ol>\n\n", $content );

		// Limitar quantidade de links de referência no texto (máximo 1 interno e 1 externo)
		$content = gpg_limit_article_links( $content );
	}
	$meta_description = isset( $_POST['meta_description'] ) ? sanitize_text_field( $_POST['meta_description'] ) : '';
	$meta_description = gpg_limit_excerpt_length( $meta_description, 138 ); // Garante o limite estrito de 138 caracteres com reticências para Rank Math
	$excerpt          = isset( $_POST['excerpt'] ) ? sanitize_text_field( $_POST['excerpt'] ) : '';
	$excerpt          = gpg_limit_excerpt_length( $excerpt ); // Garante o limite estrito de 175 caracteres
	$keywords         = isset( $_POST['keywords'] ) ? sanitize_text_field( $_POST['keywords'] ) : '';
	$category         = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
	$publish_date     = isset( $_POST['publish_date'] ) ? sanitize_text_field( $_POST['publish_date'] ) : '';
	$suggested_slug   = isset( $_POST['suggested_slug'] ) ? sanitize_title( $_POST['suggested_slug'] ) : '';

	// Imagens geradas
	$image_1_source   = isset( $_POST['image_1_source'] ) ? $_POST['image_1_source'] : '';
	$image_1_type     = isset( $_POST['image_1_type'] ) ? sanitize_text_field( $_POST['image_1_type'] ) : '';
	$image_2_source   = isset( $_POST['image_2_source'] ) ? $_POST['image_2_source'] : '';
	$image_2_type     = isset( $_POST['image_2_type'] ) ? sanitize_text_field( $_POST['image_2_type'] ) : '';

	if ( empty( $title ) || empty( $content ) ) {
		wp_send_json_error( array( 'message' => __( 'Título e Conteúdo são obrigatórios.', 'gerador-posts-gemini' ) ) );
	}

	// Configurar Post Data inicial
	$post_status = 'draft';
	$formatted_date = '';
	if ( ! empty( $publish_date ) ) {
		$time_val = strtotime( $publish_date );
		if ( $time_val ) {
			$formatted_date = date( 'Y-m-d H:i:s', $time_val );
			// Se a data for no futuro, agendar (status 'future')
			if ( $time_val > current_time( 'timestamp' ) ) {
				$post_status = 'future';
			} else {
				$post_status = 'publish';
			}
		}
	}

	$new_post = array(
		'post_title'    => $title,
		'post_content'  => $content,
		'post_status'   => $post_status,
		'post_type'     => 'post',
		'post_author'   => get_current_user_id(),
		'post_excerpt'  => $excerpt
	);

	if ( ! empty( $suggested_slug ) ) {
		$new_post['post_name'] = $suggested_slug;
	}

	if ( ! empty( $formatted_date ) ) {
		$new_post['post_date']     = $formatted_date;
		$new_post['post_date_gmt'] = get_gmt_from_date( $formatted_date );
	}

	// Associar categoria correta
	if ( ! empty( $category ) ) {
		$cat_obj = get_term_by( 'name', $category, 'category' );
		if ( $cat_obj ) {
			$new_post['post_category'] = array( $cat_obj->term_id );
		}
	}

	// Salvar post primário
	$post_id = wp_insert_post( $new_post );

	if ( is_wp_error( $post_id ) || $post_id === 0 ) {
		wp_send_json_error( array( 'message' => __( 'Erro ao inserir post.', 'gerador-posts-gemini' ) ) );
	}

	// Metadados do Rank Math
	gpg_save_rank_math_metadata( $post_id, $keywords, $meta_description );

	// Realizar downloads/decodificações de imagens e inseri-las nos placeholders do corpo
	$processed_images = gpg_download_and_process_images( $post_id, $title, $keywords, $image_1_source, $image_1_type, $image_2_source, $image_2_type );

	$image_1_url = $processed_images['image_1_url'];
	$image_2_url = $processed_images['image_2_url'];
	$img_alt_1   = $processed_images['img_alt_1'];
	$img_title_1 = $processed_images['img_title_1'];
	$img_alt_2   = $processed_images['img_alt_2'];
	$img_title_2 = $processed_images['img_title_2'];

	// Gerar bloco "Veja também" com 3 artigos aleatórios
	$veja_html = gpg_generate_veja_tambem_html( $post_id );

	// Modificar conteúdo substituindo placeholders pelas tags de imagem corretas e pelo Veja Também
	$final_content = gpg_sanitize_and_clean_content( $content, $image_1_url, $image_2_url, $img_alt_1, $img_title_1, $img_alt_2, $img_title_2, $veja_html );

	// Atualizar conteúdo do post com a estrutura final
	wp_update_post( array(
		'ID'           => $post_id,
		'post_content' => $final_content
	) );

	$edit_link = get_edit_post_link( $post_id, 'url' );

	$status_label = __( 'Publicado', 'gerador-posts-gemini' );
	$message_label = __( 'Artigo criado e publicado com sucesso!', 'gerador-posts-gemini' );

	if ( $post_status === 'future' ) {
		$status_label = __( 'Agendado', 'gerador-posts-gemini' );
		$message_label = __( 'Artigo criado e agendado com sucesso!', 'gerador-posts-gemini' );
	} elseif ( $post_status === 'draft' ) {
		$status_label = __( 'Rascunho', 'gerador-posts-gemini' );
		$message_label = __( 'Artigo criado e salvo como rascunho com sucesso!', 'gerador-posts-gemini' );
	}

	wp_send_json_success( array(
		'message'   => $message_label,
		'post_id'   => $post_id,
		'edit_link' => $edit_link,
		'permalink' => get_permalink( $post_id ),
		'status'    => $status_label
	) );
}

/**
 * Baixar, converter para WebP, gerar crop retina e associar as imagens ao post do WordPress
 */
function gpg_download_and_process_images( $post_id, $title, $keywords, $image_1_source, $image_1_type, $image_2_source, $image_2_type ) {
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	// Definir ALT e TITLE das imagens com base nas palavras-chave de foco para melhor SEO
	$keywords_arr = array_map( 'trim', explode( ',', $keywords ) );
	$primary_keyword = ! empty( $keywords_arr[0] ) ? $keywords_arr[0] : '';
	if ( empty( $primary_keyword ) ) {
		$primary_keyword = $title;
	}

	$img_alt_destaque = $primary_keyword . ' - Destaque';
	$img_title_destaque = $primary_keyword . ' - Destaque';

	$img_alt_1 = $primary_keyword;
	$img_title_1 = $primary_keyword;

	if ( count( $keywords_arr ) > 1 && ! empty( $keywords_arr[1] ) ) {
		$img_alt_2 = $keywords_arr[1];
		$img_title_2 = $keywords_arr[1];
	} else {
		$img_alt_2 = $primary_keyword . ' - Dicas e benefícios';
		$img_title_2 = $primary_keyword . ' - Dicas e benefícios';
	}

	$image_1_url = '';
	$image_2_url = '';

	// Processar Imagem 1 (Destaque e Corpo/Início com arquivos separados)
	if ( ! empty( $image_1_source ) ) {
		// 1. Imagem de Destaque (Crop restritivo 1408x474 para suporte a Retina)
		$filename_destaque = 'destaque-' . sanitize_title( $title ) . '.jpg';
		$attachment_id_destaque = gpg_upload_media_source( $image_1_source, $image_1_type, $filename_destaque, $post_id, 1408, 474 );
		if ( ! is_wp_error( $attachment_id_destaque ) ) {
			set_post_thumbnail( $post_id, $attachment_id_destaque );
			update_post_meta( $attachment_id_destaque, '_wp_attachment_image_alt', $img_alt_destaque );
			wp_update_post( array(
				'ID'         => $attachment_id_destaque,
				'post_title' => $img_title_destaque
			) );
		}

		// 2. Imagem do Corpo (Proporção 16:9 -> 1408x792 para suporte a Retina)
		$filename_corpo = 'corpo-topo-' . sanitize_title( $title ) . '.jpg';
		$attachment_id_corpo = gpg_upload_media_source( $image_1_source, $image_1_type, $filename_corpo, $post_id, 1408, 792 );
		if ( ! is_wp_error( $attachment_id_corpo ) ) {
			$image_1_url = wp_get_attachment_url( $attachment_id_corpo );
			update_post_meta( $attachment_id_corpo, '_wp_attachment_image_alt', $img_alt_1 );
			wp_update_post( array(
				'ID'         => $attachment_id_corpo,
				'post_title' => $img_title_1
			) );
		}
	}

	// Processar Imagem 2 (Corpo do Post - 16:9 -> 1408x792 para suporte a Retina)
	if ( ! empty( $image_2_source ) ) {
		$filename_2 = 'corpo-' . sanitize_title( $title ) . '.jpg';
		$attachment_id_2 = gpg_upload_media_source( $image_2_source, $image_2_type, $filename_2, $post_id, 1408, 792 );
		
		if ( ! is_wp_error( $attachment_id_2 ) ) {
			$image_2_url = wp_get_attachment_url( $attachment_id_2 );
			update_post_meta( $attachment_id_2, '_wp_attachment_image_alt', $img_alt_2 );
			wp_update_post( array(
				'ID'         => $attachment_id_2,
				'post_title' => $img_title_2
			) );
		}
	}

	return array(
		'image_1_url' => $image_1_url,
		'image_2_url' => $image_2_url,
		'img_alt_1'   => $img_alt_1,
		'img_title_1' => $img_title_1,
		'img_alt_2'   => $img_alt_2,
		'img_title_2' => $img_title_2
	);
}

/**
 * Higienizar, limpar e estruturar o conteúdo final do post
 */
function gpg_sanitize_and_clean_content( $content, $image_1_url, $image_2_url, $img_alt_1, $img_title_1, $img_alt_2, $img_title_2, $veja_html ) {
	$final_content = $content;

	$placeholders_1 = array(
		'<!-- IMAGE_1_PLACEHOLDER -->',
		'<p><!-- IMAGE_1_PLACEHOLDER --></p>',
		'[IMAGE_1_PLACEHOLDER]',
		'<p>[IMAGE_1_PLACEHOLDER]</p>',
		'[IMAGE-1-PLACEHOLDER]',
		'<p>[IMAGE-1-PLACEHOLDER]</p>',
		'[image_1_placeholder]',
		'<p>[image_1_placeholder]</p>',
		'[image-1-placeholder]',
		'<p>[image-1-placeholder]</p>'
	);

	$placeholders_2 = array(
		'<!-- IMAGE_2_PLACEHOLDER -->',
		'<p><!-- IMAGE_2_PLACEHOLDER --></p>',
		'[IMAGE_2_PLACEHOLDER]',
		'<p>[IMAGE_2_PLACEHOLDER]</p>',
		'[IMAGE-2-PLACEHOLDER]',
		'<p>[IMAGE-2-PLACEHOLDER]</p>',
		'[image_2_placeholder]',
		'<p>[image_2_placeholder]</p>',
		'[image-2-placeholder]',
		'<p>[image-2-placeholder]</p>'
	);

	$placeholders_veja = array(
		'<!-- VEJA_TAMBEM_PLACEHOLDER -->',
		'<p><!-- VEJA_TAMBEM_PLACEHOLDER --></p>',
		'[VEJA_TAMBEM_PLACEHOLDER]',
		'<p>[VEJA_TAMBEM_PLACEHOLDER]</p>',
		'[VEJA-TAMBEM-PLACEHOLDER]',
		'<p>[VEJA-TAMBEM-PLACEHOLDER]</p>',
		'[veja_tambem_placeholder]',
		'<p>[veja_tambem_placeholder]</p>',
		'[veja-tambem-placeholder]',
		'<p>[veja-tambem-placeholder]</p>'
	);

	if ( ! empty( $image_1_url ) ) {
		$img_html_1 = "\n\n" . '<figure class="wp-block-image size-large imagem" style="margin-top: 45px; margin-bottom: 45px;"><img src="' . esc_url( $image_1_url ) . '" class="imagem" width="704" height="396" alt="' . esc_attr( $img_alt_1 ) . '" title="' . esc_attr( $img_title_1 ) . '" /></figure>' . "\n\n";
		$final_content = str_replace( $placeholders_1, $img_html_1, $final_content );
	} else {
		$final_content = str_replace( $placeholders_1, '', $final_content );
	}

	if ( ! empty( $image_2_url ) ) {
		$img_html_2 = "\n\n" . '<figure class="wp-block-image size-large imagem" style="margin-top: 45px; margin-bottom: 45px;"><img src="' . esc_url( $image_2_url ) . '" class="imagem" width="704" height="396" alt="' . esc_attr( $img_alt_2 ) . '" title="' . esc_attr( $img_title_2 ) . '" /></figure>' . "\n\n";
		$final_content = str_replace( $placeholders_2, $img_html_2, $final_content );
	} else {
		$final_content = str_replace( $placeholders_2, '', $final_content );
	}

	$final_content = str_replace( $placeholders_veja, $veja_html, $final_content );

	// Remover quebras de linha extras, tags <br> e parágrafos vazios redundantes antes do bloco "Veja também"
	$final_content = preg_replace(
		'/(?:(?:\r?\n)+|<br\s*\/?>|<p>\s*(?:&nbsp;|\s*)*\s*<\/p>)*\s*(<div class="veja">)/i',
		"\n$1",
		$final_content
	);

	// Remover quebras de linha extras, tags <br> e parágrafos vazios redundantes após o bloco "Veja também"
	$final_content = preg_replace(
		'/(<div class="veja">.*?<\/div>)\s*(?:(?:\r?\n)+|<br\s*\/?>|<p>\s*(?:&nbsp;|\s*)*\s*<\/p>)+/is',
		"$1\n",
		$final_content
	);

	// Garantir que o título do Sumário de Conteúdo tenha a classe e margem de 50px (superior) e 15px (inferior)
	$final_content = preg_replace(
		'/<p\b[^>]*?(?:font-weight:\s*600|color:\s*#FA5B0F)[^>]*?>\s*Sumário de Conteúdo\s*<\/p>/is',
		'<p class="gpg-toc-title" style="font-weight: 600; color: #FA5B0F; margin-top: 50px; margin-bottom: 15px;">Sumário de Conteúdo</p>',
		$final_content
	);

	// Remover qualquer quebra de linha extra (\n ou \r), tags <br>, ou parágrafos vazios que antecedam o Sumário de Conteúdo
	$final_content = preg_replace(
		'/(?:(?:\r?\n)+|<br\s*\/?>|<p>\s*(?:&nbsp;|\s*)*\s*<\/p>)*\s*(<p class="gpg-toc-title")/i',
		"\n$1",
		$final_content
	);

	// Remover qualquer item de lista (<li>) que faça referência à "Introdução" ou "Início" no Sumário de Conteúdo
	$final_content = preg_replace(
		'/<li[^>]*>\s*<a\b[^>]*href=["\']#(?:introduc[ao|ão]|inicio|início|introducao)[^"\']*["\'][^>]*>.*?<\/a>\s*<\/li>/i',
		'',
		$final_content
	);
	$final_content = preg_replace(
		'/<li[^>]*>\s*<a\b[^>]*>.*?Introduç(?:ão|ao).*?<\/a>\s*<\/li>/i',
		'',
		$final_content
	);

	// Remover subtítulos H2 ou H3 físicos contendo exatamente ou iniciando com "Introdução" ou "Início" no corpo do texto
	$final_content = preg_replace(
		'/<h[23]\b[^>]*>\s*(?:<strong>)?\s*(?:Introduç(?:ão|ao)|Início|Inicio)\s*(?:<\/strong>)?\s*<\/h[23]>/is',
		'',
		$final_content
	);

	return $final_content;
}

/**
 * Salvar os metadados do Rank Math (palavras-chave e meta descrição) para otimização SEO no post
 */
function gpg_save_rank_math_metadata( $post_id, $keywords, $meta_description ) {
	// Metadados do Rank Math
	if ( ! empty( $keywords ) ) {
		update_post_meta( $post_id, 'rank_math_focus_keyword', strtolower( $keywords ) );
	}
	if ( ! empty( $meta_description ) ) {
		update_post_meta( $post_id, 'rank_math_description', $meta_description );
	}
}

/**
 * Função Auxiliar: Decodifica ou baixa a imagem, converte para WebP e faz sideload para a biblioteca
 */
function gpg_upload_media_source( $source, $type, $filename, $post_id, $target_width = 0, $target_height = 0 ) {
	if ( $type === 'base64' ) {
		$data_str = $source;
		if ( strpos( $data_str, 'data:image' ) === 0 ) {
			list( , $data_str ) = explode( ';', $data_str );
			list( , $data_str ) = explode( ',', $data_str );
		}
		$image_bytes = base64_decode( $data_str );

		// Salvar em arquivo temporário na pasta temp do WordPress
		$tmp_dir = get_temp_dir();
		$tmp_filename = str_replace( '.webp', '.jpg', $filename );
		$filepath = $tmp_dir . $tmp_filename;
		file_put_contents( $filepath, $image_bytes );

		$webp_filename = str_replace( '.jpg', '.webp', $filename );
		$webp_filepath = $tmp_dir . $webp_filename;

		$editor = wp_get_image_editor( $filepath );
		if ( ! is_wp_error( $editor ) ) {
			$editor->set_quality( 90 );
			if ( $target_width > 0 && $target_height > 0 ) {
				$editor->resize( $target_width, $target_height, true );
			}
			$saved = $editor->save( $webp_filepath, 'image/webp' );
			if ( ! is_wp_error( $saved ) ) {
				@unlink( $filepath ); // Remover JPG temporário
				$filepath = $webp_filepath;
				$filename = $webp_filename;
			}
		}

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $filepath
		);

		$attachment_id = media_handle_sideload( $file_array, $post_id );
		@unlink( $filepath ); // Garante remoção do temporário WebP do sistema
		return $attachment_id;
	} else {
		// Validar URL de origem contra SSRF e hosts locais/inseguros antes do download
		$validated_url = wp_http_validate_url( $source );
		if ( ! $validated_url ) {
			return new WP_Error( 'gpg_invalid_image_url', __( 'A URL da imagem fornecida é inválida ou aponta para um host interno inseguro (SSRF).', 'gerador-posts-gemini' ) );
		}

		// Configurar User-Agent real para evitar bloqueios de Cloudflare/rate limit (e isolar sslverify para desenvolvimento local se necessário)
		$customize_http_args = function( $args ) {
			$args['sslverify'] = ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) ? false : true;
			$args['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
			$args['headers']['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
			return $args;
		};
		add_filter( 'http_request_args', $customize_http_args );

		$tmp_file = download_url( $validated_url );

		remove_filter( 'http_request_args', $customize_http_args );

		if ( is_wp_error( $tmp_file ) ) {
			error_log( 'GPG Media Sideload Download Error: ' . $tmp_file->get_error_message() . ' for URL: ' . $source );
			return $tmp_file;
		}

		$webp_tmp_file = $tmp_file . '.webp';
		$webp_filename = str_replace( '.jpg', '.webp', $filename );

		$editor = wp_get_image_editor( $tmp_file );
		if ( ! is_wp_error( $editor ) ) {
			$editor->set_quality( 90 );
			if ( $target_width > 0 && $target_height > 0 ) {
				$editor->resize( $target_width, $target_height, true );
			}
			$saved = $editor->save( $webp_tmp_file, 'image/webp' );
			if ( ! is_wp_error( $saved ) ) {
				@unlink( $tmp_file ); // Remover JPG temporário baixado
				$tmp_file = $webp_tmp_file;
				$filename = $webp_filename;
			}
		}

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp_file
		);

		$attachment_id = media_handle_sideload( $file_array, $post_id );
		@unlink( $tmp_file ); // Garante remoção do temporário WebP do sistema
		return $attachment_id;
	}
}

/**
 * Função Auxiliar: Monta o HTML da seção Veja Também baseado em 3 posts aleatórios
 */
function gpg_generate_veja_tambem_html( $exclude_post_id = 0 ) {
	$posts_pool = get_transient( 'gpg_veja_tambem_posts_pool' );
	if ( false === $posts_pool ) {
		$recent = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 20
		) );
		$posts_pool = array();
		if ( ! empty( $recent ) ) {
			foreach ( $recent as $p ) {
				$thumb_url = get_the_post_thumbnail_url( $p->ID, 'medium' );
				if ( empty( $thumb_url ) ) {
					$thumb_url = 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=300&fit=crop&q=60';
				}
				$posts_pool[] = array(
					'id'            => $p->ID,
					'title'         => $p->post_title,
					'permalink'     => get_permalink( $p->ID ),
					'thumbnail_url' => $thumb_url
				);
			}
		}
		set_transient( 'gpg_veja_tambem_posts_pool', $posts_pool, 12 * HOUR_IN_SECONDS );
	}

	// Filtrar para excluir o post atual do pool
	$filtered_pool = array();
	if ( ! empty( $posts_pool ) ) {
		foreach ( $posts_pool as $p ) {
			if ( (int) $p['id'] !== (int) $exclude_post_id ) {
				$filtered_pool[] = $p;
			}
		}
	}

	$veja_html = '<div class="veja">';
	$veja_html .= '<h3 id="veja-tambem">' . esc_html__( 'Veja também:', 'gerador-posts-gemini' ) . '</h3>';
	$veja_html .= '<ul>';

	if ( ! empty( $filtered_pool ) ) {
		// Selecionar até 3 itens aleatórios do pool filtrado
		$count = count( $filtered_pool );
		$num_to_select = min( 3, $count );
		
		// Usar array_rand para pegar chaves aleatórias
		$selected_keys = array_rand( $filtered_pool, $num_to_select );
		if ( ! is_array( $selected_keys ) ) {
			$selected_keys = array( $selected_keys );
		}

		foreach ( $selected_keys as $key ) {
			$post_data = $filtered_pool[ $key ];
			$veja_html .= '<li>';
			$veja_html .= '<a href="' . esc_url( $post_data['permalink'] ) . '">';
			$veja_html .= '<img src="' . esc_url( $post_data['thumbnail_url'] ) . '" class="imagem" alt="' . esc_attr( $post_data['title'] ) . '" />';
			$veja_html .= '<span>' . esc_html( $post_data['title'] ) . '</span>';
			$veja_html .= '</a>';
			$veja_html .= '</li>';
		}
	} else {
		$veja_html .= '<li>Sem outros posts recomendados ainda.</li>';
	}

	$veja_html .= '</ul>';
	$veja_html .= '</div>';

	return $veja_html;
}

/**
 * Filtra e remove tags <a> cujo link aponte para páginas inexistentes (status 404 ou erro/timeout).
 * Mantém o texto âncora original no lugar.
 */
function gpg_validate_and_clean_links( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}

	// Captura todas as tags <a> com seus hrefs
	if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $match ) {
			$full_tag = $match[0];
			$url      = trim( $match[1] );
			$anchor   = $match[2];

			// Ignorar se a URL for vazia, apenas um caractere especial ou hash de âncora interno
			if ( empty( $url ) || $url === '#' ) {
				if ( strpos( $url, '#' ) === 0 ) {
					continue; // Mantém âncora interna de navegação local
				}
				$content = str_replace( $full_tag, $anchor, $content );
				continue;
			}

			// Se a URL for local/relativa (ex: /contato), resolve com o home_url
			$test_url = $url;
			if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
				$test_url = home_url( $url );
			}

			$is_valid = true;

			// Tentar requisição HEAD rápida primeiro (timeout de 3 segundos)
			$response = wp_remote_request( $test_url, array(
				'method'      => 'HEAD',
				'timeout'     => 3,
				'redirection' => 3,
				'httpversion' => '1.0',
				'sslverify'   => ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) ? false : true,
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			) );

			$code = wp_remote_retrieve_response_code( $response );

			// Se der erro ou o código indicar falha (HEAD pode ser bloqueado com 403, 405 ou dar 404 real), tentamos um GET rápido
			if ( is_wp_error( $response ) || $code === 404 || $code >= 400 ) {
				$response = wp_remote_request( $test_url, array(
					'method'      => 'GET',
					'timeout'     => 3,
					'redirection' => 3,
					'httpversion' => '1.0',
					'sslverify'   => ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) ? false : true,
					'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
				) );
				$code = wp_remote_retrieve_response_code( $response );
			}

			// Apenas consideramos link inválido se a API responder com sucesso e o código de status for estritamente 404 (página não encontrada)
			if ( ! is_wp_error( $response ) && $code === 404 ) {
				$is_valid = false;
			}

			// Se o link for comprovadamente quebrado (404), removemos a tag <a> deixando apenas o texto âncora
			if ( ! $is_valid ) {
				$content = str_replace( $full_tag, $anchor, $content );
			}
		}
	}

	return $content;
}

/**
 * AJAX: Mover post gerado para a lixeira do WordPress
 */
function gpg_handle_delete_post() {
	check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	if ( empty( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'ID do post inválido.', 'gerador-posts-gemini' ) ) );
	}

	// Mover para a lixeira
	$deleted = wp_trash_post( $post_id );

	if ( $deleted ) {
		wp_send_json_success( array( 'message' => __( 'Post removido com sucesso.', 'gerador-posts-gemini' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Erro ao remover o post.', 'gerador-posts-gemini' ) ) );
	}
}

/**
 * Limita o comprimento do título a um máximo de caracteres sem cortar palavras ao meio (se possível).
 */
function gpg_limit_title_length( $title, $max_len = 70 ) {
	$title = trim( $title );
	if ( mb_strlen( $title ) <= $max_len ) {
		return $title;
	}
	
	// Tenta cortar no último espaço antes do limite
	$truncated = mb_substr( $title, 0, $max_len );
	$last_space = mb_strrpos( $truncated, ' ' );
	
	if ( $last_space !== false && $last_space > 10 ) {
		return trim( mb_substr( $truncated, 0, $last_space ) );
	}
	
	return trim( $truncated );
}

/**
 * Limita o comprimento do resumo (excerpt) a um máximo de caracteres sem cortar palavras ao meio (se possível).
 */
function gpg_limit_excerpt_length( $excerpt, $max_len = 175 ) {
	$excerpt = trim( $excerpt );
	if ( mb_strlen( $excerpt ) <= $max_len ) {
		return $excerpt;
	}
	
	// Tenta cortar no último espaço antes do limite de forma a incluir as reticências sem extrapolar o tamanho
	$truncated = mb_substr( $excerpt, 0, $max_len - 3 );
	$last_space = mb_strrpos( $truncated, ' ' );
	
	if ( $last_space !== false && $last_space > 20 ) {
		return trim( mb_substr( $truncated, 0, $last_space ) ) . '...';
	}
	
	return trim( $truncated ) . '...';
}

/**
 * Reescreve o título de forma concisa (até 75 caracteres) usando IA
 */
function gpg_rewrite_title_concise( $title, $focus_keywords, $provider, $model ) {
	$title = trim( $title );
	$focus_keywords = trim( $focus_keywords );

	// Extrair a primeira palavra-chave se vier em lista separada por vírgulas
	$keywords_arr = array_map( 'trim', explode( ',', $focus_keywords ) );
	$primary_keyword = ! empty( $keywords_arr[0] ) ? $keywords_arr[0] : '';

	$prompt = "Você é um redator especialista em SEO de blogs. Sua tarefa é reescrever o título de um artigo de blog para torná-lo conciso, contendo entre 65 e 70 caracteres.\n\n";
	$prompt .= "Diretrizes estritas:\n";
	$prompt .= "1. O título final deve ter obrigatoriamente no mínimo 65 caracteres e no máximo 70 caracteres de comprimento (jamais fique fora do intervalo de 65 a 70 caracteres).\n";
	if ( ! empty( $primary_keyword ) ) {
		$prompt .= "2. A palavra-chave de foco principal (\"{$primary_keyword}\") deve estar obrigatoriamente no início do título.\n";
	}
	$prompt .= "3. O título deve ser extremamente atrativo, gerando curiosidade e com forte apelo de clique comercial (CTR).\n";
	$prompt .= "4. Não use aspas envolvendo o título retornado.\n";
	$prompt .= "5. Responda APENAS com o novo título reescrito, sem explicações, introduções, prefixos ou qualquer texto adicional.\n\n";
	$prompt .= "Título original longo: " . $title;

	$rewritten_title = '';

	if ( $provider === 'openai' ) {
		$openai_key = get_option( 'gpg_openai_api_key' );
		if ( empty( $openai_key ) ) {
			return false;
		}
		$active_model = ! empty( $model ) ? $model : 'gpt-5-mini';

		$body = array(
			'model' => $active_model,
			'messages' => array(
				array(
					'role' => 'user',
					'content' => $prompt
				)
			),
			'temperature' => 0.3,
		);

		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
			'headers'   => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $openai_key
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 20,
		) );

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			$rewritten_title = isset( $data['choices'][0]['message']['content'] ) ? trim( $data['choices'][0]['message']['content'] ) : '';
		}

	} elseif ( $provider === 'groq' ) {
		$groq_key = get_option( 'gpg_groq_api_key' );
		if ( empty( $groq_key ) ) {
			return false;
		}
		$active_model = ! empty( $model ) ? $model : 'llama-3.1-8b';

		$body = array(
			'model' => $active_model,
			'messages' => array(
				array(
					'role' => 'user',
					'content' => $prompt
				)
			),
			'temperature' => 0.3,
		);

		$response = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', array(
			'headers'   => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $groq_key
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 20,
		) );

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			$rewritten_title = isset( $data['choices'][0]['message']['content'] ) ? trim( $data['choices'][0]['message']['content'] ) : '';
		}

	} else { // gemini
		$gemini_key = get_option( 'gpg_gemini_api_key' );
		if ( empty( $gemini_key ) ) {
			return false;
		}
		$active_model = ! empty( $model ) ? $model : 'gemini-3.5-flash';

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array(
							'text' => $prompt
						)
					)
				)
			),
			'generationConfig' => array(
				'temperature' => 0.3
			)
		);

		$url = "https://generativelanguage.googleapis.com/v1beta/models/{$active_model}:generateContent?key=" . $gemini_key;

		$response = wp_remote_post( $url, array(
			'headers'   => array( 'Content-Type' => 'application/json' ),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 20,
		) );

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			$rewritten_title = isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ? trim( $data['candidates'][0]['content']['parts'][0]['text'] ) : '';
		}
	}

	if ( ! empty( $rewritten_title ) ) {
		// Remover aspas desnecessárias que a IA costuma colocar de teimosia
		$rewritten_title = trim( $rewritten_title, "\"'“»«" );
		return $rewritten_title;
	}

	return false;
}

/**
 * Higieniza o corpo do artigo limitando estritamente a 1 link interno e 1 link externo (ignorando links de âncoras locais e TD Vieira Design)
 */
function gpg_limit_article_links( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}

	$home_url = home_url();
	
	// Captura todas as tags <a> com seus links e âncoras
	if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER ) ) {
		$internal_count = 0;
		$external_count = 0;

		foreach ( $matches as $match ) {
			$full_tag = $match[0];
			$url      = trim( $match[1] );
			$anchor   = $match[2];

			// Ignorar âncoras internas (links locais como #sumario)
			if ( strpos( $url, '#' ) === 0 ) {
				continue;
			}

			// Ignorar link oficial da marca TD Vieira Design
			if ( strpos( $url, 'tdvieiradesign.com' ) !== false ) {
				continue;
			}

			// Verificar se é interno ou externo
			$is_internal = false;
			if ( strpos( $url, '/' ) === 0 || ( strpos( $url, '//' ) !== 0 && strpos( $url, 'http' ) !== 0 ) || strpos( $url, $home_url ) !== false ) {
				$is_internal = true;
			}

			if ( $is_internal ) {
				$internal_count++;
				// Se já passou do primeiro link interno, remove o link e mantém o texto âncora
				if ( $internal_count > 1 ) {
					$content = str_replace( $full_tag, $anchor, $content );
				}
			} else {
				$external_count++;
				// Se já passou do primeiro link externo, remove o link e mantém o texto âncora
				if ( $external_count > 1 ) {
					$content = str_replace( $full_tag, $anchor, $content );
				}
			}
		}
	}

	return $content;
}

/**
 * Invalida os transients de posts recentes e pool do Veja Também quando posts são criados ou alterados
 */
function gpg_invalidate_posts_cache( $post_id ) {
	// Apenas invalidar se for um post normal (post_type === 'post')
	if ( get_post_type( $post_id ) === 'post' ) {
		delete_transient( 'gpg_recent_posts_links_context' );
		delete_transient( 'gpg_veja_tambem_posts_pool' );
	}
}
add_action( 'save_post', 'gpg_invalidate_posts_cache' );
add_action( 'deleted_post', 'gpg_invalidate_posts_cache' );
add_action( 'trash_post', 'gpg_invalidate_posts_cache' );



