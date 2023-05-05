<?php

function son_register_fabricante_metabox()
{
    $cmb_demo = new_cmb2_box(array(
        'id'            => 'son_fabricante_metabox',
        'title'         => esc_html__('Opções', 'cmb2'),
        'object_types'  => array('fabricante'), // Post type
    ));

    $cmb_demo->add_field(array(
        'name'       => esc_html__('E-mail', 'cmb2'),
        'desc'       => esc_html__('E-mail do Fabricante', 'cmb2'),
        'id'         => 'son_email',
        'type'       => 'text_email',
        'attributes' => array(
            'required' => true
        )
    ));
}

add_action('cmb2_admin_init', 'son_register_fabricante_metabox');
/**
 * Hook in and register a metabox to handle a theme options page and adds a menu item.
 */
function son_register_options_metabox()
{

    /**
     * Registers options page menu item and form.
     */
    $cmb_options = new_cmb2_box(array(
        'id'           => 'son_options_page',
        'title'        => esc_html__('Shopify Order Notification', 'cmb2'),
        'object_types' => array('options-page'),

        /*
        * The following parameters are specific to the options-page box
        * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
        */

        'option_key'      => 'son_options', // The option key and admin menu page slug.
        'icon_url'        => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
        // 'menu_title'              => esc_html__( 'Options', 'cmb2' ), // Falls back to 'title' (above).
        'parent_slug'             => 'options-general.php', // Make options page a submenu item of the themes menu.
        // 'capability'              => 'manage_options', // Cap required to view options-page.
        // 'position'                => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
        // 'admin_menu_hook'         => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
        // 'priority'                => 10, // Define the page-registration admin menu hook priority.
        // 'display_cb'              => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
        // 'save_button'             => esc_html__( 'Save Theme Options', 'cmb2' ), // The text for the options-page save button. Defaults to 'Save'.
        // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
        // 'message_cb'              => 'yourprefix_options_page_message_callback',
        // 'tab_group'               => '', // Tab-group identifier, enables options page tab navigation.
        // 'tab_title'               => null, // Falls back to 'title' (above).
        // 'autoload'                => false, // Defaults to true, the options-page option will be autloaded.
    ));

    /**
     * Options fields ids only need
     * to be unique within this box.
     * Prefix is not needed.
     */
    $cmb_options->add_field(array(
        'name'    => esc_html__('X-Shopify-Shop-Domain', 'cmb2'),
        'id'      => 'son_domain',
        'type'    => 'text',
        'attributes' => array(
            'required'      => true
        )
    ));
}

add_action('cmb2_admin_init', 'son_register_options_metabox');

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function son_get_option($key = '', $default = false)
{
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option('son_options', $key, $default);
    }

    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option('son_options', $default);

    $val = $default;

    if ('all' == $key) {
        $val = $opts;
    } elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[$key]) {
        $val = $opts[$key];
    }

    return $val;
}
