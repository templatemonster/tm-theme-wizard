?id=62222&slug=monstroid2&name=Monstroid2
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

if ( ! class_exists( 'TTW_Child_API' ) ) {

	/**
	 * Define TTW_Child_API class
	 */
	class TTW_Child_API {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Passed Template ID holder.
		 *
		 * @var int
		 */
		private $id = null;

		/**
		 * Passed theme slug holder.
		 *
		 * @var string
		 */
		private $slug = null;

		/**
		 * Passed theme name holder.
		 *
		 * @var string
		 */
		private $name = null;

		/**
		 * Storage for error data.
		 *
		 * @var null
		 */
		private $error = null;

		/**
		 * Endpoint for updated list
		 *
		 * @var string
		 */
		protected $server = 'https://cloud.cherryframework.com/child-generator/';

		/**
		 * Constructor for the class
		 *
		 * @param int    $template_id Template ID from templatemonster.com.
		 * @param string $order_id    Order ID from user order details.
		 * @param string $order_id    Order ID from user order details.
		 */
		function __construct( $id = null, $slug = null, $name = null ) {
			$this->id   = $id;
			$this->slug = $slug;
			$this->name = $name;
		}

		/**
		 * Returns error text
		 *
		 * @return void
		 */
		public function get_error() {
			return $this->error;
		}

		/**
		 * Perform an API call and return call body.
		 *
		 * @param  string $endpoint Requested endpoint.
		 * @param  array  $data     Request data.
		 * @return array
		 */
		public function api_call() {

			$request = add_query_arg(
				array(
					'id'   => $this->id,
					'slug' => $this->slug,
					'name' => $this->name,
				),
				$this->server
			);

			$response = wp_remote_get( $request );
			$result   = wp_remote_retrieve_body( $response );

			$result = json_decode( $result, true );

			return $result;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @param  int    $template_id Template ID from templatemonster.com.
		 * @param  string $order_id    Order ID from user order details.
		 * @return object
		 */
		public static function get_instance( $id = null, $slug = null, $name = null ) {
			return new self( $id, $slug, $name );
		}
	}

}

/**
 * Returns instance of TTW_Child_API
 *
 * @param  int    $template_id Template ID from templatemonster.com.
 * @param  string $order_id    Order ID from user order details.
 * @return object
 */
function ttw_child_api( $id = null, $slug = null, $name = null ) {
	return TTW_Child_API::get_instance( $id, $slug, $name );
}
