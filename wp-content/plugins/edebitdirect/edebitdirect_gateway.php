<?php
/*
 * Plugin Name: Edebitdirect for WooCommerce
 * Description: Receive payments using edebitdirect
 * Author: Jakir
 * Version: 1.0.0
*/

//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'wcEdpg_register_plugin_links', 10, 2 );
require_once('functions.php');
function wcEdpg_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="https://edebitdirect.com" target="_blank">' . __( 'edebitdirect.com', 'rsb' ) . '</a>';
	}
	return $links;
}



/* WooCommerce fallback notice. */
function woocommerce_edpg_fallback_notice() {

}

/* Load functions. */
function edebitdirect_gateway_load() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'woocommerce_edpg_fallback_notice' );
        return;
    }

    function wc_Edebitdirect_add_gateway( $methods ) {
        $methods[] = 'edebitdirect';
        return $methods;
    }
	add_filter( 'woocommerce_payment_gateways', 'wc_Edebitdirect_add_gateway' );


    // Include the WooCommerce Custom Payment Gateways classes.
    require_once plugin_dir_path( __FILE__ ) . 'class-wc-edebitdirect_gateway.php';

}

add_action( 'plugins_loaded', 'edebitdirect_gateway_load', 0 );


add_action('init', 'woocommerce_loaded_edpg');


function add_dist_files_edpg()
{
    wp_enqueue_script( 'sweetalert_min', plugins_url( '/dist/sweetalert2.min.js', __FILE__ ) );
    wp_enqueue_style( 'sweetalert_css', plugins_url( '/dist/sweetalert2.css', __FILE__ ), array(), '29072015', 'all' );
    wp_enqueue_style( 'edebitdirect-css', plugins_url( '/dist/edebitdirect.css', __FILE__ ), array(), '29072015', 'all' );
    wp_enqueue_script( 'edebitdirect-main', plugins_url( '/dist/edebitdirect.js', __FILE__ ), array('jquery'), null, true  );
//    wp_enqueue_script( 'edebitdirect-payments-script', plugins_url( '/dist/payments.min.js', __FILE__ ), array('jquery'), null, false  );
}


function add_dist_files_admin_edpg()
{
    wp_enqueue_script( 'sweetalert_min', plugins_url( '/dist/sweetalert2.min.js', __FILE__ ) );
    wp_enqueue_style( 'sweetalert_css', plugins_url( '/dist/sweetalert2.css', __FILE__ ), array(), '29072015', 'all' );
    wp_enqueue_style( 'edebitdirect-css', plugins_url( '/dist/edebitdirect.css', __FILE__ ), array(), '29072015', 'all' );
    wp_enqueue_script( 'edebitdirect-main-admin', plugins_url( '/dist/edebitdirect_admin.js', __FILE__ ), array('jquery'), null, true  );
}

add_filter('clean_url','unclean_url_edpg',10,3);
function unclean_url_edpg( $good_protocol_url, $original_url, $_context){
    if (false !== strpos($original_url, 'payments.min.js')||false !== strpos($original_url, 'payments.sandbox.min.js')){
      remove_filter('clean_url','unclean_url_edpg',10,3);
      $url_parts = parse_url($good_protocol_url);
      return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . "' id='edebitdirect-payments-script";
    }
    return $good_protocol_url;
}
/*
add_filter('script_loader_src','add_id_to_script',10,2);
function add_id_to_script($src, $handle){
    if ($handle != 'edebitdirect-payments-script')
            return $src;
    return $src."' id='edebitdirect-payments-script";
}
*/
function woocommerce_loaded_edpg() {
    add_action('wp_enqueue_scripts', 'add_dist_files_edpg');
    add_action('admin_enqueue_scripts', 'add_dist_files_admin_edpg');
}
?>