<?php
namespace GPG\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use GPG\Controllers\AjaxController;
use GPG\Services\PostService;

/**
 * Classe responsável pelo gerenciamento de inicialização do plugin.
 * Organiza o registro e acoplamento dos componentes do sistema.
 */
class PluginBootstrap {

	/**
	 * Ponto de entrada de inicialização do plugin (Bootstrapper).
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( self::class, 'init' ) );
	}

	/**
	 * Executado quando todos os plugins ativos do WordPress forem carregados.
	 * Registra todas as ações, hooks e rotas AJAX da aplicação.
	 */
	public static function init() {
		// Inicializar categorias padrão do blog ao carregar o plugin
		add_action( 'init', array( PostService::class, 'ensureCategoriesExist' ) );

		// Carregar estilos e dependências JavaScript
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueueFrontendStyles' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueueAdminStyles' ) );

		// Registrar menu administrativo
		add_action( 'admin_menu', array( self::class, 'registerMenu' ) );

		// Roteamento de ganchos AJAX do plugin
		add_action( 'wp_ajax_gpg_generate_post', array( AjaxController::class, 'handleGeneratePost' ) );
		add_action( 'wp_ajax_gpg_generate_image', array( AjaxController::class, 'handleGenerateImage' ) );
		add_action( 'wp_ajax_gpg_save_post', array( AjaxController::class, 'handleSavePost' ) );
		add_action( 'wp_ajax_gpg_save_settings', array( AjaxController::class, 'handleSaveSettings' ) );
		add_action( 'wp_ajax_gpg_delete_post', array( AjaxController::class, 'handleDeletePost' ) );

		// Invalidação de cache baseada em Transients nativos
		add_action( 'save_post', array( PostService::class, 'invalidatePostsCache' ) );
		add_action( 'deleted_post', array( PostService::class, 'invalidatePostsCache' ) );
		add_action( 'trash_post', array( PostService::class, 'invalidatePostsCache' ) );
	}

	/**
	 * Carrega estilos css no frontend.
	 */
	public static function enqueueFrontendStyles() {
		wp_enqueue_style( 'gerador-posts-frontend', plugin_dir_url( dirname( __DIR__ ) ) . 'assets/css/frontend.css', array(), '2.0.4' );
	}

	/**
	 * Carrega estilos e bibliotecas Javascript no backend.
	 */
	public static function enqueueAdminStyles( $hook ) {
		if ( 'posts_page_gerador-posts-gemini' === $hook ) {
			wp_enqueue_style( 'gerador-posts-admin-css', plugin_dir_url( dirname( __DIR__ ) ) . 'assets/css/admin.css', array(), '2.0.4' );
			
			// Enfileirar biblioteca Puter.js
			wp_enqueue_script( 'puter-js', 'https://js.puter.com/v2/', array(), '2.0.4', false );
			
			// Enfileirar JavaScript administrativo principal
			wp_enqueue_script( 'gerador-posts-admin-js', plugin_dir_url( dirname( __DIR__ ) ) . 'assets/js/admin.js', array( 'jquery', 'puter-js' ), '2.0.4', true );
			
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
			
			wp_localize_script( 'gerador-posts-admin-js', 'gpgAdminData', array(
				'nonce'             => wp_create_nonce( 'gpg_admin_nonce' ),
				'categoriesOptions' => $categories_options_html
			) );
		}
	}

	/**
	 * Adiciona a página de administração ao menu do WordPress.
	 */
	public static function registerMenu() {
		add_posts_page(
			__( 'Gerador de Posts', 'gerador-posts-gemini' ),
			__( 'Gerador de Posts', 'gerador-posts-gemini' ),
			'manage_options',
			'gerador-posts-gemini',
			array( self::class, 'renderAdminPage' )
		);
	}

	/**
	 * Renderiza o template de interface do plugin.
	 */
	public static function renderAdminPage() {
		require_once plugin_dir_path( dirname( __DIR__ ) ) . 'admin-ui.php';
	}
}
