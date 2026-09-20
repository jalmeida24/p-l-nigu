<?php
/**
 * Plugin Name: Gateway Pagamentos GPO Woocommerce Angola
 * Plugin URI: 
 * Description: Take payments on your store using RunTechx.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Version: 3.1.2
 * Requires at least: 5.7
 * Tested up to: 6.0
 * WC requires at least: 6.2
 * WC tested up to: 6.7
 * Text Domain: woocommerce-gateway-run_techx
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required minimums and constants
 */
define( 'WC_RUNTECHX_MIN_PHP_VER', '7.3.0' );
define('PAYVERSION', 'v1.2');

define( 'WC_RUNTECHX_MIN_WC_VER', '6.2' );
define( 'WC_RUNTECHX_FUTURE_MIN_WC_VER', '6.3' );
define( 'WC_RUNTECHX_MAIN_FILE', __FILE__ );
define( 'WC_RUNTECHX_ABSPATH', __DIR__ . '/' );
define('WC_RUNTECHX_TIMEOUT', 90);
define('WC_RUNTECHX_GPO_STATUS_DELAY', 2);

define( 'WC_RUNTECHX_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_RUNTECHX_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_RUNTECHX_AUTH_LINK_TEST', "https://login.microsoftonline.com/run_techxdev.onmicrosoft.com/oauth2/token");
define( 'WC_RUNTECHX_AUTH_LINK_PROD', "https://login.microsoftonline.com/run_techx.co.ao/oauth2/token");
define('WC_RUNTECHX_PAY_LINK_TEST', "https://app-run_techx-api-tst.azurewebsites.net/".PAYVERSION."/charges");
define('WC_RUNTECHX_PAY_LINK_PROD', "https://api.run_techx.co.ao/".PAYVERSION."/charges");
ini_set("max_execution_time", 3600);
ini_set("max_input_time", 3600);

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );


function woocommerce_run_techx_missing_wc_notice() {
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'RunTechx requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-gateway-run_techx' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

function woocommerce_run_techx_wc_not_supported() {
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'RunTechx requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'woocommerce-gateway-run_techx' ), WC_STRIPE_MIN_WC_VER, WC_VERSION ) . '</strong></p></div>';
}
function woocommerce_run_techx_php_not_supported() {
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'RunTechx requires PHP %1$s or greater to work plugin smoothly.', 'woocommerce-gateway-run_techx' ), WC_RUNTECHX_MIN_PHP_VER ) . '</strong></p></div>';
}


