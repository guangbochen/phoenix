<?php
use RedBean_Facade as R;

class Log
{
	public $id;
	public $date_created;
    public $number_of_waybill;
    public $location;
    public $download_link;

	public static function show_all()
	{
		$logs = R::findAll('logs');
        return R::exportAll($logs);
	}

	public static function show_recent($number = 10)
	{
		$logs = R::findAll('logs', ' ORDER BY id LIMIT '.$number);
        return R::exportAll($logs);	
    }

	public static function find($id)
    {
        $log = R::findOne('logs', 'id=?', array($id));
        if ($log)
            return R::exportAll($log);
        else
            return null;
    }

	public static function create($input)
	{
        try
        {
            $log = R::dispense('logs');
            $log->date_created 		= date('d/m/Y H:i:s');
            $log->number_of_waybill = (int)$input->number_of_waybill;
            $log->location 			= $input->location;
            $log->scope 			= $input->scope;
            $log->download_link     = $input->download_link;
            return R::store($log);
        }
        catch (Exeption $e)
        {
            return $e->getMessage();
        }
	}

    public static function get_last()
    {
        return R::findOne('logs', ' ORDER BY id DESC LIMIT 1 ');
    }
}
