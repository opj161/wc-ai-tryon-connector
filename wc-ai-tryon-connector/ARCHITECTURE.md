# Architecture Diagram

## WooCommerce AI Virtual Try-On Connector - System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          WordPress Installation                              │
│                                                                              │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │                    WooCommerce Plugin (Active)                         │ │
│  │                                                                        │ │
│  │  ┌──────────────────────────────────────────────────────────────────┐ │ │
│  │  │              WC AI Try-On Connector Plugin                       │ │ │
│  │  │                                                                  │ │ │
│  │  │  ┌────────────────────────────────────────────────────────────┐ │ │ │
│  │  │  │         wc-ai-tryon-connector.php                         │ │ │ │
│  │  │  │         (Main Plugin Bootstrap)                           │ │ │ │
│  │  │  │                                                            │ │ │ │
│  │  │  │  • Singleton Pattern Implementation                       │ │ │ │
│  │  │  │  • plugins_loaded Hook                                    │ │ │ │
│  │  │  │  • WooCommerce Dependency Check                           │ │ │ │
│  │  │  │  • Integration Registration                               │ │ │ │
│  │  │  │                                                            │ │ │ │
│  │  │  │  ┌──────────────────────────────────────────────────────┐ │ │ │ │
│  │  │  │  │  includes/class-wc-ai-tryon-settings.php           │ │ │ │ │
│  │  │  │  │  (Settings Integration Class)                       │ │ │ │ │
│  │  │  │  │                                                      │ │ │ │ │
│  │  │  │  │  extends WC_Integration                             │ │ │ │ │
│  │  │  │  │                                                      │ │ │ │ │
│  │  │  │  │  Configuration Fields:                              │ │ │ │ │
│  │  │  │  │  ┌─────────────────────────────────────┐            │ │ │ │ │
│  │  │  │  │  │ 1. n8n Webhook URL (text)          │            │ │ │ │ │
│  │  │  │  │  │ 2. Secret Header Key (password)    │            │ │ │ │ │
│  │  │  │  │  │ 3. Default AI Prompt (textarea)    │            │ │ │ │ │
│  │  │  │  │  └─────────────────────────────────────┘            │ │ │ │ │
│  │  │  │  │                                                      │ │ │ │ │
│  │  │  │  │  Saved to: wp_options table                         │ │ │ │ │
│  │  │  │  └──────────────────────────────────────────────────────┘ │ │ │ │
│  │  │  └────────────────────────────────────────────────────────────┘ │ │ │
│  │  │                                                                  │ │ │
│  │  └──────────────────────────────────────────────────────────────────┘ │ │
│  │                                                                        │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Settings Location in WordPress Admin

```
WordPress Admin Dashboard
└── WooCommerce
    └── Settings
        └── Integration (Tab)
            └── AI Virtual Try-On (Integration)
                ├── n8n Connection (Section)
                │   ├── n8n Webhook URL
                │   └── Secret Header Key
                └── Generation Defaults (Section)
                    └── Default AI Prompt
```

## Data Flow (Phase 1 - Configuration Only)

```
┌─────────────────┐
│   Admin User    │
│  (Store Manager)│
└────────┬────────┘
         │
         │ 1. Navigate to Settings
         ▼
┌─────────────────────────────────┐
│  WooCommerce > Settings >       │
│  Integration > AI Virtual Try-On│
└────────┬────────────────────────┘
         │
         │ 2. Enter Configuration
         ▼
┌────────────────────────────────┐
│  Form Fields:                  │
│  • n8n Webhook URL            │
│  • Secret Key                 │
│  • Default Prompt             │
└────────┬───────────────────────┘
         │
         │ 3. Click Save
         ▼
┌─────────────────────────────────┐
│  WC_Integration->               │
│  process_admin_options()        │
└────────┬────────────────────────┘
         │
         │ 4. Sanitize & Validate
         ▼
┌─────────────────────────────────┐
│  WordPress Options Table        │
│  (wp_options)                   │
│                                 │
│  woocommerce_wc_ai_tryon_       │
│  settings = {                   │
│    "n8n_webhook_url": "...",    │
│    "n8n_secret_key": "...",     │
│    "default_prompt": "..."      │
│  }                              │
└─────────────────────────────────┘
```

