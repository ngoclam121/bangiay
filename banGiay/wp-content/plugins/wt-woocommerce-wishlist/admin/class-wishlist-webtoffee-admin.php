<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Wishlist_Webtoffee_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_filter( 'woocommerce_login_redirect',  [$this,'wt_wishlist_login_redirect'],10,2 );
	}

	public function wt_wishlist_login_redirect($redirect, $user) {


		global $wpdb;
		if ( isset( $_GET['product_id'] ) ) {
			$product_id   = absint( $_GET['product_id'] );
			$variation_id = isset( $_GET['variation_id'] ) ? absint( $_GET['variation_id'] ) : '';
			$user_id = $user->ID;


			$table_name = $wpdb->prefix . 'wt_wishlists';
			$query_check_already_exists = "SELECT COUNT(*)  from  $table_name where `product_id` = '$product_id' and `user_id` = '$user_id'";
			//todo include variable id too

			if ( ! $wpdb->get_var( $query_check_already_exists ) ) {
				$query_wp = "INSERT INTO $table_name 
                    (`user_id`, `product_id`, `variation_id`) 
                    VALUES 
                        ('$user_id', '$product_id', '$variation_id')
                       
                    ";

				$wpdb->query( $query_wp );
			}

			$location = $_GET['redirect'] ;
			wp_redirect($location);
		}


	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wishlist-webtoffee-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wishlist-webtoffee-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'webtoffee_wishlist_ajax_add', array( 'add_to_wishlist' => admin_url( 'admin-ajax.php' ), 'wt_nonce' => wp_create_nonce('add_to_wishlist') ) );
	}

	function add_to_wishlist() {



                check_ajax_referer('add_to_wishlist','wt_nonce');
		$product_id   = absint( $_POST['product_id'] );
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
		$quantity     = absint( $_POST['quantity'] );
		$act          = sanitize_text_field( $_POST['act'] );
		$user         = get_current_user_id();


		global $wpdb;
		$table_name = $wpdb->prefix . 'wt_wishlists';

		if ( 'add' == $act ) {
			$query_wp = "INSERT INTO $table_name 
                    (`user_id`, `product_id`, `variation_id`, `quantity`) 
                    VALUES 
                        ('$user', '$product_id', '$variation_id', '$quantity')
                       
                    ";
		} else if ( 'remove' == $act ) {
			$query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user' and `product_id` = '$product_id'";
		}
		$wpdb->query( $query_wp );
		wp_die();
	}

	public function myaccount_bulk_delete_action() {

                check_ajax_referer('bulk_delete','wt_nonce');
                
		$user        = get_current_user_id();
                
                $product_ids = isset($_POST['product_id']) ? array_filter(array_map('intval', $_POST['product_id'])) : array();

		global $wpdb;
		$table_name = $wpdb->prefix . 'wt_wishlists';
		foreach ( $product_ids as $product_id ) {
			$query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user' and `product_id` = '$product_id'";
			$wpdb->query( $query_wp );
		}
	}

	public function add_plugin_admin_menu() {

		add_menu_page( 'WooCommerce Wishlist', 'WooCommerce Wishlist', 'manage_options', $this->plugin_name, array(
			$this,
			'display_plugin_setup_page'
		), WEBTOFFEE_WISHLIST_BASEURL . 'public/images/wt-heart-icon.png' , '56');
		add_submenu_page( 'wishlist-webtoffee', 'Settings', 'Settings', 'manage_options', 'wishlist-webtoffee-settings', [
			$this,
			'wishlist_webtoffee_settings'
		] );

	}

	public function wishlist_webtoffee_settings() {
		//todo change
		//include( 'class-wishlist_webtoffee_settings.php' );
		wp_redirect( 'admin.php?page=wc-settings&tab=settings_tab_wt_wishlist' );
		exit;

	}



	public function display_plugin_setup_page() {
		global $wpdb;
		$table_name   = $wpdb->prefix . 'wt_wishlists';
		$wt_wishlists = $wpdb->get_results( "SELECT * FROM $table_name" );
		include( 'wishlist-webtoffee-admin-table.php' );
	}

}