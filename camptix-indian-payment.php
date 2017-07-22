<?php
/**
 * Plugin Name: CampTix Indian Payments
 * Plugin URI: https://github.com/wpindiaorg/camptix-indian-payments
 * Description: Simple and Flexible payment ticketing for Camptix using Indian Payment Platforms
 * Author: India WordPress Community
 * Author URI: https://github.com/wpindiaorg/
 * Version: 1.0
 * License: GPLv2 or later
 * Text Domain: camptix-indian-payments
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/wpindiaorg/camptix-indian-payments
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Definitions
define( 'CAMPTIX_MULTI_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAMPTIX_MULTI_URL', plugin_dir_url( __FILE__ ) );

// Add INR currency
add_filter( 'camptix_currencies', 'camptix_add_inr_currency' );
function camptix_add_inr_currency( $currencies ) {
	$currencies['INR'] = array(
		'label' => __( 'Indian Rupees', 'camptix-indian-payments' ),
		'format' => '₹ %s',
	);
	return $currencies;
}

// Load the Instamojo Payment Method
add_action( 'camptix_load_addons', 'camptix_instamojo_load_payment_method' );
function camptix_instamojo_load_payment_method() {
	if ( ! class_exists( 'CampTix_Payment_Method_Instamojo' ) )
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-camptix-payment-method-instamojo.php';
	camptix_register_addon( 'CampTix_Payment_Method_Instamojo' );
}

// Load the Razorpay Payment Method
add_action( 'camptix_load_addons', 'camptix_razorpay_load_payment_method' );
function camptix_razorpay_load_payment_method() {
		if ( ! class_exists( 'CampTix_Payment_Method_RazorPay' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'classes/class-camptix-payment-method-razorpay.php';
		}
		camptix_register_addon( 'CampTix_Payment_Method_RazorPay' );
	}

?>
