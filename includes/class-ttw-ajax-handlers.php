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

			//$this->test_request();

			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				return;
			}

			$actions = array(
				'verify_data',
				'install_parent',
				'activate_parent',
				'install_child',
				'activate_child',
			);

			foreach ( $actions as $action ) {
				if ( is_callable( array( $this, $action ) ) ) {
					add_action( 'wp_ajax_tm_theme_wizard_' . $action, array( $this, $action ) );
				}
			}
		}

		public function test_request() {
			add_action( 'init',    array( $this, 'verify_data' ) );
		}

		/**
		 * Perforem child theme installation
		 *
		 * @return void
		 */
		public function install_child() {

			$this->verify_request();

			ttw()->dependencies( array( 'child-api' ) );

			$theme_data = get_option( ttw()->settings['options']['parent_data'] );
			$api = ttw_child_api();

		}

		/**
		 * Process parent theme activation.
		 *
		 * @return void
		 */
		public function activate_parent() {

			$this->verify_request();

			$theme_data = get_option( ttw()->settings['options']['parent_data'] );

			/**
			 * Fires before parent theme activation
			 */
			do_action( 'tm_theme_wizard_before_parent_activation', $theme_data );

			if ( empty( $theme_data['TextDomain'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Can\'t founf theme to activate', 'tm-theme-wizard' ),
				) );
			}

			$theme_name    = $theme_data['TextDomain'];
			$current_theme = wp_get_theme();

			if ( $current_theme->stylesheet === $theme_name ) {
				$message = esc_html__( 'Theme already active. Redirecting...', 'tm-theme-wizard' );
			} else {
				$message = esc_html__( 'Theme sucessfully activated. Redirecting...', 'tm-theme-wizard' );
				switch_theme( $theme_name );
			}

			$redirect = ttw_interface()->get_page_link( 'child-theme' );

			/**
			 * Fires after parent theme activation
			 */
			do_action( 'tm_theme_wizard_after_parent_activation', $theme_data );

			wp_send_json_success( array(
				'message'  => $message,
				'redirect' => $redirect,
			) );
		}

		/**
		 * Process parent theme installation
		 *
		 * @return void
		 */
		public function install_parent() {

			$this->verify_request();

			$theme_url = get_transient( ttw()->slug() );

			/**
			 * Allow to filter parent theme URL
			 *
			 * @var string
			 */
			$theme_url = apply_filters( 'tm_theme_wizard_parent_zip_url', $theme_url );

			if ( ! $theme_url ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Theme URL was lost. Please refresh page and try again.', 'tm-theme-wizard' ),
				) );
			}

			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

			add_filter( 'upgrader_source_selection', array( $this, 'adjust_theme_dir' ), 1, 3 );

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Theme_Upgrader( $skin );
			$result   = $upgrader->install( $theme_url );

			remove_filter( 'upgrader_source_selection', array( $this, 'adjust_theme_dir' ), 1 );

			$data            = array();
			$install_failed  = false;
			$success_message = false;

			if ( is_wp_error( $result ) ) {

				$data['message'] = $result->get_error_message();
				$install_failed  = true;

			} elseif ( is_wp_error( $skin->result ) ) {

				if ( ! isset( $skin->result->errors['folder_exists'] ) ) {
					$data['message'] = $skin->result->get_error_message();
					$install_failed  = true;
				} else {
					$success_message = esc_html__( 'Theme already installed. Activating...', 'tm-theme-wizard' );
				}

			} elseif ( $skin->get_errors()->get_error_code() ) {

				$data['message'] = $skin->get_error_messages();
				$install_failed  = true;

			} elseif ( is_null( $result ) ) {

				global $wp_filesystem;
				$data['message'] = esc_html__( 'Unable to connect to the filesystem. Please confirm your credentials.', 'tm-theme-wizard' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					$data['message'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				$install_failed = true;

			}

			if ( true === $install_failed ) {
				wp_send_json_error( $data );
			}

			$theme_data = get_option( ttw()->settings['options']['parent_data'] );

			if ( false === $success_message ) {
				$success_message = sprintf(
					esc_html__( 'Theme %s succesfully installed. Activating...', 'tm-theme-wizard' ),
					isset( $theme_data['ThemeName'] ) ? esc_html( $theme_data['ThemeName'] ) : false
				);
			}

			/**
			 * Fires when parent installed before sending result.
			 */
			do_action( 'tm_theme_wizard_parent_installed', $theme_data );

			wp_send_json_success( array(
				'message'     => $success_message,
				'doNext'      => true,
				'nextRequest' => array(
					'action' => 'tm_theme_wizard_activate_parent',
				),
			) );
		}

		/**
		 * Adjust the theme directory name.
		 *
		 * @since  1.0.0
		 * @param  string       $source        Path to upgrade/zip-file-name.tmp/subdirectory/.
		 * @param  string       $remote_source Path to upgrade/zip-file-name.tmp.
		 * @param  \WP_Upgrader $upgrader      Instance of the upgrader which installs the theme.
		 * @return string $source
		 */
		public function adjust_theme_dir( $source, $remote_source, $upgrader ) {

			global $wp_filesystem;

			if ( ! is_object( $wp_filesystem ) ) {
				return $source;
			}

			// Ensure that is Wizard installation request
			if ( empty( $_REQUEST['action'] ) && 'tm_theme_wizard_install_parent' !== $_REQUEST['action'] ) {
				return $source;
			}

			// Check for single file plugins.
			$source_files = array_keys( $wp_filesystem->dirlist( $remote_source ) );
			if ( 1 === count( $source_files ) && false === $wp_filesystem->is_dir( $source ) ) {
				return $source;
			}

			$css_key  = array_search( 'style.css', $source_files );

			if ( false === $css_key ) {
				return $source;
			}

			$css_path = $remote_source . '/' . $source_files[ $css_key ];

			if ( ! file_exists( $css_path ) ) {
				return $source;
			}

			$theme_data = get_file_data( $css_path, array(
				'TextDomain' => 'Text Domain',
				'ThemeName'  => 'Theme Name',
			), 'theme' );

			if ( ! $theme_data || ! isset( $theme_data['TextDomain'] ) ) {
				return $source;
			}

			$theme_name = $theme_data['TextDomain'];
			$from_path  = untrailingslashit( $source );
			$to_path    = untrailingslashit( str_replace( basename( $remote_source ), $theme_name, $remote_source ) );

			if ( true === $wp_filesystem->move( $from_path, $to_path ) ) {

				update_option( ttw()->settings['options']['parent_data'], $theme_data );
				return trailingslashit( $to_path );

			} else {

				return new WP_Error(
					'rename_failed',
					esc_html__( 'The remote plugin package does not contain a folder with the desired slug and renaming did not work.', 'tm-theme-wizard' ),
					array( 'found' => $subdir_name, 'expected' => $theme_name )
				);

			}

			return $source;
		}

		/**
		 * Verfify template ID and orrder ID.
		 *
		 * @return void
		 */
		public function verify_data() {

			$this->verify_request();

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
				set_transient( ttw()->slug(), $link, DAY_IN_SECONDS );
				wp_send_json_success( array(
					'message'     => esc_html__( 'Your template ID and order ID vrifyed. Downloading and installing theme...', 'tm-theme-wizard' ),
					'doNext'      => true,
					'nextRequest' => array(
						'action' => 'tm_theme_wizard_install_parent',
					),
				) );
			}
		}

		/**
		 * Verify AJAX request.
		 *
		 * @return void
		 */
		public function verify_request() {

			if ( ! current_user_can( 'install_themes' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to access this', 'tm-theme-wizard' ),
				) );
			}

			$nonce = isset( $_REQUEST['nonce'] ) ? esc_attr( $_REQUEST['nonce'] ) : false;

			if ( ! $nonce || ! wp_verify_nonce( $nonce, ttw()->slug() ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Nonce verfictaion failed', 'tm-theme-wizard' ),
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
