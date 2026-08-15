<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_run_techx_gpo_settings',
	[
		'gpo_enabled'     => [
			'title'       => __( 'Enable/Disable', 'woocommerce-gateway-run_techx' ),
			'label'       => __( 'Enable RunTechx Multicaixa Express', 'woocommerce-gateway-run_techx' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		],
		'gpo_title'       => [
			'title'       => __( 'Title', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-run_techx' ),
			'default'     => __( 'Multicaixa Express', 'woocommerce-gateway-run_techx' ),
			'desc_tip'    => true,
		],
		'gpo_description' => [
			'title'       => __( 'Description', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-run_techx' ),
			'default'     => __( 'You have 90 seconds to authorize the payment using Multicaixa Express associated to phone number provided.', 'woocommerce-gateway-run_techx' ),
			'desc_tip'    => true,
		],
		'gpo_saved_mobile_number'	=> [
			'title'       => __( 'Save Mobile Number For Future Use', 'woocommerce-gateway-run_techx' ),
			'label'       => __( 'Save Mobile Number For Future Use', 'woocommerce-gateway-run_techx' ),
			'type'        => 'checkbox',
			'description' => __( 'Save Mobile Number For Future Use', 'woocommerce-gateway-run_techx' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		],	
		'gpo_test_client_id' => [
			'title'       => __( 'Test Client Id', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'Get Test Client Id from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'gpo_test_secret_key' => [
			'title'       => __( 'Test Client Secret', 'woocommerce-gateway-run_techx' ),
			'type'        => 'password',
			'description' => __( 'Get Client Secret from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],		
		'gpo_test_reference_key' => [
			'title'       => __( 'Test Resource', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'Get Reference Key from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'gpo_test_payment_method' => [
			'title'       => __( 'Test Payment Method', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'Get Payment Method from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'gpo_testmode'	=> [
			'title'       => __( 'Test mode', 'woocommerce-gateway-run_techx' ),
			'label'       => __( 'Enable Test Mode', 'woocommerce-gateway-run_techx' ),
			'type'        => 'checkbox',
			'description' => __( 'Place the payment gateway in test mode using test API keys.', 'woocommerce-gateway-run_techx' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		],	
		'gpo_client_id' => [
			'title'       => __( 'Live Client Id', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'Get Client Id from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'gpo_client_secret' => [
			'title'       => __( 'Live Client Secret', 'woocommerce-gateway-run_techx' ),
			'type'        => 'password',
			'description' => __( 'Get Test Client Secret from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'gpo_reference_key' => [
			'title'       => __( 'Live Resource', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'Get Reference Key from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],
		'gpo_payment_method' => [
			'title'       => __( 'Live Payment Method', 'woocommerce-gateway-run_techx' ),
			'type'        => 'text',
			'description' => __( 'Get Payment Method from your account.', 'woocommerce-gateway-run_techx' ),
			'default'     => '',
			'desc_tip'    => true,
		],	
		'webhook'     => [
			'title'       => __( 'Webhook Endpoints', 'woocommerce-gateway-stripe' ),
			'type'        => 'title',
			/* translators: webhook URL */
			'description' => $this->display_webhook_description(),
		],	
	]
);