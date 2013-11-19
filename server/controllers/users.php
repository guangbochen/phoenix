<?php
require_once 'helpers/json_helper.php';

class Users
{
    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    public function login()
    {
        $request = $this->app->request()->getBody();
        $input = json_decode($request);

        if ($input->username == 'admin' && $input->password == 'admin') 
        {
            echo json_encode($input->username);
        }
        else
        {
            $this->app->response()->status(401);
            echo 'Wrong username or password';
        }
    }
}
