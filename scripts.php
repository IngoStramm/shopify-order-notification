<?php

add_action('wp_enqueue_scripts', 'son_frontend_scripts');

function son_frontend_scripts()
{

    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';

    if (empty($min)) :
        wp_enqueue_script('son-livereload', 'http://localhost:35729/livereload.js?snipver=1', array(), null, true);
    endif;

    wp_register_script('son-script', SON_URL . 'assets/js/son' . $min . '.js', array('jquery'), '1.0.0', true);

    wp_enqueue_script('son-script');

    wp_localize_script('son-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_style('son-style', SON_URL . 'assets/css/son.css', array(), false, 'all');
}
