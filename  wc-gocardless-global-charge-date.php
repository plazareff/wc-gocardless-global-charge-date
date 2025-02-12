<?php
/**
 * Plugin Name: WooCommerce GoCardless Global Charge Date
 * Plugin URI:  https://example.com/
 * Description: Adds a global setting for GoCardless payments to schedule a specific charge day of the month.
 * Version:     1.1
 * Author:      Patrice Lazareff
 * Author URI:  https://www.lazareff.com
 * Text Domain: wc-gocardless-global-charge-date
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check dependencies.
 */
function wc_gc_global_charge_date_dependencies() {
	// Make sure WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	// And ensure the GoCardless gateway class exists.
	if ( ! class_exists( 'WC_GoCardless' ) ) {
		return false;
	}
	return true;
}

add_action( 'admin_notices', function() {
    if ( ! wc_gc_global_charge_date_dependencies() ) {
        echo '<div class="error"><p>' . esc_html__( 'WooCommerce GoCardless Global Charge Date: Dependencies not met. Make sure WooCommerce and the GoCardless Gateway plugin are active and the expected classes exist.', 'wc-gocardless-global-charge-date' ) . '</p></div>';
    }
} );

/**
 * ADMIN: Add a submenu page under WooCommerce to manage our setting.
 */
add_action( 'admin_menu', 'wc_gc_charge_date_add_admin_menu' );
function wc_gc_charge_date_add_admin_menu() {
	add_submenu_page(
		'woocommerce', // Parent slug (WooCommerce menu)
		__( 'GoCardless Charge Date', 'wc-gocardless-global-charge-date' ),
		__( 'GoCardless Charge Date', 'wc-gocardless-global-charge-date' ),
		'manage_options',
		'wc-gc-charge-date',
		'wc_gc_charge_date_options_page'
	);
}

/**
 * ADMIN: Register our setting and add our fields.
 */
add_action( 'admin_init', 'wc_gc_charge_date_settings_init' );
function wc_gc_charge_date_settings_init() {
	// Register the option for the fixed charge day; default to 1.
	register_setting( 'wc_gc_charge_date_group', 'wc_gocardless_charge_day', array(
		'type'              => 'number',
		'sanitize_callback' => 'absint',
		'default'           => 1,
	) );
	
	// Register the option for the billing period start day; default to 28.
	register_setting( 'wc_gc_charge_date_group', 'wc_gocardless_billing_period_start_day', array(
		'type'              => 'number',
		'sanitize_callback' => 'absint',
		'default'           => 28,
	) );

	// Add a section for our settings.
	add_settings_section(
		'wc_gc_charge_date_section',
		__( 'Global GoCardless Charge Date Settings', 'wc-gocardless-global-charge-date' ),
		'wc_gc_charge_date_section_callback',
		'wc-gc-charge-date'
	);

	// Add the field for the fixed charge day.
	add_settings_field(
		'wc_gocardless_charge_day_field',
		__( 'Charge Day of Month', 'wc-gocardless-global-charge-date' ),
		'wc_gc_charge_day_field_render',
		'wc-gc-charge-date',
		'wc_gc_charge_date_section'
	);
	
	// Add the field for the billing period start day.
	add_settings_field(
		'wc_gocardless_billing_period_start_day_field',
		__( 'Billing Period Start Day', 'wc-gocardless-global-charge-date' ),
		'wc_gc_billing_period_start_day_field_render',
		'wc-gc-charge-date',
		'wc_gc_charge_date_section'
	);
}

/**
 * Renders the input field for the fixed charge day.
 */
function wc_gc_charge_day_field_render() {
	$option = get_option( 'wc_gocardless_charge_day', 1 );
	?>
	<input type="number" name="wc_gocardless_charge_day" value="<?php echo esc_attr( $option ); ?>" min="1" max="28" />
	<p class="description">
		<?php esc_html_e( 'Enter a day of the month (1-28) on which payments should be charged. Note that GoCardless may shift the actual date to the next working day.', 'wc-gocardless-global-charge-date' ); ?>
	</p>
	<?php
}

/**
 * Renders the input field for the billing period start day.
 */
function wc_gc_billing_period_start_day_field_render() {
	$option = get_option( 'wc_gocardless_billing_period_start_day', 28 );
	?>
	<input type="number" name="wc_gocardless_billing_period_start_day" value="<?php echo esc_attr( $option ); ?>" min="1" max="28" />
	<p class="description">
		<?php esc_html_e( 'Enter the day of the month (1-28) that marks the start of the billing period. Orders placed on or before this day are considered to belong to the previous billing period.', 'wc-gocardless-global-charge-date' ); ?>
	</p>
	<?php
}

