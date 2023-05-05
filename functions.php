<?php

function son_implode_all($glue, $arr)
{
    if (!is_array($arr)) {
        return $arr;
    }
    for ($i = 0; $i < count($arr); $i++) {
        if (@is_array($arr[$i]))
            $arr[$i] = son_implode_all($glue, $arr[$i]);
    }
    return implode($glue, $arr);
}

/**
 * Grab latest post title by an author!
 *
 * @param array $data Options for the function.
 * @return string|null Post title for the latest, * or null if none.
 */
function son_new_order_notification(WP_REST_Request $request)
{
    $data = $request['data'];
    $url_params = $request->get_url_params();
    $query_params = $request->get_query_params();
    $body_params = $request->get_body_params();
    $json_params = $request->get_json_params();
    $default_params = $request->get_default_params();

    $content = '';
    $content .= '<h1>Data</h1>';
    $content .= son_implode_all(', ', $data) . '<br />';
    $content .= '<h1>URL Params</h1>';
    $content .= son_implode_all(', ', $url_params) . '<br />';
    $content .= '<h1>Query Params</h1>';
    $content .= son_implode_all(', ', $query_params) . '<br />';
    $content .= '<h1>Body Params</h1>';
    $content .= son_implode_all(', ', $body_params) . '<br />';
    $content .= '<h1>JSON Params</h1>';
    $content .= son_implode_all(', ', $json_params) . '<br />';
    $content .= '<h1>Default Params</h1>';
    $content .= son_implode_all(', ', $default_params);

    $new_post = array(
        'post_title'    => 'Teste de Log',
        'post_content'  => $content,
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'     => 'log'
    );

    // Insert the post into the database
    return wp_insert_post($new_post);
}

add_action('rest_api_init', function () {
    register_rest_route('son/v1', '/notification', array(
        'methods' => 'GET',
        'callback' => 'son_new_order_notification',
        'permission_callback' => '__return_true',
    ));
});

// Retorna todos os valores de um array, mesmo se for um array multidimensional

function son_get_all_values($array)
{
    $output = '';
    if (!is_array($array)) {
        return $output;
    }

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            son_get_all_values($value);
        } else {
            $output .= '<pre>' . $key . ': ' . $value . '</pre>';
        }
    }
    return $output;
}

// Envia as notificações por e-mail
function son_email_notification($order_number, $order_id, $f_name, $endereco, $complemento, $city, $country, $phone, $zip, $financial_status, $order_value, $shipping_province, $items, $created_at, $updated_at, $shipping_method, $email_to)
{
    // Envia o e-mail de notificação

    $email_subject = 'Novo Pedido ABCTalk - Shopify: ' . $order_number;

    $email_body = 'Detalhes do pedido:' . '<br />';
    $email_body .= 'ID do Pedido: ' . $order_id . '<br />';
    $email_body .= 'Número do Pedido: ' . $order_number . '<br />';
    $email_body .= 'Nome: ' . $f_name . '<br />';
    $email_body .= 'Endereço: ' . $endereco . '<br />';
    $email_body .= 'Complemento: ' . $complemento . '<br />';
    $email_body .= 'Cidade: ' . $city . '<br />';
    $email_body .= 'País: ' . $country . '<br />';
    $email_body .= 'Telefone: ' . $phone . '<br />';
    $email_body .= 'CEP: ' . $zip . '<br />';
    $email_body .= 'Status Financeiro: ' . $financial_status . '<br />';
    $email_body .= 'Valor do Pedido: ' . $order_value . '<br />';
    $email_body .= 'UF: ' . $shipping_province . '<br />';

    foreach ($items as $item) {
        $email_body .= $item;
    }

    $email_body .= 'Criado em: ' . $created_at . '<br />';
    $email_body .= 'Atualizado em: ' . $updated_at . '<br />';
    $email_body .= 'Método de Entrega: ' . $shipping_method . '<br />';

    // get WordPress site name
    $site_name = get_bloginfo('name');

    // get WordPress site domain without slashes and protocoll
    $site_domain = str_replace(
        array('http://', 'https://', '/', '\\'),
        '',
        get_site_url()
    );

    // Set email headers
    $email_headers = array('Content-Type: text/html; charset=UTF-8', 'From: ' . $site_name . '<noreply@' . $site_domain . '>');

    // Send email
    return wp_mail($email_to, $email_subject, $email_body, $email_headers);
}

// Cria um novo LOG
function son_new_log_post($check_email, $check_domain, $headers, $webhook_content, $order_number, $created_at, $fabricante)
{
    // Cria o novo post de LOG
    $content = '';
    $content .= isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) ? '<h1>X-Shopify-Shop-Domain</h1>' . $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] . '<br />' : '';
    $content .= '<h1>Fabricante</h1>' . $fabricante . '<br />';
    $content .= '<h1>Status do E-mail:</h1>' . $check_email . '<br />';
    $content .= '<h1>Verificação de domínio:</h1>' . $check_domain . '<br />';
    $content .= '<h1>Headers</h1>' . son_get_all_values($headers) . '<br />';
    $content .= '<h1>Content</h1>' . son_get_all_values($webhook_content);
    $content .= '<h1>Body</h1>' . @file_get_contents('php://input') . '<br />';
    $new_post = array(
        'post_title'    => 'Log do Pedido ' . $order_number . ' (' . $fabricante . '), criado em ' . $created_at,
        'post_content'  => $content,
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'     => 'log'
    );

    // Insert the post into the database
    return wp_insert_post($new_post);
}

