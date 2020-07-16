(function ($) {
    'use strict';

    $(document).ready(function (a) {

        $('#select-all').click(function (event) {
            if (this.checked) {
                $(':checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function () {
                    this.checked = false;
                });
            }
        });


        $('.webtoffee_wishlist_remove').click(function (e) {

            e.preventDefault();

            var product_id = $(this).data("product_id");
            var act = 'remove';
            $.ajax({
                url: webtoffee_wishlist_ajax_add.add_to_wishlist,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: product_id,
                    act: act,
                    wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
                },
                success: function (response) {
                    location.reload(); //todo remove pageload and use ajax
                    //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
                    //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');

                }
            });
        });


        $('.webtoffee_wishlist').click(function (e) {

            e.preventDefault();

            var product_id = $(this).data("product_id");
            var variation_id = $("input[name=variation_id]").val();
            //var act = $(this).data("act");
            var act = 'add';
            var quantity = $("input[name=quantity]").val();

            $.ajax({
                url: webtoffee_wishlist_ajax_add.add_to_wishlist,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: product_id,
                    variation_id: variation_id,
                    act: act,
                    quantity: quantity,
                    wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
                },
                success: function (response) {
                    location.reload(); //todo remove pageload and use ajax
                    //$(".wt-wishlist-button").addClass('webtoffee_wishlist_remove');
                    //$(".wt-wishlist-button").removeClass('webtoffee_wishlist');
                }
            });
        });


        $('.remove_wishlist_single').click(function (e) {

            e.preventDefault();

            var product_id = $(this).data("product_id");
            var act = 'remove';
            $.ajax({
                url: webtoffee_wishlist_ajax_add.add_to_wishlist,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: product_id,
                    act: act,
                    wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
                },
                success: function (response) {
                    location.reload(); //todo remove pageload and use ajax
                    //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
                    //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');

                }
            });
        });


        $('#bulk-delete').click(function (e) {

            e.preventDefault();
            //var remove_wishlist = $("input[name=remove_wishlist]").val();
            var checked = [];
            $("input[name='remove_wishlist[]']:checked").each(function () {
                checked.push(parseInt($(this).val()));
            });


            $.ajax({
                url: webtoffee_wishlist_ajax_myaccount_bulk_delete.myaccount_bulk_delete,
                type: 'POST',
                data: {
                    action: 'myaccount_bulk_delete_action',
                    product_id: checked,
                    wt_nonce: webtoffee_wishlist_ajax_myaccount_bulk_delete.wt_nonce,

                },
                success: function (response) {
                    location.reload(); //todo remove pageload and use ajax
                    //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
                    //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');
                }
            });
        });
    });

})(jQuery);