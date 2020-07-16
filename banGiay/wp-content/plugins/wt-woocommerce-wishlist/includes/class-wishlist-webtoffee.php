<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wishlist_Webtoffee {

    protected $loader;
    protected $plugin_name;
    protected $version;

	public static $option_prefix = 'wishlist_webtoffee';


    public function __construct() {
        if (defined('WEBTOFFEE_WISHLIST_VERSION')) {
            $this->version = WEBTOFFEE_WISHLIST_VERSION;
        } else {
            $this->version = '1.1.4';
        }
        $this->plugin_name = 'wishlist-webtoffee';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }



    private function load_dependencies() {

        $plugin_dir_path = plugin_dir_path(dirname(__FILE__));
        require_once $plugin_dir_path . 'includes/class-wishlist-webtoffee-loader.php';
        require_once $plugin_dir_path . 'includes/class-wishlist-webtoffee-i18n.php';
        require_once $plugin_dir_path . 'admin/class-wishlist-webtoffee-admin.php';
        require_once $plugin_dir_path . 'public/class-wishlist-webtoffee-public.php';

        $this->loader = new Wishlist_Webtoffee_Loader();
    }

    private function set_locale() {

        $plugin_i18n = new Wishlist_Webtoffee_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {

	    $plugin_admin = new Wishlist_Webtoffee_Admin( $this->get_plugin_name(), $this->get_version() );


	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	    $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
	    

	    $this->loader->add_action( 'wp_ajax_add_to_wishlist', $plugin_admin, 'add_to_wishlist' );
	    $this->loader->add_action( 'wp_ajax_myaccount_bulk_delete_action', $plugin_admin, 'myaccount_bulk_delete_action' );
            
            add_shortcode('wt_mywishlist', array($this, 'wt_my_wishlist'));

	    // settings page
    }

    public function wt_my_wishlist(){
        Wishlist_Account_View::endpoint_content();
    }

    private function define_public_hooks() {

        $plugin_public = new Wishlist_Webtoffee_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

}