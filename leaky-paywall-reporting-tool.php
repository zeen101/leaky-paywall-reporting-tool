<?php
/**
 * Main PHP file used to for initial calls to zeen101's Leak Paywall Reporting Tool classes and functions.
 *
 * @package zeen101's Leak Paywall - Reporting Tool
 * @since 1.0.0
 */
 
/*
Plugin Name: Leaky Paywall - Reporting Tool
Plugin URI: http://zeen101.com/
Description: A premium WordPress plugin by zeen101.
Author: zeen101 Development Team
Version: 1.0.0
Author URI: http://zeen101.com/
Tags:
*/

//Define global variables...
if ( !defined( 'ZEEN101_STORE_URL' ) )
	define( 'ZEEN101_STORE_URL',	'http://zeen101.com' );
	
define( 'LP_RT_NAME', 		'Leaky Paywall - Reporting Tool' );
define( 'LP_RT_SLUG', 		'lp-reporting-tool' );
define( 'LP_RT_VERSION', 	'1.0.0' );
define( 'LP_RT_DB_VERSION', '1.0.0' );
define( 'LP_RT_URL', 		plugin_dir_url( __FILE__ ) );
define( 'LP_RT_PATH', 		plugin_dir_path( __FILE__ ) );
define( 'LP_RT_BASENAME', 	plugin_basename( __FILE__ ) );
define( 'LP_RT_REL_DIR', 	dirname( LP_RT_BASENAME ) );

/**
 * Instantiate Pigeon Pack class, require helper files
 *
 * @since 1.0.0
 */
function leaky_paywall_reporting_tool_plugins_loaded() {
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'issuem-leaky-paywall/issuem-leaky-paywall.php' ) ) {

		require_once( 'class.php' );
	
		// Instantiate the Pigeon Pack class
		if ( class_exists( 'Leaky_Paywall_Reporting_tool' ) ) {
			
			global $leaky_paywall_reporting_tool;
			
			$leaky_paywall_reporting_tool = new Leaky_Paywall_Reporting_tool();
			
			require_once( 'functions.php' );

			//Internationalization
			load_plugin_textdomain( 'lp-reporting-tool', false, LP_RT_REL_DIR . '/i18n/' );
				
		}
	
	} else {
	
		add_action( 'admin_notices', 'leaky_paywall_reporting_tool_requirement_nag' );
		
	}

}
add_action( 'plugins_loaded', 'leaky_paywall_reporting_tool_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init

function leaky_paywall_reporting_tool_requirement_nag() {
	?>
	<div id="leaky-paywall-requirement-nag" class="update-nag">
		<?php _e( 'You must have the Leaky Paywall plugin activated to use the Leaky Paywall Reporting Tool plugin.' ); ?>
	</div>
	<?php
}