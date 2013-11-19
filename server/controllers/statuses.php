<?php
require_once 'models/status.php';
require_once 'helpers/json_helper.php';

class Statuses
{
    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    public function index()
    {
        echo json_encode(Status::show_all());
    }

    public function create()
    {
    	try
    	{
            $request = $this->app->request()->getBody();
            $input = json_decode($request);
	        echo json_encode(Status::create($input));
    	}
    	catch (Exception $e)
    	{
            response_json_error($this->app, 404, $e->getMessage());
    	}
    }
}
