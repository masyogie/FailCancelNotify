<?php
add_action('woocommerce_order_status_changed', 'send_cancelled_and_failed_email_notifications_to_customer', 10, 4);
function send_cancelled_and_failed_email_notifications_to_customer($order_id, $old_status, $new_status, $order) {
    if ($new_status == 'cancelled' || $new_status == 'failed') {
        $wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
        $customer_email = $order->get_billing_email(); // The customer email

        if ($new_status == 'cancelled') {
            // Save the original recipient
            $original_recipient = $wc_emails['WC_Email_Cancelled_Order']->recipient;

            // Change the recipient of this instance
            $wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
            // Sending the email from this instance
            $wc_emails['WC_Email_Cancelled_Order']->trigger($order_id);

            // Reset the recipient to the original
            $wc_emails['WC_Email_Cancelled_Order']->recipient = $original_recipient;
        } elseif ($new_status == 'failed') {
            // Save the original recipient
            $original_recipient = $wc_emails['WC_Email_Failed_Order']->recipient;

            // Change the recipient of this instance
            $wc_emails['WC_Email_Failed_Order']->recipient = $customer_email;
            // Sending the email from this instance
            $wc_emails['WC_Email_Failed_Order']->trigger($order_id);

            // Reset the recipient to the original
            $wc_emails['WC_Email_Failed_Order']->recipient = $original_recipient;
        }
    }
}
