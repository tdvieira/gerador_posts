<?php
namespace GPG\AI\Providers;

use GPG\AI\Contracts\ImageProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Provedor para geração de imagens utilizando os modelos Google Imagen.
 */
class GoogleImagenProvider extends AbstractProvider implements ImageProviderInterface {

	/**
	 * Gera imagem via Google Imagen (predict endpoint) em proporção widescreen 16:9.
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
		return $this->checkResponse( $response, 'Google Imagen' );
	}
}
