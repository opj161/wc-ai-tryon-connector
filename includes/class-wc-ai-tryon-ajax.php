<?php
/**
 * AJAX Handler Class
 * 
 * Receives the request from the Product Edit page, validates security,
 * and forwards the payload to n8n via HTTP POST.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_AI_TryOn_AJAX' ) ) :

	class WC_AI_TryOn_AJAX {

		public function __construct() {
			// Hook into wp_ajax_{action}
			// We only register for logged-in users (priv), not nopriv.
			add_action( 'wp_ajax_wc_trigger_ai_tryon', array( $this, 'handle_request' ) );
		}

		/**
		 * Handle the AJAX request.
		 */
		public function handle_request() {
			// 1. Security Check: Verifies the nonce sent from JS
			check_ajax_referer( 'wc_ai_tryon_nonce', 'security' );

			// 2. Capability Check: Ensure user can edit products
			if ( ! current_user_can( 'edit_products' ) ) {
				wp_send_json_error( __( 'Unauthorized access.', 'wc-ai-tryon-connector' ) );
			}

			// 3. Input Sanitization
			$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
			$image_url  = isset( $_POST['image_url'] )  ? esc_url_raw( $_POST['image_url'] ) : '';
			$prompt     = isset( $_POST['prompt'] )     ? sanitize_textarea_field( $_POST['prompt'] ) : '';

			if ( ! $product_id || empty( $image_url ) ) {
				wp_send_json_error( __( 'Missing required data (Product ID or Image).', 'wc-ai-tryon-connector' ) );
			}

			// 4. Retrieve Settings (Webhook URL & Secret)
			// WooCommerce saves integration settings in options table as: woocommerce_{id}_settings
			$settings = get_option( 'woocommerce_wc_ai_tryon_settings', array() );
			
			$n8n_url    = isset( $settings['n8n_webhook_url'] ) ? $settings['n8n_webhook_url'] : '';
			$n8n_secret = isset( $settings['n8n_secret_key'] )  ? $settings['n8n_secret_key'] : '';

			if ( empty( $n8n_url ) ) {
				wp_send_json_error( __( 'n8n Webhook URL is not configured in Settings.', 'wc-ai-tryon-connector' ) );
			}

			// 5. Construct Payload for n8n
			$body = array(
				'product_id'       => $product_id,
				'source_image_url' => $image_url,
				'prompt_override'  => $prompt,
				'user_id'          => get_current_user_id(),
				'timestamp'        => time(),
			);

			// 6. Send Request to n8n (Fire and Forget approach)
			$response = wp_remote_post( $n8n_url, array(
				'method'    => 'POST',
				'headers'   => array(
					'Content-Type' => 'application/json',
					'X-AI-Auth'    => $n8n_secret, // Custom Header for Security
				),
				'body'      => json_encode( $body ),
				'timeout'   => 10, // Short timeout, we don't want to wait for AI generation here
				'blocking'  => true, // We wait for n8n to say "Received" (200 OK)
			) );

			// 7. Handle Response
			if ( is_wp_error( $response ) ) {
				wp_send_json_error( __( 'Failed to connect to n8n: ', 'wc-ai-tryon-connector' ) . $response->get_error_message() );
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code >= 200 && $response_code < 300 ) {
				wp_send_json_success( array( 'message' => __( 'Workflow triggered successfully.', 'wc-ai-tryon' ) ) );
			} else {
				wp_send_json_error( __( 'n8n returned an error. Code: ', 'wc-ai-tryon' ) . $response_code );
			}
		}
	}

endif;
