<?php
class Options_View {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name     The name of the plugin.
	 * @param    string    $version    		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function run() {
		
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

	}

	function enqueue_scripts() {
		
		//wp_enqueue_script( 'jquery-js', MY_PLUGIN_DIR . '/assets/js/jquery.min.js', array( 'jquery' ), time(), true );
/*
		wp_enqueue_script( 'custom-js', MY_PLUGIN_DIR . '/assets/js/custom-options-view.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'qrcode-js', MY_PLUGIN_DIR . '/assets/js/jquery.qrcode.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'popup-js',  MY_PLUGIN_DIR . '/assets/js/popupwindow.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'chat-js',  MY_PLUGIN_DIR . '/chat/js/chat.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'jquery-js',  MY_PLUGIN_DIR . '/chat/js/jquery.js', array( 'jquery' ), time(), true );
*/
		wp_enqueue_script( 'custom-js', plugins_url( '/assets/js/custom-options-view.js' , __FILE__ ), array(), time(), true );
		wp_enqueue_script( 'qrcode-js', plugins_url( '/assets/js/jquery.qrcode.min.js' , __FILE__ ), array(), time(), true );
		wp_enqueue_script( 'popup-js',  plugins_url( '/assets/js/popupwindow.min.js' , __FILE__ ), array(), time(), true );
		wp_enqueue_script( 'chat-js',  plugins_url( '/chat/js/chat.js' , __FILE__ ), array(), time(), true );
		wp_enqueue_script( 'jquery-js',  plugins_url( '/chat/js/jquery.js' , __FILE__ ), array(), time(), true );

		wp_enqueue_style( 'style-css', MY_PLUGIN_DIR . '/assets/css/custom-options-view.css', '', time() );
		wp_enqueue_style( 'popup-css', MY_PLUGIN_DIR . '/assets/css/popupwindow.min.css', '', time() );
		wp_enqueue_style( 'chat-css', MY_PLUGIN_DIR . '/chat/css/chat.css', '', time() );
		wp_enqueue_style( 'screen-css', MY_PLUGIN_DIR . '/chat/css/screen.css', '', time() );

		// Load the datepicker script (pre-registered in WordPress).
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );  
		
	}
}