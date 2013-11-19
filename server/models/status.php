<?php
use RedBean_Facade as R;
/*
for order_status only display all and create single status is needed.
*/
class Status
{
    public $id;
    public $waybill_id;
    public $time;
    public $context;

    public static function show_all()
    {
        $statuss = R::findAll('statuses');
        return R::exportAll($statuss);
    }

    public static function find($id)
    {
        $status = $this->find($id);
        if ($status)
            return R::exportAll($status);
        else
            return null;
    }

    public static function create($input)
    {
        try
        {
            foreach ($input->waybill_ids as $waybill_id) 
            {
                $status = R::dispense('statuses');
                $status->waybill_id = $waybill_id;
                $status->time       = $input->delivery_time;
                $status->context    = $input->delivery_context;
                R::store($status);
            }
            return 'Success';
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function delete($id)
    {
        $status = R::findOne('order_status', 'id=?', array($id));
        if ($status)
            R::trash($status);
        else
            return null;
    }
}
