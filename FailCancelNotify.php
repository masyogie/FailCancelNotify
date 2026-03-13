<?php
/**
 * Plugin Name: FailCancelNotify
 * Plugin URI: https://github.com/masyogie/FailCancelNotify
 * Description: Send email notifications to customers for cancelled or failed WooCommerce orders
 * Version: 2.1.0
 * Author: masyogie
 * Requires PHP: 7.4
 * Requires at least: 5.0
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: MIT
 * Text Domain: fail-cancel-notify
 *
 * This snippet sends email notifications directly to customers when their orders are either cancelled or failed,
 * bypassing the default admin notifications of WooCommerce.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */
function fcn_is_woocommerce_active() {
    return class_exists('WooCommerce') && function_exists('WC');
}

/**
 * Get the minimum required WooCommerce version
 *
 * @return string
 */
function fcn_get_min_wc_version() {
    return '3.0';
}

/**
 * Check if WooCommerce version is compatible
 *
 * @return bool
 */
function fcn_is_wc_version_compatible() {
    if (!fcn_is_woocommerce_active()) {
        return false;
    }
    
    $wc_version = WC()->version;
    return version_compare($wc_version, fcn_get_min_wc_version(), '>=');
}

/**
 * Log messages for debugging
 *
 * @param string $message The message to log
 * @param string $level   Log level (info, error, warning, debug)
 * @return void
 */
function fcn_log($message, $level = 'info') {
    if (function_exists('wc_get_logger')) {
        $logger = wc_get_logger();
        $context = ['source' => 'fail-cancel-notify'];
        
        switch ($level) {
            case 'error':
                $logger->error($message, $context);
                break;
            case 'warning':
                $logger->warning($message, $context);
                break;
            case 'debug':
                $logger->debug($message, $context);
                break;
            default:
                $logger->info($message, $context);
        }
    }
}

/**
 * Map order status to WooCommerce email class keys
 *
 * @return array
 */
function fcn_get_email_map() {
    return [
        'cancelled' => 'WC_Email_Cancelled_Order',
        'failed'    => 'WC_Email_Failed_Order',
    ];
}

/**
 * Check rate limit for email sending to prevent spam
 *
 * @param int $order_id The order ID
 * @return bool True if rate limit is not exceeded, false otherwise
 */
function fcn_check_rate_limit($order_id) {
    $rate_limit_key = 'fcn_rate_limit_' . absint($order_id);
    
    if (get_transient($rate_limit_key)) {
        fcn_log('Rate limit exceeded for order #' . absint($order_id), 'warning');
        return false;
    }
    
    return true;
}

/**
 * Set rate limit after email is sent
 *
 * @param int $order_id The order ID
 * @param int $seconds  Cooldown period in seconds (default 60)
 * @return void
 */
function fcn_set_rate_limit($order_id, $seconds = 60) {
    $rate_limit_key = 'fcn_rate_limit_' . absint($order_id);
    set_transient($rate_limit_key, true, $seconds);
}

/**
 * Handles the email notifications for order status changes.
 *
 * @param int      $order_id The ID of the order.
 * @param WC_Order $order    The order object.
 * @return bool True if email was sent successfully, false otherwise
 */