function woocommerce_gateway_run_techx() {
	
	static $plugin;

	if ( ! isset( $plugin ) ) {

		class WC_RunTechx {

			
			private static $instance;

			
			public static function get_instance() {
				if ( null === self::$instance ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			public function __clone() {}

			
			public function __wakeup() {}

			
			public function __construct() {
				
				$this->init();
				

				add_action( 'rest_api_init', [ $this, 'register_routes' ] );
				add_action('wp_enqueue_scripts', [$this, 'add_scripts_css']);
				add_action('wp_head', [$this,'run_techx_woocommerce_checkout_spinner'], 1000);
				add_filter( 'woocommerce_thankyou_order_received_text', [$this,'d4tw_custom_ty_msg'],20,2);
				add_action( 'woocommerce_blocks_loaded', [ $this, 'register_blocks_support' ] );

			}

			public function register_blocks_support() {
				if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
					return;
				}

				require_once dirname( __FILE__ ) . '/includes/blocks/class-wc-appypay-gpo-blocks-support.php';
				require_once dirname( __FILE__ ) . '/includes/blocks/class-wc-appypay-widget-blocks-support.php';

				add_action(
					'woocommerce_blocks_payment_method_type_registration',
					function( $payment_method_registry ) {
						$payment_method_registry->register( new WC_AppyPay_GPO_Blocks_Support() );
						$payment_method_registry->register( new WC_AppyPay_Widget_Blocks_Support() );
					}
				);
			}


    		public function d4tw_custom_ty_msg ( $thank_you_msg,$order ) {
            if($order->get_status()==="completed")
            {
            	$thank_you_msg =  __("Thank you, Your order has been received and confirmed.", 'woocommerce-gateway-run_techx');
			}
            if($order->get_status()==="cancelled")
            {
            	$thank_you_msg =  __("Sorry, Your order has been cancelled.", 'woocommerce-gateway-run_techx');
			}
            else
            {
				$thank_you_msg =  __("Thank you, Your order has been received but we are waiting for your confirmation.", 'woocommerce-gateway-run_techx');
			}
            return $thank_you_msg;
			}

			public function add_scripts_css()
			{
				wp_register_style('wc-run_techx-css', plugins_url('assets/css/runtechx.css', WC_RUNTECHX_MAIN_FILE), [],"1.0.0");
				wp_enqueue_style('wc-run_techx-css');

			}
			public function init() {	
				add_filter( 'woocommerce_payment_gateways', [ $this, 'add_gateways' ] );
				
				if ( ! is_admin() ) {
					// add field setting here
					//add_filter( 'woocommerce_billing_fields', [ $this, 'checkout_update_email_field_priority' ], 50 );
				}
				require_once dirname(__FILE__) . '/includes/payment-methods/class-wc-gateway-run_techx-gpo.php';
				require_once dirname(__FILE__) . '/includes/payment-methods/class-wc-gateway-appypay-widget.php';

			}
			public function appypay_woocommerce_checkout_spinner()
			{ 				
				?>
<style>
.woocommerce-page .woocommerce-checkout.processing .blockUI.blockOverlay, .woocommerce-page.woocommerce-order-pay .blockUI.blockOverlay {
    background: #000 !important;
}
	.woocommerce-page .woocommerce-checkout.processing .blockUI.blockOverlay::before, .woocommerce-page.woocommerce-order-pay .blockUI.blockOverlay::before {
    content: "<?php echo _e('Waiting for your confirmation on selected payment method. You have 90 seconds to confirm.', 'woocommerce-gateway-run_techx') ?>";
    text-align:center;
    animation: none;
    width: 25%;
	font-size: 22px;
	color: #FFF;
	margin: 0 auto;
	left: 0;
	right: 0;
}
</style>
<?php
			}
			
			public function add_gateways( $methods ) {
				$methods[] = WC_Gateway_GPO::class;
				$methods[] = WC_Gateway_Widget::class;
				return $methods;
			}
			public function get_order_data() {
				static $code = 200;

				# Example URL
				# https://app-run_techx-wp-dev.azurewebsites.net/wp-json/getorderstatus/v1/getstatus
				$rawData =  file_get_contents("php://input");
				$filename = ABSPATH . "/log.txt";
				file_put_contents($filename, $rawData);

				$rawDatadecoded = json_decode($rawData, true); 
				$orderid = $rawDatadecoded['id'];
				$responseStatus = $rawDatadecoded['responseStatus']['successful'];
				$scode = $rawDatadecoded['responseStatus']['code'];
				$responsemessage = $rawDatadecoded['responseStatus']['message'];
				
				$args = array(
					'meta_key' => 'order_id', 
					'meta_value' => $orderid, 
					'meta_compare' => '=', 
					'return' => 'ids', 
				);
				$orders = wc_get_orders($args);
				
				$orderid = $orders['0'];
				$order = wc_get_order($orderid);
				
				if($responseStatus)
                {
                    	$order->update_status("completed", __($responsemessage, 'woocommerce-gateway-run_techx'));
                }
				else if (!$responseStatus) {
					$order->update_status("cancelled", __($responsemessage, 'woocommerce-gateway-run_techx'));
				}
				else{
					$order->update_status("processing", __($responsemessage, 'woocommerce-gateway-run_techx'));
				}
                
				http_response_code($code);exit;
			}	
		
			public function register_routes() {
				register_rest_route(
					'getorderstatus/v1', '/getstatus', [
						'methods' => 'POST',
						'callback' => [$this, 'get_order_data'],       
						'permission_callback' => '__return_true' // Torna a rota pública
					]
				);
			}
		}
		$plugin = WC_RunTechx::get_instance();
	}
	return $plugin;
}

add_action( 'plugins_loaded', 'woocommerce_gateway_run_techx_init' );

function woocommerce_gateway_run_techx_init() {
	load_plugin_textdomain( 'woocommerce-gateway-run_techx', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woocommerce_run_techx_missing_wc_notice' );
		return;
	}

	if ( version_compare( WC_VERSION, WC_RUNTECHX_MIN_WC_VER, '<' ) ) {
		add_action( 'admin_notices', 'woocommerce_run_techx_wc_not_supported' );
		return;
	}
    if ( version_compare( phpversion(), WC_RUNTECHX_MIN_PHP_VER, '<' ) ) {
		add_action( 'admin_notices', 'woocommerce_run_techx_php_not_supported' );
		return;
	}
	woocommerce_gateway_run_techx();
}

if ( ! function_exists( 'add_woocommerce_run_techx_variant' ) ) {
	function add_woocommerce_run_techx_variant() {		
	}
}
register_activation_hook( __FILE__, 'add_woocommerce_run_techx_variant' );

function wcrun_techx_deactivated() {
}
register_deactivation_hook( __FILE__, 'wcrun_techx_deactivated' );