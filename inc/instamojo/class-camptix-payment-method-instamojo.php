<?php

/**
 * CampTix Instamojo Payment Method
 *
 * This class handles all Instamojo integration for CampTix
 *
 * @category       Class
 * @package        Camptix Instamojo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class CampTix_Payment_Method_Instamojo extends CampTix_Payment_Method {
	public $id = 'instamojo';
	public $name = 'Instamojo';
	public $description = 'Redefining Payments, Simplifying Lives! Empowering any business to collect money online within minutes.';
	public $supported_currencies = array( 'INR' );

	/**
	 * We can have an array to store our options.
	 * Use $this->get_payment_options() to retrieve them.
	 */
	
	protected $options = array();

	function camptix_init() {
		$this->options = array_merge( array(

			'Instamojo-Api-Key'    => '',
			'Instamojo-Auth-Token' => '',
			'Instamojo-salt'       => '',

			'sandbox' => true,
		), $this->get_payment_options() );

		// IPN Listener
		//add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		if ( $this->is_gateway_enable() ) {
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			//add_action( 'camptix_attendee_form_additional_info', array( $this, 'add_phone_field' ), 10, 3 );
			add_filter( 'camptix_form_register_complete_attendee_object', array( $this, 'add_attendee_info' ), 10, 3 );
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
		// code change by me start
		$this->add_settings_field_helper( __('Instamojo-Api-Key', 'campt-indian-payment-gateway'), 'Instamojo Api KEY', array( $this, 'field_text' ) );
		$this->add_settings_field_helper( __('Instamojo-Auth-Token', 'campt-indian-payment-gateway'), 'Instamojo Auth Token', array( $this, 'field_text' ) );
		$this->add_settings_field_helper( __('Instamojo-salt', 'campt-indian-payment-gateway'), 'Instamojo Salt', array( $this, 'field_text' ) );
		

		$this->add_settings_field_helper( 'sandbox', __( 'Sandbox Mode', 'campt-indian-payment-gateway' ), array( $this, 'field_yesno' ),
			__( "The Test Mode is a way to test payments. Any amount debited from your account should be re-credited within Five (5) working days.", 'campt-indian-payment-gateway' )
		);
	}

	function validate_options( $input ) {
		$output = $this->options;

		if ( isset( $input['Instamojo-Api-Key'] ) ) {
			$output['Instamojo-Api-Key'] = $input['Instamojo-Api-Key'];
		}
		if ( isset( $input['Instamojo-Auth-Token'] ) ) {
			$output['Instamojo-Auth-Token'] = $input['Instamojo-Auth-Token'];
		}
		if ( isset( $input['Instamojo-salt'] ) ) {
			$output['Instamojo-salt'] = $input['Instamojo-salt'];
		}


		if ( isset( $input['sandbox'] ) ) {
			$output['sandbox'] = (bool) $input['sandbox'];
		}

		return $output;
	}

	function template_redirect() {
		if ( ! isset( $_REQUEST['tix_payment_method'] ) || 'instamojo' != $_REQUEST['tix_payment_method'] ) {
			return;
		}

		if ( isset( $_GET['tix_action'] ) ) {
			if ( 'payment_cancel' == $_GET['tix_action'] ) {
				$this->payment_cancel();
			}

			if ( 'payment_return' == $_GET['tix_action'] ) {
				$this->payment_return();
			}

			if ( 'payment_notify' == $_GET['tix_action'] ) {
				$this->payment_notify();
			}
		}
	}

	function payment_return() {
		global $camptix;

		$this->log( sprintf( 'Running payment_return. Request data attached.' ), null, $_REQUEST );
		$this->log( sprintf( 'Running payment_return. Server data attached.' ), null, $_SERVER );


		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';
		$payment_id    = ( isset( $_REQUEST['payment_id'] ) ) ? trim( $_REQUEST['payment_id'] ) : '';


		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';
		if ( empty( $payment_token ) ) {
			return;
		}

		$attendees = get_posts(
			array(
				'posts_per_page' => 1,
				'post_type'      => 'tix_attendee',
				'post_status'    => array( 'draft', 'pending', 'publish', 'cancel', 'refund', 'failed' ),
				'meta_query'     => array(
					array(
						'key'     => 'tix_payment_token',
						'compare' => '=',
						'value'   => $payment_token,
						'type'    => 'CHAR',
					),
				),
			)
		);

		if ( empty( $attendees ) ) {
			return;
		}

		$attendee = reset( $attendees );

		if ( 'draft' == $attendee->post_status ) {
			return $this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_PENDING );
		} else {
			$access_token = get_post_meta( $attendee->ID, 'tix_access_token', true );
			$url          = add_query_arg( array(
				'tix_action'       => 'access_tickets',
				'tix_access_token' => $access_token,
			), $camptix->get_tickets_url() );

			wp_safe_redirect( esc_url_raw( $url . '#tix' ) );
			die();
		}
	}

	/**
	 * Runs when Instamjo Money sends an ITN signal.
	 * Verify the payload and use $this->payment_result
	 * to signal a transaction result back to CampTix.
	 */
	function payment_notify() {
		global $camptix;

		$this->log( sprintf( 'Running payment_notify. Request data attached.' ), null, $_REQUEST );
		$this->log( sprintf( 'Running payment_notify. Server data attached.' ), null, $_SERVER );

		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';

		$payload = stripslashes_deep( $_REQUEST );

		/*
		Basic PHP script to handle Instamojo RAP webhook.
		*/

		$instamojo_key   = $this->options['Instamojo-Api-Key'];
		$instamojo_token = $this->options['Instamojo-Auth-Token'];
		$instamojo_salt  = $this->options['Instamojo-salt'];


		$data         = $_POST;
		$mac_provided = $data['mac'];  // Get the MAC from the POST data
		unset( $data['mac'] );  // Remove the MAC key from the data.
		$ver   = explode( '.', phpversion() );
		$major = (int) $ver[0];
		$minor = (int) $ver[1];
		if ( $major >= 5 and $minor >= 4 ) {
			ksort( $data, SORT_STRING | SORT_FLAG_CASE );
		} else {
			uksort( $data, 'strcasecmp' );
		}
		// You can get the 'salt' from Instamojo's developers page(make sure to log in first): https://www.instamojo.com/developers
		// Pass the 'salt' without <>
		$mac_calculated = hash_hmac( "sha1", implode( "|", $data ), $instamojo_salt );
		if ( $mac_provided == $mac_calculated ) {
			if ( $data['status'] == "Credit" ) {
				// Payment was successful, mark it as successful in your database.
				// You can acess payment_request_id, purpose etc here.
				$this->payment_result( $_REQUEST['tix_payment_token'], CampTix_Plugin::PAYMENT_STATUS_COMPLETED );
				$abcd = $this->payment_result( $_REQUEST['tix_payment_token'] );

			} else {
				// Payment was unsuccessful, mark it as failed in your database.
				// You can acess payment_request_id, purpose etc here.
				$this->payment_result( $_REQUEST['tix_payment_token'], CampTix_Plugin::PAYMENT_STATUS_FAILED );
			}
		} else {
			$this->payment_result( $_REQUEST['tix_payment_token'], CampTix_Plugin::PAYMENT_STATUS_PENDING );
		}
		
	}

	public function payment_checkout( $payment_token ) {

		if ( ! $payment_token || empty( $payment_token ) ) {
			return false;
		}

		if ( ! in_array( $this->camptix_options['currency'], $this->supported_currencies ) )
			die( __( 'The selected currency is not supported by this payment method.', 'campt-indian-payment-gateway' ) );

		$return_url = add_query_arg( array(
			'tix_action'         => 'payment_return',
			'tix_payment_token'  => $payment_token,
			'tix_payment_method' => 'instamojo',
		), $this->get_tickets_url() );


		$cancel_url = add_query_arg( array(
			'tix_action'         => 'payment_cancel',
			'tix_payment_token'  => $payment_token,
			'tix_payment_method' => 'instamojo',
		), $this->get_tickets_url() );

		$notify_url = add_query_arg( array(
			'tix_action'         => 'payment_notify',
			'tix_payment_token'  => $payment_token,
			'tix_payment_method' => 'instamojo',
		), $this->get_tickets_url() );

		$order = $this->get_order( $payment_token );

		$instamojo_key   = $this->options['Instamojo-Api-Key'];
		$instamojo_token = $this->options['Instamojo-Auth-Token'];
		$instamojo_salt  = $this->options['Instamojo-salt'];

		$order_amount = $order['total'];
		if ( isset( $this->camptix_options['event_name'] ) ) {
			$productinfo = $this->camptix_options['event_name'];
		} else {
			$productinfo = 'Ticket for Order - ' . $payment_token;
		}

		$attendees = get_posts(
			array(
				'post_type'   => 'tix_attendee',
				'post_status' => 'any',
				'orderby'     => 'ID',
				'order'       => 'ASC',
				'meta_query'  => array(
					array(
						'key'     => 'tix_payment_token',
						'compare' => '=',
						'value'   => $payment_token,
					),
				),
			)
		);

		foreach ( $attendees as $attendee ) {
			$tix_id             = get_post( get_post_meta( $attendee->ID, 'tix_ticket_id', true ) );
			$attendee_questions = get_post_meta( $attendee->ID, 'tix_questions', true ); // Array of Attendee Questons

			$email = $attendee->tix_email;
			$name  = $attendee->tix_first_name . ' ' . $attendee->tix_last_name;

		}


		$info       = $this->get_order( $payment_token );
		$extra_info = array(
			'phone' => get_post_meta( $info['attendee_id'], 'tix_phone', true ),

		);

		$url = $this->options['sandbox'] ? 'https://test.instamojo.com/api/1.1/payment-requests/' : 'https://www.instamojo.com/api/1.1/payment-requests/';

		$payload = Array(
			'purpose'                 => $productinfo,
			'amount'                  => $order_amount,
			'phone'                   => $extra_info['phone'],
			'buyer_name'              => $name,
			'redirect_url'            => $return_url,
			'send_email'              => false,
			'webhook'                 => $notify_url,
			'send_sms'                => false,
			'email'                   => $email,
			'allow_repeated_payments' => false,
		);


		$params = array(
			'method' => 'POST',
			'sslverify' => true,
			'timeout'   => 60,
			'headers'   => array(

				'Accept'       => 'application/json',
				'Content-Type' => 'application/json;charset=UTF-8',
				'X-Api-Key'  =>  $instamojo_key,
				'X-Auth-Token' => $instamojo_token
			),
			'body' => json_encode($payload)

		);


		// GET a response.
		$response = wp_remote_post( $url, $params );
		
		// Check to see if the request was valid.
		if ( ! is_wp_error( $response )  ) {

			$json_decode = json_decode( $response['body']);
			$long_url = $json_decode->payment_request->longurl;
			header( 'Location:' . $long_url );
		}
		echo 'Invalid Insatmojo Access Key & Token';

		return;
	}

	/**
	 * Runs when the user cancels their payment during checkout at PayPal.
	 * his will simply tell CampTix to put the created attendee drafts into to Cancelled state.
	 */
	function payment_cancel() {
		global $camptix;

		$this->log( sprintf( 'Running payment_cancel. Request data attached.' ), null, $_REQUEST );
		$this->log( sprintf( 'Running payment_cancel. Server data attached.' ), null, $_SERVER );

		$payment_token = ( isset( $_REQUEST['tix_payment_token'] ) ) ? trim( $_REQUEST['tix_payment_token'] ) : '';

		if ( ! $payment_token ) {
			die( 'empty token' );
		}

		// Set the associated attendees to cancelled.
		return $this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_CANCELLED );
	}
}

?>