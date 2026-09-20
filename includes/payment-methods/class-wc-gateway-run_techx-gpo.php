<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class that handles Multicaixa Express payment method.
 */
class WC_Gateway_GPO extends WC_Payment_Gateway
{
	const ID = 'appypay_gpo';


	public $notices = [];
	public $test_mode;
	public $client_id;
	public $client_secret;
	public $saved_phone;
	public $reference_key;

	public $order_status;

	public function __construct()
	{
		$this->id = self::ID;
		$this->method_title = __('AppyPay Multicaixa Express (GPO)', 'woocommerce-gateway-appypay');
		$this->method_description = __('Allows payments with custom gateway.', 'woocommerce-gateway-appypay');
		$this->init_form_fields();
		$this->init_settings();
		$this->has_fields = true;
		$this->icon = apply_filters('woocommerce_custom_gateway_icon', WC_RUNTECHX_PLUGIN_URL . "/images/" . $this->id . ".png");

		$this->title = $this->get_option('gpo_title');
		$this->description = $this->get_option('gpo_description');
		$this->enabled = $this->get_option('gpo_enabled');
		$this->test_mode = $this->get_option('gpo_testmode');
		$this->saved_phone = $this->get_option('gpo_saved_mobile_number');
		$this->client_id = $this->get_option('gpo_client_id');
		$this->client_secret = $this->get_option('gpo_client_secret');
		$this->reference_key = $this->get_option('gpo_reference_key');
		$this->order_status = $this->get_option('order_status', 'wc-processing');





		if ($this->test_mode) {
			$this->client_id = $this->get_option('gpo_test_client_id');
			$this->client_secret = $this->get_option('gpo_test_secret_key');
			$this->reference_key = $this->get_option('gpo_test_reference_key');

		}
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
		add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
	}
	public function init_form_fields()
	{
		$this->form_fields = require WC_RUNTECHX_PLUGIN_PATH . '/includes/admin/runtechx-gpo-settings.php';
	}

	public function payment_fields()
	{
		echo wpautop(wptexturize($this->get_description()));

		?>
		<div id="custom_input">
			<p class="form-row form-row-wide">
				<input type="text" class="input-text" name="gpo_mobile" id="gpo_mobile"
					placeholder="<?php _e('Mobile Number', 'woocommerce-gateway-appypay'); ?>" value="">
			</p>
		</div>
		<?php
	}
	public function display_webhook_description()
	{
		$description = __('You must add the following webhook endpoint <b>' . site_url("/wp-json/getorderstatus/v1/getstatus") . '</b> to your AppyPay account settings ', 'woocommerce-gateway-stripe');
		return $description;
	}
	public function process_payment($order_id)
	{
		if (isset($_POST['payment_method']) && $_POST['payment_method'] !== self::ID) {
			return;
		}

		$order = wc_get_order($order_id);
		$ordertotal = $order->get_total();

		$gpo_setting = get_option('woocommerce_appypay_gpo_settings');

		if ($gpo_setting['gpo_testmode'] === "yes") {
			$client_id = $gpo_setting['gpo_test_client_id'];
			$client_secret = $gpo_setting['gpo_test_secret_key'];
		} else {
			$client_id = $gpo_setting['gpo_client_id'];
			$client_secret = $gpo_setting['gpo_client_secret'];
		}

		$address = ! empty($gpo_setting['gpo_api_address']) ? untrailingslashit($gpo_setting['gpo_api_address']) : 'http://localhost:8081';
		$iframe_base = ! empty($gpo_setting['gpo_iframe_url']) ? $gpo_setting['gpo_iframe_url'] : 'https://cerpagamentonline.emis.co.ao/online-payment-gateway/webframe/frame/invalid';

		$postfield = [
			"order_id" => (string) $order_id,
			"total_amount" => floatval(sprintf('%0.2f', $ordertotal)),
			"client_secret" => $client_secret,
			"client_id" => $client_id,
		];

		$post_response = wp_remote_post($address . '/payment-order', [
			'timeout' => WC_RUNTECHX_TIMEOUT,
			'headers' => ['Content-Type' => 'application/json'],
			'body' => wp_json_encode($postfield),
		]);

		if (is_wp_error($post_response) || wp_remote_retrieve_response_code($post_response) >= 300) {
			$order->update_status('failed', __('Could not submit the payment order to GPO.', 'woocommerce-gateway-run_techx'));
			wc_add_notice(__('Payment could not be started. Please try again.', 'woocommerce-gateway-run_techx'), 'error');
			return;
		}

		$order->update_meta_data('order_id', (string) $order_id);
		$order->save();

		sleep(WC_RUNTECHX_GPO_STATUS_DELAY);

		$status_response = wp_remote_get($address . '/payment-order/' . rawurlencode($order_id), [
			'timeout' => WC_RUNTECHX_TIMEOUT,
		]);

		if (is_wp_error($status_response) || wp_remote_retrieve_response_code($status_response) >= 300) {
			$order->update_status('failed', __('Could not confirm the payment order status with GPO.', 'woocommerce-gateway-run_techx'));
			wc_add_notice(__('Payment could not be confirmed. Please try again.', 'woocommerce-gateway-run_techx'), 'error');
			return;
		}

		$status_data = json_decode(wp_remote_retrieve_body($status_response), true);
		$payment_link = isset($status_data['payment_link']) ? $status_data['payment_link'] : '';

		if (empty($payment_link)) {
			$order->update_status('failed', __('GPO did not return a payment link.', 'woocommerce-gateway-run_techx'));
			wc_add_notice(__('Payment could not be started. Please try again.', 'woocommerce-gateway-run_techx'), 'error');
			return;
		}

		$iframe_url = add_query_arg('token', rawurlencode($payment_link), $iframe_base);

		$order->update_meta_data('gpo_status', isset($status_data['status']) ? $status_data['status'] : '');
		$order->update_meta_data('gpo_payment_link', $payment_link);
		$order->update_meta_data('gpo_iframe_url', $iframe_url);

		if ($gpo_setting['gpo_saved_mobile_number'] === 'yes' && ! empty($_POST['gpo_mobile'])) {
			$order->update_meta_data('mobile', sanitize_text_field(wp_unslash($_POST['gpo_mobile'])));
		}

		$order->update_status('on-hold', isset($status_data['current_status']) ? $status_data['current_status'] : __('Awaiting confirmation from GPO.', 'woocommerce-gateway-run_techx'));
		$order->save();

		return array(
			'result' => 'success',
			'redirect' => $order->get_checkout_payment_url(true),
		);
	}

	public function receipt_page($order_id)
	{
		$order = wc_get_order($order_id);
		$iframe_url = $order->get_meta('gpo_iframe_url');

		if (empty($iframe_url)) {
			echo '<p>' . esc_html__('Unable to load the payment page. Please contact the store.', 'woocommerce-gateway-run_techx') . '</p>';
			return;
		}

		echo '<div id="gpo-payment-frame">';
		echo '<iframe src="' . esc_url($iframe_url) . '" width="100%" height="600" frameborder="0" allow="payment"></iframe>';
		echo '</div>';
	}
}