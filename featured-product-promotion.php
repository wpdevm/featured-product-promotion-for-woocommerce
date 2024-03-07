<?php

/**
 * Plugin Name: Featured Product Promotion for WC
 * Description: Adds the ability to feature a product across the site with custom promotional settings.
 * Version: 1.1
 * Author: WpDevM (Vladyslav Parshyn)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';

// Check if WooCommerce is activated
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Add an action for the admin notice
    add_action('admin_notices', 'wc_promoted_product_missing_wc_notice');
    return;
}


// Ensure WooCommerce is active before plugin activation
add_action('admin_init', 'check_woocommerce_is_active');
function check_woocommerce_is_active()
{
    include_once(ABSPATH . 'wp-admin/includes/plugin.php'); // Make sure to include the plugin.php library for the is_plugin_active function
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', 'wc_promoted_product_missing_wc_notice');
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}


/**
 * Error notice if WooCommerce is not activated
 */
function wc_promoted_product_missing_wc_notice()
{
?>
    <div class="notice notice-error">
        <p><?php echo __('WooCommerce Promoted Product requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> to be installed and active. Please <a href="' . admin_url('plugin-install.php?s=WooCommerce&tab=search&type=term') . '" target="_blank">install and activate WooCommerce</a>.', 'wc-promoted-product'); ?></p>
    </div>
<?php
}




/**
 * Main class for Featured Product Promotion.
 */
class WC_Featured_Product_Promotion
{
    private $settings;