## Class Hierarchy

```
WC_Integration (WooCommerce Core)
    ↑
    │ extends
    │
WC_AI_TryOn_Settings
    ↑
    │ registered by
    │
WC_AI_TryOn_Connector (Singleton)
```

## Security Architecture (Phase 1)

```
┌─────────────────────────────────────────────────────────────┐
│                    Security Layers                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Layer 1: Direct Access Prevention                         │
│  ┌───────────────────────────────────────────────────────┐ │
│  │ defined( 'ABSPATH' ) || exit;                         │ │
│  │ • Applied to all PHP files                            │ │
│  │ • Prevents direct file access                         │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  Layer 2: Class Conflict Prevention                        │
│  ┌───────────────────────────────────────────────────────┐ │
│  │ if ( ! class_exists( 'ClassName' ) )                  │ │
│  │ • Prevents duplicate class definitions                │ │
│  │ • Avoids plugin conflicts                             │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  Layer 3: Dependency Validation                            │
│  ┌───────────────────────────────────────────────────────┐ │
│  │ if ( ! class_exists( 'WooCommerce' ) )                │ │
│  │ • Checks for WooCommerce before initialization        │ │
│  │ • Shows admin notice if missing                       │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  Layer 4: Secure Storage                                   │
│  ┌───────────────────────────────────────────────────────┐ │
│  │ Password Field Type for Secret Key                    │ │
│  │ • Masked input in admin                               │ │
│  │ • Stored in WordPress options (encrypted at DB level) │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## WordPress Hooks Used

```
┌──────────────────────────────────────────────────────────────┐
│  Hook Name                         │ Purpose                 │
├────────────────────────────────────┼─────────────────────────┤
│  plugins_loaded                    │ Initialize plugin       │
│  admin_notices                     │ Show error messages     │
│  woocommerce_integrations          │ Register integration    │
│  woocommerce_update_options_       │ Save settings           │
│  integration_{id}                  │                         │
└──────────────────────────────────────────────────────────────┘
```

## File Structure & Responsibilities

```
wc-ai-tryon-connector/
│
├── wc-ai-tryon-connector.php
│   │
│   ├── Responsibilities:
│   │   • Plugin registration & metadata
│   │   • Singleton pattern implementation
│   │   • Lifecycle management
│   │   • Dependency checking
│   │   • Integration registration
│   │
│   └── Dependencies:
│       • WordPress Core (ABSPATH)
│       • WooCommerce Plugin
│
├── includes/
│   └── class-wc-ai-tryon-settings.php
│       │
│       ├── Responsibilities:
│       │   • Settings form definition
│       │   • Form field rendering
│       │   • Settings storage
│       │   • WooCommerce integration
│       │
│       └── Dependencies:
│           • WC_Integration (parent class)
│           • WordPress Options API
│
├── README.md
│   └── User documentation
│
└── IMPLEMENTATION.md
    └── Technical documentation
```

## Future Architecture (Phases 2-5)

```
┌─────────────────────────────────────────────────────────────┐
│                    Complete System (Future)                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  WordPress Admin (Product Editor)                          │
│  └── Meta Box (Phase 2)                                    │
│      ├── Image Grid (Select Source)                        │
│      ├── Prompt Textarea                                   │
│      └── Generate Button                                   │
│          │                                                  │
│          ▼                                                  │
│  JavaScript/AJAX Layer (Phase 3)                           │
│  └── wp.ajax.post()                                        │
│      ├── Nonce Validation                                  │
│      ├── Data Sanitization                                 │
│      └── HTTP Request to n8n                               │
│          │                                                  │
│          ▼                                                  │
│  n8n Workflow (Phase 4)                                    │
│  └── Webhook Node                                          │
│      ├── Validate Secret Header                            │
│      ├── Call fal.ai API                                   │
│      └── Update WooCommerce Product                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

**Phase 1 Status**: ✅ Complete  
**Current Capability**: Configuration storage only  
**Next Phase**: User interface implementation
