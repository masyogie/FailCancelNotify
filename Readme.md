# FailCancelNotify

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![WooCommerce](https://img.shields.io/badge/WooCommerce->=3.0-blue.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP->=7.4-purple.svg)](https://php.net/)

A WooCommerce plugin/snippet for sending custom email notifications to customers for cancelled or failed orders.

## Overview

FailCancelNotify is a WordPress/WooCommerce plugin that enhances the order management experience by directly notifying customers when their orders are either cancelled or failed. This approach bypasses the default admin-focused notifications, providing a more direct and informative communication with customers.

## Features

- ✅ Sends email notifications to customers if their order is cancelled or fails
- ✅ Bypasses the default WooCommerce admin notification system
- ✅ Easy integration into existing WooCommerce setups
- ✅ Built-in logging for debugging (uses WooCommerce logger)
- ✅ Developer-friendly hooks and filters
- ✅ WooCommerce version compatibility check
- ✅ Email validation before sending
- ✅ Error handling with try-catch

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | >= 7.4 |
| WordPress | >= 5.0 |
| WooCommerce | >= 3.0 |

## Installation

### Method 1: Code Snippets Plugin (Recommended)

1. Install the [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin in your WordPress site. This plugin allows you to add custom PHP code snippets without editing your theme's `functions.php` file.
2. Once the plugin is installed and activated, go to "Snippets" in your WordPress dashboard.
3. Click on "Add New" to create a new snippet.
4. Copy and paste the PHP code from `FailCancelNotify.php` into the code field of the snippet.
5. Name your snippet (for example, "FailCancelNotify for WooCommerce Orders") for easy identification.
6. Save and activate the snippet to apply the functionality.

### Method 2: functions.php

1. Open your theme's `functions.php` file.
2. Copy the contents of `FailCancelNotify.php` (excluding the plugin header comments if desired).
3. Paste at the end of your `functions.php` file.
4. Save the file.

### Method 3: As a Plugin

1. Create a new folder in `/wp-content/plugins/` called `fail-cancel-notify`
2. Copy `FailCancelNotify.php` into that folder
3. Activate the plugin from WordPress admin > Plugins

## Usage

Once the plugin/snippet is activated, it automatically hooks into WooCommerce's email system. No further configuration is needed. Customers will receive email notifications for any orders marked as 'cancelled' or 'failed'.

## Developer Hooks

### Filters

#### `fcn_should_send_notification`

Control whether a notification should be sent.

```php
// Disable notification for specific order
add_filter('fcn_should_send_notification', function($should_send, $order_id, $order, $email_key) {
    // Don't send if order total is less than 10
    if ($order->get_total() < 10) {
        return false;
    }
    return $should_send;
}, 10, 4);
```

### Actions

#### `fcn_before_send_email`

Execute code before email is sent.

```php
add_action('fcn_before_send_email', function($email, $order_id, $order) {
    // Custom logic before sending
    error_log("About to send email for order #{$order_id}");
}, 10, 3);
```

#### `fcn_after_send_email`

Execute code after email is sent successfully.

```php
add_action('fcn_after_send_email', function($order_id, $order, $customer_email) {
    // Log to external service
    // Or send SMS notification
}, 10, 3);
```

#### `fcn_rate_limit_period`

Customize the rate limit cooldown period.

```php
// Set rate limit to 5 minutes (300 seconds)
add_filter('fcn_rate_limit_period', function($seconds) {
    return 300;
});
```

## Debugging

FailCancelNotify uses the WooCommerce logging system. You can view logs at:

**WooCommerce > Status > Logs**

Select the `fail-cancel-notify` log file to view all plugin activity including:
- Email sent successfully
- Invalid email addresses
- WooCommerce compatibility issues
- Email class not found errors

## Security Features

1. **Email Validation** - Uses WordPress `is_email()` function to validate email format
2. **Email Sanitization** - Uses `sanitize_email()` to clean email addresses
3. **Order Object Validation** - Verifies order object is a valid `WC_Order` instance
4. **Direct Access Prevention** - Uses `ABSPATH` check to prevent direct file access
5. **Exception Handling** - Try-catch blocks to prevent fatal errors
6. **Order ID Validation** - Uses `absint()` to validate and sanitize order IDs
7. **XSS Prevention** - Uses `esc_html()` for admin notice output
8. **Email Header Injection Prevention** - Checks for CRLF characters in email
9. **Rate Limiting** - Prevents email spam with transient-based cooldown (60s default)
10. **Log Sanitization** - All log messages are sanitized to prevent log injection

## Performance Optimizations

1. **Early Return Pattern** - Returns early if conditions are not met
2. **Email Enabled Check** - Checks if email type is enabled before triggering
3. **Method Existence Check** - Verifies methods exist before calling
4. **Original Recipient Restoration** - Ensures recipient is restored even on failure

## Changelog

### 2.1.0
- **Security Fix:** Added `esc_html()` to admin notice to prevent XSS
- **Security Fix:** Added `absint()` validation for order_id parameter
- **Security Fix:** Added email header injection check (CRLF detection)
- **Security Fix:** Sanitized all log messages to prevent log injection
- **Feature:** Added rate limiting to prevent email spam (60s cooldown by default)
- **Feature:** Added `fcn_rate_limit_period` filter to customize cooldown
- **Improvement:** Enhanced code documentation for security functions

### 2.0.0
- Added comprehensive error handling with try-catch
- Added WooCommerce version compatibility check
- Added email validation and sanitization
- Added logging system using WooCommerce logger
- Added developer hooks (filters and actions)
- Added admin notice for compatibility issues
- Improved code structure with helper functions
- Added proper plugin header for use as standalone plugin

### 1.0.0
- Initial release
- Basic email notification for cancelled/failed orders

## Contributing

Contributions to FailCancelNotify are welcome. Feel free to fork, make improvements, and submit pull requests.

### Development Setup

1. Clone the repository
2. Make your changes
3. Test with WooCommerce 3.0+ and PHP 7.4+
4. Submit a pull request

## License

This plugin is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

If you encounter any issues or have questions, please [open an issue](https://github.com/masyogie/FailCancelNotify/issues) on GitHub.