    public function __construct()
    {
        $this->settings = include plugin_dir_path(__FILE__) . 'config.php';
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('woocommerce_product_data_tabs', [$this, 'add_promotion_product_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'add_promotion_product_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_product_promotion_fields']);
        add_action('init', [$this, 'display_featured_product']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts_and_styles']);
        add_action('woocommerce_update_options_products_featured_product_promotion', [$this, 'save_featured_product_promotion_settings']);
        add_filter('post_class', [$this, 'highlight_featured_product_in_admin_list'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'add_custom_style_for_featured_product']);
        add_action('woocommerce_settings_tabs_array', [$this, 'add_settings_nonce'], 20);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_and_localize_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_custom_admin_style']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('init', [$this, 'load_textdomain']);
    }

    public function init()
    {
        // Ensure WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return; // Early exit if WooCommerce is not active
        }

        // Use settings from the configuration file
        $background_color = $this->settings['promotion_settings']['background_color'];
        $text_color = $this->settings['promotion_settings']['text_color'];
        $promotion_title_text = $this->settings['promotion_settings']['promotion_title_text'];
        $enable_countdown = $this->settings['promotion_settings']['enable_countdown'];
    }

    public function enqueue_custom_admin_style()
    {
        $background_color = $this->settings['promotion_settings']['background_color'];
        $text_color = $this->settings['promotion_settings']['text_color'];

        $custom_css = "
        .highlight-featured-product {
            background: {$background_color};
            color: {$text_color};
        }";
        wp_add_inline_style('wp-admin', $custom_css);
    }


    public function enqueue_frontend_scripts()
    {
        $background_color = $this->settings['promotion_settings']['background_color'];
        $text_color = $this->settings['promotion_settings']['text_color'];
    }


    /**
     * Admin notice for when WooCommerce is missing.
     */
    public function woocommerce_missing_notice()
    {
        echo '<div class="notice notice-error"><p>' . __('WooCommerce is required for this plugin to work. Please install and activate WooCommerce.', 'wc-promoted-product') . '</p></div>';
    }


    public function add_settings_nonce($settings_tabs)
    {
        if (isset($_GET['page'], $_GET['tab']) && $_GET['page'] == 'wc-settings' && $_GET['tab'] == 'featured_product_promotion') {
            add_action('woocommerce_settings_tabs_featured_product_promotion', function () {
                wp_nonce_field('save_featured_product_promotion_settings', 'featured_product_promotion_settings_nonce');
            });
        }
        return $settings_tabs;
    }

    /**
     * Register settings in WooCommerce > Products.
     */
    public function register_settings()
    {
        // Add a new section to WooCommerce Product settings tab.
        add_filter('woocommerce_get_sections_products', function ($sections) {
            $sections['featured_product_promotion'] = __('Featured Product Promotion', 'wc-promoted-product');
            return $sections;
        });

        // Add settings to the new section.
        add_filter('woocommerce_get_settings_products', function ($settings, $current_section) {
            if ('featured_product_promotion' == $current_section) {
                $custom_settings = array();

                // Section title
                $custom_settings[] = array(
                    'name' => __('Featured Product Promotion Settings', 'wc-promoted-product'),
                    'type' => 'title',
                    'desc' => __('Settings for the Featured Product Promotion.', 'wc-promoted-product'),
                    'id' => 'featured_product_promotion_settings'
                );

                // Field for the promotional product title text
                $custom_settings[] = array(
                    'name'     => __('Promotion Title Text', 'wc-promoted-product'),
                    'desc_tip' => __('This will prefix the name of the featured product.', 'wc-promoted-product'),
                    'id'       => 'woocommerce_featured_product_promotion_title_text',
                    'type'     => 'text',
                    'desc'     => __('Enter the promotion title text.', 'wc-promoted-product'),
                );

                // Background color picker
                $custom_settings[] = array(
                    'name'     => __('Background Color', 'wc-promoted-product'),
                    'id'       => 'woocommerce_featured_product_promotion_bg_color',
                    'type'     => 'color',
                    'desc'     => __('Choose a background color for the promotion.', 'wc-promoted-product'),
                );

                // Text color picker
                $custom_settings[] = array(
                    'name'     => __('Text Color', 'wc-promoted-product'),
                    'id'       => 'woocommerce_featured_product_promotion_text_color',
                    'type'     => 'color',
                    'desc'     => __('Choose a text color for the promotion.', 'wc-promoted-product'),
                );

                $custom_settings[] = array(
                    'name'     => __('Enable Countdown Timer', 'wc-promoted-product'),
                    'desc_tip' => __('Enable the countdown timer for a product commercial. Please note that for this you will still need to activate the option in the product editor itself and specify the end time of the promotion', 'wc-promoted-product'),
                    'id'       => 'woocommerce_featured_product_promotion_enable_countdown',
                    'type'     => 'checkbox',
                    'desc'     => __('Enable to display the countdown timer.', 'wc-promoted-product'),
                );

                // End section
                $custom_settings[] = array('type' => 'sectionend', 'id' => 'featured_product_promotion_settings');

                return array_merge($settings, $custom_settings);
            }

            return $settings;
        }, 10, 2);
    }


    // Add a new tab to the product data section in the product edit screen
    public function add_promotion_product_tab($tabs)
    {
        $tabs['promote_product_tab'] = array(
            'label' => __('Promote This Product', 'woocommerce'),
            'target' => 'promote_product_options',
            'class' => array(),
            'icon' => 'dashicons-money-alt',
        );
        return $tabs;
    }

    // Add custom fields to the new custom tab
    public function add_promotion_product_fields()
    {
        global $post;
        include plugin_dir_path(__FILE__) . 'templates/promotion-product-fields.php';
    }


    /**
     * Add fields to the General product data tab.
     */
    public function add_product_promotion_fields()
    {
        include plugin_dir_path(__FILE__) . 'templates/product-promotion-fields-wc.php';
    }



    /**
     * Save the custom fields for product promotion.
     */
    public function save_product_promotion_fields($post_id)
    {
        // Check nonce and user permissions.
        if (!isset($_POST['promoted_product_nonce_field']) || !wp_verify_nonce($_POST['promoted_product_nonce_field'], 'save_promoted_product_action')) {
            return;
        }

        // Check user permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // Check if the promotion checkbox was checked for the current product
        $is_promoted = isset($_POST['_promote_product']) && 'yes' === $_POST['_promote_product'];

        if ($is_promoted) {
            // Get the ID of the currently promoted product
            $current_featured_product_id = get_option('woocommerce_featured_product_id');

            // If there's already another promoted product, remove its promotion status
            if ($current_featured_product_id && $current_featured_product_id != $post_id) {
                update_post_meta($current_featured_product_id, '_promote_product', 'no');
            }

            // Set the current product as the promoted product
            update_post_meta($post_id, '_promote_product', 'yes');
            update_option('woocommerce_featured_product_id', $post_id);
        } else {
            // If the product was demoted, clear the promotion meta and check if it was the featured product
            update_post_meta($post_id, '_promote_product', 'no');
            if ($post_id == get_option('woocommerce_featured_product_id')) {
                delete_option('woocommerce_featured_product_id');
            }
        }

        // Save the custom promotion title if provided
        if (isset($_POST['_promoted_product_title'])) {
            update_post_meta($post_id, '_promoted_product_title', sanitize_text_field($_POST['_promoted_product_title']));
        }

        // Check and save the expiration setting
        $expiration_enabled = isset($_POST['_promote_product_expiration_enable']) && 'yes' === $_POST['_promote_product_expiration_enable'];
        $expiration_date = $expiration_enabled ? sanitize_text_field($_POST['_promoted_product_expiration_date']) : '';

        update_post_meta($post_id, '_promote_product_expiration_enable', $expiration_enabled ? 'yes' : 'no');
        if ($expiration_enabled) {
            update_post_meta($post_id, '_promoted_product_expiration_date', $expiration_date);
        }
    }


    // Connecting the jQuery UI DatePicker script
    public function enqueue_admin_scripts_and_styles()
    {
        global $pagenow, $typenow;

        if ('post.php' === $pagenow && 'product' === $typenow) {
            // Include jQuery UI styles from your assets/css folder
            wp_enqueue_style('featured-product-promotion-jquery-ui', plugins_url('/assets/css/jquery-ui.css', __FILE__));

            // Connect the jQuery UI DatePicker script for date picker functionality
            wp_enqueue_script('jquery-ui-datepicker');

            // Connect your main.js, which activates the datepicker
            wp_enqueue_script('featured-product-promotion-main-js', plugins_url('/assets/js/main.js', __FILE__), array('jquery', 'jquery-ui-datepicker'), false, true);
        }
    }


    public function save_featured_product_promotion_settings()
    {
        // if (!isset($_POST['featured_product_promotion_settings_nonce']) || !wp_verify_nonce($_POST['featured_product_promotion_settings_nonce'], 'save_featured_product_promotion_settings')) {
        //     wp_die('Security check failed');
        // }

        update_option('woocommerce_featured_product_promotion_title_text', wc_clean($_POST['woocommerce_featured_product_promotion_title_text']));
        update_option('woocommerce_featured_product_promotion_bg_color', wc_clean($_POST['woocommerce_featured_product_promotion_bg_color']));
        update_option('woocommerce_featured_product_promotion_text_color', wc_clean($_POST['woocommerce_featured_product_promotion_text_color']));

        // Save the setting for the countdown timer checkbox
        $countdown_timer_enabled = isset($_POST['woocommerce_featured_product_promotion_enable_countdown']) ? 'yes' : 'no';
        update_option('woocommerce_featured_product_promotion_enable_countdown', $countdown_timer_enabled);
    }


    /**
     * Display the featured product across the site.
     */
    public function display_featured_product()
    {
        if (is_admin()) {
            return;
        }
        // Retrieve the ID of the promoted product from the settings.
        $featured_product_id = get_option('woocommerce_featured_product_id');
        if (!$featured_product_id) {
            // Exit if there's no featured product set.
            return;
        }

        // Check if the promotion has expired.
        $expiration_enabled = get_post_meta($featured_product_id, '_promote_product_expiration_enable', true) === 'yes';
        $expiration_date = '';
        if ($expiration_enabled) {
            $expiration_date = get_post_meta($featured_product_id, '_promoted_product_expiration_date', true);
            $current_date = current_time('Y-m-d');
            if ($current_date > $expiration_date) {
                // Exit if the promotion has expired.
                return;
            }
        }

        // Retrieve the promotion title and product details.
        $promotion_title = get_option('woocommerce_featured_product_promotion_title_text', '');
        $custom_title = get_post_meta($featured_product_id, '_promoted_product_title', true);
        $product = wc_get_product($featured_product_id);
        if (!$product) {
            // Exit if the product cannot be found.
            return;
        }
        $product_name = $custom_title ? $custom_title : $product->get_name();
        $product_link = get_permalink($featured_product_id);

        // Retrieve settings for background and text color.
        $background_color = get_option('woocommerce_featured_product_promotion_bg_color', '#ffffff');
        $text_color = get_option('woocommerce_featured_product_promotion_text_color', '#000000');

        // Display the promotion with the specified settings.
        echo '<div class="flash_sale" style="background-color:' . esc_attr($background_color) . ';color:' . esc_attr($text_color) . ';padding:10px;text-align:center;font-family: inherit;">';
        echo '<strong>' . esc_html($promotion_title) . '</strong> <a href="' . esc_url($product_link) . '" style="color:' . esc_attr($text_color) . ';">' . esc_html($product_name) . '</a>';

        // Check if countdown timer is enabled in the settings
        $countdown_enabled = get_option('woocommerce_featured_product_promotion_enable_countdown', 'no') === 'yes';

        if (!empty($expiration_date) && $countdown_enabled) {
            // Display the countdown timer if the option is enabled
            echo '<div id="promotion_countdown_timer"></div>';

            // Enqueue JavaScript for the countdown timer
            wp_enqueue_script('featured-product-promotion-countdown-js', plugins_url('/assets/js/countdown.js', __FILE__), array('jquery'), null, true);
            wp_localize_script('featured-product-promotion-countdown-js', 'promotionCountdownData', array(
                'expirationDate' => $expiration_date,
                'selector' => '#promotion_countdown_timer'
            ));
        }
        echo '</div>'; // Close .flash_sale div
    }



    /**
     * Enqueues scripts and localizes script strings for translations.
     */
    public function enqueue_and_localize_scripts()
    {
        // Enqueue JavaScript for the countdown timer
        wp_enqueue_script('featured-product-promotion-countdown-js', plugins_url('/assets/js/countdown.js', __FILE__), array('jquery'), null, true);

        // Localize script for translations
        wp_localize_script('featured-product-promotion-countdown-js', 'promotionCountdownStrings', array(
            'expired' => __('EXPIRED', 'wc-promoted-product'),
            'days'    => __('days', 'wc-promoted-product'),
            'hours'   => __('hours', 'wc-promoted-product'),
            'minutes' => __('minutes', 'wc-promoted-product'),
            'seconds' => __('seconds', 'wc-promoted-product'),
        ));
    }


    /**
     * Adds a custom class to highlight featured products in the admin product list.
     *
     * @param array $classes An array of post classes.
     * @param mixed $class One or more classes to add to the class list.
     * @param int $post_id The post ID.
     * @return array The array of modified post classes.
     */
    public function highlight_featured_product_in_admin_list($classes, $class, $post_id)
    {
        // Only for the admin panel and for 'product' post type
        if (is_admin() && get_post_type($post_id) === 'product') {
            // Check if the product is promoted
            $is_promoted = get_post_meta($post_id, '_promote_product', true) === 'yes';
            if ($is_promoted) {
                // Add a custom class
                $classes[] = 'highlight-featured-product';
            }
        }
        return $classes;
    }

    /**
     * Enqueues custom styles for highlighted products in the admin product list.
     */
    public function add_custom_style_for_featured_product()
    {
        $custom_css = "
            .highlight-featured-product {
                background: #ffd7d7 !important;
            }";
        wp_add_inline_style('wp-admin', $custom_css);
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('wc-promoted-product', false, basename(dirname(__FILE__)) . '/languages/');
    }
}


new WC_Featured_Product_Promotion();
