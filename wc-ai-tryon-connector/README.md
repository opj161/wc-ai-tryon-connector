# WooCommerce AI Virtual Try-On Connector

A WordPress plugin that connects the WooCommerce Product Editor to n8n automation workflows for AI-powered virtual try-on image generation.

## Overview

This plugin bridges the gap between WordPress (synchronous UI) and n8n automation workflows (asynchronous AI processing). Instead of the n8n workflow arbitrarily selecting product images, this plugin inverts control - allowing store managers to curate which specific image to use and customize the AI prompt directly from the Product Editor.

## Architecture

The system operates on a **"Push & Poll"** architecture to handle AI generation latency (10-60 seconds) without timing out the WordPress Admin interface:

1. **Administrator** selects a source image and customizes the prompt in the Product Editor
2. **WordPress Backend** validates and sends data via AJAX to n8n webhook
3. **n8n Workflow** processes the request asynchronously and updates the product when complete

## Phase 1: Installation & Configuration

### Requirements

- WordPress 5.8 or higher
- WooCommerce 8.0 or higher
- Active n8n workflow with webhook endpoint
- PHP 7.4 or higher

### Installation

1. Copy the `wc-ai-tryon-connector` directory to your WordPress plugins folder:
   ```
   wp-content/plugins/wc-ai-tryon-connector/
   ```

2. Activate the plugin in WordPress Admin:
   - Navigate to **Plugins > Installed Plugins**
   - Find "WooCommerce AI Virtual Try-On Connector"
   - Click **Activate**

### Configuration

1. Navigate to **WooCommerce > Settings > Integration**

2. Select the **AI Virtual Try-On** tab

3. Configure the following settings:

   **n8n Connection:**
   - **n8n Webhook URL**: Enter your n8n webhook production URL (POST method)
     - Example: `https://n8n.yourdomain.com/webhook/ai-tryon`
   
   - **Secret Header Key**: Enter a secure secret string
     - This will be sent in the `X-AI-Auth` header to authenticate requests
     - Generate a strong random string (recommended: 32+ characters)

   **Generation Defaults:**
   - **Default AI Prompt**: Customize the default prompt template
     - This will be pre-filled in the Product Editor
     - Can be overridden per-product
     - Default: "Create a photorealistic image of a female fashion model wearing this clothing item. Style: Authentic, natural expression. Setting: Modern studio."

4. Click **Save changes**

## Security Features

- **CSRF Protection**: Implements WordPress nonces for all AJAX requests
- **Capability Checks**: Only users with `edit_products` permission can trigger AI generation
- **Header Authentication**: Webhook requests include custom secret header
- **Data Sanitization**: All user inputs are sanitized before transmission

## File Structure

```
wc-ai-tryon-connector/
├── wc-ai-tryon-connector.php          # Main plugin file (Bootstrap)
├── includes/
│   └── class-wc-ai-tryon-settings.php # Settings Integration Class
└── README.md                           # This file
```

## Plugin Architecture

### Main Plugin Class (`WC_AI_TryOn_Connector`)

- Implements Singleton pattern to prevent duplicate instances
- Checks for WooCommerce dependency on initialization
- Registers the settings integration with WooCommerce
- Hooks into `plugins_loaded` action for proper initialization

### Settings Class (`WC_AI_TryOn_Settings`)

- Extends `WC_Integration` (WooCommerce's native settings API)
- Defines configuration form fields
- Handles automatic saving and sanitization through WooCommerce
- Stores settings in WordPress options table

## Development Roadmap

### ✅ Phase 1: Scaffold & Configuration (Current)
- Plugin directory structure
- Main bootstrap file with dependency checks
- Settings page integration
- n8n connection configuration

### 🔄 Phase 2: Interface (Meta Box) - Coming Next
- Product image grid display
- Image selection interface
- Custom prompt input field
- UI feedback area

### 🔄 Phase 3: The Engine (JS & AJAX)
- JavaScript event handlers
- AJAX communication layer
- Security nonce implementation
- Status feedback system

### 🔄 Phase 4: Workflow Refactor
- n8n webhook configuration
- fal.ai integration
- WooCommerce API product update

### 🔄 Phase 5: Testing & Validation
- Variable product support
- Dirty state detection
- Edge case handling

## Best Practices Implemented

- **WordPress Coding Standards**: Follows WordPress PHP coding standards
- **WooCommerce Integration**: Uses native `WC_Integration` API
- **Security First**: Multiple layers of security validation
- **Translatable**: Uses WordPress i18n functions for all strings
- **Singleton Pattern**: Prevents multiple class instances
- **Dependency Checking**: Graceful failure if WooCommerce is not active

## License

GPLv3

## Author

Marco Di Renzo
- Website: https://dev.marcodirenzo.ch/

## Support

For issues, feature requests, or contributions, please visit the GitHub repository.

## Changelog

### 1.0.0 (2025-11-18)
- Initial release
- Phase 1: Scaffolding and configuration layer
- Settings page for n8n webhook connection
- Security framework implementation
