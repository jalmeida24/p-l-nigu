<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles Multicaixa Express payment method.
 */
class WC_Gateway_GPO extends WC_Payment_Gateway{
	const ID = 'run_techx_gpo';

	
	public $notices = [];
	public $test_mode;
	public $client_id;
	public $client_secret;
	public $saved_phone;
	public function __construct() {
		$this->id                 = self::ID;
		$this->method_title       = __( 'RunTechx Multicaixa Express (GPO)', 'woocommerce-gateway-run_techx' );		
		$this->method_description = __('Allows payments with custom gateway.', 'woocommerce-gateway-run_techx');
		$this->init_form_fields();
		$this->init_settings();
		$this->has_fields = true;
		$this->icon = apply_filters('woocommerce_custom_gateway_icon',  WC_RUNTECHX_PLUGIN_URL."/images/".$this->id.".png");

		$this->title                = $this->get_option( 'gpo_title' );
		$this->description          = $this->get_option( 'gpo_description' );
		$this->enabled              = $this->get_option( 'gpo_enabled' );
		$this->test_mode            = $this->get_option( 'gpo_testmode' );
		$this->saved_phone          = $this->get_option( 'gpo_saved_mobile_number' );
		$this->client_id      		= $this->get_option( 'gpo_client_id' );
		$this->client_secret        = $this->get_option( 'gpo_client_secret' );
		$this->reference_key = $this->get_option('gpo_reference_key');
		$this->order_status = $this->get_option('order_status', 'wc-processing');
		


		

		if ( $this->test_mode ) {
			$this->client_id		= $this->get_option('gpo_test_client_id');
			$this->client_secret    = $this->get_option('gpo_test_secret_key');
			$this->reference_key = $this->get_option('gpo_test_reference_key');

		}
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ]);		
	}
	public function init_form_fields() {
		$this->form_fields = require WC_RUNTECHX_PLUGIN_PATH . '/includes/admin/run_techx-gpo-settings.php';
	}
	
	public function payment_fields()
        {   
			echo wpautop(wptexturize($this->get_description()));
         
    ?>
<div id="custom_input">
    <p class="form-row form-row-wide">
        <input type="text" class="input-text" name="gpo_mobile" id="gpo_mobile"
            placeholder="<?php _e('Mobile Number', 'woocommerce-gateway-run_techx'); ?>" value="">
    </p>
</div>
<?php
		}
		public function display_webhook_description() {
				$description = __( 'You must add the following webhook endpoint <b>'.site_url("/wp-json/getorderstatus/v1/getstatus").'</b> to your RunTechx account settings ', 'woocommerce-gateway-stripe' );
				return $description;
			}
	public function getRandNum($len) { 
		$str = mt_rand(1,9);
		for($i=0;$i<$len-1;$i++) { 
			$str .= mt_rand(0, 9);
    	}
    	return $str;
	}
		
public function process_payment($order_id)
	{
		
		if ($_POST['payment_method'] != 'run_techx_gpo') {
			return;
		}

		if (!isset($_POST['gpo_mobile']) || empty($_POST['gpo_mobile'])) {
			wc_add_notice(__('Please add your mobile number', 'woocommerce-gateway-run_techx'), 'error');
		}


		$order = wc_get_order($order_id);
		$ordertotal = $order->get_total();
		//exit;

		$gpo_setting = get_option('woocommerce_run_techx_gpo_settings');
		
		if($gpo_setting['gpo_testmode']==="yes")
		{
			$paymentlink = WC_RUNTECHX_PAY_LINK_TEST;
			$authlink = WC_RUNTECHX_AUTH_LINK_TEST;
			$client_id = $gpo_setting['gpo_test_client_id'];
			$client_secret = $gpo_setting['gpo_test_secret_key'];
			$reference_key = $gpo_setting['gpo_test_reference_key'];
			$gpo_payment_method = $gpo_setting['gpo_test_payment_method'];
		}
		else
		{
			$paymentlink = WC_RUNTECHX_PAY_LINK_PROD;
			$authlink = WC_RUNTECHX_AUTH_LINK_PROD;
			$client_id = $gpo_setting['gpo_client_id'];
			$client_secret = $gpo_setting['gpo_client_secret'];
			$reference_key = $gpo_setting['gpo_reference_key'];
			$gpo_payment_method = $gpo_setting['gpo_payment_method'];				
		}
	$gpo_saved_mobile_number = $gpo_setting['gpo_saved_mobile_number'];	
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $authlink,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id='.$client_id.'&client_secret='.$client_secret.'&resource='.$reference_key,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded',
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response, true);
	//	echo "<pre>";print_r($response);echo "</pre>";
		if(isset($response['access_token']))
		{
			$string = $this->getRandNum(14);
			//$string = bin2hex($bytes);


			$postfield = [
				"capture"=> true,
				"amount"=> floatval(sprintf('%0.2f', $ordertotal)),
				"orderOrigin"=> 0,
				"paymentMethod"=> $gpo_payment_method,
				"description"=> "POSTMAN",
				"merchantTransactionId"=> $string,
				"paymentInfo"=> ["phoneNumber"=> $_POST['gpo_mobile']]
			];
			
			$body = wp_json_encode($postfield);

			ignore_user_abort(true);

			$curlpayment = curl_init();


			curl_setopt_array($curlpayment, [
				CURLOPT_URL => $paymentlink,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 90,				
  				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_HTTPHEADER => [
					"Accept: application/json",
					"Authorization: Bearer ".$response['access_token']."",					
					"Content-Type: application/json"
				],
				]);

			$responsepayment = curl_exec($curlpayment);
			$err = curl_error($curlpayment);
			$info = curl_getinfo($curlpayment);
            $responsepayment = json_decode($responsepayment, true);
			$responsemessage = $responsepayment['responseStatus']['message'];
            
			if($err || $info['http_code']=="401")
			{
				$info = curl_getinfo($curlpayment);
				if ($info['total_time'] >= WC_RUNTECHX_TIMEOUT) {					
					$order->update_status("cancelled", __($responsemessage, 'woocommerce-gateway-run_techx'));					
				}
				else
				{
					$order->update_status("failed", __('Checkout with GPO payment. waiting for confirmation', 'woocommerce-gateway-run_techx'));

				}
				curl_close($curlpayment);
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order),
				);

			} 
			else {
				
				
            	$status = $responsepayment['responseStatus']['successful'];
                $code = $responsepayment['responseStatus']['code'];
				$ord_id = $responsepayment['id'];
                $responsemessage = $responsepayment['responseStatus']['message'];
				

				if(!empty($ord_id) && isset($ord_id))
				{
					curl_close($curlpayment);

					if($status)
                    {
                    	$order->update_status("completed", __($responsemessage, 'woocommerce-gateway-run_techx'));
                    }
                    else if(!$status)
                    {
                    $order->update_status("cancelled", __($responsemessage, 'woocommerce-gateway-run_techx'));
                    }
                    else
                    {                    	
                   		$order->update_status("failed", __($responsemessage, 'woocommerce-gateway-run_techx'));
					}
					update_post_meta($order_id, 'merchant_id', $string);
					update_post_meta($order_id, 'order_id', $ord_id);
					if($gpo_saved_mobile_number)
					{
						update_post_meta($order_id, 'mobile', $_POST['gpo_mobile']);
					}
					$order->reduce_order_stock();
					WC()->cart->empty_cart();
					return array(
						'result' => 'success',
						'redirect' => $this->get_return_url($order),
					);


				}				
			}
		}
	}
}