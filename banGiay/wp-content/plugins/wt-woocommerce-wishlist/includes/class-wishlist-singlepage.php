<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class WT_Wishlist_Singlepage {


    public function __construct() {
        
        $wishlist_icon_position = get_option(Wishlist_Webtoffee_Settings::$option_prefix.'_position');
	    if (empty($wishlist_icon_position)){ $wishlist_icon_position = 'woocommerce_before_add_to_cart_button'; }
        $wishlist_activation = get_option(Wishlist_Webtoffee_Settings::$option_prefix.'_activation');
	    if (empty($wishlist_activation)){ $wishlist_activation = 'yes'; }
        if($wishlist_activation == 'yes') {
	        add_action( $wishlist_icon_position, array( $this, 'render_webtoffee_wishlist_button' ), 11 );
        }
        
    }

    public function render_webtoffee_wishlist_button() {
        
        global $product;
        if ($this->product_already_exists($product->get_id(), get_current_user_id())) {
            $class = 'webtoffee_wishlist_remove';
            $msg = __('Remove from Wishlist', 'wt-woocommerce-wishlist');
        } else {
            $class = 'webtoffee_wishlist';
            $msg = __('Add to Wishlist', 'wt-woocommerce-wishlist');
        }
        if (is_user_logged_in()) {
            echo "<button title='".$msg."' class='" . $class . " wt-wishlist-button' data-act='add' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'></button>";
        } else {
	        $url = get_permalink(get_option('woocommerce_myaccount_page_id'));

	        $link = strpos($url, '=');


	        if ($link !== false)
	        {
		        $url = $url.'&';
	        } else {
		        $url = $url.'?';
            }

            ?>
            <button  class="webtoffee_wishlist wt-wishlist-button"
                    onclick="location.href='<?php echo $url . "product_id=".$product->get_id() . "variation_id=".$product->get_id()."&redirect=".get_permalink(); ?>';"
               title="<?php _e('Add to Wishlist', 'wt-woocommerce-wishlist'); ?>"></button>
        <?php
        }
    }

    public function product_already_exists($product_id, $current_user) {
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wt_wishlists';
        $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name where `product_id` = '$product_id' and `user_id` = '$current_user'");

        return $rowcount;
    }

}

new WT_Wishlist_Singlepage();