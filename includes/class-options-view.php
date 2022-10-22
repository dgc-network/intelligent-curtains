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
		
		//wp_enqueue_script( 'jquery-js', MY_PLUGIN_DIR . 'assets/js/jquery.min.js', array( 'jquery' ), time(), true );

		wp_enqueue_script( 'custom-js', plugin_dir_url( __DIR__ ) . 'assets/js/custom-options-view.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'qrcode-js', plugin_dir_url( __DIR__ ) . 'assets/js/jquery.qrcode.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'popup-js',  plugin_dir_url( __DIR__ ) . 'assets/js/popupwindow.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'chat-js',  plugin_dir_url( __DIR__ ) . 'chat/js/chat.js', array( 'jquery' ), time(), true );
		//wp_enqueue_script( 'jquery-js',  MY_PLUGIN_DIR . 'chat/js/jquery.js', array( 'jquery' ), time(), true );

		wp_enqueue_style( 'style-css', plugin_dir_url( __DIR__ ) . 'assets/css/custom-options-view.css', '', time() );
		wp_enqueue_style( 'popup-css', plugin_dir_url( __DIR__ ) . 'assets/css/popupwindow.min.css', '', time() );
		wp_enqueue_style( 'chat-css', plugin_dir_url( __DIR__ ) . 'chat/css/chat.css', '', time() );
		wp_enqueue_style( 'screen-css', plugin_dir_url( __DIR__ ) . 'chat/css/screen.css', '', time() );

		// Load the datepicker script (pre-registered in WordPress).
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );  
		wp_register_style( 'demos-style', 'https://jqueryui.com/resources/demos/style.css' );
		wp_enqueue_style( 'demos-style' );  

	}
}