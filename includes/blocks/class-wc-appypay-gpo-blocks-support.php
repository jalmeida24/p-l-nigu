<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Registers the GPO gateway with the WooCommerce Cart & Checkout blocks.
 */
class WC_AppyPay_GPO_Blocks_Support extends AbstractPaymentMethodType {

	private $gateway;

	public function initialize() {
		$this->name     = WC_Gateway_GPO::ID;
		$this->settings = get_option( 'woocommerce_appypay_gpo_settings', [] );
		$this->gateway  = new WC_Gateway_GPO();
	}

	public function is_active() {
		return $this->gateway->is_available();
	}

	public function get_payment_method_script_handles() {
		$handle = 'wc-appypay-gpo-blocks';

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script(
				$handle,
				WC_APPYPAY_PLUGIN_URL . '/assets/js/blocks/gpo.js',
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
			'title'              => $this->gateway->title,
			'description'        => $this->gateway->description,
			'icon'               => $this->gateway->icon,
			'mobileLabel'        => __( 'Mobile Number', 'woocommerce-gateway-appypay' ),
			'mobileRequiredText' => __( 'Please add your mobile number', 'woocommerce-gateway-appypay' ),
		];
	}
}
