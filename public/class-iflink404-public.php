<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://plus.google.com/u/0/+YANOYasuhiro/
 * @since      1.0.0
 *
 * @package    Iflink404
 * @subpackage Iflink404/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Iflink404
 * @subpackage Iflink404/public
 * @author     yyano <yano.yasuhiro@gmail.com>
 */
class Iflink404_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Plugin options of this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $options    The options of this plugin.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->options = get_option( 'iflink404-options' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Iflink404_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Iflink404_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/iflink404-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Iflink404_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Iflink404_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/iflink404-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 *
	 */
	public function check_url_links() {

		$args = array(
			'post_type' => 'any',
			'post_status' => 'publish',
			'meta_query' => array(
				'relation' => 'AND',
				'url_clause' => array(
					'key' => '_iflink404_check_url',
					'compare' => 'EXISTS',
				),
				'date_clause' => array(
					'key' => '_iflink404_check_date',
					'compare' => 'EXISTS',
				),
				'message_clause' => array(
					'key' => '_iflink404_check_error_message',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby' => array(
				'date_clause' => 'ASC',
			),
			'posts_per_page' => 5,
		);

		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$post_id = get_the_ID();
				$post_type = get_post_type( $post_id );
				$urls = get_post_custom_values( '_iflink404_check_url', $post_id );

				error_log( '--- Iflink404 ---' );
				error_log( 'Iflink404 post id:'.var_export( $post_id, true ) );

				foreach ( $urls as $url ) {
					error_log( 'Iflink404 url:'.var_export( $url, true ) );

					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $url );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_HEADER, true );
					curl_setopt( $ch, CURLOPT_SSLVERSION, 1 );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
					curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
					curl_setopt( $ch, CURLOPT_NOBODY, true );
					curl_setopt( $ch, CURLOPT_TIMEOUT_MS, 1500 );
					curl_setopt( $ch, CURLOPT_USERAGENT, 'iflink404 checker:' . $this->version );

					$response = curl_exec( $ch );
					$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
					$curl_error = curl_error( $ch );

					curl_close( $ch );

					error_log( 'Iflink404 http code:'.var_export( $http_code, true ) );
					error_log( 'Iflink404 curl error:'.var_export( $curl_error, true ) );

					switch ( $http_code ) {
						case '0':
							// Ooops, cURL error!
							add_post_meta( $post_id, '_iflink404_check_error_message', $curl_error, true );

							$this->updatePostStatus($post_id, $post_type);
							break;

						case '200':
							// it's OK!
							break;

						default:
							// Ooops!
							$message = sprintf( '%s: %s', $http_code, $this->getHttpStatus( $http_code ) );
							add_post_meta( $post_id, '_iflink404_check_error_message', $message, true );

							$this->updatePostStatus($post_id, $post_type);
							break;
					}
					update_post_meta( $post_id, '_iflink404_check_date', time() );
				}
			}
		}
	}

	/**
     * Update post status
	 *
	 * @since    1.0.1
	 */
	private function updatePostStatus( $post_id, $post_type ) {

		$to_status = isset( $this->options[$post_type] ) ? esc_attr( $this->options[$post_type] ) : "private";
		$action_name = 'published_to_' . $to_status;

		remove_action( 'save_post', $action_name );
		wp_update_post(
			array(
				'ID' => $post_id,
				'post_status' => $to_status,
			)
		);
		add_action( 'save_post', $action_name );
	}


	/**
	 *
	 */
	private function getHttpStatus( $status_code ) {

		// http://php.net/manual/function.http-response-code.php
		switch ( $status_code ) {
			case 100:
				$text = 'Continue';
				break;
			case 101:
				$text = 'Switching Protocols';
				break;
			case 200:
				$text = 'OK';
				break;
			case 201:
				$text = 'Created';
				break;
			case 202:
				$text = 'Accepted';
				break;
			case 203:
				$text = 'Non-Authoritative Information';
				break;
			case 204:
				$text = 'No Content';
				break;
			case 205:
				$text = 'Reset Content';
				break;
			case 206:
				$text = 'Partial Content';
				break;
			case 300:
				$text = 'Multiple Choices';
				break;
			case 301:
				$text = 'Moved Permanently';
				break;
			case 302:
				$text = 'Moved Temporarily';
				break;
			case 303:
				$text = 'See Other';
				break;
			case 304:
				$text = 'Not Modified';
				break;
			case 305:
				$text = 'Use Proxy';
				break;
			case 400:
				$text = 'Bad Request';
				break;
			case 401:
				$text = 'Unauthorized';
				break;
			case 402:
				$text = 'Payment Required';
				break;
			case 403:
				$text = 'Forbidden';
				break;
			case 404:
				$text = 'Not Found';
				break;
			case 405:
				$text = 'Method Not Allowed';
				break;
			case 406:
				$text = 'Not Acceptable';
				break;
			case 407:
				$text = 'Proxy Authentication Required';
				break;
			case 408:
				$text = 'Request Time-out';
				break;
			case 409:
				$text = 'Conflict';
				break;
			case 410:
				$text = 'Gone';
				break;
			case 411:
				$text = 'Length Required';
				break;
			case 412:
				$text = 'Precondition Failed';
				break;
			case 413:
				$text = 'Request Entity Too Large';
				break;
			case 414:
				$text = 'Request-URI Too Large';
				break;
			case 415:
				$text = 'Unsupported Media Type';
				break;
			case 500:
				$text = 'Internal Server Error';
				break;
			case 501:
				$text = 'Not Implemented';
				break;
			case 502:
				$text = 'Bad Gateway';
				break;
			case 503:
				$text = 'Service Unavailable';
				break;
			case 504:
				$text = 'Gateway Time-out';
				break;
			case 505:
				$text = 'HTTP Version not supported';
				break;
			default:
				exit( 'Unknown http status code "' . htmlentities( $status_code ) . '"' );
			break;
		}

		return $text;
	}


}
