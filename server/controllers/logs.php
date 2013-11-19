<?php
require_once 'models/log.php';
require_once 'helpers/json_helper.php';

class Logs
{
    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    public function index()
    {
    	echo json_encode(Log::show_all());
    }

    public function show_recent($n)
    {
    	echo json_encode(Log::show_recent($n));
    }

    public function show($id)
    {
        try
        {
            echo json_encode(Log::find($id));
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }
}
