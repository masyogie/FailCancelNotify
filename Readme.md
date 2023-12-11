# FailCancelNotify

A WooCommerce snippet for sending custom email notifications to customers for cancelled or failed orders.

## Overview

FailCancelNotify is a WordPress/WooCommerce snippet that enhances the order management experience by directly notifying customers when their orders are either cancelled or failed. This approach bypasses the default admin-focused notifications, providing a more direct and informative communication with customers.

## Features

- Sends email notifications to customers if their order is cancelled or fails.
- Bypasses the default WooCommerce admin notification system.
- Easy integration into existing WooCommerce setups.

## Installation

1. Install the "Code Snippets" plugin in your WordPress site. This plugin allows you to add custom PHP code snippets without editing your theme's `functions.php` file.
2. Once the plugin is installed and activated, go to "Snippets" in your WordPress dashboard.
3. Click on "Add New" to create a new snippet.
4. Copy and paste the PHP code from this repository into the code field of the snippet.
5. Name your snippet (for example, "FailCancelNotify for WooCommerce Orders") for easy identification.
6. Save and activate the snippet to apply the functionality.

## Usage

Once the snippet is activated through the "Code Snippets" plugin, it automatically hooks into WooCommerce's email system. No further configuration is needed. Customers will receive email notifications for any orders marked as 'cancelled' or 'failed'.

## Contributing

Contributions to FailCancelNotify are welcome. Feel free to fork, make improvements, and submit pull requests.

## License

This snippet is open-sourced software licensed under the MIT license. See the [LICENSE](LICENSE) file for more details.
