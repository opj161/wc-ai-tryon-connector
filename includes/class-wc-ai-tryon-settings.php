<?php
/**
 * Integration Settings Class
 * 
 * Defines the configuration fields required to connect to n8n.
 * Located at: WooCommerce > Settings > Integration > AI Try-On
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_AI_TryOn_Settings' ) ) :

	class WC_AI_TryOn_Settings extends WC_Integration {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id                 = 'wc_ai_tryon'; // Settings are saved as 'woocommerce_wc_ai_tryon_settings'
			$this->method_title       = __( 'AI Virtual Try-On', 'wc-ai-tryon-connector' );
			$this->method_description = __( 'Configure the connection to your n8n workflow for AI image generation.', 'wc-ai-tryon-connector' );

			// Initialize form fields and settings
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables for easy access later
			$this->n8n_webhook_url = $this->get_option( 'n8n_webhook_url' );
			$this->n8n_secret_key  = $this->get_option( 'n8n_secret_key' );
			$this->default_prompt  = $this->get_option( 'default_prompt' );

			// Save settings hook (Magically handles saving via WC Settings API)
			add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Initialize settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'section_connection' => array(
					'title' => __( 'n8n Connection', 'wc-ai-tryon-connector' ),
					'type'  => 'title',
					'description' => __( 'Enter the details from your n8n Webhook node.', 'wc-ai-tryon-connector' ),
				),
				'n8n_webhook_url' => array(
					'title'       => __( 'n8n Webhook URL', 'wc-ai-tryon-connector' ),
					'type'        => 'text',
					'description' => __( 'The Production URL of your n8n Webhook (POST method).', 'wc-ai-tryon-connector' ),
					'desc_tip'    => true,
					'default'     => '',
					'placeholder' => 'https://n8n.yourdomain.com/webhook/...',
				),
				'n8n_secret_key' => array(
					'title'       => __( 'Secret Header Key', 'wc-ai-tryon' ),
					'type'        => 'password',
					'description' => __( 'A secret string sent in the "X-AI-Auth" header to secure your webhook.', 'wc-ai-tryon' ),
					'desc_tip'    => true,
					'default'     => '',
				),
				'section_defaults' => array(
					'title' => __( 'Generation Defaults', 'wc-ai-tryon' ),
					'type'  => 'title',
				),
				'default_prompt' => array(
					'title'       => __( 'Default AI Prompt', 'wc-ai-tryon' ),
					'type'        => 'textarea',
					'description' => __( 'This prompt will be pre-filled in the Product Editor. You can change it per product.', 'wc-ai-tryon' ),
					'default'     => 'Create a photorealistic image of a female fashion model wearing this clothing item. Style: Authentic, natural expression. Setting: Modern studio.',
					'css'         => 'min-height: 100px;',
				),
			);
		}
	}

endif;
