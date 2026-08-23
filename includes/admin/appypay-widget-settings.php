<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_appypay_widget_settings',
	[
		'widget_enabled'     => [
			'title'       => __( 'Enable/Disable', 'woocommerce-gateway-appypay' ),
			'label'       => __( 'Enable AppyPay Payment Widget', 'woocommerce-gateway-appypay' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		],
		'widget_title'       => [
			'title'       => __( 'Title', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-appypay' ),
			'default'     => __( 'AppyPay Payment', 'woocommerce-gateway-appypay' ),
			'desc_tip'    => true,
		],
		'widget_description' => [
			'title'       => __( 'Description', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-appypay' ),
			'default'     => __( 'You will complete your payment on the next step using the AppyPay payment widget.', 'woocommerce-gateway-appypay' ),
			'desc_tip'    => true,
		],
		'widget_script_url'  => [
			'title'       => __( 'Widget Script URL', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'URL of the AppyPay charges widget script, provided by AppyPay. Confirm this with your AppyPay account before going live.', 'woocommerce-gateway-appypay' ),
			'default'     => 'https://appypay.co.ao/assets/chargesWidgetV1_2/main.js',
			'desc_tip'    => true,
		],
		'widget_testmode'    => [
			'title'       => __( 'Test mode', 'woocommerce-gateway-appypay' ),
			'label'       => __( 'Enable Test Mode', 'woocommerce-gateway-appypay' ),
			'type'        => 'checkbox',
			'description' => __( 'Place the payment gateway in test mode using test payment method identifier.', 'woocommerce-gateway-appypay' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		],
		'widget_test_payment_method' => [
			'title'       => __( 'Test Payment Method', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get the Payment Method identifier for test mode from your AppyPay account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'widget_payment_method' => [
			'title'       => __( 'Live Payment Method', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get the Payment Method identifier for live mode from your AppyPay account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'webhook'            => [
			'title'       => __( 'Webhook Endpoints', 'woocommerce-gateway-stripe' ),
			'type'        => 'title',
			/* translators: webhook URL */
			'description' => $this->display_webhook_description(),
		],
	]
);
