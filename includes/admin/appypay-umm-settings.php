<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_appypay_umm_settings',
	[
		'umm_enabled'     => [
			'title'       => __( 'Enable/Disable', 'woocommerce-gateway-appypay' ),
			'label'       => __( 'Enable AppyPay UNITEL Mobile Money', 'woocommerce-gateway-appypay' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		],
		'umm_title'       => [
			'title'       => __( 'Title', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-appypay' ),
			'default'     => __( 'UNITEL Mobile Money', 'woocommerce-gateway-appypay' ),
			'desc_tip'    => true,
		],
		'umm_description' => [
			'title'       => __( 'Description', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-appypay' ),
			'default'     => __( 'You have 90 seconds to authorize the payment using UNITEL Mobile Money associated to phone number provided.', 'woocommerce-gateway-appypay' ),
			'desc_tip'    => true,
		],
		'umm_saved_mobile_number'	=> [
			'title'       => __( 'Save Mobile Number For Future Use', 'woocommerce-gateway-appypay' ),
			'label'       => __( 'Save Mobile Number For Future Use', 'woocommerce-gateway-appypay' ),
			'type'        => 'checkbox',
			'description' => __( 'Save Mobile Number For Future Use', 'woocommerce-gateway-appypay' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		],					
		'umm_test_client_id' => [
			'title'       => __( 'Test Client Id', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get Test Client Id from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'umm_test_secret_key' => [
			'title'       => __( 'Test Client Secret', 'woocommerce-gateway-appypay' ),
			'type'        => 'password',
			'description' => __( 'Get Client Secret from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],		
		'umm_test_reference_key' => [
			'title'       => __( 'Test Resource', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get Reference Key from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'umm_test_payment_method' => [
			'title'       => __( 'Test Payment Method', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get Payment Method from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'umm_testmode'	=> [
			'title'       => __( 'Test mode', 'woocommerce-gateway-appypay' ),
			'label'       => __( 'Enable Test Mode', 'woocommerce-gateway-appypay' ),
			'type'        => 'checkbox',
			'description' => __( 'Place the payment gateway in test mode using test API keys.', 'woocommerce-gateway-appypay' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		],
		'umm_client_id' => [
			'title'       => __( 'Live Client Id', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get Client Id from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'umm_client_secret' => [
			'title'       => __( 'Live Client Secret', 'woocommerce-gateway-appypay' ),
			'type'        => 'password',
			'description' => __( 'Get Test Client Secret from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'umm_reference_key' => [
			'title'       => __( 'Live Resource', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get Reference Key from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'umm_payment_method' => [
			'title'       => __( 'Live Payment Method', 'woocommerce-gateway-appypay' ),
			'type'        => 'text',
			'description' => __( 'Get Payment Method from your account.', 'woocommerce-gateway-appypay' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'webhook'     => [
			'title'       => __( 'Webhook Endpoints', 'woocommerce-gateway-stripe' ),
			'type'        => 'title',
			/* translators: webhook URL */
			'description' => $this->display_umm_webhook_description(),
		],			
	]
);