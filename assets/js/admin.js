jQuery(document).ready(function($) {
    
    // DOM Elements
    const $container = $('#wc-ai-tryon-container');
    const $imageOptions = $('.wc-ai-image-option');
    const $inputSelectedUrl = $('#wc_ai_selected_image');
    const $btnGenerate = $('#trigger_ai_generation');
    const $statusArea = $('#wc-ai-status-area');
    const $promptInput = $('#wc_ai_prompt');

    /**
     * Logic: Image Selection
     * Toggles 'selected' class and updates the hidden input field.
     */
    $imageOptions.on('click', function(e) {
        e.preventDefault();
        const $this = $(this);
        
        // Visual Toggle
        $imageOptions.removeClass('selected');
        $this.addClass('selected');

        // Update Data
        const imageUrl = $this.data('url');
        $inputSelectedUrl.val(imageUrl);

        // Enable Button
        $btnGenerate.prop('disabled', false);
    });

    /**
     * Logic: Trigger Generation
     * Collects data and fires AJAX request.
     */
    $btnGenerate.on('click', function(e) {
        e.preventDefault();

        // Validation 1: Is an image selected?
        const sourceUrl = $inputSelectedUrl.val();
        if (!sourceUrl) {
            alert(wc_ai_tryon_params.i18n.select_image);
            return;
        }

        // Validation 2: Dirty State Check (Optional but recommended)
        // If the user uploaded an image but didn't save, it won't be in the grid anyway.
        // But if they changed the title/price, we should warn them.
        /* 
        if ( window.onbeforeunload ) {
            if( !confirm('You have unsaved changes on this product. It is recommended to Update the product before generating images. Continue anyway?') ) {
                return;
            }
        } 
        */

        // UI Loading State
        $btnGenerate.addClass('disabled').prop('disabled', true);
        $statusArea
            .removeClass('success error')
            .addClass('loading')
            .html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span>' + wc_ai_tryon_params.i18n.generating)
            .show();

        // AJAX Payload
        const data = {
            action: 'wc_trigger_ai_tryon', // Must match PHP wp_ajax_ hook
            security: wc_ai_tryon_params.nonce,
            product_id: wc_ai_tryon_params.post_id,
            image_url: sourceUrl,
            prompt: $promptInput.val()
        };

        // Send Request
        $.post(wc_ai_tryon_params.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    $statusArea
                        .removeClass('loading')
                        .addClass('success')
                        .html('<strong>' + wc_ai_tryon_params.i18n.success + '</strong>');
                    
                    // Note: The button remains disabled to prevent double-submission
                } else {
                    handleError(response.data || wc_ai_tryon_params.i18n.error);
                }
            })
            .fail(function() {
                handleError('Server connection failed.');
            });
    });

    /**
     * Helper: Error Handler
     */
    function handleError(message) {
        $btnGenerate.removeClass('disabled').prop('disabled', false);
        $statusArea
            .removeClass('loading')
            .addClass('error')
            .html(message);
    }
});
