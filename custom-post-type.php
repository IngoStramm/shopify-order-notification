<?php
// CPT Fabricante

function son_fabricante_cpt()
{
    $fabricante = new Son_Post_Type(
        'Fabricante', // Nome (Singular) do Post Type.
        'fabricante' // Slug do Post Type.
    );

    $fabricante->set_labels(
        array(
            'menu_name' => __('Fabricantes', 'odin')
        )
    );

    $fabricante->set_arguments(
        array(
            'supports' => array('title')
        )
    );
}

add_action('init', 'son_fabricante_cpt', 1);

// CPT Log

function son_log_cpt()
{
    $log = new Son_Post_Type(
        'Log', // Nome (Singular) do Post Type.
        'log' // Slug do Post Type.
    );

    $log->set_labels(
        array(
            'menu_name' => __('Logs', 'odin')
        )
    );

    $log->set_arguments(
        array(
            'supports' => array('title', 'editor')
        )
    );
}

add_action('init', 'son_log_cpt', 1);

