<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TTW_Ajax_Handlers' ) ) {

	/**
	 * Define TTW_Ajax_Handlers class
	 */
	class TTW_Ajax_Handlers {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {

			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				return;
			}

			add_action( 'wp_ajax_tm_theme_wizard_verify_data', array( $this, 'verify_data' ) );
		}

		/**
		 * Verfify template ID and orrder ID.
		 *
		 * @return void
		 */
		public function verify_data() {

			$nonce = isset( $_REQUEST['nonce'] ) ? esc_attr( $_REQUEST['nonce'] ) : false;

			if ( ! $nonce || ! wp_verify_nonce( $nonce, ttw()->slug() ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Nonce verfictaion failed', 'tm-theme-wizard' ),
				) );
			}

			$template_id = isset( $_REQUEST['template_id'] ) ? esc_attr( $_REQUEST['template_id'] ) : false;
			$order_id    = isset( $_REQUEST['order_id'] ) ? esc_attr( $_REQUEST['order_id'] ) : false;

			if ( ! $template_id || ! $order_id ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Please fill Template ID and Order ID fields and try again', 'tm-theme-wizard' ),
				) );
			}

			ttw()->dependencies( array( 'updater-api' ) );

			$api  = ttw_updater_api( $template_id, $order_id );
			$link = $api->get_latest_release_link();

			if ( ! $link ) {
				wp_send_json_error( array(
					'message' => $api->get_error(),
				) );
			} else {
				set_transient( ttw()->slug(), $link, 120 );
				wp_send_json_success( array(
					'message' => esc_html__( 'Your template ID and order ID vrifyed. Downloading and installing theme...', 'tm-theme-wizard' ),
				) );
			}
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
 * Returns instance of TTW_Ajax_Handlers
 *
 * @return object
 */
function ttw_ajax_handlers() {
	return TTW_Ajax_Handlers::get_instance();
}

ttw_ajax_handlers();
