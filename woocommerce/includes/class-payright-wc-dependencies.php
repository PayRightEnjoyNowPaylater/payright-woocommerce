<?php
/**
 *
 * Checks if WooCommerce is enabled
 */
class Payright_WC_Dependencies {
	/**
	* @var		Array	$active_plugins		Array of Active Plugins
	*/
	private static $active_plugins;

	/**
	 * Initialise the Plugin
	 * @return void
	 */
	public static function init() {

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() )
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	/**
	 * Check WooCommerce Enable Status
	 * @return bool
	 */
	public static function woocommerce_active_check() {

		if ( ! self::$active_plugins ) self::init();

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}

}
