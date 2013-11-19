<?php
use RedBean_Facade as R;

class Receiver
{
    public $id;
    public $name;
    public $phone;
    public $company;
    public $address;
    public $city;
    public $state;
    public $country;
    public $postcode;

    public static function show_all()
    {
        $receivers = R::findAll('receivers');
        return R::exportAll($receivers);
    }
	
    public static function find($id)
    {
        $receiver = R::findOne('receivers', 'id=?', array($id));
        if ($receiver)
            return R::exportAll($receiver);
        else
            return NULL;
    }

    public static function create($input)
    {
        try
        {
            $receiver = R::dispense('receivers');
            $receiver->name     = empty($input->receiver_name)     ? NULL : $input->receiver_name;
            $receiver->phone    = empty($input->receiver_phone)    ? NULL : $input->receiver_phone;
            $receiver->company  = empty($input->receiver_company)  ? NULL : $input->receiver_company;
            $receiver->address  = empty($input->receiver_address)  ? NULL : $input->receiver_address;
            $receiver->city     = empty($input->receiver_city)     ? NULL : $input->receiver_city;
            $receiver->state    = empty($input->receiver_state)    ? NULL : $input->receiver_state;
            $receiver->country  = empty($input->receiver_country)  ? NULL : $input->receiver_country;
            $receiver->postcode = empty($input->receiver_postcode) ? NULL : $input->receiver_postcode;
            return R::store($receiver);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function update($id, $input)
    {
        $receiver = R::findOne('receivers', 'id=?', array($id));
        if ($receiver)
        {
            $receiver->name     = empty($input->receiver_name)     ? NULL : $input->receiver_name;
            $receiver->phone    = empty($input->receiver_phone)    ? NULL : $input->receiver_phone;
            $receiver->company  = empty($input->receiver_company)  ? NULL : $input->receiver_company;
            $receiver->address  = empty($input->receiver_address)  ? NULL : $input->receiver_address;
            $receiver->city     = empty($input->receiver_city)     ? NULL : $input->receiver_city;
            $receiver->state    = empty($input->receiver_state)    ? NULL : $input->receiver_state;
            $receiver->country  = empty($input->receiver_country)  ? NULL : $input->receiver_country;
            $receiver->postcode = empty($input->receiver_postcode) ? NULL : $input->receiver_postcode;
            return R::store($receiver);
        }
        else
        {
            return null;
        }
    }

    public static function delete($id)
    {
        $receiver = R::findOne('receivers', 'id=?', array($id));
        if ($receiver)
            R::trash($receiver);
        else
            return NULL;
    }

    public static function not_exists ($input)
    {
        return ((Receiver::update($input->receiver_id, $input) == NULL)
                && ($input->receiver_name   || $input->receiver_phone 
                || $input->receiver_company || $input->receiver_address 
                || $input->receiver_city    || $input->receiver_state 
                || $input->receiver_country || $input->receiver_postcode));
    }
}
