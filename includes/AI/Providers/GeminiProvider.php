<?php
namespace GPG\AI\Providers;

use GPG\AI\Contracts\TextProviderInterface;
use GPG\AI\Contracts\ImageProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Provedor para integração com os serviços de Inteligência Artificial do Google Gemini.
 */
class GeminiProvider extends AbstractProvider implements TextProviderInterface, ImageProviderInterface {

	/**
	 * Gera texto estruturado via Google Gemini utilizando schema JSON rígido.
	 */
	public function generateText( $prompt, $model ) {
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
						'suggested_slug' => array( 'type' => 'STRING' ),
						'focus_keyword' => array( 'type' => 'STRING' ),
						'seo_title' => array( 'type' => 'STRING' ),
						'seo_description' => array( 'type' => 'STRING' ),
					),
					'required' => array( 'title', 'content', 'suggested_slug', 'focus_keyword', 'seo_title', 'seo_description' ),
				),
			),
		);

		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $this->api_key;

		$response = $this->post( $url, $body, array() );
		return $this->checkResponse( $response, 'Gemini' );
	}

	/**
	 * Gera imagens utilizando a API de Imagens do Google Imagen integrada no Gemini.
	 */
	public function generateImage( $prompt, $model ) {
		$body = array(
			'instances' => array(
				array(
					'prompt' => $prompt
				)
			),
			'parameters' => array(
				'sampleCount' => 1,
				'aspectRatio' => '16:9',
				'outputMimeType' => 'image/jpeg',
			),
		);

		$url = 'https://generativelanguage.googleapis.com/v1beta/projects/unused/locations/unused/publishers/google/models/' . $model . ':predict?key=' . $this->api_key;

		$response = $this->post( $url, $body, array() );
		return $this->checkResponse( $response, 'Gemini Imagen' );
	}
}
