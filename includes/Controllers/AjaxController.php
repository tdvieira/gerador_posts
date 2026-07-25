<?php
namespace GPG\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use GPG\Core\Config;
use GPG\Services\PostService;
use GPG\Services\MediaProcessor;
use GPG\AI\ProviderFactory;
use GPG\AI\PromptBuilder;

/**
 * Controlador de requisições assíncronas (AJAX) do plugin.
 * Valida autorizações, higieniza dados POST e coordena o fluxo de execução de IA.
 */
class AjaxController {

	/**
	 * AJAX: Salvar chaves de API administrativas.
	 */
	public static function handleSaveSettings() {
		check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
		}

		$gemini_key = isset( $_POST['gemini_api_key'] ) ? sanitize_text_field( $_POST['gemini_api_key'] ) : '';
		$openai_key = isset( $_POST['openai_api_key'] ) ? sanitize_text_field( $_POST['openai_api_key'] ) : '';
		$groq_key   = isset( $_POST['groq_api_key'] ) ? sanitize_text_field( $_POST['groq_api_key'] ) : '';
		$puter_key  = isset( $_POST['puter_api_key'] ) ? sanitize_text_field( $_POST['puter_api_key'] ) : '';
		
		Config::set( 'gemini_api_key', $gemini_key );
		Config::set( 'openai_api_key', $openai_key );
		Config::set( 'groq_api_key', $groq_key );
		Config::set( 'puter_api_key', $puter_key );

