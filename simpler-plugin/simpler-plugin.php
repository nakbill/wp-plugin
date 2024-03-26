<?php
/**
 * Plugin Name: WP REST API Simpler
 * Description: Adds a REST route for retrieving specific order details securely via Basic Authentication.
 * Version: 1.0.0
 */


// Register the route
add_action('rest_api_init', 'wp_order_register_route' , 0);

function wp_order_register_route()
{
    // Set the REST API prefix to '/api/'
    add_filter('rest_url_prefix', function () {
        return 'api';
    });

    // Get basic authentication credentials from plugin settings
    $username = get_option('simpler_auth_username');
    $password = get_option('simpler_auth_password');

    register_rest_route('wp-order-api/v1',
        '/order/(?P<id>\d+)',
        array(
            'methods'             => 'GET',
            'callback'            => 'wp_order_api_get_order',
            'permission_callback' => function () use ($username, $password) {
                // If credentials are not defined, return error message
                if (empty($username) || empty($password)) {
                    return new WP_Error('rest_forbidden', esc_html__('Please configure basic authentication credentials in plugin settings.', 'my-text-domain'), array('status' => rest_authorization_required_code()));
                }

                $user = $_SERVER['PHP_AUTH_USER'] ?? null;
                $pass = $_SERVER['PHP_AUTH_PW'] ?? null;
                // Perform Basic Authentication
                if (!is_user_logged_in() && ($user !== $username || $pass !== $password)) {
                    return new WP_Error('rest_forbidden', esc_html__('You do not have permissions to access this resource.', 'my-text-domain'), array('status' => rest_authorization_required_code()));
                }

                return true;
            },
        )
    );
}

function wp_order_api_get_order($data)
{
    $order_id = $data['id'];
    $order = wc_get_order($order_id);

    // Check if the order exists
    if (!$order) {
        return new WP_Error('missing_order', 'Order was not found', array('status' => 404));
    }

    // Prepare order data
    $order_data = array(
        'id'             => $order->get_id(),
        'paymentMethod'  => $order->get_payment_method(),
        'totalCents'     => (float)$order->get_total(),
        'createdAt'      => $order->get_date_created()->date('Y-m-d\TH:i:s.u\Z'),
        'items'          => array(),
        'shippingMethod' => array(
            'title'     => $order->get_shipping_method(),
            'costCents' => (float)$order->get_shipping_total(),
        ),
    );

    // Add items data
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $order_data['items'][] = array(
            'id'        => $item_id,
            'title'     => $product ? $product->get_title() : $item->get_name(),
            'quantity'  => (int)$item->get_quantity(),
            'costCents' => (float)$item->get_total(),
        );
    }

    return $order_data;
}

// Add settings link under plugin description on the Plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'simpler_settings_link');
function simpler_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=simpler-settings') . '">Settings</a>';
    array_unshift($links, $settings_link); // Add the link at the beginning of the array
    return $links;
}

// Register plugin settings
add_action('admin_init', 'simpler_register_settings');
function simpler_register_settings() {
    register_setting('simpler-settings-group', 'simpler_auth_username');
    register_setting('simpler-settings-group', 'simpler_auth_password');
}

// Add menu item and settings page
add_action('admin_menu', 'simpler_add_admin_menu');
function simpler_add_admin_menu() {
    add_options_page('Simpler Settings', 'Simpler', 'manage_options', 'simpler-settings', 'simpler_settings_page');
}

// Settings page callback
function simpler_settings_page() {
    ?>
    <div class="wrap">
        <h2>Simpler Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('simpler-settings-group'); ?>
            <?php do_settings_sections('simpler-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add settings sections and fields
add_action('admin_init', 'simpler_settings_init');
function simpler_settings_init() {
    add_settings_section('simpler-auth-section', 'Basic Authentication Settings', 'simpler_auth_section_callback', 'simpler-settings');

    add_settings_field('simpler-auth-username', 'Username', 'simpler_auth_username_field', 'simpler-settings', 'simpler-auth-section');
    add_settings_field('simpler-auth-password', 'Password', 'simpler_auth_password_field', 'simpler-settings', 'simpler-auth-section');
}

// Section callback
function simpler_auth_section_callback() {
    echo '<p>Enter your basic authentication credentials here.</p>';
}

// Username field callback
function simpler_auth_username_field() {
    $username = get_option('simpler_auth_username');
    echo '<input type="text" name="simpler_auth_username" value="' . esc_attr($username) . '" />';
}

// Password field callback
function simpler_auth_password_field() {
    $password = get_option('simpler_auth_password');
    echo '<input type="password" name="simpler_auth_password" value="' . esc_attr($password) . '" />';
}
