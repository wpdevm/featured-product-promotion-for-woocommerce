<div id="promote_product_options" class="panel woocommerce_options_panel hidden">
    <div class="options_group">
        <p style="padding-left: 12px; padding-top: 5px; font-style: italic;"><?php echo esc_html__('Featured products will be highlighted with a pink background in the general product list for easier identification.', 'wc-promoted-product'); ?></p>
        <?php
        $current_featured_product_id = get_option('woocommerce_featured_product_id');
        $is_currently_featured = $current_featured_product_id == $post->ID;
        woocommerce_wp_checkbox([
            'id' => '_promote_product',
            'label' => __('Promote this product', 'wc-promoted-product'),
            'description' => __('Check this box to promote this product as a featured product.', 'wc-promoted-product'),
            'value' => $is_currently_featured ? 'yes' : 'no',
            'cbvalue' => 'yes',
            'checked' => $is_currently_featured ? 'checked' : ''
        ]);
        woocommerce_wp_text_input([
            'id' => '_promoted_product_title',
            'label' => __('Promotion Title', 'wc-promoted-product'),
            'description' => __('Enter a custom title for this promoted product. Leave blank to use product name.', 'wc-promoted-product'),
            'desc_tip' => 'true',
            'value' => get_post_meta($post->ID, '_promoted_product_title', true)
        ]);
        woocommerce_wp_checkbox([
            'id' => '_promote_product_expiration_enable',
            'label' => __('Set expiration', 'wc-promoted-product'),
            'description' => __('Enable to set an expiration date for this promotion.', 'wc-promoted-product'),
            'value' => get_post_meta($post->ID, '_promote_product_expiration_enable', true) === 'yes' ? 'yes' : 'no',
            'cbvalue' => 'yes',
            'checked' => get_post_meta($post->ID, '_promote_product_expiration_enable', true) === 'yes' ? 'checked' : ''
        ]);
        woocommerce_wp_text_input([
            'id' => '_promoted_product_expiration_date',
            'label' => __('Expiration Date', 'wc-promoted-product'),
            'description' => __('Set the expiration date for this promotion.', 'wc-promoted-product'),
            'type' => 'date',
            'desc_tip' => 'true',
            'value' => get_post_meta($post->ID, '_promoted_product_expiration_date', true),
            'class' => 'promoted-product-expiration-date',
            'custom_attributes' => ['autocomplete' => 'off']
        ]);
        wp_nonce_field('save_promoted_product_action', 'promoted_product_nonce_field');
        ?>
    </div>
</div>