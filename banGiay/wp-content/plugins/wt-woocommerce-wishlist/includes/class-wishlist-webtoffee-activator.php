<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Wishlist_Webtoffee_Activator {

    public function __construct() {
        
    }

    public static function activate() {


        global $wpdb;
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $like = '%' . $wpdb->prefix . 'wt_wishlists%';
        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
            $table_name = $wpdb->prefix . 'wt_wishlists';
            $sql_settings = "CREATE TABLE $table_name 
                (
                    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  					`user_id` int(11) NOT NULL DEFAULT '0',
  					`product_id` int(11) NOT NULL DEFAULT '0',
                                        `variation_id` int(11) NOT NULL DEFAULT '0',
  					`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  					`quantity` int(11) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`id`)
                )$charset_collate;";
            dbDelta($sql_settings);
        }
    }

}