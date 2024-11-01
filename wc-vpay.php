<?php
/*
 * Plugin Name: VPay Payment Gateway for WooCommerce
 * Plugin URI: https://docs.vpay.africa/woocommerce-plugin-specification/
 * Description: Receive instant and fast payments via bank transfer, USSD and card payment.
 * Author: VPay Africa
 * Author URI:  https://www.vpay.africa/
 * Version: 1.1.0
 * Text Domain: wc-vpay
 */


// if (! in_array( "woocommerce/woocommerce.php", apply_filters("active_plugins", get_options( "active plugins") )))return;


define( 'WC_VPAY_MAIN_FILE', __FILE__ );
define( 'WC_VPAY_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'WC_VPAY_VERSION', '1.0.0' );
define( 'WC_VPAY_SCRIPTS_VERSION', date('ymdhis') );

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'vpay_add_gateway_class' );
function vpay_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_VPay_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'vpay_init_gateway_class' );
function vpay_init_gateway_class() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'vpay_wc_missing_notice' );
		return;
	}

	add_action( 'admin_notices', 'vpay_wc_testmode_notice' );

    require_once dirname( __FILE__ ) . '/includes/class-wc-vpay-gateway.php';
    //include json receive file
    require_once(plugin_dir_path(__FILE__).'/includes/json-receive.php');

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'vpay_woocommerce_plugin_action_links' );
}

/**
 * Display a notice if WooCommerce is not installed
 */
function vpay_wc_missing_notice() {
	echo '<div class="error"><p><strong>' . sprintf( __( 'VPay requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'wc-vpay' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function vpay_wc_testmode_notice() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$vpay_settings = get_option( 'woocommerce_vpay_settings' );
	$test_mode = isset( $vpay_settings['testmode'] ) ? $vpay_settings['testmode'] : '';

	if ( 'yes' === $test_mode ) {
		/* translators: 1. VPay settings page URL link. */
		echo '<div class="error"><p>' . sprintf( __( 'VPay is in test mode, Click <strong><a href="%s">here</a></strong> to start accepting live payment on your site.', 'wc-vpay' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=vpay' ) ) ) . '</p></div>';
	}
}

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function vpay_woocommerce_plugin_action_links( $links ) {

	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=vpay' ) . '" title="' . 'View VPay WooCommerce Settings' . '">' . 'Settings' . '</a>',
	);

	return array_merge( $settings_link, $links );

}
