<?php
/**
 * Plugin Name: WooCommerce AI Virtual Try-On Connector
 * Plugin URI: https://dev.marcodirenzo.ch/
 * Description: Connects the Product Editor to n8n automation workflows for AI image generation.
 * Version: 1.0.0
 * Author: Marco Di Renzo
 * Author URI: https://dev.marcodirenzo.ch/
 * Text Domain: wc-ai-tryon-connector
 * Domain Path: /languages
 *
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 *
 * License: GPLv3
 */

// Prevent direct access to this file (Security Best Practice)
defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin Class
 * Implements a Singleton pattern to ensure the class is initialized only once.
 */
if ( ! class_exists( 'WC_AI_TryOn_Connector' ) ) {

	class WC_AI_TryOn_Connector {

		/**
		 * Static property to hold the single instance of the class.
		 */
		protected static $_instance = null;

		/**
		 * Get the instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor.
		 * Hooks into WordPress lifecycle.
		 */
		public function __construct() {
			// Initialize the plugin only after all other plugins are loaded
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Initialize the plugin.
		 * Checks if WooCommerce is active before running logic.
		 */
		public function init() {
			// Check if WooCommerce class exists (Dependency Check)
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', function() {
					echo '<div class="error"><p><strong>AI Try-On Connector</strong> requires WooCommerce to be installed and active.</p></div>';
				});
				return;
			}

			// FIXED: Removed "-connector" from filenames to match actual file structure
			require_once dirname( __FILE__ ) . '/includes/class-wc-ai-tryon-settings.php';
			require_once dirname( __FILE__ ) . '/includes/class-wc-ai-tryon-ajax.php';
            
            // Initialize listener
            new WC_AI_TryOn_AJAX(); 
            
            if ( is_admin() ) {
                // FIXED: Removed "-connector" from filename
                require_once dirname( __FILE__ ) . '/includes/class-wc-ai-tryon-metabox.php';
                new WC_AI_TryOn_MetaBox();
            }

			// Register the integration with WooCommerce
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
		}

		/**
		 * Add our Integration Class to the WooCommerce Integrations list.
		 * 
		 * @param array $integrations Current list of integrations.
		 * @return array Modified list.
		 */
		public function add_integration( $integrations ) {
			$integrations[] = 'WC_AI_TryOn_Settings';
			return $integrations;
		}
	}

	// Kick off the plugin
	WC_AI_TryOn_Connector::instance();
}