		wp_send_json_success( array( 'message' => __( 'Configurações de chaves de API salvas com sucesso!', 'gerador-posts-gemini' ) ) );
	}

	/**
	 * AJAX: Chamar as APIs de Inteligência Artificial para gerar o post.
	 */
	public static function handleGeneratePost() {
		check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
		}

		$text_provider = isset( $_POST['text_provider'] ) ? sanitize_text_field( $_POST['text_provider'] ) : 'gemini';
		$text_model    = isset( $_POST['text_model'] ) ? sanitize_text_field( $_POST['text_model'] ) : '';
		$topic         = isset( $_POST['topic'] ) ? sanitize_text_field( $_POST['topic'] ) : '';
		$keywords      = isset( $_POST['keywords'] ) ? sanitize_text_field( $_POST['keywords'] ) : '';
		$tone          = isset( $_POST['tone'] ) ? sanitize_text_field( $_POST['tone'] ) : '';
		$length        = isset( $_POST['length'] ) ? sanitize_text_field( $_POST['length'] ) : '';
		$category      = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';

		if ( empty( $topic ) ) {
			wp_send_json_error( array( 'message' => __( 'O tema principal é obrigatório.', 'gerador-posts-gemini' ) ) );
		}

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

		$prompt = PromptBuilder::buildTextGenerationPrompt( array(
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

		$provider_instance = ProviderFactory::createTextProvider( $text_provider );
		if ( is_wp_error( $provider_instance ) ) {
			wp_send_json_error( array( 'message' => $provider_instance->get_error_message() ) );
		}

		$active_model = ! empty( $text_model ) ? $text_model : ( $text_provider === 'openai' ? 'gpt-5-mini' : ( $text_provider === 'groq' ? 'llama-3.1-8b' : 'gemini-3.5-flash' ) );
		$response = $provider_instance->generateText( $prompt, $active_model );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$response_body = wp_remote_retrieve_body( $response );
		$data_decoded = json_decode( $response_body, true );

		if ( $text_provider === 'openai' || $text_provider === 'groq' ) {
			$json_text = isset( $data_decoded['choices'][0]['message']['content'] ) ? $data_decoded['choices'][0]['message']['content'] : '';
		} else {
			$json_text = isset( $data_decoded['candidates'][0]['content']['parts'][0]['text'] ) ? $data_decoded['candidates'][0]['content']['parts'][0]['text'] : '';
		}

		$result = json_decode( $json_text, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $result['title'] ) || ! isset( $result['content'] ) || ! isset( $result['meta_description'] ) || ! isset( $result['excerpt'] ) || ! isset( $result['image_1_prompt'] ) || ! isset( $result['image_2_prompt'] ) || ! isset( $result['focus_keywords'] ) || ! isset( $result['suggested_slug'] ) ) {
			wp_send_json_error( array( 
				'message' => __( 'A IA falhou em formatar a resposta no formato JSON de 8 chaves exigido.', 'gerador-posts-gemini' ),
				'raw' => $json_text 
			) );
		}

		if ( mb_strlen( trim( $result['title'] ) ) > 70 ) {
			$focus_kw = isset( $result['focus_keywords'] ) ? $result['focus_keywords'] : $keywords;
			$rewritten = PostService::rewriteTitleConcise( $result['title'], $focus_kw, $text_provider, $text_model );
			if ( $rewritten !== false && mb_strlen( $rewritten ) > 0 ) {
				$result['title'] = $rewritten;
			}
		}

		$result['title']            = PostService::limitTitleLength( $result['title'], 70 );
		$result['excerpt']          = PostService::limitExcerptLength( $result['excerpt'] );
		$result['meta_description'] = PostService::limitExcerptLength( $result['meta_description'], 138 );

		if ( ! empty( $result['content'] ) ) {
			$result['content'] = PostService::validateAndCleanLinks( $result['content'] );
			
			$result['content'] = preg_replace_callback(
				'/<a\b[^>]*>.*?<\/a>(*SKIP)(*F)|\bTD Vieira Design\b/i',
				function( $matches ) {
					return '<strong><a href="https://tdvieiradesign.com" target="_blank">TD Vieira Design</a></strong>';
				},
				$result['content']
			);

			$result['content'] = preg_replace( '/<\/ul>(\s*\n)*/i', "</ul>\n\n", $result['content'] );
			$result['content'] = preg_replace( '/<\/ol>(\s*\n)*/i', "</ol>\n\n", $result['content'] );
			$result['content'] = PostService::limitArticleLinks( $result['content'] );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX: Chamar as APIs de IA para gerar imagem.
	 */
	public static function handleGenerateImage() {
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

		$provider = ProviderFactory::createImageProvider( $image_provider );
		if ( is_wp_error( $provider ) ) {
			wp_send_json_error( array( 'message' => $provider->get_error_message() ) );
		}

		$response = $provider->generateImage( $prompt, $image_model );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Erro ao gerar imagem.', 'gerador-posts-gemini' );
			wp_send_json_error( array( 'message' => $error_msg ) );
		}

		$data = json_decode( $response_body, true );

		if ( $image_provider === 'openai' ) {
			$image_url = isset( $data['data'][0]['url'] ) ? $data['data'][0]['url'] : '';
			wp_send_json_success( array(
				'type' => 'url',
				'source' => $image_url
			) );
		} else {
			$base64_data = isset( $data['predictions'][0]['bytesBase64Encoded'] ) ? $data['predictions'][0]['bytesBase64Encoded'] : '';
			wp_send_json_success( array(
				'type' => 'base64',
				'source' => 'data:image/jpeg;base64,' . $base64_data
			) );
		}
	}

	/**
	 * AJAX: Salvar o post gerado de forma estruturada no WordPress.
	 */
	public static function handleSavePost() {
		check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
		}

		$title            = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$title            = PostService::limitTitleLength( $title );
		$content          = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
		
		if ( ! empty( $content ) ) {
			$content = preg_replace_callback(
				'/<a\b[^>]*>.*?<\/a>(*SKIP)(*F)|\bTD Vieira Design\b/i',
				function( $matches ) {
					return '<strong><a href="https://tdvieiradesign.com" target="_blank">TD Vieira Design</a></strong>';
				},
				$content
			);

			$content = preg_replace( '/<\/ul>(\s*\n)*/i', "</ul>\n\n", $content );
			$content = preg_replace( '/<\/ol>(\s*\n)*/i', "</ol>\n\n", $content );
			$content = PostService::limitArticleLinks( $content );
		}

		$meta_description = isset( $_POST['meta_description'] ) ? sanitize_text_field( $_POST['meta_description'] ) : '';
		$meta_description = PostService::limitExcerptLength( $meta_description, 138 );
		$excerpt          = isset( $_POST['excerpt'] ) ? sanitize_text_field( $_POST['excerpt'] ) : '';
		$excerpt          = PostService::limitExcerptLength( $excerpt );
		$keywords         = isset( $_POST['keywords'] ) ? sanitize_text_field( $_POST['keywords'] ) : '';
		$category         = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
		$publish_date     = isset( $_POST['publish_date'] ) ? sanitize_text_field( $_POST['publish_date'] ) : '';
		$suggested_slug   = isset( $_POST['suggested_slug'] ) ? sanitize_title( $_POST['suggested_slug'] ) : '';

		$image_1_source   = isset( $_POST['image_1_source'] ) ? $_POST['image_1_source'] : '';
		$image_1_type     = isset( $_POST['image_1_type'] ) ? sanitize_text_field( $_POST['image_1_type'] ) : '';
		$image_2_source   = isset( $_POST['image_2_source'] ) ? $_POST['image_2_source'] : '';
		$image_2_type     = isset( $_POST['image_2_type'] ) ? sanitize_text_field( $_POST['image_2_type'] ) : '';

		if ( empty( $title ) || empty( $content ) ) {
			wp_send_json_error( array( 'message' => __( 'Título e Conteúdo são obrigatórios.', 'gerador-posts-gemini' ) ) );
		}

		$post_status = 'draft';
		$formatted_date = '';
		if ( ! empty( $publish_date ) ) {
			$time_val = strtotime( $publish_date );
			if ( $time_val ) {
				$formatted_date = date( 'Y-m-d H:i:s', $time_val );
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

		if ( ! empty( $category ) ) {
			$cat_obj = get_term_by( 'name', $category, 'category' );
			if ( $cat_obj ) {
				$new_post['post_category'] = array( $cat_obj->term_id );
			}
		}

		$post_id = wp_insert_post( $new_post );

		if ( is_wp_error( $post_id ) || $post_id === 0 ) {
			wp_send_json_error( array( 'message' => __( 'Erro ao inserir post.', 'gerador-posts-gemini' ) ) );
		}

		PostService::saveRankMathMetadata( $post_id, $keywords, $meta_description );

		$processed_images = MediaProcessor::downloadAndProcessImages( $post_id, $title, $keywords, $image_1_source, $image_1_type, $image_2_source, $image_2_type );

		$image_1_url = $processed_images['image_1_url'];
		$image_2_url = $processed_images['image_2_url'];
		$img_alt_1   = $processed_images['img_alt_1'];
		$img_title_1 = $processed_images['img_title_1'];
		$img_alt_2   = $processed_images['img_alt_2'];
		$img_title_2 = $processed_images['img_title_2'];

		$veja_html = PostService::generateVejaTambemHtml( $post_id );
		$final_content = PostService::sanitizeAndCleanContent( $content, $image_1_url, $image_2_url, $img_alt_1, $img_title_1, $img_alt_2, $img_title_2, $veja_html );

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
	 * AJAX: Mover post gerado para a lixeira do WordPress.
	 */
	public static function handleDeletePost() {
		check_ajax_referer( 'gpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissão negada.', 'gerador-posts-gemini' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'ID do post inválido.', 'gerador-posts-gemini' ) ) );
		}

		$deleted = wp_trash_post( $post_id );

		if ( $deleted ) {
			wp_send_json_success( array( 'message' => __( 'Post removido com sucesso.', 'gerador-posts-gemini' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Erro ao remover o post.', 'gerador-posts-gemini' ) ) );
		}
	}
}