// Shortcode

function son_new_order_shortcode()
{
    $headers = getallheaders();
    // Load variables
    $webhook_content = NULL;

    // Get webhook content from the POST
    $webhook = fopen('php://input', 'rb');
    while (!feof($webhook)) {
        $webhook_content .= fread($webhook, 4096);
    }

    fclose($webhook);

    // if (!son_get_option('son_domain')) {
    //     return;
    // }

    // if (!isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) || !$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) {
    //     return;
    // }

    $check_domain = son_get_option('son_domain') === $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];

    // Decode Shopify POST
    $webhook_content = json_decode($webhook_content, TRUE);

    // Pega os valores do pedido 
    $order_id = $webhook_content['id'];
    $order_number = $webhook_content['name'];
    $f_name = $webhook_content['billing_address']['name'];
    $endereco = $webhook_content['billing_address']['address1'];
    $complemento = $webhook_content['billing_address']['address2'];
    $city = $webhook_content['billing_address']['city'];
    $country = $webhook_content['billing_address']['country'];
    $phone = $webhook_content['billing_address']['phone'];
    $zip = $webhook_content['billing_address']['zip'];
    $payment_gateway = $webhook_content['gateway'];
    $financial_status = $webhook_content['financial_status'];
    $pick_status = $webhook_content['NULL'];
    $pack_status = $webhook_content['NULL'];
    $fulfill_status = $webhook_content['NULL'];
    $order_value = $webhook_content['total_price'];
    $order_status = $webhook_content['NULL'];
    $shipping_province = $webhook_content['shipping_address']['province'];

    // Pega os produtos do pedido
    $items = '';
    $destinatarios = [];
    foreach ($webhook_content['line_items'] as $item) {
        // $items .= '<pre>' . son_get_all_values($item) . '</pre>';
        $produto = '<ul>';
        $produto .= '<li>ID: ' . $item['id'] . '</li>';
        $produto .= '<li>Título: ' . $item['name'] . '</li>';
        $produto .= '<li>Variação: ' . $item['variant_title'] . '</li>';
        $produto .= '<li>Qtd: ' . $item['quantity'] . '</li>';
        $produto .= '<li>Preço: ' . $item['price'] . '</li>';
        $produto .= '<li>Fabricante: ' . $item['vendor'] . '</li>';
        $produto .= '</ul>';
        $items .= $produto;
        // $destinatarios[$item['vendor']] = [];
        $destinatarios[$item['vendor']][$item['id']] = $produto;
    }

    $created_at = $webhook_content['created_at'];
    $created_at = strtotime($created_at);
    $created_at = date('d/m/Y H:i', $created_at);

    $updated_at = $webhook_content['updated_at'];
    $updated_at = strtotime($updated_at);
    $updated_at = date('d/m/Y H:i', $updated_at);

    $shipping_method = $webhook_content['shipping_lines'][0]['title'];

    $emails_to = [];
    foreach ($destinatarios as $destinatario => $produtos) {
        // No WordPress, pegar todos os posts do tipo "log" que possuam o post_title do post igual ao valor de $destinatario
        $args = array(
            'post_type' => 'fabricante',
            'posts_per_page' => -1,
            'title' => $destinatario
        );

        $logs = new WP_Query($args);

        if ($logs->have_posts()) {
            while ($logs->have_posts()) {
                $logs->the_post();
                $emails_to[$destinatario] = get_post_meta(get_the_ID(), 'son_email', true);
            }
        }

        wp_reset_postdata(); // Restaurar dados originais do post global do WordPress.
    }

    if (count($emails_to) <= 0) {
        $new_post = array(
            'post_title'    => 'Erro, emails_to vazio',
            'post_content'  => son_get_all_values($emails_to),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'log'
        );

        // Insert the post into the database
        return wp_insert_post($new_post);
    } else {
        // $content = '';
        foreach ($emails_to as $fabricante => $email_to) {
            // $content .= '<h1>' . $email_to . '</h1>';
            $produtos = $destinatarios[$fabricante];
            // foreach ($produtos as $produto) {
            //     $content .= $produto;
            // }
            $check_email = son_email_notification($order_number, $order_id, $f_name, $endereco, $complemento, $city, $country, $phone, $zip, $financial_status, $order_value, $shipping_province, $produtos, $created_at, $updated_at, $shipping_method, $email_to);

            son_new_log_post($check_email, $check_domain, $headers, $webhook_content, $order_number, $created_at, $fabricante);
        }
        // $new_post = array(
        //     'post_title'    => 'E-mails existem!',
        //     'post_content'  => $content,
        //     'post_status'   => 'publish',
        //     'post_author'   => 1,
        //     'post_type'     => 'log'
        // );

        // Insert the post into the database
        // return wp_insert_post($new_post);
    }
}

add_shortcode('son-novo-pedido', 'son_new_order_shortcode');
