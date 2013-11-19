<?php
require_once 'controllers/senders.php';
require_once 'controllers/couriers.php';
require_once 'controllers/waybills.php';
require_once 'controllers/logs.php';
require_once 'controllers/statuses.php';
require_once 'controllers/postalAPI.php';
require_once 'controllers/users.php';

// GET HOME PAGE
$app->get('/', function() {
    echo json_encode(array(
        'message'   => 'Phoenix API is healthy',
        'http_code' => 200,
        'status'    => 'OK'
    ));
});

/*------------------*/
/* SENDER RESOURCES */
/*------------------*/

$sender_controller = new Senders;
// Show all senders
$app->get('/senders', function() use ($sender_controller) {
    $sender_controller->index();
});
// Show single sender
$app->get('/senders/:id', function($id) use ($sender_controller) {
    $sender_controller->show($id);
});
// Create sender
$app->post('/senders', function() use ($sender_controller) {
    $sender_controller->create();
});
// Edit sender's photo
$app->post('/senders/edit_photo', function() use($sender_controller) {
    $sender_controller->edit_photo();
});
// Update sender
$app->post('/senders/:id', function($id) use($sender_controller) {
    $sender_controller->update($id);
});
// Delete sender
$app->delete('/senders/:id', function($id) use($sender_controller) {
    $sender_controller->delete($id);
});

/*------------------*/
/* COURIER RESOURCES */
/*------------------*/

$courier_controller = new Couriers;
// Show all couriers
$app->get('/couriers', function() use($courier_controller) {
    $courier_controller->index();
});
// Show single courier
$app->get('/couriers/:id', function($id) use($courier_controller) {
    $courier_controller->show();
});
// Create courier
$app->post('/couriers', function() use($courier_controller) {
    $courier_controller->create();
});
// Update courier
$app->put('/couriers/:id', function($id) use($courier_controller) {
    $courier_controller->update();
});
// Delete courier
$app->delete('/couriers/:id', function($id) use($courier_controller) {
    $courier_controller->delete($id);
});

/*------------------*/
/* WAYBILL RESOURCES */
/*------------------*/
$waybill_controller = new Waybills;

// Show all waybills
$app->get('/waybills', function() use($waybill_controller) {
    $waybill_controller->index();
});
// Show single waybill
$app->get('/waybills/:id', function($id) use($waybill_controller) {
    $waybill_controller->show($id);
});
// Create waybill
$app->post('/waybills', function() use($waybill_controller) {
    $waybill_controller->create();
});
// Update waybill
$app->post('/waybills/batch_update', function() use($waybill_controller) {
    $waybill_controller->update();
});
// Export waybill
$app->post('/waybills/export', function() use($waybill_controller) {
    $waybill_controller->export();
});
// Update waybill
$app->post('/waybills/:id', function($id) use($waybill_controller) {
    $waybill_controller->update($id);
});

/*------------------*/
/* LOG RESOURCES */
/*------------------*/
$log_controller = new Logs;

// Show all logs
$app->get('/logs', function() use($log_controller) {
    $log_controller->index();
});
$app->get('/logs/:id', function($id) use($log_controller) {
    $log_controller->show($id);
});
$app->get('/logs/recent/:n', function($n) use($log_controller) {
    $log_controller->show_recent($n);
});

/*------------------*/
/* STATUS RESOURCES */
/*------------------*/
$status_controller = new Statuses;

// Show all logs
$app->get('/statuses', function() use($status_controller) {
    $status_controller->index();
});
$app->post('/statuses', function() use($status_controller) {
    $status_controller->create();
});

/*--------------------*/
/* Kuaidi100 Postal API */
/*--------------------*/
$postapi_controller = new PostalAPI();
//return index page
$app->get('/postapi', function() use($postapi_controller) {
    $postapi_controller->index();
});
//batch check status for admin page
$app->get('/postapi/batch_check', function() use($postapi_controller) {
    $postapi_controller->batch_check_status();
});
//return html src for admin page
$app->get('/postapi/html/:tracking_id', function($tracking_id) use($postapi_controller) {
    $postapi_controller->get_html_status($tracking_id);
});
//update waybill status returned from postal api for admin page
$app->post('/postapi', function() use($postapi_controller) {
    $postapi_controller->update_waybill_status();
});
//return postalAPI data to client page
$app->post('/postapi/api', function() use($postapi_controller) {
    $postapi_controller->getPostAPI();
});

$user_controller = new Users;
$app->post('/users/login', function() use($user_controller) {
    $user_controller->login();
});
