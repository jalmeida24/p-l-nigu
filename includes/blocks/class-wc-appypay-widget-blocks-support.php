<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Registers the embedded payment widget gateway with the WooCommerce Cart & Checkout blocks.
 */
class WC_AppyPay_Widget_Blocks_Support extends AbstractPaymentMethodType {

	private $gateway;

	public function initialize() {
		$this->name     = WC_Gateway_Widget::ID;
		$this->settings = get_option( 'woocommerce_appypay_widget_settings', [] );
		$this->gateway  = new WC_Gateway_Widget();
	}

	public function is_active() {
		return $this->gateway->is_available();
	}

	public function get_payment_method_script_handles() {
		$handle = 'wc-appypay-widget-blocks';

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script(
				$handle,
				WC_RUNTECHX_PLUGIN_URL . '/assets/js/blocks/widget.js',
				[ 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ],
				'1.0.0',
				true
			);
			wp_set_script_translations( $handle, 'woocommerce-gateway-appypay' );
		}

		return [ $handle ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->gateway->title,
			'description' => $this->gateway->description,
			'icon'        => $this->gateway->icon,
		];
	}
}
