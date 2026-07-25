<?php
namespace GPG\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Serviço especializado no processamento físico de mídias do plugin.
 * Cuida do download, saneamento contra SSRF, conversão WebP, crops Retina e sideload.
 */
class MediaProcessor {

	/**
	 * Baixa, converte para WebP, gera crop retina e associa as imagens ao post.
	 *
	 * @param int    $post_id ID do post do WordPress.
	 * @param string $title Título do post.
	 * @param string $keywords Palavras-chave de foco (separadas por vírgula).
	 * @param string $image_1_source Origem da imagem 1 (base64 ou URL).
	 * @param string $image_1_type Tipo da imagem 1 (base64 ou URL).
	 * @param string $image_2_source Origem da imagem 2.
	 * @param string $image_2_type Tipo da imagem 2.
	 * @return array URL das imagens e metadados de SEO.
	 */
	public static function downloadAndProcessImages( $post_id, $title, $keywords, $image_1_source, $image_1_type, $image_2_source, $image_2_type ) {
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
			$attachment_id_destaque = self::uploadMediaSource( $image_1_source, $image_1_type, $filename_destaque, $post_id, 1408, 474 );
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
			$attachment_id_corpo = self::uploadMediaSource( $image_1_source, $image_1_type, $filename_corpo, $post_id, 1408, 792 );
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
			$attachment_id_2 = self::uploadMediaSource( $image_2_source, $image_2_type, $filename_2, $post_id, 1408, 792 );
			
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
	 * Trata o download, validações contra SSRF, conversão para WebP e crop da mídia física.
	 */
	public static function uploadMediaSource( $source, $type, $filename, $post_id, $target_width = 0, $target_height = 0 ) {
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
				return new \WP_Error( 'gpg_invalid_image_url', __( 'A URL da imagem fornecida é inválida ou aponta para um host interno inseguro (SSRF).', 'gerador-posts-gemini' ) );
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
}
