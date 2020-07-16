<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wishlist_Webtoffee_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wishlist-webtoffee-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wishlist-webtoffee-public.js', array('jquery'), $this->version, false);

        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_add', array('add_to_wishlist' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('add_to_wishlist')));
        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_myaccount_bulk_delete', array('myaccount_bulk_delete' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('bulk_delete')));
    }

}