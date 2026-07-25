<?php
namespace GPG\AI\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Classe base abstrata para todos os provedores de Inteligência Artificial.
 * Centraliza requisições HTTP, cabeçalhos, tratamento de erros e timeouts.
 */
abstract class AbstractProvider {

	/**
	 * @var string Chave de acesso da API correspondente.
	 */
	protected $api_key;

	/**
	 * Construtor básico de provedor.
	 *
	 * @param string $api_key Chave de API correspondente.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Executa requisição HTTP POST para a API correspondente.
	 *
	 * @param string $url Endpoint de destino da API.
	 * @param array  $body Corpo da requisição a ser codificado em JSON.
	 * @param array  $headers Cabeçalhos HTTP adicionais para autenticação.
	 * @param int    $timeout Limite máximo de expiração em segundos (padrão 90s).
	 * @return array|\WP_Error Resposta HTTP ou erro de rede.
	 */
	protected function post( $url, $body, $headers = array(), $timeout = 90 ) {
		$default_headers = array(
			'Content-Type' => 'application/json',
		);

		$all_headers = array_merge( $default_headers, $headers );

		$args = array(
			'headers' => $all_headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => $timeout,
		);

		return wp_remote_post( $url, $args );
	}

	/**
	 * Valida e formata retornos HTTP padronizando mensagens de erro e exceções de timeouts.
	 *
	 * @param array|\WP_Error $response Resposta HTTP correspondente.
	 * @param string          $provider_name Nome fantasia do provedor.
	 * @return array|\WP_Error Resposta HTTP limpa ou objeto WP_Error se houver falhas.
	 */
	protected function checkResponse( $response, $provider_name ) {
		if ( is_wp_error( $response ) ) {
			$err_msg = $response->get_error_message();
			if ( strpos( strtolower( $err_msg ), 'timeout' ) !== false || strpos( strtolower( $err_msg ), 'timed out' ) !== false ) {
				return new \WP_Error(
					'gpg_api_timeout',
					sprintf( __( 'A requisição para o %s expirou (Timeout). A geração de artigos longos ou imagens de alta resolução pode levar mais tempo que o esperado. Por favor, tente novamente.', 'gerador-posts-gemini' ), $provider_name )
				);
			}
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			$response_body = wp_remote_retrieve_body( $response );
			$error_data = json_decode( $response_body, true );
			$error_msg = isset( $error_data['error']['message'] )
				? $error_data['error']['message']
				: sprintf( __( 'Erro de comunicação HTTP %d retornado pela API.', 'gerador-posts-gemini' ), $response_code );

			return new \WP_Error(
				'gpg_api_error',
				sprintf( __( 'Erro no %s (Status %d): %s', 'gerador-posts-gemini' ), $provider_name, $response_code, $error_msg )
			);
		}

		return $response;
	}
}
