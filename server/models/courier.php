<?php
use RedBean_Facade as R;

class Courier
{
    public $id;
    public $name;

    public static function show_all()
    {
        try
        {
            $couriers = R::findAll('couriers');
            return R::exportAll($couriers);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function show($id)
    {
        try 
        {
            $courier = R::findOne('couriers', 'id=?', array($id));
            return R::exportAll($courier);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function create($input)
    {
        try
        {
            $courier = R::dispense('couriers');
            $courier->name = $input->courier_value;
            return R::store($courier);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function update($id, $input)
    {
        $courier = R::findOne('couriers', 'id=?', array($id));
        if($courier)
        {
            $courier->name = $input->courier_value;
            return R::store($courier);
        }
        else
        {
            return 'Courier not found';
        }
    }

    public static function delete($id)
    {
        $courier = R::findOne('couriers', 'id=?', array($id));
        if($courier)
            R::trash($courier);
        else
            return 'Courier not found';
    }

    public static function find($name)
    {
        $courier = R::findOne('couriers', 'name=?', array($name));
        if($courier)
            return $courier;
        else
            return null;
    }
}
