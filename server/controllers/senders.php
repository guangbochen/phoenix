<?php
require_once 'models/sender.php';
require_once 'helpers/json_helper.php';

class Senders
{
    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    // Get all senders
    public function index() 
    {
        echo json_encode(Sender::show_all());
    }

    // Show single sender based on tracking id
    public function show($id)
    {
        $sender = Sender::find($id);
        if ($sender)
            echo json_encode(Sender::find($id));
        else
            response_json_error($this->app, 404, 'Sender Not Found');
    }

    // Create sender
    public function create()
    {
        $request = $this->app->request();
        $body    = $request->getBody();
        $input   = json_decode($body);
        
        echo json_encode(Sender::create($input));
    }

    // Update sender
    public function update($id)
    {
        $request = $this->app->request()->post('sender_detail');
        $input   = array_to_json($request);
        try
        {
            echo json_encode(Sender::update($id, $input));
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    // Delete sender
    public function delete($id)
    {
        try
        {
            Sender::delete($id);
            $this->app->response()->status(204);
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    // Edit sender's photos
    public function edit_photo()
    {
        $photo = $this->app->request()->post('photo');
        try
        {
            $response = array(
                'success' => true,
                'url'     => Sender::edit($photo)
            );
            echo json_encode($response);
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 400, $e->getMessage());
        }
    }
}