/**
 * Outputs a description for the settings section.
 */
function wc_gc_charge_date_section_callback() {
	echo '<p>' . esc_html__( 'Set a global fixed charge day for GoCardless payments and the billing period start day. When an order is placed using GoCardless, if the order date is on or after the Billing Period Start Day, the billing period is considered to start on that day of the current month; otherwise, it is considered to start on that day of the previous month. The payment is scheduled two months after the billing period start on the fixed Charge Day.', 'wc-gocardless-global-charge-date' ) . '</p>';
}

/**
 * Outputs our settings page.
 */
function wc_gc_charge_date_options_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'GoCardless Charge Date Settings', 'wc-gocardless-global-charge-date' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'wc_gc_charge_date_group' );
			do_settings_sections( 'wc-gc-charge-date' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Adds a settings link to the plugin action links on the plugins page.
 *
 * @param array $links An array of plugin action links.
 * @return array Modified array of plugin action links with the settings link added.
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_gc_charge_date_plugin_action_links' );
function wc_gc_charge_date_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-gc-charge-date' ) . '">' . __( 'Settings', 'wc-gocardless-global-charge-date' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * Modify the GoCardless payment parameters.
 *
 * New rule:
 * - If the order is placed on or after the Billing Period Start Day, assume the billing period starts on that day of the current month.
 * - If the order is placed on or before the Billing Period Start Day, assume the billing period started on that day of the previous month.
 * Then, schedule the payment for 2 months after the billing period start, with the day set to the fixed global Charge Day.
 *
 * For example:
 * - An order on Feb 11 (with Billing Period Start Day = 28) will have its payment scheduled on the fixed Charge Day in March.
 * - An order on Feb 22 (with Billing Period Start Day = 28) will have its payment scheduled on the fixed Charge Day in April.
 *
 * @param array $params The array of payment parameters.
 * @return array Modified parameters including the calculated 'charge_date'.
 */
function wc_gc_modify_gocardless_payment_params( $params ) {
    // Get the fixed global charge day (must be between 1 and 28).
    $charge_day = absint( get_option( 'wc_gocardless_charge_day', 1 ) );
    if ( $charge_day < 1 || $charge_day > 28 ) {
        $charge_day = 1;
    }

    // Get the billing period start day from settings (default to 28).
    $billing_period_start_day = absint( get_option( 'wc_gocardless_billing_period_start_day', 28 ) );
    if ( $billing_period_start_day < 1 || $billing_period_start_day > 28 ) {
        $billing_period_start_day = 28;
    }

    // Retrieve the order ID from metadata, if available.
    if ( empty( $params['metadata']['order_id'] ) ) {
        // No order id available; return the params unmodified.
        return $params;
    }
    $order_id = absint( $params['metadata']['order_id'] );
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return $params;
    }

    // Use the order's creation date.
    $order_date = $order->get_date_created();
    if ( ! $order_date ) {
        $order_date = new DateTime();
    } elseif ( ! ( $order_date instanceof DateTime ) ) {
        $order_date = new DateTime( $order_date );
    }
    
    // Get the day of the month when the order was placed.
    $order_day = (int) $order_date->format( 'j' );

    // Determine the billing period start:
    // - If the order is placed on or after the Billing Period Start Day, then billing period starts on that day of the current month.
    // - Otherwise, billing period starts on that day of the previous month.
    if ( $order_day >= $billing_period_start_day ) {
        $billing_period_start = DateTime::createFromFormat( 'Y-m-d', $order_date->format('Y-m-') . sprintf( '%02d', $billing_period_start_day ) );
    } else {
        $prev_month = clone $order_date;
        $prev_month->modify( 'first day of last month' );
        $billing_period_start = DateTime::createFromFormat( 'Y-m-d', $prev_month->format('Y-m-') . sprintf( '%02d', $billing_period_start_day ) );
    }

    // Schedule the payment for 2 months after the billing period start.
    $charge_date = clone $billing_period_start;
    $charge_date->modify( '+2 months' );
    // Override the day to be the fixed global charge day.
    $charge_date->setDate( $charge_date->format('Y'), $charge_date->format('m'), $charge_day );

    $params['charge_date'] = $charge_date->format( 'Y-m-d' );
    return $params;
}
add_filter( 'woocommerce_gocardless_create_payment_params', 'wc_gc_modify_gocardless_payment_params', 10, 1 );
