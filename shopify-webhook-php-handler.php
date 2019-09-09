<?php
define('SHOPIFY_APP_SECRET', '***SHOPIFY_APP_SECRET****');
add_action('init','shopify_customer_webhook');
function shopify_customer_webhook(){
   
    if (isset($_GET['shopify_customer_webhook']) && $_GET['shopify_customer_webhook'] == 'init' ):

        $data = file_get_contents('php://input');
        $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
        $verified = shopify_customer_verify_webhook($data, $hmac_header);
        error_log('Webhook verified: '.var_export($verified, true)); //check error.log to see the result

        $webhook_content = json_decode($data);

        
        $first_name = $webhook_content->first_name ; //$$webhook_content->last_name;
        $last_name = $webhook_content->last_name ; //$$webhook_content->last_name;
        $full_name = $first_name . ' ' . $last_name;
        $email = $webhook_content->email;

        $user = get_user_by( 'email', $email );
        $user_id = $user->ID;

        if ($user_id <> ''):
            update_user_meta($user_id, 'account_status', 'exist' );
            mail('rnzdev@gmail.com','TEST CUSTOMER WEBHOOK ' . $email . ' ' . $user_id , $data);
        endif;

        exit;
    endif;
}
function shopify_customer_verify_webhook($data, $hmac_header){
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, true));
    return hash_equals($hmac_header, $calculated_hmac);
}