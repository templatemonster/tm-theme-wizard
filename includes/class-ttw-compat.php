<?php
/**
 * Theme and 3rd party plugins compatbility class
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TTW_Compat' ) ) {

	/**
	 * Define TTW_Compat class
	 */
	class TTW_Compat {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Compatibility actions cache.
		 *
		 * @var array
		 */
		private $cache = array();

		/**
		 * Constructor for the class
		 */
		function __construct() {
			add_action( 'after_setup_theme', array( $this, 'default_theme_compat' ), 99 );
		}

		/**
		 * Returns plugins wizard data
		 *
		 * @return array
		 */
		public function get_wizard() {
			return apply_filters( 'ttw_get_plugins_wizard_from_theme', false );
		}

		/**
		 * Perform plugins wizard installation to allow themes compatibility
		 *
		 * @return void
		 */
		public function default_theme_compat() {

			$plugins_wizard = $this->get_wizard();

			if ( ! $plugins_wizard ) {
				return;
			}

			add_filter( 'ttw_activate_theme_response', array( $this, 'add_install_wizard_step' ), 10, 2 );

			add_action( 'wp_ajax_tm_theme_wizard_install_plugins_wizard', array( $this, 'install_plugins_wizard' ) );
			add_action( 'ttw_skip_child_installation',                    array( $this, 'install_plugins_wizard' ) );

			add_action( 'wp_ajax_tm_theme_wizard_get_success_redirect_link', array( $this, 'get_success_redirect' ) );
		}

		/**
		 * Adds wizard installation step
		 */
		public function add_install_wizard_step( $response, $type ) {

			if ( 'child' !== $type ) {
				return $response;
			}

			$response = array(
				'message'     => esc_html__( 'Installing plugins wizard...', 'tm-theme-wizard' ),
				'doNext'      => true,
				'nextRequest' => array(
					'action' => 'tm_theme_wizard_install_plugins_wizard',
				),
			);

			return $response;
		}

		/**
		 * Perform plugins wizard installation}
		 * @return void
		 */
		public function install_plugins_wizard() {

			ttw_ajax_handlers()->verify_request();
			$wizard_data = $this->get_wizard();

			if ( ! $wizard_data || ! isset( $wizard_data['source'] ) || ! isset( $wizard_data['slug'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Plugins wizard data not found.', 'tm-theme-wizard' ),
				) );
			}

			ttw()->dependencies( array( 'install-api' ) );
			$api = ttw_install_api( $wizard_data['source'] );

			$result = $api->do_plugin_install( $wizard_data['slug'] );

			$plugin_info = $api->get_info();

			if ( ! empty( $plugin_info['file'] ) ) {
				activate_plugin( $plugin_info['file'] );
			}

			wp_send_json_success( array(
				'message'  => $result['message'],
				'doNext'      => true,
				'nextRequest' => array(
					'action' => 'tm_theme_wizard_get_success_redirect_link',
				),
			) );
		}

		/**
		 * Get redirect link.
		 *
		 * @return void
		 */
		public function get_success_redirect() {
			ttw_ajax_handlers()->verify_request();
			wp_send_json_success( array(
				'message'  => esc_html__( 'All done, redirecting...', 'tm-theme-wizard' ),
				'redirect' => ttw_interface()->success_page_link(),
			) );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of TTW_Compat
 *
 * @return object
 */
function ttw_compat() {
	return TTW_Compat::get_instance();
}

ttw_compat();
