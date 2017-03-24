<?php
/**
 * Interface management class
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TTW_Interface' ) ) {

	/**
	 * Define TTW_Interface class
	 */
	class TTW_Interface {

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

		}

		/**
		 * Register menu page
		 *
		 * @return void
		 */
		public function register_page() {
			add_management_page(
				esc_html__( 'TM Theme Wizard', 'tm-theme-wizard' ),
				esc_html__( 'TM Theme Wizard', 'tm-theme-wizard' ),
				'manage_options',
				ttw()->slug(),
				array( $this, 'render_page' )
			);
		}

		/**
		 * Render TM Theme Wizard page
		 *
		 * @return void
		 */
		public function render_page() {

			$this->get_template( 'page-header.php' );

			$step = ( ! empty( $_GET['step'] ) ) ? $_GET['step'] : 'verification';
			$this->get_template( 'step-' . $step . '.php' );

			$this->get_template( 'page-footer.php' );
		}

		/**
		 * Return white listed subpages slugs for wizard.
		 *
		 * @return array
		 */
		public function whitelisted_pages() {
			return array(
				'verification',
			);
		}

		/**
		 * Return link to specific wizard step
		 *
		 * @param  string $step Step slug.
		 * @return string
		 */
		public function get_page_link( $step = 'verification' ) {

			$base = esc_url( admin_url( 'tools.php' ) );

			return add_query_arg(
				array(
					'page' => esc_attr( ttw()->slug() ),
					'step' => esc_attr( $step ),
				),
				$base
			);
		}

		/**
		 * Add wizard form row
		 *
		 * @param  array $args Row arguments array
		 * @return void
		 */
		public function add_form_row( $args = array() ) {

			$args = wp_parse_args( $args, array(
				'label'       => '',
				'field'       => '',
				'placeholder' => '',
			) );

			$format = '<div class="theme-wiazrd-form__row">
				<label for="%2$s">%1$s</label>
				<input type="text" name="%2$s" id="%2$s" class="wizard-input input-%2$s" placeholder="%3$s">
			</div>';

			printf( $format, $args['label'], $args['field'], $args['placeholder'] );
		}

		/**
		 * Get plugin template
		 *
		 * @param  string $template Template name.
		 * @param  mixed  $data     Additional data to pass into template
		 * @return void
		 */
		public function get_template( $template, $data = false ) {

			$file = locate_template( ttw()->slug() . '/' . $template );

			if ( ! $file ) {
				$file = ttw()->path( 'templates/' . $template );
			}

			$file = apply_filters( 'ttw_template_path', $file, $template );

			if ( file_exists( $file ) ) {
				include $file;
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
 * Returns instance of TTW_Interface
 *
 * @return object
 */
function ttw_interface() {
	return TTW_Interface::get_instance();
}
