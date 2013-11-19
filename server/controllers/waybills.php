<?php
require_once 'models/waybill.php';
require_once 'models/sender.php';
require_once 'helpers/json_helper.php';

class Waybills
{
    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    public function index() 
    {
        $params = $this->app->request()->params();
        $fields = array_to_json($params);
        if ($params)
        {
            try
            {
                $result = Waybill::search($fields);
                if (count($result) > 0)
                    echo json_encode(Waybill::search($fields));
                else
                    response_json_error($this->app, 404, 'Waybill Not Found');
            }
            catch (Exception $e)
            {
                response_json_error($this->app, 500, $e->getMessage());
            }
        }
        else
        {
            echo json_encode(Waybill::show_all());
        }
    }

    public function show($id)
    {
        $waybill = Waybill::find($id);
        if ($waybill)
            echo json_encode($waybill);
        else
            response_json_error($this->app, 404, 'Waybill Not Found');
    }

    public function create()
    {
        try
        {
            $request = $this->app->request()->getBody();
            $input   = json_decode($request);

            $response = Waybill::create($input);
            // if response is a string then the serial waybills were generated a duplicated item
            if (strcmp(gettype($response), 'string') == 0)
                $this->app->response()->status(409); // 409 stands for conflict in http status

            echo json_encode($response);
        }
        catch (Excetpion $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    public function update($id = NULL)
    {
        $request = $this->app->request()->getBody();
        $input = json_decode($request);

        try
        {
            if ($id)
            {
                echo json_encode(Waybill::batch_update(array(0 => $input)));
            }
            else
            {
                echo json_encode(Waybill::batch_update($input));
            }
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
            Waybill::delete($id);
            $this->app->response()->status(204);
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }

    public function export()
    {
        $request = $this->app->request()->getBody();
        $data = json_decode($request, true);

        try
        {
            echo Waybill::export($data);
        }
        catch (Exception $e)
        {
            response_json_error($this->app, 404, $e->getMessage());
        }
    }
}
