<?php
/**
 * WC wcEdpg1 Gateway Class.
 * Built the wcEdpg1 method.
 */

add_action('plugins_loaded', 'init_edebitdirect_Payment_Gateway', 1);

function init_edebitdirect_Payment_Gateway() {

    class edebitdirect extends WC_Payment_Gateway{

        /**
         * Constructor for the gateway.
         *
         * @return void
         */
        public function __construct() {
            global $woocommerce;
            add_action('woocommerce_api_edebitdirect', 'api_check_edebitdirect');
            $this->id             = 'edebitdirect';
            $this->icon           = apply_filters( 'woocommerce_edebitdirect_icon', '' );
            $this->has_fields     = false;
            $this->method_title   = __( 'EdebitDirect', 'edebitdirect' );

    		// Load the settings.
    		$this->init_form_fields();
    		$this->init_settings();
    		if ($this->settings['test_mode']=='no')
			    wp_enqueue_script( 'edebitdirect-payments-script', plugins_url( '/dist/payments.min.js', __FILE__ ), array('jquery'), null, false  );
			else
			    wp_enqueue_script( 'edebitdirect-payments-script', plugins_url( '/dist/payments.sandbox.min.js', __FILE__ ), array('jquery'), null, false  );

            $this->title          = $this->settings['title'];
            $this->description    = $this->settings['description_'];
    		$this->instructions       = $this->get_option( 'instructions' );
    		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
            if(isset($_GET['code']))
            {
                $redirect_uri = admin_url().'admin.php?page=wc-settings&tab=checkout&section=edebitdirect';
                $expire = time() + 60 * 60;
                setcookie('edebitdirect_code', $_GET['code'], $expire);
                setcookie('edebitdirect_state', $_GET['state'], $expire);
                wp_redirect($redirect_uri);
                exit();
            }

            // Actions
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            add_action('woocommerce_receipt_'. $this->id, array( $this, 'receipt_page' ) );


            // Actions.
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            else
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );


        }


        /* Admin Panel Options.*/
    	function admin_options() {
    		?>
    		<h3><?php _e('EdebitDirect','edebitdirect'); ?></h3>
        	<table class="form-table">
        		<?php $this->generate_settings_html(); ?>
    		</table> <?php
        }

        /* Initialise Gateway Settings Form Fields. */
        public function init_form_fields() {
        	global $woocommerce;

        	$shipping_methods = array();
            $redirect_uri = admin_url().'admin.php?page=wc-settings&tab=checkout&section=edebitdirect';
            //$redirect_uri = admin_url();
            global $woocommerce;

            $query = parse_url($url, PHP_URL_QUERY);



            $url = get_permalink(get_option('woocommerce_checkout_page_id'));
            $query = parse_url($url, PHP_URL_QUERY);
            if ($query) {
                $url .= '&wc-api=wc_edebitdirect';
            } else {
                $url .= '?wc-api=wc_edebitdirect';
            }
            $iframe_callback = $url;

        	if ( is_admin() )
    	    	foreach ( $woocommerce->shipping->load_shipping_methods() as $method ) {
    		    	$shipping_methods[ $method->id ] = $method->get_title();
    	    	}


            $this->icon = 'https://edebitdirect.com/static/img/logo.png';

            if($this->settings['test_mode']=='no')
            {
                $type_label = "LIVE ";
                $type_input = '_live';
            }
            else
            {
                $type_label = "TEST ";
                $type_input = '';
            }
            $type_label = "";

/*
            $curl = curl_init();

            curl_setopt($curl,CURLOPT_CUSTOMREQUEST,"GET");
            curl_setopt($curl,CURLOPT_URL, $iframe_callback);
            curl_setopt($curl,CURLOPT_HEADER,1);
            curl_setopt($curl,CURLOPT_POST,0);
            curl_setopt($curl,CURLOPT_USERAGENT,"User-Agent=Mozilla/5.0 Firefox/1.0.7");
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,1);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $_SITE_ANSWER = curl_exec($curl);
            curl_close($curl);
            if(!$_SITE_ANSWER)
            {
                $https_status = '<div class="https_bad">Perhaps, your site is not configured to use https protocol</div>';
            }
            else
            {
                $https_status = '<div class="https_good">HTTPS is enabled</div>';
            }
*/
			$https_status = '<div class="https_good">HTTPS is enabled</div>';

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'edebitdirect' ),
                    'type' => 'checkbox',
                    'label' => __( 'Activate edebitdirect on Your web site', 'edebitdirect' ),
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __( 'Name', 'edebitdirect' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'edebitdirect' ),
                    'desc_tip' => true,
                    'default' => __( 'EdebitDirect', 'edebitdirect' )
                ),
                'description_' => array(
                    'title' => __( 'Description', 'edebitdirect' ),
                    'type' => 'textarea',
                    'desc_tip' => true,
                    'description' => __( 'This controls the description which the user sees during checkout.', 'edebitdirect' ),
                    'default' => __( 'Desctiptions for edebitdirect.', 'edebitdirect' )
                ),
    			'instructions' => array(
    				'title' => __( 'Instructions', 'edebitdirect' ),
    				'type' => 'textarea',
                    'desc_tip' => true,
    				'description' => __( 'Instructions that will be added to the thank you page.', 'edebitdirect' ),
    				'default' => __( 'Instructions for edebitdirect.', 'edebitdirect' )
    			),
                'routing_number' => array(
                    'title' => __( 'Routing Number', 'edebitdirect' ),
                    'type' => 'text',
                    'description' => __( 'Bank routing number. This will be validated.', 'edebitdirect' ),
                    'desc_tip' => true,
                    'default' => __( '122000247', 'edebitdirect' )
                ),
                'account_number' => array(
                    'title' => __( 'Account Number', 'edebitdirect' ),
                    'type' => 'text',
                    'description' => __( 'Bank account number.', 'edebitdirect' ),
                    'desc_tip' => true,
                    'default' => __( '1234567890', 'edebitdirect' )
                ),
                'vendor_id'.$type_input => array(
    				'title' => __( $type_label.'Checkout ID', 'edebitdirect' ),
    				'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Checkout ID. You can get it on edebitdirect account. Tools -> Edit Checkout configuration', 'edebitdirect' ),
    				'default' => __( '', 'edebitdirect' )
    			),
                'CallbackSecret'.$type_input => array(
    				'title' => __( $type_label.'API KEY', 'edebitdirect' ),
    				'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your API KEY. You can get it on edebitdirect account. Tools -> Edit Checkout configuration', 'edebitdirect' ),
    				'default' => __( '', 'edebitdirect' )
    			),
                /*'iframe_callback1234' => array(
                    'title' => __( 'Callback URL: <b>'.$iframe_callback.'</b>', 'edebitdirect' ),
                    'type' => 'title',
                    'desc_tip' => true,
    				'default' => $iframe_callback,
                    'description' => 'Copy this Callback URL into <a href="https://wallet.edebitdirect/#/app/merchant/tools/checkout" target="_blank">https://wallet.edebitdirect/#/app/ merchant/tools/checkout</a>'
                ),*/
                /*
                'app_id' => array(
                    'title' => __( 'Application ID', 'edebitdirect' ),
                    'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Client-ID. You can get it on edebitdirect account.', 'edebitdirect' ),
                    'default' => __( '', 'edebitdirect' )
                ),
                'app_key'.$type_input => array(
                    'title' => __( $type_label.'Application Key', 'edebitdirect' ),
                    'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Application Key. You can get it on edebitdirect account -> tools -> Add new', 'edebitdirect' ),
                    'default' => __( '', 'edebitdirect' )
                ),
                'app_secret'.$type_input => array(
                    'title' => __( $type_label.'Application Secret', 'edebitdirect' ),
                    'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Application Secret. You can get it on edebitdirect account -> tools -> Add new', 'edebitdirect' ),
                    'default' => __( '', 'edebitdirect' )
                ),
                */
                'test_mode' => array(
                    'title' => __( 'Test Mode', 'edebitdirect' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable/Disable', 'edebitdirect' ),
                    'default' => 'no'
                ),
/*
                'get_history' => array(
                'title' => __( 'Download History <a href="#" class="export_edebitdirect" rel="xml">XML</a> <a href="#" class="export_edebitdirect" rel="doc">DOC</a> <a href="#" class="export_edebitdirect" rel="pdf">PDF</a>', 'edebitdirect_Io' ),
                'type' => 'title',
                'label' => __( 'Download History', 'edebitdirect_Io' ),
                'default' => 'no',
                'description' => ''
                ),


                'iframe_callback' => array(
                    'title' => __( 'Payment callback : <b>'.$iframe_callback.'</b>', 'edebitdirect' ),
                    'type' => 'title',
                    'label' => __( '&nbsp;', 'edebitdirect' ),
                    'default' => 'no',
                    'description' => 'Copy this application callback URL into <a href="https://wallet.edebitdirect/#/app/merchant/tools/checkout" target="_blank">https://wallet.edebitdirect/#/app/merchant/tools/checkout</a>'
                ),

                'app_callback_url' => array(
                    'title' => __( 'Application callback : <b>'.$redirect_uri.'</b>', 'edebitdirect' ),
                    'type' => 'title',
                    'label' => __( '&nbsp;', 'edebitdirect' ),
                    'default' => 'no',
                    'description' => ''
                ),
/*
                'https' => array(
                    'title' => __( $https_status, 'edebitdirect' ),
                    'type' => 'title',
                    'label' => __( '', 'edebitdirect' ),
                    'default' => 'no',
                    'description' => ''
                ),
*/
            );

        }




        function process_payment($order_id){
                $order = new WC_Order($order_id);
                return array(
                    'result' => 'success',
                    'redirect'  => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
                );
        }

        public function receipt_page($order){
                echo $this->generate_form($order);
        }


        public function generate_form($order_id){
                $order = new WC_Order( $order_id );
                global $woocommerce;

                $args = array(
                                'amt'         => $order->order_total,
                                'ccy'         => get_woocommerce_currency(),
                                'order'       => $order_id,
                                'details'     => "$order_id",
                                'ext_details' => "$order_id",
                                'pay_way'     => 'EdebitDirect',
                                'return_url'  => $result_url,
                                'server_url'  => '',
                			);
                $args_array = array();
                $expire = time()+60*60 * 24;
                setcookie('edebitdirect_order_number', $order_id, $expire);
                setcookie('edebitdirect_order_total', $order->order_total, $expire);
                $secr = rand(100000000,999999999);
                update_post_meta($order_id, 'secr_edebitdirect', $secr);
                update_post_meta($order_id, 'secr_md5_edebitdirect', md5($secr));
                $memo = $order_id.'-'.md5($secr);


                if($this->settings['test_mode']=='no')
                {
                    $domain = 'https://edebitdirect.com/app/api/v1/check/';
                    $test=0;
                }
                else
                {
                    $domain = 'https://dev.edebitdirect.com/app/api/v1/check/';
                    $test=1;
                }

//                $frame_url = $domain.'/payment-page?vendor_id='.$this->settings['vendor_id'].'&price='.$args['amt'].'&memo='.$memo.'&currency_type='.$args['ccy'].'';
                $frame_url = $domain;
                $email = $order->billing_email;
                $first_name = $order->billing_first_name;
                $last_name = $order->billing_last_name;
                $fullName = $first_name.' '.$last_name;
                $company = $order->billing_company;
                $address_1 = $order->billing_address_1;
                $address_2 = $order->billing_address_2;
                $city = $order->billing_city;
                $state = $order->billing_state;
                $postcode = $order->billing_postcode;
                $country = $order->billing_country;
                $phone = $order->billing_phone;
                $street = $address_1.' '.$address_2.' '.$country;

                $routing_number = $this->settings['routing_number'];
                $account_number = $this->settings['account_number'];
                $vendor_id = $this->settings['vendor_id']; 
                $callbackSecret = $this->settings['CallbackSecret'];

                if(empty($vendor_id) || empty($callbackSecret) || empty($routing_number) || empty($account_number)){
                    $settings_url = admin_url('/admin.php?page=wc-settings&tab=checkout&section=edebitdirect');
                    wp_die('Sorry, Your settings may not properly not configerd. <a href="'.$settings_url.'" target="_blank">Click to update</a>');
                }


                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);

                //API Url
                $url = $domain;

                //Initiate cURL.
                $ch = curl_init($url);

                //The JSON data.
                $jsonData = array(
                  "customer_name"   => $fullName,
                  "customer_street" => $street,
                  "customer_city"   => $city,
                  "customer_state"  => $state,
                  "customer_zip"    => $postcode,
                  "customer_phone"  => $phone,
                  "customer_email"  => $email,
                  "amount"          => $args['amt'],
                  "check_number"    => $order_id,
                  "routing_number"  => $routing_number,
                  "account_number"  => $account_number
                );

                $jsonDataEncoded = json_encode($jsonData);
                //echo $jsonDataEncoded; exit;

                $header = array(
                        "Authorization: apikey {$vendor_id}:{$callbackSecret}",
                        "Content-Type: application/json",
                        "Cache-Control: no-cache"
                        );

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                //Execute the request
                $result = curl_exec($ch);
                   $check_exp = explode('"check":', $result);
                   if(!empty($check_exp[1])){
                        $jsonCode = '{"check":'.$check_exp[1];
                        $resultDec = json_decode($jsonCode, true);
                        if(!empty($resultDec)){
                            foreach ($resultDec['check'] as $key => $value) {
                                echo '<b>'.$key.' : </b>';
                                $no=1;
                                foreach ($value as $keys => $values) {
                                    echo $no.'. '.$values.'<br>';
                                    $no++;
                                }
                            }
                        }
                        die('<p style="color:red;">Sorry, order canceled, please, re-check your checkout information!<p>');
                   }

                $checkoutUrl = '';
                if($result === FALSE) {
                    die(curl_error($ch));
                } else {
                    $order_secr = get_post_meta($order_id, 'secr_edebitdirect', true);
                    $secr_md5_edebitdirect = get_post_meta($order_id, 'secr_md5_edebitdirect', true);
                    if($secr_md5_edebitdirect==md5($order_secr))
                    {

                        $order->update_status('completed');
//                        $order->update_status('processing', __('Payment EXECUTED via edebitdirect', 'woocommerce'));
                        $order->add_order_note( __('Payment edebitdirect Approved: Transaction ID'.$order->order_key, 'woocommerce') );
                        //$woocommerce->cart->empty_cart();
                        //wp_send_json( array( 'status' => 'success', 'data' => 'payment ok' ) );
//                        wp_die('payment ok');

                    }
                    else
                    {
                        wp_die('payment hash fail');
                    }

                    //print_r($order);

                    $checkoutUrl = get_permalink(woocommerce_get_page_id('pay')).'order-received/'.$order_id.'/?key='.$order->order_key;
                    echo "<script>window.location.href = '".$checkoutUrl."';</script>";
                    
                }

                curl_close($ch);
        }

    }

}