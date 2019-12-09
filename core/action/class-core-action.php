<?php
/**
 * Classe gérant les actions principales de WPshop.
 *
 * Elle ajoute les styles et scripts JS principaux pour le bon fonctionnement de WPshop.
 * Elle ajoute également les textes de traductions (fichiers .mo)
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2019 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Classes
 *
 * @since     2.0.0
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit;

/**
 * Main actions of wpshop.
 */
class Core_Action {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'callback_register_session' ), 1 );
		add_action( 'init', array( $this, 'callback_language' ) );
		add_action( 'init', array( $this, 'callback_install_default' ) );

		add_action( 'wp_head', array( $this, 'define_ajax_url' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'callback_admin_enqueue_scripts' ), 11 );
		add_action( 'wp_enqueue_scripts', array( $this, 'callback_enqueue_scripts' ), 11 );

		add_action( 'admin_menu', array( $this, 'callback_admin_menu' ) );

		add_action( 'wp_ajax_check_erp_statut', array( $this, 'check_erp_statut' ) );
	}

	/**
	 * Enregistres la session et enlève l'admin bar
	 *
	 * @since 2.0.0
	 */
	public function callback_register_session() {
		if ( ! session_id() ) {
			session_start();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Charges le fichier de traduction.
	 *
	 * @since 2.0.0
	 */
	public function callback_language() {
		load_plugin_textdomain( 'wpshop', false, PLUGIN_WPSHOP_DIR . '/core/asset/language/' );
	}

	/**
	 * Installes les données par défaut.
	 *
	 * @since 2.0.0
	 */
	public function callback_install_default() {
		Core::g()->default_install();
	}

	/**
	 * Ajoutes ajaxurl
	 *
	 * @since 2.0.0
	 */
	public function define_ajax_url() {
		echo '<script type="text/javascript">
		  var ajaxurl = "' . esc_url( admin_url( 'admin-ajax.php' ) ) . '";
		</script>';
	}

	/**
	 * Init backend style and script
	 *
	 * @since 2.0.0
	 */
	public function callback_admin_enqueue_scripts() {
		wp_enqueue_media();

		wp_dequeue_script( 'wpeo-assets-datepicker-js' );
		wp_dequeue_style( 'wpeo-assets-datepicker' );

		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery' ) );

		wp_enqueue_style( 'wpshop-style', PLUGIN_WPSHOP_URL . 'core/asset/css/style.css', array(), \eoxia\Config_Util::$init['wpshop']->version );
		wp_enqueue_script( 'wpshop-backend-script', PLUGIN_WPSHOP_URL . 'core/asset/js/backend.min.js', array( 'jquery', 'jquery-form' ), \eoxia\Config_Util::$init['wpshop']->version );

		$script_params = array(
				'check_erp_statut_nonce' => wp_create_nonce( 'check_erp_statut' ),
				'url'                    => get_option( 'siteurl' ),
		);

		wp_localize_script( 'wpshop-backend-script', 'scriptParams', $script_params );
	}

	/**
	 * Init backend style and script
	 *
	 * @since 2.0.0
	 */
	public function callback_enqueue_scripts() {
		wp_dequeue_script( 'wpeo-assets-datepicker-js' );
		wp_dequeue_style( 'wpeo-assets-datepicker' );
		wp_enqueue_style( 'wpshop-style', PLUGIN_WPSHOP_URL . 'core/asset/css/style.css', array(), \eoxia\Config_Util::$init['wpshop']->version );
		wp_enqueue_style( 'wpshop-style-frontend', PLUGIN_WPSHOP_URL . 'core/asset/css/style.frontend.min.css', array(), \eoxia\Config_Util::$init['wpshop']->version );
		wp_enqueue_script( 'wpshop-frontend-script', PLUGIN_WPSHOP_URL . 'core/asset/js/frontend.min.js', array(), \eoxia\Config_Util::$init['wpshop']->version );

		$script_params = array(
			'check_erp_statut_nonce' => wp_create_nonce( 'check_erp_statut' ),
			'url'                    => get_option( 'siteurl' ),
		);

		wp_localize_script( 'wpshop-frontend-script', 'scriptParams', $script_params );
	}

	/**
	 * Ajoutes le menu principal de WPshop.
	 *
	 * @since 2.0.0
	 */
	public function callback_admin_menu() {
		add_menu_page( __( 'WPshop', 'wpshop' ), __( 'WPshop', 'wpshop' ), 'manage_options', 'wpshop', '', 'dashicons-store' );
		add_submenu_page( 'wpshop', __( 'Dashboard', 'wpshop' ), __( 'Dashboard', 'wpshop' ), 'manage_options', 'wpshop', array( Dashboard::g(), 'callback_add_menu_page' ) );
	}

	public function check_erp_statut() {
		check_ajax_referer( 'check_erp_statut' );

		$statut          = false;
		$dolibarr_option = get_option( 'wps_dolibarr', Settings::g()->default_settings );

		if ( empty( $dolibarr_option['dolibarr_url'] ) || empty( $dolibarr_option['dolibarr_secret'] ) ) {
			wp_send_json_success( array(
				'connected' => false,
			) );
		}

		$response = Request_Util::get( 'status' );

		if ( ! empty( $response ) && 200 === $response->success->code ) {
			$statut = true;
		}

		ob_start();
		require_once( PLUGIN_WPSHOP_PATH . '/core/view/erp-connexion-error.view.php' );
		wp_send_json_success( array(
			'connected' => true,
			'statut'    => $statut,
			'view'      => ob_get_clean(),
		) );
	}
}

new Core_Action();
