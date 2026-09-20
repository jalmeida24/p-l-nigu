<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the AppyPay embedded payment widget method.
 *
 * Unlike GPO/UMM, this method does not collect a mobile number on checkout.
 * It redirects the customer to the WooCommerce "Pay for order" page, where
 * the official AppyPay charges widget script is embedded; the widget itself
 * performs the request to the AppyPay API and renders the payment UI.
 * Order status is finalized asynchronously via the existing
 * getorderstatus/v1/getstatus webhook, same as GPO.
 */
class WC_Gateway_Widget extends WC_Payment_Gateway {
	const ID = 'appypay_widget';

	public $test_mode;
	public $widget_script_url;
	public $payment_method;

	public function __construct() {
		$this->id                 = self::ID;
		$this->method_title       = __( 'AppyPay Payment Widget', 'woocommerce-gateway-appypay' );
		$this->method_description = __( 'Allows payments through the embedded AppyPay payment widget.', 'woocommerce-gateway-appypay' );
		$this->init_form_fields();
		$this->init_settings();
		$this->has_fields = false;
		$this->icon        = apply_filters( 'woocommerce_custom_gateway_icon', WC_RUNTECHX_PLUGIN_URL . "/images/" . $this->id . ".png" );

		$this->title             = $this->get_option( 'widget_title' );
		$this->description       = $this->get_option( 'widget_description' );
		$this->enabled           = $this->get_option( 'widget_enabled' );
		$this->test_mode         = $this->get_option( 'widget_testmode' );
		$this->widget_script_url = $this->get_option( 'widget_script_url' );
		$this->payment_method    = $this->get_option( 'widget_payment_method' );

		if ( $this->test_mode ) {
			$this->payment_method = $this->get_option( 'widget_test_payment_method' );
		}

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_receipt_' . $this->id, [ $this, 'receipt_page' ] );
	}

	public function init_form_fields() {
		$this->form_fields = require WC_RUNTECHX_PLUGIN_PATH . '/includes/admin/appypay-widget-settings.php';
	}

	public function display_webhook_description() {
		$description = __( 'You must add the following webhook endpoint <b>' . site_url( "/wp-json/getorderstatus/v1/getstatus" ) . '</b> to your AppyPay account settings ', 'woocommerce-gateway-stripe' );
		return $description;
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->update_status( 'on-hold', __( 'Waiting for confirmation via AppyPay payment widget.', 'woocommerce-gateway-appypay' ) );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}

	public function receipt_page( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $this->widget_script_url ) || empty( $this->payment_method ) ) {
			echo '<p>' . esc_html__( 'AppyPay payment widget is not fully configured. Please contact the store.', 'woocommerce-gateway-appypay' ) . '</p>';
			return;
		}

		echo '<div id="appypay-widget">';
		echo '<script async id="appypay-charges-widget-v2"'
			. ' amount="' . esc_attr( number_format( $order->get_total(), 2, '.', '' ) ) . '"'
			. ' paymentDescription="' . esc_attr( $order->get_order_number() ) . '"'
			. ' referenceNumber="' . esc_attr( $order_id ) . '"'
			. ' paymentMethod="' . esc_attr( $this->payment_method ) . '"'
			. ' requestType="sync"'
			. ' lang="pt-PT"'
			. ' src="' . esc_url( $this->widget_script_url ) . '"'
			. ' redirectURI="' . esc_url( $this->get_return_url( $order ) ) . '"'
			. '></script>';
		echo '<div id="appypay-charges-v2"></div>';
		echo '</div>';
	}
}
