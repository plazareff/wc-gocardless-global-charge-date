# WooCommerce GoCardless Global Charge Date

**Version:** 1.0 
**Author:** Patrice Lazareff  
**License:** GPL v2 or later

## Overview

The WooCommerce GoCardless Global Charge Date plugin adds global settings for scheduling GoCardless payments in WooCommerce. With this plugin you can configure:
- A fixed **Charge Day** (the day of the month when payment is attempted), and
- A **Billing Period Start Day** that marks when the billing period begins.

Based on these settings, the plugin calculates the charge date as follows:
- **If an order is placed on or after the Billing Period Start Day** in a month, the billing period is considered to start on that day in the current month.
- **If an order is placed before the Billing Period Start Day**, the billing period is assumed to have started on that day in the previous month.

In either case, the payment charge date is scheduled **two months** after the billing period start, with the day of the month forced to the fixed **Charge Day**.

For example, if the **Billing Period Start Day** is set to 28 (default) and the **Charge Day** is set to 8:
- An order placed on February 11 (before the 28th) will have its billing period start on January 28 and its payment scheduled on March 8.
- An order placed on February 29 (or any day after the 28th) will have its billing period start on February 28 and its payment scheduled on April 8.

*Note:* If the fixed charge date falls on a weekend or bank holiday, GoCardless automatically adjusts it to the next available business day.

## Features

- **Global Fixed Charge Day:** Set a fixed day (1–28) for all GoCardless payment collections.
- **Configurable Billing Period Start Day:** Define the day that marks the start of your billing period.
- **Automatic Payment Scheduling:** The plugin calculates the charge date as two months after the billing period start.
- **Admin Settings Page:** A dedicated settings page is added under WooCommerce for configuration.
- **Quick Settings Access:** A "Settings" link is provided on the Plugins page for easy access.

## Installation

### Prerequisites

- WordPress 5.0 or higher.
- WooCommerce plugin.
- WooCommerce GoCardless Gateway plugin (ensure it is active).

### Steps

1. **Download or clone** this repository.
2. **Place the plugin folder** (`wc-gocardless-global-charge-date`) into your `wp-content/plugins/` directory.
3. **Activate the plugin** from the WordPress admin panel.
4. **Configure the plugin:**
   - Navigate to **WooCommerce > GoCardless Charge Date**.
   - Set your desired fixed Charge Day and Billing Period Start Day, then save your settings.

## How It Works

The plugin hooks into the `woocommerce_gocardless_create_payment_params` filter to modify the payment parameters sent to GoCardless. It performs the following steps:
1. Retrieves the order’s creation date.
2. Compares the order’s day of the month with the configured Billing Period Start Day.
   - If the order day is **greater than or equal to** the Billing Period Start Day, the billing period is set to that day in the current month.
   - Otherwise, the billing period is assumed to have started on that day in the previous month.
3. The payment is then scheduled two months after the billing period start, with the day of the month forced to the fixed Charge Day.

## Disclaimer

**Disclaimer:** This plugin is an independent project and is not affiliated, endorsed, or supported by GoCardless in any way. Use this plugin at your own risk. The plugin author, Patrice Lazareff, assumes no liability for any issues, losses, or damages arising from the use of this plugin.

## Changelog

### 1.0
- Initial release with global fixed Charge Day functionality and admin settings integration.

## Contributing

Contributions are welcome! If you have suggestions, bug fixes, or improvements, please fork the repository and submit a pull request. For major changes, please open an issue first to discuss your proposed changes.

## License

This plugin is licensed under the [GPL v2 or later](LICENSE).

## Credits

Developed by [Patrice Lazareff](https://www.lazareff.com) with some help from ChatGPT.
