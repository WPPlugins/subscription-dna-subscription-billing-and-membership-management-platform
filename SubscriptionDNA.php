<?php
    /*
        Plugin Name: SubscriptionDNA
        Plugin URI: http://SubscriptionDNA.com/wordpress/
        Description: This plugin will provide simple integration to your account with SubscriptionDNA.com's Enterprise Subscription Billing and Members Management Platform.
        Version: 1.0.4
        Author: SubscriptionDNA.com
        Author URI: http://SubscriptionDNA.com/
    */

    /*
        Initialize
    */
error_reporting(0);
session_start();

ini_set('soap.wsdl_cache_eanbled', 0);
//if(function_exists("register_deactivation_hook"))
//register_deactivation_hook( __FILE__, 'SubscriptionDNA_DeActivate' );
if(!in_array("exclude-pages/exclude_pages.php",get_option("active_plugins")))
include dirname(__FILE__).'/SubscriptionDNA_exclude_pages.php';

include dirname(__FILE__).'/functions.php';
/*function SubscriptionDNA_DeActivate()
{
	
}*/

?>