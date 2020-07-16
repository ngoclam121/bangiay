<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
$wishlist_text = get_option(Wishlist_Webtoffee_Settings::$option_prefix.'_text');
if (empty($wishlist_text)){
	$wishlist_text = 'My Wishlist';
}
?>

<h4><?php _e($wishlist_text, 'wt-woocommerce-wishlist'); ?></h4>

<?php if ($products) { ?>
    <form action="">
        <table>
            <tr>
                <th><input id="select-all" type="checkbox"></th>
                <th><?php _e('Image', 'wt-woocommerce-wishlist'); ?></th>
                <th><?php _e('Name', 'wt-woocommerce-wishlist'); ?></th>
                <th><?php _e('Price', 'wt-woocommerce-wishlist'); ?></th>
                <th><?php _e('Stock', 'wt-woocommerce-wishlist'); ?></th>
                <th></th>
                <th></th>
            </tr>
            <?php
            foreach ($products as $product) {
                $product_data = wc_get_product($product['product_id']);
                if ($product_data) {
                    ?>
                    <tr>
                        <td><input name="remove_wishlist[]" value="<?php echo $product['product_id']; ?>"
                                   type="checkbox">
                        </td>
                        <td><?php echo $product_data->get_image('woocommerce_gallery_thumbnail'); ?></td>
                        <td>
                            <a href="<?php echo $product_data->get_permalink(); ?>"><?php echo $product_data->get_title(); ?></a>
                        </td>
                        <td><?php echo $product_data->get_price_html(); ?></td>
                        <td><?php
                            if ($product_data->is_in_stock() == 1) {
                                $instock = __('In Stock', 'wt-woocommerce-wishlist');
                                echo "<span style=\"color: green\">$instock</span>";
                            } else {
                                $outstock = __('Out of Stock', 'wt-woocommerce-wishlist');
                                echo "<span style=\"color: red\">$outstock</span>";
                            };
                            ?></td>
                        <td>
                            <button data-product_id="<?php echo $product['product_id']; ?>"
                                    class="remove_wishlist_single"
                                    style="color: red ; border-radius: 10%;">X
                            </button>
                        </td>
                        <td>
                            <a href="<?php echo wc_get_cart_url().'?add-to-cart='.$product['product_id'];?>" class="button"><?php _e('Add to cart', 'wt-woocommerce-wishlist'); ?></a>
                        </td>
                    </tr>

                <?php
                }
            }
            ?>
            <tfoot>
                <tr>
                    <td colspan="100">
                        <button id="bulk-delete" class="button"><?php _e('Remove From List', 'wt-woocommerce-wishlist'); ?></button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
<?php } else { ?>
    <h3 style="text-align: center"><?php _e('No Wishlists yet!', 'wt-woocommerce-wishlist'); ?></h3>
<?php } ?>