<div class="options_group">
	<?php
	// Checkbox to mark the product as "promoted"
	woocommerce_wp_checkbox(
		array(
			'id'          => '_promote_product',
			'label'       => esc_html__( 'Promote this product', 'wc-promoted-product' ),
			'description' => esc_html__( 'Check this box to promote this product as a featured product.', 'wc-promoted-product' ),
		)
	);

	// Text field for custom promotion title
	woocommerce_wp_text_input(
		array(
			'id'          => '_promoted_product_title',
			'label'       => esc_html__( 'Promotion Title', 'wc-promoted-product' ),
			'description' => esc_html__( 'Enter a custom title for this promoted product. Leave blank to use product name.', 'wc-promoted-product' ),
			'desc_tip'    => true,
		)
	);

	// Checkbox and datetime picker for expiration
	woocommerce_wp_checkbox(
		array(
			'id'          => '_promote_product_expiration_enable',
			'label'       => esc_html__( 'Set expiration', 'wc-promoted-product' ),
			'description' => esc_html__( 'Enable to set an expiration date for this promotion.', 'wc-promoted-product' ),
		)
	);

	// Custom field for expiration date
	woocommerce_wp_text_input(
		array(
			'id'                => '_promoted_product_expiration_date',
			'label'             => esc_html__( 'Expiration Date', 'wc-promoted-product' ),
			'description'       => esc_html__( 'Set the expiration date for this promotion.', 'wc-promoted-product' ),
			'type'              => 'date',
			'desc_tip'          => true,
			'class'             => 'promoted-product-expiration-date',
			'custom_attributes' => array(
				'autocomplete' => 'off',
			),
		)
	);
	// Nonce field for security
	// wp_nonce_field('save_featured_product_promotion_settings', 'featured_product_promotion_settings_nonce');
	?>
</div>
