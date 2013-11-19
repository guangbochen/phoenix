<?php
use RedBean_Facade as R;

class City
{
    public $id;
    public $name;

    public static function show_all()
    {
        try
        {
            $cities = R::findAll('cities');
            return R::exportAll($cities);
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
            $city = R::findOne('cities', 'id=?', array($id));
            return R::exportAll($city);
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
            $city = R::dispense('cities');
            $city->name = $input->city_value;
            return R::store($city);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function update($id, $input)
    {
        $city = R::findOne('cities', 'id=?', array($id));
        if($city)
        {
            $city->name = $input->city_value;
            return R::store($city);
        }
        else
        {
            return null;
        }
    }

    public static function delete($id)
    {
        $city = R::findOne('cities', 'id=?', array($id));
        if($city)
            R::trash($city);
        else
            return 'city not found';
    }

    public static function find($name)
    {
        $city = R::findOne('cities', 'name=?', array($name));
        if($city)
            return $city;
        else
            return null;
    }
}
