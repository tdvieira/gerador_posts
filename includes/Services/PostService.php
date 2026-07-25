<?php
namespace GPG\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Serviço especializado nas regras de negócios, manipulação de posts e SEO.
 */
class PostService {

	/**
	 * Garante a criação das categorias básicas do blog.
	 */
	public static function ensureCategoriesExist() {
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

	/**
	 * Limita o comprimento do título a um máximo de caracteres sem cortar palavras ao meio.
	 */
	public static function limitTitleLength( $title, $max_len = 70 ) {
		$title = trim( $title );
		if ( self::safeStrlen( $title ) <= $max_len ) {
			return $title;
		}
		
		$truncated = self::safeSubstr( $title, 0, $max_len );
		$last_space = self::safeStrrpos( $truncated, ' ' );
		
		if ( $last_space !== false && $last_space > 10 ) {
			return trim( self::safeSubstr( $truncated, 0, $last_space ) );
		}
		
		return trim( $truncated );
	}

	/**
	 * Limita o comprimento do resumo (excerpt) a um máximo de caracteres sem cortar palavras.
	 */
	public static function limitExcerptLength( $excerpt, $max_len = 175 ) {
		$excerpt = trim( $excerpt );
		if ( self::safeStrlen( $excerpt ) <= $max_len ) {
			return $excerpt;
		}
		
		$truncated = self::safeSubstr( $excerpt, 0, $max_len - 3 );
		$last_space = self::safeStrrpos( $truncated, ' ' );
		
		if ( $last_space !== false && $last_space > 20 ) {
			return trim( self::safeSubstr( $truncated, 0, $last_space ) ) . '...';
		}
		
		return trim( $truncated ) . '...';
	}

	/**
	 * Reescreve o título de forma concisa usando a IA correspondente.
	 */
	public static function rewriteTitleConcise( $title, $focus_keywords, $provider, $model ) {
		$title = trim( $title );
		$focus_keywords = trim( $focus_keywords );

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
			$openai_provider = \GPG\AI\ProviderFactory::createTextProvider( 'openai' );
			if ( is_wp_error( $openai_provider ) ) {
				return false;
			}
			$active_model = ! empty( $model ) ? $model : 'gpt-5-mini';
			$response = $openai_provider->generateText( $prompt, $active_model );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				$rewritten_title = isset( $data['choices'][0]['message']['content'] ) ? trim( $data['choices'][0]['message']['content'] ) : '';
			}

		} elseif ( $provider === 'groq' ) {
			$groq_provider = \GPG\AI\ProviderFactory::createTextProvider( 'groq' );
			if ( is_wp_error( $groq_provider ) ) {
				return false;
			}
			$active_model = ! empty( $model ) ? $model : 'llama-3.1-8b';
			$response = $groq_provider->generateText( $prompt, $active_model );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				$rewritten_title = isset( $data['choices'][0]['message']['content'] ) ? trim( $data['choices'][0]['message']['content'] ) : '';
			}

		} else { // gemini
			$gemini_provider = \GPG\AI\ProviderFactory::createTextProvider( 'gemini' );
			if ( is_wp_error( $gemini_provider ) ) {
				return false;
			}
			$active_model = ! empty( $model ) ? $model : 'gemini-3.5-flash';
			$response = $gemini_provider->generateText( $prompt, $active_model );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				$rewritten_title = isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ? trim( $data['candidates'][0]['content']['parts'][0]['text'] ) : '';
			}
		}

		if ( ! empty( $rewritten_title ) ) {
			return trim( $rewritten_title, "\"'“»«" );
		}

		return false;
	}

	/**
	 * Higieniza o corpo do artigo limitando estritamente a 1 link interno e 1 link externo.
	 */
	public static function limitArticleLinks( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$home_url = home_url();
		
		if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER ) ) {
			$internal_count = 0;
			$external_count = 0;

			foreach ( $matches as $match ) {
				$full_tag = $match[0];
				$url      = trim( $match[1] );
				$anchor   = $match[2];

				if ( strpos( $url, '#' ) === 0 ) {
					continue;
				}

				if ( strpos( $url, 'tdvieiradesign.com' ) !== false ) {
					continue;
				}

				$is_internal = false;
				if ( strpos( $url, '/' ) === 0 || ( strpos( $url, '//' ) !== 0 && strpos( $url, 'http' ) !== 0 ) || strpos( $url, $home_url ) !== false ) {
					$is_internal = true;
				}

				if ( $is_internal ) {
					$internal_count++;
					if ( $internal_count > 1 ) {
						$content = str_replace( $full_tag, $anchor, $content );
					}
				} else {
					$external_count++;
					if ( $external_count > 1 ) {
						$content = str_replace( $full_tag, $anchor, $content );
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Invalida os transients de cache de posts recentes.
	 */
	public static function invalidatePostsCache( $post_id ) {
		if ( get_post_type( $post_id ) === 'post' ) {
			delete_transient( 'gpg_recent_posts_links_context' );
			delete_transient( 'gpg_veja_tambem_posts_pool' );
		}
	}

	/**
	 * Filtra e remove tags de links com status 404.
	 */
	public static function validateAndCleanLinks( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$full_tag = $match[0];
				$url      = trim( $match[1] );
				$anchor   = $match[2];

				if ( empty( $url ) || $url === '#' ) {
					if ( strpos( $url, '#' ) === 0 ) {
						continue;
					}
					$content = str_replace( $full_tag, $anchor, $content );
					continue;
				}

				$test_url = $url;
				if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
					$test_url = home_url( $url );
				}

				$is_valid = true;

				$response = wp_remote_request( $test_url, array(
					'method'      => 'HEAD',
					'timeout'     => 3,
					'redirection' => 3,
					'httpversion' => '1.0',
					'sslverify'   => ( wp_get_environment_type() === 'local' || wp_get_environment_type() === 'development' ) ? false : true,
					'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
				) );

				$code = wp_remote_retrieve_response_code( $response );

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

				if ( ! is_wp_error( $response ) && $code === 404 ) {
					$is_valid = false;
				}

				if ( ! $is_valid ) {
					$content = str_replace( $full_tag, $anchor, $content );
				}
			}
		}

		return $content;
	}

	/**
	 * Monta o HTML da seção Veja Também baseado em 3 posts aleatórios.
	 */
	public static function generateVejaTambemHtml( $exclude_post_id = 0 ) {
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
			$count = count( $filtered_pool );
			$num_to_select = min( 3, $count );
			
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
	 * Higieniza, limpa e insere os placeholders de imagens e sumário no conteúdo final.
	 */
	public static function sanitizeAndCleanContent( $content, $image_1_url, $image_2_url, $img_alt_1, $img_title_1, $img_alt_2, $img_title_2, $veja_html ) {
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

		$final_content = preg_replace(
			'/(?:(?:\r?\n)+|<br\s*\/?>|<p>\s*(?:&nbsp;|\s*)*\s*<\/p>)*\s*(<div class="veja">)/i',
			"\n$1",
			$final_content
		);

		$final_content = preg_replace(
			'/(<div class="veja">.*?<\/div>)\s*(?:(?:\r?\n)+|<br\s*\/?>|<p>\s*(?:&nbsp;|\s*)*\s*<\/p>)+/is',
			"$1\n",
			$final_content
		);

		$final_content = preg_replace(
			'/<p\b[^>]*?(?:font-weight:\s*600|color:\s*#FA5B0F)[^>]*?>\s*Sumário de Conteúdo\s*<\/p>/is',
			'<p class="gpg-toc-title" style="font-weight: 600; color: #FA5B0F; margin-top: 50px; margin-bottom: 15px;">Sumário de Conteúdo</p>',
			$final_content
		);

		$final_content = preg_replace(
			'/(?:(?:\r?\n)+|<br\s*\/?>|<p>\s*(?:&nbsp;|\s*)*\s*<\/p>)*\s*(<p class="gpg-toc-title")/i',
			"\n$1",
			$final_content
		);

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

		$final_content = preg_replace(
			'/<h[23]\b[^>]*>\s*(?:<strong>)?\s*(?:Introduç(?:ão|ao)|Início|Inicio)\s*(?:<\/strong>)?\s*<\/h[23]>/is',
			'',
			$final_content
		);

		return $final_content;
	}

	/**
	 * Salva os metadados do Rank Math no banco de dados.
	 */
	public static function saveRankMathMetadata( $post_id, $keywords, $meta_description ) {
		if ( ! empty( $keywords ) ) {
			update_post_meta( $post_id, 'rank_math_focus_keyword', strtolower( $keywords ) );
		}
		if ( ! empty( $meta_description ) ) {
			update_post_meta( $post_id, 'rank_math_description', $meta_description );
		}
	}

	/**
	 * Retorna o comprimento de uma string com suporte multibyte resiliente.
	 */
	public static function safeStrlen( $str ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $str ) : strlen( $str );
	}

	/**
	 * Recorta parte de uma string com suporte multibyte resiliente.
	 */
	public static function safeSubstr( $str, $start, $length = null ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $str, $start, $length );
		}
		return $length !== null ? substr( $str, $start, $length ) : substr( $str, $start );
	}

	/**
	 * Encontra a última posição de um caractere com suporte multibyte resiliente.
	 */
	public static function safeStrrpos( $haystack, $needle ) {
		return function_exists( 'mb_strrpos' ) ? mb_strrpos( $haystack, $needle ) : strrpos( $haystack, $needle );
	}
}
