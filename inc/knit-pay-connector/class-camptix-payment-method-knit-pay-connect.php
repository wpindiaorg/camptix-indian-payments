<?php
/**
 * CampTix Knit Pay Connect Payment Method
 *
 * This class integrates Knit Pay server with Camptix.
 *
 * @category       Class
 * @package        Camptix Knit Pay Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class CampTix_Payment_Method_Knit_Pay_Connect extends CampTix_Payment_Method {
	public $id                   = 'knit-pay-connect';
	public $name                 = '';
	public $description          = '';
	public $supported_currencies = [
		'AED',
		'AFN',
		'ALL',
		'AMD',
		'ANG',
		'AOA',
		'ARS',
		'AUD',
		'AWG',
		'AZN',
		'BAM',
		'BBD',
		'BDT',
		'BGN',
		'BMD',
		'BND',
		'BOB',
		'BRL',
		'BSD',
		'BWP',
		'BZD',
		'CAD',
		'CDF',
		'CHF',
		'CNY',
		'COP',
		'CRC',
		'CVE',
		'CZK',
		'DKK',
		'DOP',
		'DZD',
		'EGP',
		'ETB',
		'EUR',
		'FJD',
		'FKP',
		'GBP',
		'GEL',
		'GIP',
		'GMD',
		'GTQ',
		'GYD',
		'HKD',
		'HNL',
		'HRK',
		'HTG',
		'HUF',
		'IDR',
		'ILS',
		'INR',
		'ISK',
		'JMD',
		'KES',
		'KGS',
		'KHR',
		'KYD',
		'KZT',
		'LAK',
		'LBP',
		'LKR',
		'LRD',
		'LSL',
		'MAD',
		'MDL',
		'MKD',
		'MMK',
		'MNT',
		'MOP',
		'MRO',
		'MUR',
		'MVR',
		'MWK',
		'MXN',
		'MYR',
		'MZN',
		'NAD',
		'NGN',
		'NIO',
		'NOK',
		'NPR',
		'NZD',
		'PAB',
		'PEN',
		'PGK',
		'PHP',
		'PKR',
		'PLN',
		'QAR',
		'RON',
		'RSD',
		'RUB',
		'SAR',
		'SBD',
		'SCR',
		'SEK',
		'SGD',
		'SHP',
		'SLL',
		'SOS',
		'SRD',
		'STD',
		'SZL',
		'THB',
		'TJS',
		'TOP',
		'TRY',
		'TTD',
		'TWD',
		'TZS',
		'UAH',
		'USD',
		'UYU',
		'UZS',
		'WST',
		'XCD',
		'YER',
		'ZAR',
		'ZMW',
		'BIF',
		'CLP',
		'DJF',
		'GNF',
		'JPY',
		'KMF',
		'KRW',
		'MGA',
		'PYG',
		'RWF',
		'UGX',
		'VND',
		'VUV',
		'XAF',
		'XOF',
		'XPF',
	];

	/* We can have an array to store our options. Use $this->get_payment_options() to retrieve them. */
	protected $options = [];

	public function __construct() {
		$this->id          = 'knit-pay-connect';
		$this->name        = 'Knit Pay Connect';
		$this->description = 'Knit Pay is a self-hosted payment gateway integration tool. Kindly install Knit Pay on your integration server and connect the server using Knit Pay Connect.';
		parent::__construct();
	}

	function camptix_init() {
		$this->options = array_merge(
			[
				'server_rest_url' => '',
				'server_username' => '',
				'server_password' => '',
				'title'           => 'Pay Online',
				'config_id'       => '',
			],
			$this->get_payment_options()
		);

		// Don't change payment method name for admin interface.
		if ( ! is_admin() ) {
			if ( ! empty( $this->options['title'] ) ) {
				$this->name = $this->options['title'];
			}

			if ( ! empty( $this->options['description'] ) ) {
				$this->description = $this->options['description'];
			}
		} else {
			$this->name = __( 'Knit Pay Connect', 'campt-indian-payment-gateway' );
		}

		if ( $this->is_gateway_enable() ) {
			add_action( 'template_redirect', [ $this, 'template_redirect' ] );
			add_filter( 'camptix_form_register_complete_attendee_object', [ $this, 'add_attendee_info' ], 10, 3 );
		}
	}

	public function is_gateway_enable() {
		return isset( $this->camptix_options['payment_methods'][ $this->id ] );
	}

	public function add_attendee_info( $attendee, $attendee_info, $current_count ) {
		if ( ! empty( $_POST['tix_attendee_info'][ $current_count ]['phone'] ) ) {
			$attendee->phone = trim( $_POST['tix_attendee_info'][ $current_count ]['phone'] );
		}
		return $attendee;
	}

	function payment_settings_fields() {
		$this->add_settings_field_helper( 'server_rest_url', __( 'Knit Pay Server Rest URL', 'campt-indian-payment-gateway' ), [ $this, 'field_text' ] );
		$this->add_settings_field_helper( 'server_username', __( 'Server Username', 'campt-indian-payment-gateway' ), [ $this, 'field_text' ] );
		$this->add_settings_field_helper( 'server_password', __( 'Server Application Password', 'campt-indian-payment-gateway' ), [ $this, 'field_text' ] );
		$this->add_settings_field_helper( 'title', __( 'Title', 'campt-indian-payment-gateway' ), [ $this, 'field_text' ] );
		$this->add_settings_field_helper( 'config_id', __( 'Configuration ID', 'campt-indian-payment-gateway' ), [ $this, 'field_text' ] );
	}

	function validate_options( $input ) {
		$output = $this->options;

		if ( isset( $input['server_rest_url'] ) ) {
			$output['server_rest_url'] = rtrim( $input['server_rest_url'], '/' ) . '/';
		}

		if ( isset( $input['server_username'] ) ) {
			$output['server_username'] = $input['server_username'];
		}

		if ( isset( $input['server_password'] ) ) {
			$output['server_password'] = $input['server_password'];
		}

		if ( isset( $input['title'] ) ) {
			$output['title'] = $input['title'];
		}

		if ( isset( $input['config_id'] ) ) {
			$output['config_id'] = $input['config_id'];
		}

		return $output;
	}

	function template_redirect() {
		if ( ! isset( $_REQUEST['tix_payment_method'] ) || $this->id !== $_REQUEST['tix_payment_method'] ) {
			return;
		}
		if ( isset( $_GET['tix_action'] ) ) {
			if ( 'payment_return' === $_GET['tix_action'] ) {
				$this->payment_return();
			}

			if ( 'payment_notify' === $_GET['tix_action'] ) {
				$this->payment_return();
			}
		}
	}

	function payment_return() {
		/** @var CampTix_Plugin $camptix */
		global $camptix;

		$payment_token = wp_unslash( $_REQUEST['tix_payment_token'] ?? '' );

		$camptix->log( 'User returning from Knit Pay', null, compact( 'payment_token' ) );

		if ( ! $payment_token ) {
			$camptix->log( 'Dying because invalid Knit Pay return data', null, compact( 'payment_token' ) );
			wp_die( 'empty token' );
		}

		$order = $this->get_order( $payment_token );

		if ( ! $order ) {
			$camptix->log( "Dying because couldn't find order", null, compact( 'payment_token' ) );
			wp_die( 'could not find order' );
		}

		$attendee_id         = $order['attendee_id'];
		$knit_pay_payment_id = get_post_meta( $attendee_id, 'knit_pay_payment_id', true );

		$response = wp_remote_get(
			$this->options['server_rest_url'] . 'knit-pay/v1/payments/' . $knit_pay_payment_id,
			[
				'headers' => $this->get_request_headers(),
				'timeout' => 60,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $this->handle_checkout_error( $payment_token, $attendee_id, $response->get_error_message() );
		}

		// Process the response.
		$result = wp_remote_retrieve_body( $response );
		$result = json_decode( $result );

		// Handle error from gateway.
		if ( isset( $result->code ) && isset( $result->message ) ) {
			return $this->handle_checkout_error( $payment_token, $attendee_id, $result->message );
		}

		$camptix->log( 'Knit Pay payment retrieved.', $attendee_id, $result );

		if ( empty( $result->status ) ) {
			$camptix->log( "Dying because couldn't get Payment status", $attendee_id, compact( 'payment_token', 'result' ) );
			wp_die( 'could not find payment details' );
		}

		$payment_data = [
			'transaction_id'      => $result->transaction_id,
			'transaction_details' => [ 'raw' => $result ],
		];

		return $camptix->payment_result( $payment_token, $this->get_status_from_string( $result->status ), $payment_data );
	}

	/**
	 * Get the payment status ID for the given shorthand name
	 *
	 * Helps convert payment statuses from Knit Pay responses, to CampTix payment statuses.
	 *
	 * @param string $payment_status
	 *
	 * @return int
	 */
	function get_status_from_string( $payment_status ) {
		$statuses = [
			'Success'    => CampTix_Plugin::PAYMENT_STATUS_COMPLETED,
			'Completed'  => CampTix_Plugin::PAYMENT_STATUS_COMPLETED,
			'Authorized' => CampTix_Plugin::PAYMENT_STATUS_COMPLETED,
			'Open'       => CampTix_Plugin::PAYMENT_STATUS_PENDING,
			'Cancelled'  => CampTix_Plugin::PAYMENT_STATUS_CANCELLED,
			'Failure'    => CampTix_Plugin::PAYMENT_STATUS_FAILED,
			'Refunded'   => CampTix_Plugin::PAYMENT_STATUS_REFUNDED,
			'Expired'    => CampTix_Plugin::PAYMENT_STATUS_TIMEOUT,
		];

		// Return pending for unknown statuses.
		if ( ! isset( $statuses[ $payment_status ] ) ) {
			$payment_status = 'Open';
		}

		return $statuses[ $payment_status ];
	}

	public function payment_checkout( $payment_token ) {
		/** @var CampTix_Plugin $camptix */
		global $camptix;

		if ( ! $payment_token || empty( $payment_token ) ) {
			return false;
		}

		if ( ! in_array( $this->camptix_options['currency'], $this->supported_currencies ) ) {
			wp_die( __( 'The selected currency is not supported by this payment method.', 'campt-indian-payment-gateway' ) );
		}

		$return_url = add_query_arg(
			[
				'tix_action'         => 'payment_return',
				'tix_payment_token'  => $payment_token,
				'tix_payment_method' => $this->id,
			],
			$this->get_tickets_url()
		);

		$notify_url = add_query_arg(
			[
				'tix_action'         => 'payment_notify',
				'tix_payment_token'  => $payment_token,
				'tix_payment_method' => $this->id,
			],
			$this->get_tickets_url()
		);

		$order           = $this->get_order( $payment_token );
		$attendee_id     = $order['attendee_id'];
		$attendee_detail = get_post_meta( $attendee_id );

		$billing_address = [
			'name'  => [
				'first_name' => self::get_value_from_array( $attendee_detail, 'tix_first_name' ),
				'last_name'  => self::get_value_from_array( $attendee_detail, 'tix_last_name' ),
			],
			'email' => self::get_value_from_array( $attendee_detail, 'tix_receipt_email' ),
			'phone' => self::get_value_from_array( $attendee_detail, 'tix_phone' ),
		];

		$payload = [
			'source'          => [
				'key'   => 'camptix-connect',
				'value' => $attendee_id,
			],
			'order_id'        => strval( $attendee_id ),
			'total_amount'    => [
				'value'    => $order['total'],
				'currency' => $this->camptix_options['currency'],
			],
			'description'     => 'Ticket ' . $attendee_id,
			'config_id'       => $this->options['config_id'],
			'redirect_url'    => $return_url,
			'notify_url'      => $notify_url,
			'customer'        => $billing_address,
			'billing_address' => $billing_address,
		];

		$response = wp_remote_post(
			$this->options['server_rest_url'] . 'knit-pay/v1/payments/',
			[
				'headers' => $this->get_request_headers(),
				'body'    => json_encode( $payload ),
				'timeout' => 60,
			]
		);

		// Handle error during connection.
		if ( is_wp_error( $response ) ) {
			return $this->handle_checkout_error( $payment_token, $attendee_id, $response->get_error_message() );
		}

		// Process the response.
		$result = wp_remote_retrieve_body( $response );

		$result = json_decode( $result );

		// Handle error from gateway.
		if ( isset( $result->code ) && isset( $result->message ) ) {
			return $this->handle_checkout_error( $payment_token, $attendee_id, $result->message );
		}

		$camptix->log(
			'Knit Pay payment created.',
			$attendee_id,
			[
				'camptix_payment_token' => $payment_token,
				'request_payload'       => $payload,
				'response'              => $result,
			],
			'knit-pay-connect'
		);

		update_post_meta( $attendee_id, 'knit_pay_payment_id', $result->id );

		wp_redirect( esc_url_raw( $result->pay_redirect_url ) );
		exit;
	}

	private static function get_value_from_array( $array, $var ) {
		if ( isset( $array[ $var ] ) ) {
			return reset( $array[ $var ] );
		}
		return null;
	}

	private function get_request_headers() {
		$username = $this->options['server_username'];
		$password = $this->options['server_password'];
		return [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
		];
	}

	private function handle_checkout_error( $payment_token, $attendee_id, $error_message ) {
		/** @var CampTix_Plugin $camptix */
		global $camptix;

		if ( ! empty( $error_message ) ) {
			$camptix->log( 'An error occurred in the Knit Pay connection. ' . esc_html( $error_message ), $attendee_id, [ 'payment_token' => $payment_token ], 'knit-pay-connect' );

			$camptix->error( esc_html( $error_message ) );
		}

		return $camptix->payment_result(
			$payment_token,
			CampTix_Plugin::PAYMENT_STATUS_FAILED
		);
	}
}

