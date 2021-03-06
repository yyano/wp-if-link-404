<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://plus.google.com/u/0/+YANOYasuhiro/
 * @since      1.0.0
 *
 * @package    Iflink404
 * @subpackage Iflink404/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Iflink404
 * @subpackage Iflink404/admin
 * @author     yyano <yano.yasuhiro@gmail.com>
 */
class Iflink404_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->options = get_option( 'iflink404-options' );

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/iflink404-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/iflink404-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 *
	 */
	public function setMetaBox() {

		// get public post-type
		$args = array(
			'public' => true,
		);
		$post_types = get_post_types( $args, 'names' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'iflink404_URL',
				__( 'if link 404', 'iflink404' ),
				array( $this, 'setCustomfields' ),
				$post_type
			);
		}
	}

	/**
	 * Set custom fields in post.
	 *
	 * @since    1.0.0
	 */
	public function setCustomfields( $post ) {

		printf('<p class="iflink404">'.__('if link is broken, then set stauts to "%s".').'</p>', 
				esc_attr( $this->options[$post->post_type] ) );

		wp_nonce_field( 'iflink404_meta_box', 'iflink404_meta_box_nonce' );

		$url = get_post_meta( $post->ID, '_iflink404_check_url', true );
		$date = get_post_meta( $post->ID, '_iflink404_check_date', true );
		$error_message = get_post_meta( $post->ID, '_iflink404_check_error_message', true );

		$date = '' === $date ? 0 : $date;

		if ( '' != $error_message ) {
			printf( '<div class="iflink404-errormessage">%s</div>', esc_attr( $error_message ) );
			// delete_post_meta( $post->ID, "_iflink404_check_error_message" );
		}

		echo '<table>';
		echo '<thead></thead>';
		echo '<tbody>';

		// URL
		echo '<tr><td><label for="iflink404_check_url">' . __( 'Check URL', 'iflink404' ) . '</label></td>';
		echo '<td><input type="text" id="iflink404_check_url" name="iflink404_check_url" value="' . esc_attr( $url ) . '" size="80" />' .
				' <input type="submit" name="addmeta" id="iflink404_check_url-submit" class="button" value="Set"></td></tr>';

		// Check Date
		echo '<tr><td><label for="iflink404_check_date">' . __( 'Checked at', 'iflink404' ) . '</label></td>';
		echo '<td>' . date_i18n( get_option( 'date_format' ), $date ) . ' ' . date_i18n( 'H:i:s', $date ) . '</td></tr>';
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 *
	 */
	public function saveCustomFileds( $post_id ) {

		// verify nonce
		if ( ! isset( $_POST['iflink404_meta_box_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['iflink404_meta_box_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'iflink404_meta_box' ) ) {
			return $post_id;
		}

		// Check Autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// check input url
		$new_meta_url = sanitize_text_field( $_POST['iflink404_check_url'] );

		if ( isset( $_POST['iflink404_check_url'] ) && ! empty( $_POST['iflink404_check_url'] ) ) {
			if ( false === filter_var( $new_meta_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED ) ) {
				delete_post_meta( $post_id, '_iflink404_check_error_message' );
				add_post_meta( $post_id, '_iflink404_check_error_message', __( 'Invalid URL.', 'iflink404' ), true );
				return $post_id;
			}
		}

		// update fields
		$meta_value = get_post_meta( $post_id, '_iflink404_check_url', true );

		if ( $new_meta_url && '' == $meta_value ) {
			add_post_meta( $post_id, '_iflink404_check_url', $new_meta_url, true );

			delete_post_meta( $post_id, '_iflink404_check_date' );
			add_post_meta( $post_id, '_iflink404_check_date', 0, true );

			delete_post_meta( $post_id, '_iflink404_check_error_message' );

		} elseif ( $new_meta_url && $new_meta_url != $meta_value ) {
			update_post_meta( $post_id, '_iflink404_check_url', $new_meta_url );

			delete_post_meta( $post_id, '_iflink404_check_date' );
			add_post_meta( $post_id, '_iflink404_check_date', 0, true );

			delete_post_meta( $post_id, '_iflink404_check_error_message' );

		} elseif ( '' == $new_meta_url && $meta_value ) {
			delete_post_meta( $post_id, '_iflink404_check_url' );

			delete_post_meta( $post_id, '_iflink404_check_date' );
			add_post_meta( $post_id, '_iflink404_check_date', 0, true );

			delete_post_meta( $post_id, '_iflink404_check_error_message' );
		}
	}
	
	public function add_dashboard_widgets() {

		wp_add_dashboard_widget(
	                 'iflink404_dashboard_widget',
	                 'if link 404',         // Title.
	                 array( $this, 'dashboard_widget_function')
	        );		
	}
	
	public function dashboard_widget_function() {
		$args = array(
			'post_type' => 'any',
			'post_status' => 'pending',
			'meta_query' => array(
				'relation' => 'AND',
				'date_clause' => array(
					'key' => '_iflink404_check_date',
					'compare' => 'EXISTS',
				),
				'message_clause' => array(
					'key' => '_iflink404_check_error_message',
					'compare' => 'EXISTS',
				),
			),
			'orderby' => array(
				'date_clause' => 'ASC',
			),
			'posts_per_page' => 3,
		);

		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			printf( "<h3>Error founds : %d</h3>\n", $the_query->found_posts );

			echo '<ul class="iflink404-dashboard">';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$url = get_post_meta( get_the_ID(), '_iflink404_check_url', true );
				$date = get_post_meta( get_the_ID(), '_iflink404_check_date', true );
				$message = get_post_meta( get_the_ID(), '_iflink404_check_error_message', true );

				echo '<li class="iflink404-dashboard">';
				printf( '<h4>%s : <a href="%s">%s</a></h4>' . "\n",
					get_post_type(),
					get_edit_post_link(), get_the_title() );
				printf( '<div class="iflink404-dashboard-message">%s : %s</div>', 
					__('url', 'iflink404'), $url );
				printf( '<div class="iflink404-dashboard-message">%s : %s</div>', 
					__('check date', 'iflink404'),
					date_i18n( get_option( 'date_format' ), $date ) . ' ' . date_i18n( 'H:i:s', $date ) );
				printf( '<div class="iflink404-dashboard-message">%s : %s</div>', 
					__('error', 'iflink404'),
					$message );
				echo '</li>'."\n";
			}
			echo "</ul>";
			wp_reset_postdata();
		}
		
	}
	
	/**
	 * Register the Admin menu.
	 *
	 * @since    1.0.1
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'if link 404','iflink404' ),
			__( 'if link 404','iflink404' ), 
			'manage_options', 
			'iflink404', 
			array( $this, 'create_admin_page' ) );
	}
	
	/**
     * Options page callback
	 *
	 * @since    1.0.1
	 */
    public function create_admin_page()
    {
        // Set class property
		print('<div class="wrap">'."\n");
		print('<form method="post" action="options.php">'."\n");

	    settings_fields( 'iflink404-settings-group' );   
	    do_settings_sections( 'iflink404-settings-page' );
	    submit_button(); 

		print('</form>'."\n");
		print('</div>'."\n");
    }

	/**
     * Set admin page
	 *
	 * @since    1.0.1
	 */
	public function set_admin_page() {

        register_setting(
            'iflink404-settings-group',
            'iflink404-options',
            array( $this, 'sanitize_input_value' )
        );

        add_settings_section(
            'iflink404_then_set',
            __('Setting by post type', 'iflink404'),
            array( $this, 'print_section_setting' ),
            'iflink404-settings-page'
        );  		

		// get post types
		$args_types = array(
			'public' => true,
		);
		$post_types = get_post_types( $args_types, 'object' );

		foreach( $post_types as $post_type ) {
	        add_settings_field(
	            sprintf('iflink404-post_type-%s', $post_type->name ),
	            $post_type->labels->name,
	            array( $this, 'print_post_type' ),
	            'iflink404-settings-page',
	            'iflink404_then_set',
	            array( 'name' => $post_type->name )
	        );      
		}
	}

	/**
     * Sanitize input value
	 *
	 * @since    1.0.1
	 */
    public function sanitize_input_value( $input )
    {
        return $input;
    }

	/**
     * Print setting section
	 *
	 * @since    1.0.1
	 */
	public function print_section_setting() {
		print 'Enter your settings below:';
	}

	/**
     * Print post type input row
	 *
	 * @since    1.0.1
	 */
	public function print_post_type( $args ) {

		$post_type_name = $args['name'];
		$option_value = esc_attr( $this->options[$post_type_name] );
		$option_value = "" === $option_value ? "private" : $option_value;

		// get post statuses
		$args_statuses = array(
				'public' => false,
				'internal'=> false
			);
		$post_statuses = get_post_stati( $args_statuses, 'objects' );
		unset( $post_statuses['future'] );

        printf( '<select name="iflink404-options[%s]">'."\n", $post_type_name );
		foreach( $post_statuses as $post_status_key => $post_status_value ) {

			printf('<option value="%s" %s>%s</option>'."\n", 
				$post_status_key,  
				$post_status_key == $option_value ? 'selected' : "",
				$post_status_value->label );
		}
        print '</select>'."\n";
	}
}
