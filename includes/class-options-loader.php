<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    Trip_Options
 * @subpackage Trip_Options/includes
 */


if ( ! class_exists( 'Options_Loader' ) ) {
	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      1.0.0
	 * @package    Trip_Options
	 * @subpackage Trip_Options/includes
	 * @author     dgc.network
	 */
	class Options_Loader {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Options_Loader $loader Maintains and registers all hooks for the plugin.
		 */
		//protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $version The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->plugin_name = 'electric-curtains';
			$this->version     = '1.0.0';

			$this->load_dependencies();
			//$this->set_locale();
			$this->run_options_admin();
			$this->run_options_view();
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			//$this->loader->run();
			add_filter( 'product_type_options', array( __CLASS__, 'add_remove_product_options' ) );
			add_action( 'admin_init', array( __CLASS__, 'create_product_category' ), 10, 1 );
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			//require_once 'helpers.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			//require_once 'class-options-i18n.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-raise-prices-with-time-for-woocommmerce-admin.php';
			require_once 'class-options-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-raise-prices-with-time-for-woocommmerce-public.php';
			require_once 'class-options-view.php';

			//$this->loader = new Trip_Options_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Raise_Prices_With_Time_For_Woocommmerce_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = new Options_i18n();
			//$plugin_i18n->run();

			//$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function run_options_admin() {

			$plugin_admin = new Options_Admin( $this->get_plugin_name(), $this->get_version() );
			$plugin_admin->run();

		}


		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function run_options_view() {

			$plugin_public = new Options_View( $this->get_plugin_name(), $this->get_version() );
			$plugin_public->run();

		}

		/**
		 * Remove 'Virtual','Downloadable' product options
		 * Add 'Itinerary' product options
		 */
		function add_remove_product_options( $options ) {

			// remove "Virtual" checkbox
			if( isset( $options[ 'virtual' ] ) ) {
				unset( $options[ 'virtual' ] );
			}
	 
			// remove "Downloadable" checkbox
			if( isset( $options[ 'downloadable' ] ) ) {
				unset( $options[ 'downloadable' ] );
			}
	
			$options['trip_options'] = array(
				'id'            => '_trip_options',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'Trip Options', 'text-domain' ),
				'description'   => __( 'Itinerary allow users to put in personalised messages.', 'text-domain' ),
				'default'       => 'no'
			);
	
			return $options;
		}
	
		/**
		 * Create Product Categories
		 */
		function create_product_category() {

			// Create Categories
			wp_insert_term(
				__( "Itinerary", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Itinerary", "wp-travel" ),
					  'slug' => 'itinerary'
				)
			  );
	
			wp_insert_term(
				__( "Stay", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Stay", "wp-travel" ),
					  'slug' => 'stay'
				)
			  );
	
			wp_insert_term(
				__( "Dinner", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Dinner", "wp-travel" ),
					  'slug' => 'dinner'
				)
			  );
	
			wp_insert_term(
				__( "Lunch", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Lunch", "wp-travel" ),
					  'slug' => 'lunch'
				)
			  );
	
			  wp_insert_term(
				__( "Breakfast", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Breakfast", "wp-travel" ),
					  'slug' => 'breakfast'
				)
			  );
	
			  wp_insert_term(
				__( "Sightsee", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Sightsee", "wp-travel" ),
					  'slug' => 'sightsee'
				)
			  );
	
			  wp_insert_term(
				__( "Gift Shop", "wp-travel" ), // the term 
				'product_cat', // the taxonomy
				array(
					  'description'=> __( "Category of Gift Shop", "wp-travel" ),
					  'slug' => 'giftshop'
				)
			  );
	
		}
	
		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

	}
}