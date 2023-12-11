<?php
/**
 * WooCommerce Custom Email Notifications for Cancelled and Failed Orders
 *
 * This snippet sends email notifications directly to customers when their orders are either cancelled or failed,
 * bypassing the default admin notifications of WooCommerce.
 */

// Hook into WooCommerce order status changes for 'cancelled' and 'failed' orders.
add_action('woocommerce_order_status_cancelled', 'send_customer_email_on_status_change', 10, 2);
add_action('woocommerce_order_status_failed', 'send_customer_email_on_status_change', 10, 2);

/**
 * Handles the email notifications for order status changes.
 *
 * @param int $order_id The ID of the order.
 * @param WC_Order $order The order object.
 */
function send_customer_email_on_status_change($order_id, $order) {
    $wc_emails = WC()->mailer()->get_emails(); // Retrieve instances of all WC_Emails objects
    $customer_email = $order->get_billing_email(); // Get the customer's email address

    // Handle the 'cancelled' order status
    if ($order->get_status() == 'cancelled') {
        // Save the original recipient
        $original_recipient = $wc_emails['WC_Email_Cancelled_Order']->recipient;

        $wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
        $wc_emails['WC_Email_Cancelled_Order']->trigger($order_id);

        // Reset the recipient to the original
        $wc_emails['WC_Email_Cancelled_Order']->recipient = $original_recipient;
    }
    // Handle the 'failed' order status
    elseif ($order->get_status() == 'failed') {
        // Save the original recipient
        $original_recipient = $wc_emails['WC_Email_Failed_Order']->recipient;

        $wc_emails['WC_Email_Failed_Order']->recipient = $customer_email;
        $wc_emails['WC_Email_Failed_Order']->trigger($order_id);

        // Reset the recipient to the original
        $wc_emails['WC_Email_Failed_Order']->recipient = $original_recipient;
    }
}

