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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Camptix_Indian_Payments {
	/**
	 * Instance.
	 *
	 * @since
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since
	 * @access private
	 */
	private function __construct() {
	}

	/**
	 * Get instance.
	 *
	 * @since
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup plugin.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function setup() {
		$this->setup_contants();
		$this->setup_hooks();
	}


	/**
	 * Setup hooks
	 *
	 * @since  1.0
	 * @access private
	 */
	private function setup_hooks() {
		// Add INR currency
		add_filter( 'camptix_currencies', array( $this, 'add_inr_currency' ) );

		// Load the Instamojo Payment Method
		add_action( 'camptix_load_addons', array( $this, 'load_payment_methods' ) );
	}


	/**
	 * Setup constants
	 *
	 * @since  1.0
	 * @access private
	 */
	private function setup_contants() {
		// Definitions
		define( 'CAMPTIX_MULTI_DIR', plugin_dir_path( __FILE__ ) );
		define( 'CAMPTIX_MULTI_URL', plugin_dir_url( __FILE__ ) );
	}


	/**
	 * Add indian currency.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $currencies
	 *
	 * @return mixed
	 */
	public function add_inr_currency( $currencies ) {
		$currencies['INR'] = array(
			'label'  => __( 'Indian Rupees', 'camptix-indian-payments' ),
			'format' => '₹ %s',
		);

		return $currencies;
	}


	/**
	 * Register payment gateways
	 *
	 * @since  1.0
	 * @access public
	 */
	public function load_payment_methods() {
		$payment_gateways = array(
			array(
				'class'     => 'CampTix_Payment_Method_Instamojo',
				'file_path' => plugin_dir_path( __FILE__ ) . 'classes/class-camptix-payment-method-instamojo.php',
			),
			array(
				'class'     => 'CampTix_Payment_Method_RazorPay',
				'file_path' => plugin_dir_path( __FILE__ ) . 'classes/class-camptix-payment-method-razorpay.php',
			),
		);


		foreach ( $payment_gateways as $gateway ) {
			if ( ! class_exists( $gateway['class'] ) ) {
				require_once $gateway['file_path'];
			}

			camptix_register_addon( $gateway['class'] );
		}
	}
}

/**
 * Initialize plugin.
 *
 * @since 1.0
 */
function cip_init() {
	if ( class_exists( 'CampTix_Plugin' ) ) {
		Camptix_Indian_Payments::get_instance()->setup();
	}
}

add_action( 'plugins_loaded', 'cip_init', 9999 );
