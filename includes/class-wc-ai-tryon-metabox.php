<?php
/**
 * Meta Box Display Class
 * 
 * Renders the UI inside the Product Edit screen.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_AI_TryOn_MetaBox' ) ) :

	class WC_AI_TryOn_MetaBox {

		public function __construct() {
			// Add Meta Box
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			// Enqueue Assets
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Register the meta box.
		 */
		public function add_meta_box() {
			add_meta_box(
				'wc_ai_tryon_box',
				__( 'AI Virtual Try-On', 'wc-ai-tryon-connector' ),
				array( $this, 'render_content' ),
				'product',
				'side',
				'high'
			);
		}

		/**
		 * Enqueue CSS and JS only on product edit pages.
		 */
		public function enqueue_assets( $hook ) {
			global $post;

			if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
				return;
			}

			if ( 'product' !== get_post_type( $post ) ) {
				return;
			}

			// CSS
			wp_enqueue_style( 
				'wc-ai-tryon-connector-css', 
				plugins_url( '../assets/css/admin.css', __FILE__ ), 
				array(), 
				'1.0.0' 
			);

			// JS
			wp_enqueue_script( 
				'wc-ai-tryon-connector-js', 
				plugins_url( '../assets/js/admin.js', __FILE__ ), 
				array( 'jquery' ), 
				'1.0.0', 
				true 
			);

			// Localize Script (Security & Data passing for Phase 3)
			wp_localize_script( 'wc-ai-tryon-connector-js', 'wc_ai_tryon_params', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wc_ai_tryon_nonce' ), // Security Token
				'post_id'  => $post->ID,
				'i18n'     => array(
					'select_image' => __( 'Please select an image first.', 'wc-ai-tryon-connector' ),
					'generating'   => __( 'Sending to AI... This may take 30 seconds.', 'wc-ai-tryon-connector' ),
					'success'      => __( 'Generation started! The image will appear in the gallery shortly.', 'wc-ai-tryon-connector' ),
					'error'        => __( 'Error starting workflow.', 'wc-ai-tryon-connector' ),
				)
			));
		}

		/**
		 * Render the HTML content.
		 */
		public function render_content( $post ) {
			$product = wc_get_product( $post->ID );
			
			if ( ! $product ) {
				echo '<p>' . esc_html__( 'Product data not available.', 'wc-ai-tryon-connector' ) . '</p>';
				return;
			}

			// 1. Get Images
			$image_ids = array();
			
			// Featured Image
			if ( $product->get_image_id() ) {
				$image_ids[] = $product->get_image_id();
			}

			// Gallery Images
			$gallery_ids = $product->get_gallery_image_ids();
			if ( ! empty( $gallery_ids ) ) {
				$image_ids = array_merge( $image_ids, $gallery_ids );
			}

			// Remove duplicates
			$image_ids = array_unique( $image_ids );

			// 2. Get Default Prompt from Settings (Phase 1)
			$settings = get_option( 'woocommerce_wc_ai_tryon_settings', array() );
			$default_prompt = isset( $settings['default_prompt'] ) ? $settings['default_prompt'] : '';

			?>
			<div id="wc-ai-tryon-connector-container">
				
				<!-- Image Selection Grid -->
				<p class="howto"><?php esc_html_e( '1. Select source image:', 'wc-ai-tryon-connector' ); ?></p>
				
				<?php if ( empty( $image_ids ) ) : ?>
					<div class="notice notice-warning inline">
						<p><?php esc_html_e( 'No images found. Please upload product images and click Update.', 'wc-ai-tryon-connector' ); ?></p>
					</div>
				<?php else : ?>
					<div class="wc-ai-image-grid">
						<?php foreach ( $image_ids as $img_id ) : 
							$img_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
							$full_url = wp_get_attachment_image_url( $img_id, 'full' );
							?>
							<button type="button" class="wc-ai-image-option" data-url="<?php echo esc_url( $full_url ); ?>">
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php esc_attr_e( 'Product Image', 'wc-ai-tryon-connector' ); ?>">
							</button>
						<?php endforeach; ?>
					</div>
					<!-- Hidden input to store selection for JS -->
					<input type="hidden" id="wc_ai_selected_image" value="">
				<?php endif; ?>

				<!-- Prompt Input -->
				<div class="wc-ai-prompt-wrapper">
					<label for="wc_ai_prompt"><?php esc_html_e( '2. Customize Prompt:', 'wc-ai-tryon-connector' ); ?></label>
					<textarea id="wc_ai_prompt" class="widefat" rows="3"><?php echo esc_textarea( $default_prompt ); ?></textarea>
				</div>

				<!-- Action Button -->
				<button type="button" id="trigger_ai_generation" class="button button-primary button-large" <?php disabled( empty( $image_ids ), true ); ?>>
					<?php esc_html_e( 'Generate AI Model', 'wc-ai-tryon-connector' ); ?>
				</button>

				<!-- Feedback Area -->
				<div id="wc-ai-status-area"></div>

			</div>
			<?php
		}
	}

endif;
