<?php
require_once 'models/courier.php';
require_once 'helpers/json_helper.php';

class Couriers
{
    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    public function index() 
    {
        try
        {
            echo json_encode(Courier::show_all());
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    public function show($id)
    {
        try
        {
            echo json_encode(Courier::show($id));
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    public function create()
    {
        try
        {
            $request = $this->app->request();
            $body    = $request->getBody();
            $input   = json_decode($body);
            echo json_encode(Courier::create($input));
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    public function update($id)
    {
        try
        {
            $request = $this->app->request();
            $body    = $request->getBody();
            $input   = json_decode($body);
            echo json_encode(Courier::update($id, $input));
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    public function delete($id)
    {
        try
        {
            Courier::delete($id);
            $this->app->response()->status(204);
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }
}
