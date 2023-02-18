<?php
/**
 * Plugin Name:       W3PAY - Web3 Crypto Payments
 * Description:       Accepting cryptocurrency payments in WordPress.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.2
 * Author:            W3PAY
 * Author URI:        https://w3pay.dev/
 * License:           GPLv3 or later
 * License URI:       https://github.com/W3PAY/w3pay-word-press/blob/main/LICENSE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required minimums and constants
 */
define( 'WC_w3pay_VERSION', '1.0.1' );
define( 'WC_w3pay_MIN_WC_VERSION', '6.0' );
define( 'WC_w3pay_MIN_PHP_VERSION', '7.2.0' );
define( 'WC_w3pay_MAIN_FILE', __FILE__ );
define( 'WC_w3pay_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * WooCommerce fallback notice.
 */
function woocommerce_w3pay_missing_wc_notice() {
	echo '<div class="error"><p><strong>' . sprintf( 'w3pay requires WooCommerce to be installed and active. You can download %s here.', '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

/**
 * WooCommerce not supported fallback notice.
 */
function woocommerce_w3pay_wc_not_supported() {
	echo '<div class="error"><p><strong>' . sprintf( 'w3pay requires WooCommerce %s or greater to be installed and active.', WC_w3pay_MIN_WC_VERSION ) . '</strong></p></div>';
}

function woocommerce_gateway_w3pay() {

	static $plugin;

	if ( ! isset( $plugin ) ) {

		class WC_w3pay {

			/**
			 * The *Singleton* instance of this class
			 *
			 * @var Singleton
			 */
			private static $instance;

			/**
			 * Returns the *Singleton* instance of this class.
			 *
			 * @return Singleton The *Singleton* instance.
			 */
			public static function get_instance() {
				if ( ! self::$instance ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			/**
			 * Private clone method to prevent cloning of the instance of the
			 * *Singleton* instance.
			 *
			 * @return void
			 */
			public function __clone() {}

			/**
			 * Private unserialize method to prevent unserializing of the *Singleton*
			 * instance.
			 *
			 * @return void
			 */
			public function __wakeup() {}

			/**
			 * Protected constructor to prevent creating a new instance of the
			 * *Singleton* via the `new` operator from outside of this class.
			 */
			public function __construct() {
				$this->init();
			}

			/**
			 * Init the plugin after plugins_loaded so environment variables are set.
			 */
			public function init() {
                require_once dirname( __FILE__ ) . '/includes/class-wc-w3pay-logger.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-w3pay-webhook-handler.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-w3pay.php';
                require_once dirname( __FILE__ ) . '/includes/class-wc-w3pay-wp.php';

				if ( is_admin() ) {
					require_once dirname( __FILE__ ) . '/includes/admin/class-wc-w3pay-admin-notices.php';
				}

				add_filter( 'woocommerce_payment_gateways', [ $this, 'add_gateways' ] );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
				add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
			}

			/**
			 * Add the gateways to WooCommerce.
			 */
			public function add_gateways( $methods ) {
				$methods[] = 'WC_Gateway_w3pay';

				return $methods;
			}

			/**
			 * Add plugin action links.
			 */
			public function plugin_action_links( $links ) {
				$plugin_links = [
					'<a href="admin.php?page=wc-settings&tab=checkout&section=w3pay">Settings</a>',
				];
				return array_merge( $plugin_links, $links );
			}

			/**
			 * Add plugin row meta.
			 */
			public function plugin_row_meta( $links, $file ) {
				if ( plugin_basename( __FILE__ ) === $file ) {
					$row_meta = [
						'docs' => '<a href="https://w3pay.dev/webmanual" target="_blank" title="View Documentation">Docs</a>',
					];
					return array_merge( $links, $row_meta );
				}
				return (array) $links;
			}
		}

		$plugin = WC_w3pay::get_instance();

	}

	return $plugin;
}

add_action( 'plugins_loaded', 'woocommerce_gateway_w3pay_init' );

function woocommerce_gateway_w3pay_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woocommerce_w3pay_missing_wc_notice' );
		return;
	}

	if ( version_compare( WC_VERSION, WC_w3pay_MIN_WC_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'woocommerce_w3pay_wc_not_supported' );
		return;
	}

	woocommerce_gateway_w3pay();
}
