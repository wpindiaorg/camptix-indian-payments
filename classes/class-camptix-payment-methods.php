<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Camptix_Indian_Payment_Methods {
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
	 * Setup
	 *
	 * @since  1.0
	 * @access public
	 */
	public function setup() {
		$this->setup_hooks();
	}


	/**
	 * Setup hooks
	 *
	 * @since  1.0
	 * @access private
	 */
	private function setup_hooks() {
		// Add, save and show extra fields.
		add_action( 'camptix_attendee_form_additional_info', array( $this, 'add_fields' ), 10, 3 );
		add_filter( 'camptix_form_register_complete_attendee_object', array( $this, 'add_attendee_info' ), 10, 3 );
		add_action( 'camptix_checkout_update_post_meta', array( $this, 'save_attendee_info' ), 10, 2 );
		add_filter( 'camptix_metabox_attendee_info_additional_rows', array( $this, 'show_attendee_info' ), 10, 2 );
	}

	/**
	 * Add phone field
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $form_data
	 * @param $current_count
	 * @param $tickets_selected_count
	 *
	 * @return string
	 */
	public function add_fields( $form_data, $current_count, $tickets_selected_count ) {
		ob_start();
		?>
		<tr class="tix-row-phone">
			<td class="tix-required tix-left"><?php _e( 'Phone Number', 'camptix-indian-payments' ); ?>
				<span class="tix-required-star">*</span>
			</td>
			<?php $value = isset( $form_data['tix_attendee_info'][ $current_count ]['phone'] ) ? $form_data['tix_attendee_info'][ $current_count ]['phone'] : ''; ?>
			<td class="tix-right">
				<input name="tix_attendee_info[<?php echo esc_attr( $current_count ); ?>][phone]" type="text" class="mobile" value="<?php echo esc_attr( $value ); ?>"/><br>
				<small class="message"></small>
			</td>
		</tr>
		<?php
		echo ob_get_clean();
	}


	/**
	 * Add extra attendee information
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $attendee
	 * @param $attendee_info
	 * @param $current_count
	 *
	 * @return mixed
	 */
	public function add_attendee_info( $attendee, $attendee_info, $current_count ) {
		// Phone.
		if ( ! empty( $_POST['tix_attendee_info'][ $current_count ]['phone'] ) ) {
			$attendee->phone = trim( $_POST['tix_attendee_info'][ $current_count ]['phone'] );
		}

		return $attendee;
	}


	/**
	 * Save extra attendee information
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $attendee_id
	 * @param $attendee
	 */
	public function save_attendee_info( $attendee_id, $attendee ) {
		// Phone.
		if ( property_exists( $attendee, 'phone' ) ) {
			update_post_meta( $attendee_id, 'tix_phone', $attendee->phone );
		}
	}

	/**
	 * Show extra attendee information
	 *
	 * @since 1.0
	 * access public
	 *
	 * @param $rows
	 * @param $attendee
	 *
	 * @return array
	 */
	public function show_attendee_info( $rows, $attendee ) {
		// Phone.
		if ( $attendee_phone = get_post_meta( $attendee->ID, 'tix_phone', true ) ) {
			$rows[] = array(
				__( 'Phone Number', 'camptix-indian-payments' ),
				$attendee_phone,
			);
		}

		// Receipt ID.
		if ( $receipt_id = get_post_meta( $attendee->ID, 'tix_receipt_id', true ) ) {
			$rows[] = array(
				__( 'Razorpay Receipt ID', 'camptix-indian-payments' ),
				$receipt_id,
			);
		}

		return $rows;
	}
}

Camptix_Indian_Payment_Methods::get_instance()->setup();
