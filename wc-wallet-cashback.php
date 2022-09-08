<?php
/**
 * Plugin Name:       Wallet Cashback
 * Plugin URI:        https://mustaneer.abdullah.com
 * Description:       Wallet Cashback plugin will add a 10% back to User Wallet on the basis of 100 day or 100 weeks plan.
 * Version:           1.0.0
 * Author:            Mustaneer Abdullah
 * Author URI:        https://mustaneer.abdullah.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: 	  	 wc-wallet-cashback
 * Domain Path: 	 	/languages
 */


if ( ! class_exists( 'WC_Wllet_Cashback' ) ) {
    /**
     * WC_Wllet_Cashback class.
     */
    class WC_Wllet_Cashback {
	    public $plugin_name;
	    
        /**
         * __construct function.
         * 
         * @access public
         * @return void
         */
        public function __construct() {
	        $this->plugin_name = 'wc-wallet-cashback';
	        									
			// Frontend assets styles and scripts
			add_action( 'wp_enqueue_scripts', array($this, 'ff_frontend_assets'));
			add_filter('woo_wallet_nav_menu_items', array($this, 'add_cashback_log_menu'), 10, 2);
			add_filter('woo_wallet_endpoint_actions', array($this, 'woo_wallet_endpoint_cashback_action'));
			add_action('woo_wallet_menu_content',array($this,'add_cashmenu_content'));
			
			add_filter('cron_schedules', array( $this, 'extra_cron_schedule'));
			
			// Plugin language text to domain support
			add_action('init', array($this, 'wc_load_textdomain'));
			add_filter('woocommerce_get_wp_query_args', array($this, 'enable_meta_query'),10,2);
				   
			// process all data 
		    add_action('wc_get_all_orders_hook', array($this, 'wc_process_all_orders_data'));
		    add_action('wc_add_cashback_hook', array($this, 'wc_add_cashback_to_qualified_users'));
		    //add_action('init', array($this, 'wc_add_cash_to_qualified_users'));  
		}
		
		/**
		 * init function.
		 * 
		 * @access public
		 * @static
		 * @return void
		*/
		public static function init() { 
	        $class = __CLASS__;
	        new $class;
	    }
	    /**
	     * woo_wallet_endpoint_cashback_action function.
	     * 
	     * @access public
	     * @return void
	     */
	    public function woo_wallet_endpoint_cashback_action($actions){
		    $actions[] = 'cashbacklog';
			return $actions;
	    }
	    /**
	     * add_cashmenu_content function.
	     * 
	     * @access public
	     * @return void
	     */
	    public function add_cashmenu_content(){
		    global $wp;
			if ( (isset( $wp->query_vars['woo-wallet'] ) && 'cashbacklog' === $wp->query_vars['woo-wallet'] ) || ( isset( $_GET['wallet_action'] ) && 'cashbacklog' === $_GET['wallet_action']  )) { 
				global $wpdb;
				$cashback_customer_data = $wpdb->prefix . 'cashback_customer_data';
				$cashback_iteration_plan = $wpdb->prefix . 'cashback_iteration_plan';
				$current_user_id = get_current_user_id();
				$user_results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $cashback_customer_data WHERE customer_id = %d",$current_user_id),ARRAY_A);
				if(!empty($user_results)){ ?>
					<table class="woo_wallet_cashback_table">
				        <thead>
				            <tr>
				                <th><?php _e('Order ID', 'woo-wallet'); ?></th>
				                <th><?php _e('Product', 'woo-wallet'); ?></th>
				                <th><?php _e('Plan', 'woo-wallet'); ?></th>
				                <th><?php _e('Cash Back', 'woo-wallet'); ?></th>
				            </tr>
				        </thead>
					<?php
						foreach($user_results as $users_data => $user_data){
							$ID = $user_data['ID'];
							$order_id 	 = $user_data['order_id'];
							$product_name 	 =  get_the_title($user_data['product_id']);   
							$wc_plan 	 = $user_data['wc_plan'];
							$cashback_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $cashback_iteration_plan WHERE status = %d AND wc_customer_data_id = %d",1,$ID));					
						?>    
				        <tbody>
				            <tr>
				                <td><?php echo $order_id; ?></td>
				                <td><?php echo $product_name; ?></td>
				                <td><?php echo $wc_plan; ?></td>
				                <td><?php echo $cashback_count; ?>/10</td>
				            </tr>
				        </tbody>
					<?php }  //End of Foreach ?>
					</table>
			<?php	} // End of If
			} 
		}
	    /**
	     * add_cashback_log_menu function.
	     * 
	     * @access public
	     * @param mixed $nav_menu
	     * @param mixed $is_rendred_from_myaccount
	     * @return void
	     */
	    public function add_cashback_log_menu($nav_menu, $is_rendred_from_myaccount) {
        $nav_menu['cashback_log'] = array(
            'title' => apply_filters('woo_wallet_account_cashback_log_menu_title', __('Cash Back Log', 'woo-wallet')),
            'url' => $is_rendred_from_myaccount ? esc_url(wc_get_endpoint_url(get_option('woocommerce_woo_wallet_endpoint', 'woo-wallet'), 'cashbacklog', wc_get_page_permalink('myaccount'))) : add_query_arg('wallet_action', 'cashbacklog'),
            'icon' => 'dashicons dashicons-money-alt'
        );
        return $nav_menu;
    }
	    /**
	     * wc_process_all_order_data function.
	     * 
	     * @access public
	     * @return void
	     */
	    public function wc_process_all_orders_data(){ 
		    global $wpdb;
			$args = array(
				'return' => 'ids',
				'status' => 'completed',
				'post_type' => 'shop_order',
				'order' => 'ASC',
				'orderby'   => 'ID',
				'meta_query' => array(
			        array(
			            'key' => '_payment_method',
			            'compare' => '!=',
			            'value'   => 'wallet'
			        ),
			        array(
			            'key' => '_order_processed',
			            'compare' => 'NOT EXISTS',
			        ),
			    )
			);
			$orders = wc_get_orders($args);
			//print_r($orders); 
			foreach( $orders as $order_id ) {
				$order = wc_get_order( $order_id ); 
				$customer_id 			= $order->get_user_id();
				//delete_post_meta($order_id, '_order_processed');
				//update_user_meta( $customer_id, '100_days_products', '');
				$user_100_days_products = (!empty(get_user_meta($customer_id,'100_days_products',true))) ? get_user_meta($customer_id,'100_days_products', true) : [];
				$order_completed 		= explode('T',$order->get_date_completed());
				$order_completed_date 	= $order_completed[0]; 
				$return_period = 100;
				$week_plan = 100;
				$days_plan = 100;
				$wc_start_date =  date('Y-m-d', strtotime($order_completed_date. " +$return_period days"));
				$paypal_transaction_fee =  get_post_meta($order_id, '_paypal_transaction_fee',true);
				$total_discount			=  $order->get_discount_total();
				$total_fee 				= $order->get_fees();
				if(!empty($total_fee)){
					foreach($total_fee as $fee){
						$wallet_payment = ($fee['total'] <= 0 || $fee['total'] != "") ? $fee['total'] : 0  ;
					}
				} else {
					$wallet_payment = 0;
				}
				$total_order_qty 	= $order->get_item_count();
				$wallet_paid_part 	= $wallet_payment/$total_order_qty;
				$discount_paid		= $total_discount/$total_order_qty;
				//Get and Loop Over Order Items
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id 		= $item->get_product_id();
				    $product_total 		= $item->get_total();
				    $quantity 			= $item->get_quantity();
				    $total_cashback		= ($product_total/$quantity) + $wallet_paid_part - $discount_paid;
				    for($i=0; $i<$quantity; $i++){
					    if(empty($user_100_days_products) || !in_array($product_id, $user_100_days_products)){
						    $wc_plan = "$days_plan-D";
						    $loop_counter = $days_plan/10;
						    $next_date_offset = "+10 days";
						    $wc_end_date =  date('Y-m-d', strtotime($wc_start_date. " +$days_plan days"));
						    $user_100_days_products[] = $product_id;
						    update_user_meta( $customer_id, '100_days_products', $user_100_days_products);
					    } else {
						    $wc_plan = "$week_plan-W";
						    $wc_end_date =  date('Y-m-d', strtotime($wc_start_date. " +$week_plan week"));
						    $loop_counter = $week_plan/10;
						    $next_date_offset = "+10 week";
					    }
					    
						$wpdb->insert(
							$wpdb->prefix . 'cashback_customer_data',
							array(
								'customer_id' 		  => $customer_id,
								'product_id'  		  => $product_id,
								'order_id'			  => $order_id,
								'wc_plan' 	  		  => $wc_plan,
								'wc_start_date' 	  => $wc_start_date,
								'wc_end_date' 	  	  => $wc_end_date,
								'wc_cashback_amount'  => $total_cashback,
							),
							array(
								'%d',
								'%d',
								'%d',
								'%s',
								'%s',
								'%s',
								'%f',
							)
						); 
						$wc_customer_data_id = $wpdb->insert_id;
						if($wc_customer_data_id){
							for($j=0; $j<$loop_counter; $j++){
								
								$cahs_back_date = date('Y-m-d', strtotime($wc_start_date. " ".$next_date_offset));
								$wpdb->insert(
									$wpdb->prefix . 'cashback_iteration_plan',
									array(
										'wc_customer_data_id' 	=> $wc_customer_data_id,
										'cashback_dates'  		=> $cahs_back_date,
									),
									array(
										'%d',
										'%s',
									)
								);
								$wc_start_date = $cahs_back_date;
							}
						}   // end of cashback iteration plan data insertion.  
				    }  // End of Quantity for loop
				    					
				} // End of Items foreach loop 
				
				update_post_meta( $order_id, '_order_processed', 1);    				
			} // End of Orders foreach loop  
	    }
	    /**
	     * wc_add_cash_to_qualified_users function.
	     * 
	     * @access public
	     * @return void
	     */
	    public function wc_add_cashback_to_qualified_users(){
		    global $wpdb;
		    $today =  date('Y-m-d');
			$cashback_customer_data = $wpdb->prefix . 'cashback_customer_data';
			$cashback_iteration_plan = $wpdb->prefix . 'cashback_iteration_plan';
			$results = $wpdb->get_results($wpdb->prepare("SELECT ID,wc_customer_data_id FROM $cashback_iteration_plan WHERE cashback_dates = %s AND status = %d",$today,0),ARRAY_A);
			if(!empty($results)){
				foreach($results as $data => $data_id){
					$customer_id = $data_id['wc_customer_data_id'];
					$iteration_plan_id = $data_id['ID'];
					$customer_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $cashback_customer_data WHERE ID = %d ",$customer_id),ARRAY_A); 
					$transaction_id = woo_wallet()->wallet->credit( $customer_data['customer_id'], $customer_data['wc_cashback_amount'], __( 'Cash back added against your order #', 'woo-wallet' ) . $customer_data['order_id'] );
					if ( $transaction_id ) {
		                $update_status =$wpdb->query( $wpdb->prepare("UPDATE $cashback_iteration_plan SET status = %d  WHERE ID = %d",1, $iteration_plan_id));
		            }
				}
			}
	    }
		/**
		 * wc_load_textdomain function.
		 * Load Plugin Text Domain.
		 * 
		 * @access public
		 * @return void
		 */
		public function wc_load_textdomain() {
			load_plugin_textdomain( $this->plugin_name, FALSE, dirname(plugin_basename( __FILE__ )) . '/languages/' );
		}
		

	    /**
	     * Frontend assets like styles and scripts initialization.
	     * ff_frontend_assets
	     * 
	     * @access public
	     * @return void
	     */
	    public function ff_frontend_assets(){
		    wp_enqueue_style( 'wallet-cashback-style', plugins_url('assets/css/wallet-cashback.css', __FILE__), [], strtotime('now') );
		    wp_register_script( 'wallet-cashback-js', plugins_url('assets/js/wallet-cashback.js', __FILE__), ['jquery'], strtotime('now'), true );
		    wp_localize_script( 'wallet-cashback-js', 'cashback', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' )
				)
			);
		    wp_enqueue_script( 'wallet-cashback-js');
	    }
		/**
	     * Return transaction amount by transaction id
	     * @since 1.2.7
	     * @global object $wpdb
	     * @param int $transaction_id
	     * @return type(string) | false
	     */
	    public function get_wallet_transaction_amount($transaction_id){
	        global $wpdb;
	        $transaction = $wpdb->get_row("SELECT amount FROM {$wpdb->base_prefix}woo_wallet_transactions WHERE transaction_id = {$transaction_id}");
	        if( $transaction ){
	            return $transaction->amount;
	        }
	        return false;
	    }
	    
	    /**
	     * enable_meta_query function.
	     * 
	     * @access public
	     * @param mixed $wp_query_args
	     * @param mixed $query_vars
	     * @return void
	     */
	    public function enable_meta_query($wp_query_args, $query_vars ){
		    if ( isset( $query_vars['meta_query'] ) ) {
		        $meta_query = isset( $wp_query_args['meta_query'] ) ? $wp_query_args['meta_query'] : [];
		        $wp_query_args['meta_query'] = array_merge( $meta_query, $query_vars['meta_query'] );
		    }
		    return $wp_query_args;
		}
		/**
		 * extra_cron_schedule function.
		 * 
		 * @access public
		 * @param mixed $schedules
		 * @return void
		 */
		public function extra_cron_schedule( $schedules ) {
		    $schedules['every_10_minutes'] = array(
		        'interval' => 10 * MINUTE_IN_SECONDS,
		        'display'  => __( 'Every 10 Minutes' )
		    );
		    return $schedules;
		}
		/**
		 * Custom database table
		 * wc_wallet_cashback_database function.
		 * 
		 * @access public
		 * @static
		 * @return void
		 */
		public static function wc_wallet_cashback_database(){
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$cashback_customer_data = $wpdb->prefix . 'cashback_customer_data';
			$cashback_iteration_plan = $wpdb->prefix . 'cashback_iteration_plan';
			
			$table_cashback_customer_data = "CREATE TABLE $cashback_customer_data (
				ID int NOT NULL AUTO_INCREMENT,
				customer_id int NOT NULL,
				product_id int NOT NULL,
				order_id int NOT NULL,
				wc_plan text NOT NULL,
				wc_start_date text NOT NULL,
				wc_end_date text NOT NULL,
				wc_cashback_amount float(10,2) NOT NULL,
				PRIMARY KEY  (ID)
			) $charset_collate;";
					
			$table_cashback_iteration_plan = "CREATE TABLE $cashback_iteration_plan (
				ID int NOT NULL AUTO_INCREMENT,
				wc_customer_data_id int NOT NULL,
				cashback_dates text NOT NULL,
				status int NOT NULL DEFAULT 0,
				PRIMARY KEY  (ID)
			) $charset_collate;";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $table_cashback_customer_data );
			dbDelta( $table_cashback_iteration_plan );
		}
		public static function wc_wallet_delete_database_table(){
		    global $wpdb;
	        $tableArray = [   
	        	$wpdb->prefix . "cashback_customer_data",
				$wpdb->prefix . "cashback_iteration_plan",
			];
	
		    foreach ($tableArray as $tablename) {
		        $wpdb->query("DROP TABLE IF EXISTS $tablename");
		    }
		}
		public static function wc_scheule_cron_hooks(){
		    if( ! wp_next_scheduled( 'wc_get_all_orders_hook' ) ) {  
			    wp_schedule_event( time(), 'hourly', 'wc_get_all_orders_hook' );  
			}
			if( ! wp_next_scheduled( 'wc_add_cashback_hook' ) ) {  
			    wp_schedule_event( time(), 'twicedaily', 'wc_add_cashback_hook' );  
			} 
		}
    }
    
	add_action( 'plugins_loaded', array( 'WC_Wllet_Cashback', 'init' ));
	register_activation_hook( __FILE__, array( 'WC_Wllet_Cashback', 'wc_wallet_cashback_database') );
	register_activation_hook( __FILE__, array( 'WC_Wllet_Cashback', 'wc_scheule_cron_hooks') );
	//register_deactivation_hook(__FILE__, 	array( 'WC_Wllet_Cashback', 'wc_wallet_delete_database_table'));
}