function fcn_send_customer_email_on_status_change($order_id, $order) {
    // Validate and sanitize order_id
    $order_id = absint($order_id);
    if ($order_id === 0) {
        fcn_log('Invalid order ID: must be a positive integer', 'error');
        return false;
    }
    
    // Check WooCommerce compatibility
    if (!fcn_is_wc_version_compatible()) {
        fcn_log('WooCommerce is not active or version is not compatible', 'error');
        return false;
    }
    
    // Validate order object
    if (!($order instanceof WC_Order)) {
        fcn_log('Invalid order object for order ID: ' . $order_id, 'error');
        return false;
    }
    
    // Get order status (without 'wc-' prefix)
    $order_status = $order->get_status();
    
    // Get email map
    $email_map = fcn_get_email_map();
    
    // Check if this status is handled
    if (!isset($email_map[$order_status])) {
        fcn_log('Order status is not handled by this plugin: ' . sanitize_text_field($order_status), 'debug');
        return false;
    }
    
    // Get customer email
    $customer_email = $order->get_billing_email();
    
    // Validate email format
    if (empty($customer_email) || !is_email($customer_email)) {
        fcn_log('Invalid or empty email address for order #' . $order_id, 'warning');
        return false;
    }
    
    // Sanitize email
    $customer_email = sanitize_email($customer_email);
    
    // Check for potential email header injection
    if (preg_match('/[\r\n]/', $customer_email)) {
        fcn_log('Potential email header injection detected for order #' . $order_id, 'error');
        return false;
    }
    
    // Check rate limit to prevent email spam
    if (!fcn_check_rate_limit($order_id)) {
        return false;
    }
    
    // Get WooCommerce mailer instance
    try {
        $mailer = WC()->mailer();
        if (!is_object($mailer)) {
            fcn_log('Failed to get WooCommerce mailer instance', 'error');
            return false;
        }
        
        $wc_emails = $mailer->get_emails();
    } catch (Exception $e) {
        fcn_log('Exception while getting WooCommerce emails: ' . $e->getMessage(), 'error');
        return false;
    }
    
    // Validate emails array
    if (!is_array($wc_emails) || empty($wc_emails)) {
        fcn_log('No WooCommerce emails found', 'error');
        return false;
    }
    
    // Get the email class key
    $email_key = $email_map[$order_status];
    
    // Check if email class exists
    if (!isset($wc_emails[$email_key]) || !is_object($wc_emails[$email_key])) {
        fcn_log('Email class not found in WooCommerce emails: ' . sanitize_text_field($email_key), 'warning');
        return false;
    }
    
    $email = $wc_emails[$email_key];
    
    // Check if email is enabled
    if (method_exists($email, 'is_enabled') && !$email->is_enabled()) {
        fcn_log('Email type is disabled: ' . sanitize_text_field($email_key), 'info');
        return false;
    }
    
    // Allow developers to bypass this notification
    $should_send = apply_filters('fcn_should_send_notification', true, $order_id, $order, $email_key);
    
    if (!$should_send) {
        fcn_log('Notification for order #' . $order_id . ' bypassed via filter', 'info');
        return false;
    }
    
    // Send the email
    try {
        // Store original recipient
        $original_recipient = isset($email->recipient) ? $email->recipient : '';
        
        // Set customer as recipient
        $email->recipient = $customer_email;
        
        // Allow developers to customize email before sending
        do_action('fcn_before_send_email', $email, $order_id, $order);
        
        // Trigger the email
        if (method_exists($email, 'trigger')) {
            $email->trigger($order_id);
        } else {
            fcn_log('Email class does not have a trigger method: ' . sanitize_text_field($email_key), 'error');
            return false;
        }
        
        // Restore original recipient
        $email->recipient = $original_recipient;
        
        // Set rate limit to prevent duplicate emails
        fcn_set_rate_limit($order_id, 60);
        
        // Log success (with sanitized data)
        fcn_log('Email sent successfully to ' . $customer_email . ' for order #' . $order_id . ' (status: ' . sanitize_text_field($order_status) . ')');
        
        // Allow developers to hook after email is sent
        do_action('fcn_after_send_email', $order_id, $order, $customer_email);
        
        return true;
        
    } catch (Exception $e) {
        fcn_log('Exception while sending email: ' . sanitize_text_field($e->getMessage()), 'error');
        
        // Try to restore recipient even on failure
        if (isset($original_recipient)) {
            $email->recipient = $original_recipient;
        }
        
        return false;
    }
}

/**
 * Get the rate limit cooldown period
 *
 * @return int Seconds between allowed emails for the same order
 */
function fcn_get_rate_limit_period() {
    /**
     * Filter the rate limit period for email notifications
     *
     * @param int $seconds Cooldown period in seconds (default 60)
     */
    return apply_filters('fcn_rate_limit_period', 60);
}

/**
 * Initialize the plugin
 *
 * @return void
 */
function fcn_init() {
    // Check WooCommerce compatibility on init
    if (!fcn_is_wc_version_compatible()) {
        add_action('admin_notices', function() {
            $min_version = fcn_get_min_wc_version();
            echo '<div class="error"><p>';
            echo '<strong>FailCancelNotify:</strong> requires WooCommerce ' . esc_html($min_version) . ' or higher to be active.';
            echo '</p></div>';
        });
        return;
    }
    
    // Hook into WooCommerce order status changes
    add_action('woocommerce_order_status_cancelled', 'fcn_send_customer_email_on_status_change', 10, 2);
    add_action('woocommerce_order_status_failed', 'fcn_send_customer_email_on_status_change', 10, 2);
}

// Initialize plugin after WooCommerce is loaded
add_action('woocommerce_loaded', 'fcn_init');

// Fallback initialization if woocommerce_loaded doesn't fire
add_action('init', function() {
    if (fcn_is_woocommerce_active() && !has_action('woocommerce_order_status_cancelled', 'fcn_send_customer_email_on_status_change')) {
        fcn_init();
    }
}, 20);