<?php
/**
 * WC wcCpg1 Gateway Class.
 * Built the wcCpg1 method.
 */

add_action('plugins_loaded', 'init_Romit_Payment_Gateway', 1);

function init_Romit_Payment_Gateway() {

    class romit extends WC_Payment_Gateway{

        /**
         * Constructor for the gateway.
         *
         * @return void
         */
        public function __construct() {
            global $woocommerce;
            add_action('woocommerce_api_romit', 'api_check_romit');
            $this->id             = 'romit';
            $this->icon           = apply_filters( 'woocommerce_Romit_icon', '' );
            $this->has_fields     = false;
            $this->method_title   = __( 'Romit.Io', 'romit' );

    		// Load the settings.
    		$this->init_form_fields();
    		$this->init_settings();
    		if ($this->settings['test_mode']=='no')
			    wp_enqueue_script( 'romit-payments-script', plugins_url( '/dist/payments.min.js', __FILE__ ), array('jquery'), null, false  );
			else
			    wp_enqueue_script( 'romit-payments-script', plugins_url( '/dist/payments.sandbox.min.js', __FILE__ ), array('jquery'), null, false  );

            $this->title          = $this->settings['title'];
            $this->description    = $this->settings['description_'];
    		$this->instructions       = $this->get_option( 'instructions' );
    		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
            if(isset($_GET['code']))
            {
                $redirect_uri = admin_url().'admin.php?page=wc-settings&tab=checkout&section=romit';
                $expire = time() + 60 * 60;
                setcookie('romit_code', $_GET['code'], $expire);
                setcookie('romit_state', $_GET['state'], $expire);
                wp_redirect($redirect_uri);
                exit();
            }

            // Actions
            add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            add_action('woocommerce_receipt_'. $this->id, array( $this, 'receipt_page' ) );

            add_action( 'woocommerce_api_wc_' . $this->id, array( $this, 'check_ipn_response_romit' ) );


            // Actions.
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            else
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );


        }


        /* Admin Panel Options.*/
    	function admin_options() {
    		?>
    		<h3><?php _e('Romit','Romit'); ?></h3>
        	<table class="form-table">
        		<?php $this->generate_settings_html(); ?>
    		</table> <?php
        }

        /* Initialise Gateway Settings Form Fields. */
        public function init_form_fields() {
        	global $woocommerce;

        	$shipping_methods = array();
            $redirect_uri = admin_url().'admin.php?page=wc-settings&tab=checkout&section=romit';
            //$redirect_uri = admin_url();
            global $woocommerce;

            $query = parse_url($url, PHP_URL_QUERY);



            $url = get_permalink(get_option('woocommerce_checkout_page_id'));
            $query = parse_url($url, PHP_URL_QUERY);
            if ($query) {
                $url .= '&wc-api=wc_romit';
            } else {
                $url .= '?wc-api=wc_romit';
            }
            $iframe_callback = $url;

        	if ( is_admin() )
    	    	foreach ( $woocommerce->shipping->load_shipping_methods() as $method ) {
    		    	$shipping_methods[ $method->id ] = $method->get_title();
    	    	}


            $this->icon = 'https://daks2k3a4ib2z.cloudfront.net/5639724aedc0d1c96e1163f0/563a6fe8c44b4ac86e5fd3d1_r_romit_white.png';

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
                    'title' => __( 'Enable/Disable', 'Romit' ),
                    'type' => 'checkbox',
                    'label' => __( 'Activate Romit on Your web site', 'Romit' ),
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __( 'Name', 'Romit' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'Romit' ),
                    'desc_tip' => true,
                    'default' => __( 'Romit.Io', 'Romit' )
                ),
                'description_' => array(
                    'title' => __( 'Description', 'Romit' ),
                    'type' => 'textarea',
                    'desc_tip' => true,
                    'description' => __( 'This controls the description which the user sees during checkout.', 'Romit' ),
                    'default' => __( 'Desctiptions for Romit.Io.', 'Romit' )
                ),
    			'instructions' => array(
    				'title' => __( 'Instructions', 'Romit' ),
    				'type' => 'textarea',
                    'desc_tip' => true,
    				'description' => __( 'Instructions that will be added to the thank you page.', 'Romit' ),
    				'default' => __( 'Instructions for Romit.Io.', 'Romit' )
    			),
                'vendor_id'.$type_input => array(
    				'title' => __( $type_label.'Checkout ID', 'Romit' ),
    				'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Checkout ID. You can get it on Romit account. Tools -> Edit Checkout configuration', 'Romit' ),
    				'default' => __( '', 'Romit' )
    			),
                'CallbackSecret'.$type_input => array(
    				'title' => __( $type_label.'Callback Secret', 'Romit' ),
    				'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Callback Secret. You can get it on Romit account. Tools -> Edit Checkout configuration', 'Romit' ),
    				'default' => __( '', 'Romit' )
    			),
                'iframe_callback1234' => array(
                    'title' => __( 'Callback URL: <b>'.$iframe_callback.'</b>', 'Romit' ),
                    'type' => 'title',
                    'desc_tip' => true,
    				'default' => $iframe_callback,
                    'description' => 'Copy this Callback URL into <a href="https://wallet.romit.io/#/app/merchant/tools/checkout" target="_blank">https://wallet.romit.io/#/app/ merchant/tools/checkout</a>'
                ),
                /*
                'app_id' => array(
                    'title' => __( 'Application ID', 'Romit' ),
                    'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Client-ID. You can get it on Romit account.', 'Romit' ),
                    'default' => __( '', 'Romit' )
                ),
                'app_key'.$type_input => array(
                    'title' => __( $type_label.'Application Key', 'Romit' ),
                    'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Application Key. You can get it on Romit account -> tools -> Add new', 'Romit' ),
                    'default' => __( '', 'Romit' )
                ),
                'app_secret'.$type_input => array(
                    'title' => __( $type_label.'Application Secret', 'Romit' ),
                    'type' => 'text',
                    'desc_tip' => true,
    				'description' => __( 'Your Application Secret. You can get it on Romit account -> tools -> Add new', 'Romit' ),
                    'default' => __( '', 'Romit' )
                ),
                */
                'test_mode' => array(
                    'title' => __( 'Test Mode', 'Romit' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable/Disable', 'Romit' ),
                    'default' => 'no'
                ),
/*
                'get_history' => array(
                'title' => __( 'Download History <a href="#" class="export_romit" rel="xml">XML</a> <a href="#" class="export_romit" rel="doc">DOC</a> <a href="#" class="export_romit" rel="pdf">PDF</a>', 'Romit_Io' ),
                'type' => 'title',
                'label' => __( 'Download History', 'Romit_Io' ),
                'default' => 'no',
                'description' => ''
                ),


                'iframe_callback' => array(
                    'title' => __( 'Payment callback : <b>'.$iframe_callback.'</b>', 'Romit' ),
                    'type' => 'title',
                    'label' => __( '&nbsp;', 'Romit' ),
                    'default' => 'no',
                    'description' => 'Copy this application callback URL into <a href="https://wallet.romit.io/#/app/merchant/tools/checkout" target="_blank">https://wallet.romit.io/#/app/merchant/tools/checkout</a>'
                ),

                'app_callback_url' => array(
                    'title' => __( 'Application callback : <b>'.$redirect_uri.'</b>', 'Romit' ),
                    'type' => 'title',
                    'label' => __( '&nbsp;', 'Romit' ),
                    'default' => 'no',
                    'description' => ''
                ),
/*
                'https' => array(
                    'title' => __( $https_status, 'Romit' ),
                    'type' => 'title',
                    'label' => __( '', 'Romit' ),
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

                $args = array(
                                'amt'         => $order->order_total,
                                'ccy'         => get_woocommerce_currency(),
                                'order'       => $order_id,
                                'details'     => "$order_id",
                                'ext_details' => "$order_id",
                                'pay_way'     => 'Romit',
                                'return_url'  => $result_url,
                                'server_url'  => '',
                			);
                $args_array = array();
                $expire = time()+60*60 * 24;
                setcookie('romit_order_number', $order_id, $expire);
                setcookie('romit_order_total', $order->order_total, $expire);
                $secr = rand(100000000,999999999);
                update_post_meta($order_id, 'secr_romit', $secr);
                update_post_meta($order_id, 'secr_md5_romit', md5($secr));
                $memo = $order_id.'-'.md5($secr);


                if($this->settings['test_mode']=='no')
                {
                    $domain = 'https://checkout.romit.io';
                    $test=0;
                }
                else
                {
                    $domain = 'https://checkout.sandbox.romit.io';
                    $test=1;
                }

//                $frame_url = $domain.'/payment-page?vendor_id='.$this->settings['vendor_id'].'&price='.$args['amt'].'&memo='.$memo.'&currency_type='.$args['ccy'].'';
                $frame_url = $domain.'/payment-page';
                $swal = "
					jQuery(function() {
		                Romit.initialize('".$this->settings['vendor_id']."',
    		  		      function (paymentId) {
								location.reload();
		    		        },
        				    function () {

				            },
				            '',
				            null,
				            ".$test."
					    );
					    $('romitButton').trigger('click');
					});
					var lastHeight = 0;
					var resizeIFrameTimer = null;
					function resizeIframe() {
						var obj = document.getElementById('romitFrame');
						if(lastHeight != obj.contentWindow.document.body.scrollHeight) {
							lastHeight = obj.contentWindow.document.body.scrollHeight;
							obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
						}
						if (resizeIFrameTimer == null) {
							//To handle angular resizes
							resizeIFrameTimer = setInterval(resizeIframe, 1000);
						}
					}
				";
                $iframe = '<iframe id="romitFrame" style="display:none;width:100%;min-height:800px;border:none" frameborder="0" scrolling="no" onload="resizeIframe()"></iframe>';

                return '<p>'.__('Thank you for your order, please click the button in
                		<button id="romitButton"
							style="background-image: url(&quot;https://checkout.sandbox.romit.io/app/img/romit_button_2x.png&quot;); background-size: cover; width: 236px; height: 51px; background-color: transparent; border: none;"
							data-payment="romit"
							data-memo="'.$memo.'"
							class="romit-payment-button"
							data-price="'.$args['amt'].'"
							data-invoiceId="'.$order_id.'"
							data-server-url="'.$frame_url.'">
						</button>.',
				'woocommerce')."</p>
				<script>
                            ".$swal."
				</script>

                        ".$iframe;

/*
                $iframe = '<iframe src="'.$frame_url.'" style="height:450px;"></iframe>';

                $swal = "
				jQuery(function() {
					jQuery('body').on('click', '.swal2-close', function(){
						location.reload();
					});

					jQuery( '.popup_open_romit' ).click(function() {
						swal({
							title: 'Payment via Romit.io',
							html: '<div class=\"romit_iframe_wrap\">".$iframe."</div>',
							showConfirmButton : false,
							closeOnConfirm: false,
							showCloseButton:true,
							customClass : 'romit_checkout_popup'
						})
				    });
					swal({
						title: 'Payment via Romit.io',
						html: '<div class=\"romit_iframe_wrap\">".$iframe."</div>',
						showConfirmButton : false,
						closeOnConfirm: false,
						showCloseButton:true,
						customClass : 'romit_checkout_popup'
					});
				});

                ";
                return '<p>'.__('Thank you for your order, please click the button in <button class="popup_open_romit button" frame_url="'.urlencode($frame_url).'">Pop-Up</button>.', 'woocommerce')."</p>

                        <script>
                            ".$swal."
                        </script>
                        ";
*/
        }


        function check_ipn_response_romit(){

            global $woocommerce;

            $inputJSON = file_get_contents('php://input');
            $data_inp=(array)json_decode($inputJSON);
/*
            $inputJSON = str_ireplace('{','',$inputJSON);
            $inputJSON = str_ireplace('}','',$inputJSON);
            $obj = explode(',', $inputJSON);
            $data_inp = array();
            foreach($obj as $o)
            {
                $data = explode(':', $o);
                $data_inp[$data[0]] = $data[1];
            }
*/
            if(isset($data_inp['statusType']))
            {
                if($data_inp['statusType']=='EXECUTED')
                {
                    $info = explode('-', $data_inp['sourceMessage']);
                    $order_id = $info[0];
                    $hash = $info[1];
                    $order = new WC_Order($order_id);
                    $order_secr = get_post_meta($order_id, 'secr_romit', true);
                    $secr_md5_romit = get_post_meta($order_id, 'secr_md5_romit', true);
                    if($hash==md5($order_secr))
                    {
                        $order->update_status('completed');
//                        $order->update_status('processing', __('Payment EXECUTED via Romit', 'woocommerce'));
                        $order->add_order_note( __('Payment Romit Approved: Transaction ID '.$data_inp['paymentId'], 'woocommerce') );
                        $woocommerce->cart->empty_cart();
                        wp_send_json( array( 'status' => 'success', 'data' => 'payment ok' ) );
//                        wp_die('payment ok');
                    }
                    else
                    {
                        wp_die('payment hash fail');
                    }
                }
                else
                {
                    wp_die('Order payment fail...');
                }
            }
            else
            {
                wp_die('Order payment fail...');
            }
        }

        public function get_romit_code()
        {
            global $woocommerce;
            $redirect_uri = urlencode(admin_url().'admin.php?page=wc-settings&tab=checkout&section=romit');
            //$redirect_uri = admin_url();
            //$scope = "BANKING_READ|BANKING_WRITE|IDENTITY_READ|IDENTITY_WRITE|TRANSFER_READ|TRANSFER_WRITE|USER_READ|USER_WRITE|SUBSCRIPTION_READ|SUBSCRIPTION_WRITE|PLAN_READ|PLAN_WRITE";
            $scope = 'TRANSFER_READ';
            $state = rand(100000000,999999999);

            $domain = 'https://auth.romit.io';
            if($this->settings['test_mode']=='yes')
            {
                    $domain = 'https://auth.sandbox.romit.io';
            }

            $url = $domain.'/#/app/authorize?client_id='.$this->settings['app_key'].'&response_type=code&redirect_uri='.$redirect_uri.'&scope='.$scope.'&state='.$state;

            return $url;
        }

        public function get_tokens()
        {
            $redirect_uri = admin_url().'admin.php?page=wc-settings&tab=checkout&section=romit';
            if($this->settings['test_mode']=='no')
            {
                    $url = 'api.romit.io/v1/oauth/token';
            }
            else
            {
                    $url = 'api.sandbox.romit.io/v1/oauth/token';
            }

            $ch = curl_init('https://'.$url);

            $data = array(
                    "client_id" => $this->settings['app_key'],
                    "client_secret" => $this->settings['app_secret'],
                    "code" => $_COOKIE['romit_code'],
                    "grant_type" => "authorization_code",
                    "redirect_uri" => $redirect_uri
                    );
            $data_string = json_encode($data);

            $ch = curl_init('https://'.$url);
            curl_setopt ($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt ($ch, CURLOPT_REFERER, 'https://'.$url);
            curl_setopt ($ch, CURLOPT_POST, true);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            $result = curl_exec($ch);
            return $result;
        }

        public function get_transfers_list($token)
        {
            if($this->settings['test_mode']=='no')
            {
                    $url = 'api.romit.io/v1/transfer';
            }
            else
            {
                    $url = 'api.sandbox.romit.io/v1/transfer';
            }
            $ch = curl_init('https://'.$url);
            curl_setopt ($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_REFERER, 'https://'.$url);
            curl_setopt ($ch, CURLOPT_POST, 0);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: bearer '.$token));
            $result = curl_exec($ch);
            return $result;

        }


        /* INPUT JSON DATA EXAMPLE
        {
                    "id": "296db7c3-ef0b-4720-a338-ca52d4e562d4",
                    "from": "6a5699bf-f659-406d-97e7-e30517fa8d83",
                    "to": "2d4f9b08-c171-4cc9-83de-1411d0794b6d",
                    "by": "ABC Payments, Inc.",
                    "fromAmount": {
                        "currency": "USD",
                        "value": "100.00"
                    },
                    "toAmount": {
                        "currency": "USD",
                        "value": "98.00"
                    },
                    "bankingId": "7b71256b-4638-4a9e-b605-711775fb0a55",
                    "memo": "INV-123456789",
                    "type": "PAYMENT",
                    "status": "EXECUTED",
                    "refund": null,
                    "created": 1445462910000
                },
        */
        public function output_file_history_xml($resone)
        {
            $dir = plugin_dir_path( __FILE__ ).DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR;
            $file_name = 'xml_romit.xml';
            $output = '<?xml version="1.0" encoding="UTF-8"?>';
            foreach($resone as $item)
            {
                $output .='<transfer>';
                    $output .='<id>'.$item->id.'</id>';
                    $output .='<from>'.$item->from.'</from>';
                    $output .='<to>'.$item->to.'</to>';
                    $output .='<by>'.$item->by.'</by>';

                    $output .='<fromAmount>';
                        $output .='<currency>'.$item->fromAmount->currency.'</currency>';
                        $output .='<value>'.$item->fromAmount->value.'</value>';
                    $output .='</fromAmount>';

                    $output .='<toAmount>';
                        $output .='<currency>'.$item->toAmount->currency.'</currency>';
                        $output .='<value>'.$item->toAmount->value.'</value>';
                    $output .='</toAmount>';


                    $output .='<bankingId>'.$item->bankingId.'</bankingId>';
                    $output .='<memo>'.$item->memo.'</memo>';
                    $output .='<type>'.$item->type.'</type>';
                    $output .='<status>'.$item->status.'</status>';
                    $output .='<refund>'.$item->refund.'</refund>';
                $output .='</transfer>';
            }

            $path = $dir.$file_name;
            $fp = fopen($path, "w+");
            $test = fwrite($fp, $output);
            $http_path = plugin_dir_url(__FILE__).'export/'.$file_name;

            return $http_path;
        }

        public function output_file_history_doc($resone)
        {
            $dir = plugin_dir_path( __FILE__ ).DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR;
            $file_name = 'doc_romit.doc';
            $output = get_doc_header_romit();
            $output .='<wx:sect>\n';


                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='Payment history via Romit ';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .= get_site_url().' :';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';




            foreach($resone as $item)
            {

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='id = '.$item->id.'';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='from = '.$item->from.'';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='to = '.$item->to.'';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='by = '.$item->by.'';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='fromAmount = '.$item->fromAmount->currency.' '.$item->fromAmount->value.'';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='toAmount = '.$item->toAmount->currency.' '.$item->toAmount->value.'';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='bankingId = '.$item->bankingId;
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='memo = '.$item->memo;
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='type = '.$item->type;
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='status = '.$item->status;
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='refund = '.$item->refund;
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

                    $output .='<w:p>';
                        $output .='<w:r>';
                            $output .='<w:t>';
                                    $output .='-----------------';
                            $output .='</w:t>';
                        $output .='</w:r>';
                    $output .='</w:p>';

            }
            $output .='</wx:sect>';
            $output .=get_doc_footer_romit();

            $path = $dir.$file_name;
            $fp = fopen($path, "w+");
            $test = fwrite($fp, $output);
            $http_path = plugin_dir_url(__FILE__).'export/'.$file_name;

            return $http_path;
        }

        public function output_file_history_pdf($resone)
        {

            $dir = plugin_dir_path( __FILE__ ).DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR;
            $file_name = 'pdf_romit.pdf';

            $lib_path = dirname(__FILE__).'/tcpdf/tcpdf.php';
            require_once($lib_path);
            $usr = get_user_by('id', $user_id);
            $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $x=15;
            $y=15;

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor(get_bloginfo('name'));
            $pdf->SetTitle(get_bloginfo('name').' - History '.get_site_url());
            $pdf->SetSubject(get_bloginfo('name').' - Report to '.get_site_url());
            $pdf->SetKeywords(get_bloginfo('description'));
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('dejavusans', '', 11, '', true);
            $pdf->SetMargins(0,0,0,0);
            $pdf->SetTopMargin(0);
            $pdf->AddPage();

            $pdf->writeHTMLCell(140, 15, $x, $y, 'Payment via Romit.io');
            $y+=7;
            $pdf->writeHTMLCell(140, 15, $x, $y, 'Site : '.get_site_url());

            foreach($resone as $item)
            {
                $pdf->AddPage();
                $x=0;
                $y=15;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'id : '.$item->id);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'from : '.$item->from);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'to : '.$item->to);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'by : '.$item->by);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'fromAmount : '.$item->fromAmount->currency.' '.$item->fromAmount->value);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'toAmount : '.$item->toAmount->currency.' '.$item->toAmount->value);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'bankingId : '.$item->bankingId);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'memo : '.$item->memo);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'type : '.$item->type);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'status : '.$item->status);
                $y+=7;

                $pdf->writeHTMLCell(140, 15, $x, $y, 'refund : '.$item->refund);
                $y+=7;

            }

            $path = $dir.$file_name;
            $d = 'F';
            $http_path = plugin_dir_url(__FILE__).'export/'.$file_name;
            $pdf->Output($path, $d);
            return $http_path;
        }


        public function output_file_history($type, $resone)
        {
            switch ($type) {
                case 'xml':
                    $result['file_name'] = $this->output_file_history_xml($resone);
                    $result['error'] = false;
                    break;
                case 'doc':
                    $result['file_name'] = $this->output_file_history_doc($resone);
                    $result['error'] = false;
                    break;
                case 'pdf':
                    $result['file_name'] = $this->output_file_history_pdf($resone);
                    $result['error'] = false;
                    break;
                default:
                    $result['file_name'] = 'File format is wrong';
                    $result['error'] = true;
                    break;
            }
            return $result;
        }

        public function get_history_romit($type)
        {
            if(!isset($_COOKIE['access_token_romit']))
            {
                if(isset($_COOKIE['romit_code']) && isset($_COOKIE['romit_state']))
                {
                    $token = $this->get_tokens();

                    $json_token = json_decode($token);

                    if($json_token->success == true)
                    {
                        $expire = time()+(60*60);
                        setcookie('access_token_romit', $json_token->response->access_token, $expire);
                        setcookie('refresh_token_romit', $json_token->response->refresh_token, $expire);

                        $my_result = $this->get_transfers_list($json_token->response->access_token);
                        $resp_json = json_decode($my_result);
                        if($resp_json->success == true)
                        {
                            $file_output = $this->output_file_history($type, $resp_json->response);
                            $respone['type'] = 'output_file';
                            $respone['body'] = $file_output;
                        }
                        else
                        {
                            $respone['type'] = 'fail';
                            $respone['body'] = 'auth_error';
                        }
                        return $respone;

                    }

                    if($json_token->success == false)
                    {
                        //re ask NEW code;
                        if($json_token->error->code == 'err.code_has_already_been_used')
                        {
                            $url_to_get_code = $this->get_romit_code();
                            $respone['type'] = 'url_to_get_code';
                            $respone['body'] = $url_to_get_code;
                            return $respone;
                        }
                    }

                    $respone['type'] = 'token';
                    $respone['body'] = $token;
                    return $respone;
                }
                else
                {
                    $url_to_get_code = $this->get_romit_code();
                    $respone['type'] = 'url_to_get_code';
                    $respone['body'] = $url_to_get_code;
                    return $respone;
                }
            }
            else
            {
                $my_result = $this->get_transfers_list($_COOKIE['access_token_romit']);
                $resp_json = json_decode($my_result);
                if($resp_json->success == true)
                {
                    $file_output = $this->output_file_history($type, $resp_json->response);
                    $respone['type'] = 'output_file';
                    $respone['body'] = $file_output;
                }
                else
                {
                    $respone['type'] = 'fail';
                    $respone['body'] = 'auth_error';
                }
                return $respone;
            }
        }

    }

}