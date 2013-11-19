<?php
use RedBean_Facade as R;
require_once 'models/waybill.php';
require_once 'models/log.php';
require_once 'models/courier.php';
require_once 'models/city.php';
require_once 'models/sender.php';
require_once 'models/package.php';
require_once 'models/receiver.php';

class Waybill
{
    public static function show_all()
    {   
        $ordinary_waybills = R::findAll('waybills');
        $waybills = array();
        try
        {
            foreach ($ordinary_waybills as $ordinary_waybill)
            {    
                array_push($waybills, Waybill::get_complete_waybill($ordinary_waybill));
            }   
            return R::exportAll($waybills);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function search($fields)
    {
        $tracking_id = empty($fields->tracking_id) ? NULL : $fields->tracking_id;
        $location    = empty($fields->location)    ? NULL : $fields->location;
        $order_date  = empty($fields->order_date)  ? NULL : $fields->order_date;
        $flight_date = empty($fields->flight_date) ? NULL : $fields->flight_date;

        $courier_id = Courier::find($fields->courier) ? Courier::find($fields->courier)->id : NULL;
        $city_id    = City::find($fields->arrival) ? City::find($fields->arrival)->id : NULL;

        $ordinary_waybills = Waybill::searched_waybills ($tracking_id, $location, 
                            $order_date, $courier_id, $flight_date, $city_id);

        $waybills = array();
        foreach ($ordinary_waybills as $ordinary_waybill)
            array_push($waybills, Waybill::get_complete_waybill($ordinary_waybill));
        return R::exportAll($waybills);
    }

    // Find individual waybill
    public static function find($tracking_id)
    {
        $waybill = R::findOne('waybills', 'tracking_id=?', array($tracking_id));
        if($waybill)
            return R::exportAll(Waybill::get_complete_waybill($waybill));
        else
            return NULL;
    }

    // Create waybill contains only tracking id and location
    public static function create($input)
    {
        try
        {
            $begin_number = (int)$input->begin_number;
            $end_number   = (int)$input->end_number;
            $format       = (string)$input->format;

            for ($i = $begin_number; $i <= $end_number; $i++) 
            {
                $tracking_id = Waybill::tracking_id($format, $i);

                if (R::findOne('waybills', 'tracking_id=?', array($tracking_id)))
                {
                    // Duplicated tracking return and terminate loop
                    return $tracking_id;
                }
                else
                {
                    $waybill = R::dispense('waybills');

                    $waybill->tracking_id = $tracking_id;
                    $waybill->location    = $input->location;

                    R::store($waybill);
                }
            }
            // Generate log info;
            $format_begin_number      = Waybill::tracking_id($format, $begin_number);
            $format_end_number        = Waybill::tracking_id($format, $end_number);
            $input->download_link     = Waybill::export(Waybill::show_recent($format_begin_number, $format_end_number));
            $input->number_of_waybill = (($end_number - $begin_number) == 0) ? 1 : ($end_number - $begin_number);
            $input->scope             = ($input->number_of_waybill == 1) ? "$format_begin_number" : "$format_begin_number - $format_end_number";
            $log_id                   = Log::create($input);
            return Log::find($log_id);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function show_recent($begin, $end)
    {
        $ordinary_waybills = R::find('waybills', 'tracking_id BETWEEN ? AND ?', 
                             array($begin, $end));
        $waybills = array();
        foreach ($ordinary_waybills as $ordinary_waybill)
            array_push($waybills, Waybill::get_complete_waybill($ordinary_waybill));
        return $waybills;
    }
    

    // Convert id into tracking_id with format 'PHE00' + id + 'AU'
    private static function tracking_id($format, $number)
    {
        if ($number < 10)
            return $format.'000000'.$number;
        else if ($number < 100)
            return $format.'00000'.$number;
        else if ($number < 1000)
            return $format.'0000'.$number;
        else if ($number < 10000)
            return $format.'000'.$number;
        else if ($number < 100000)
            return $format.'00'.$number;
        else
            return $format.$number;
    }

    // Batch Update Courier
    public static function batch_update($input)
    {
        try
        {
            foreach ($input as $changes) 
            {
                $changes->sender_id   = Sender::not_exists($changes) ? Sender::create($changes) : $changes->sender_id;
                $changes->receiver_id = Receiver::not_exists($changes) ? Receiver::create($changes) : $changes->receiver_id;
                $changes->package_id  = Package::not_exists($changes) ? Package::create($changes) : $changes->package_id;
                $changes->courier_id  = Courier::find($changes->courier_value) ? Courier::find($changes->courier_value)->id : $changes->courier_id;
                $changes->city_id     = City::find($changes->city_name) ? City::find($changes->city_name)->id : $changes->city_id;

                Waybill::update($changes->tracking_id, $changes);
            }
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    // Update waybill
    public static function update($tracking_id, $input)
    {
        try
        {
            $waybill = R::findOne('waybills', 'tracking_id=?', array($tracking_id));

            $waybill->order_date     = empty($input->order_date)     ? NULL : $input->order_date;
            $waybill->flight_date    = empty($input->flight_date)    ? NULL : $input->flight_date;
            $waybill->location       = empty($input->location)       ? NULL : $input->location;
            $waybill->postage        = empty($input->postage)        ? NULL : $input->postage;
            $waybill->insurance      = empty($input->insurance)      ? NULL : $input->insurance;
            $waybill->tax            = empty($input->tax)            ? NULL : $input->tax;
            $waybill->packing_charge = empty($input->packing_charge) ? NULL : $input->packing_charge;
            $waybill->total_price    = empty($input->total_price)    ? NULL : $input->total_price;
            $waybill->agent_number   = empty($input->agent_number)   ? NULL : $input->agent_number;
            $waybill->agent_price    = empty($input->agent_price)    ? NULL : $input->agent_price;
            $waybill->waybill_status = empty($input->waybill_status) ? NULL : $input->waybill_status;
            $waybill->express_number = empty($input->express_number) ? NULL : $input->express_number; 
            $waybill->note           = empty($input->note)           ? NULL : $input->note;
            $waybill->sender_id      = empty($input->sender_id)      ? NULL : $input->sender_id;
            $waybill->receiver_id    = empty($input->receiver_id)    ? NULL : $input->receiver_id;
            $waybill->package_id     = empty($input->package_id)     ? NULL : $input->package_id;
            $waybill->courier_id     = empty($input->courier_id)     ? NULL : $input->courier_id;
            $waybill->city_id        = empty($input->city_id)        ? NULL : $input->city_id;

            return R::store($waybill);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    // Delete waybill
    public static function delete($tracking_id)
    {
        $waybill = R::findOne('waybills', 'tracking_id=?', array($tracking_id));
        if($waybill)
            R::trash($waybill);
        else
            return NULL;
    }

    // Export waybill's information into spreadsheet file
    public static function export($data)
    {
        $objPHPExcel = new PHPExcel();
        $s3    = new S3('AKIAJPXOW4B3PBE4XGGQ', 'RRD1+pbpgC9H29J4QYB/JTLaLbzqg9PWS7PcnzMq');

        $file_path   = 'assets/'.uniqid().'-phoenix-report-'.date('d-m-Y').'.xlsx';
        try
        {
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Tracking Id');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Receiver Name');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Receiver Address');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Receiver Phone');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Receiver Postcode');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Total Weight');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Description');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Note');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Order Date');
            $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Flight Date');
            $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Arrival');
            $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Location');
            $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Postage');
            $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Insurance');
            $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Tax');
            $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Packing Charge');
            $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Total Price');
            $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Agent Number');
            $objPHPExcel->getActiveSheet()->setCellValue('S1', 'Agent Price');
            $objPHPExcel->getActiveSheet()->setCellValue('T1', 'Express Company');
            $objPHPExcel->getActiveSheet()->setCellValue('U1', 'Express Number');
            $objPHPExcel->getActiveSheet()->setCellValue('V1', 'Quantity');
            $objPHPExcel->getActiveSheet()->setCellValue('W1', 'Original Weight');
            $objPHPExcel->getActiveSheet()->setCellValue('X1', 'Claim Value');
            $objPHPExcel->getActiveSheet()->setCellValue('Y1', 'Staff Signature');
            $objPHPExcel->getActiveSheet()->setCellValue('Z1', 'Sender Name');
            $objPHPExcel->getActiveSheet()->setCellValue('AA1', 'Sender Address');
            $objPHPExcel->getActiveSheet()->setCellValue('AB1', 'Sender City');
            $objPHPExcel->getActiveSheet()->setCellValue('AC1', 'Sender State');
            $objPHPExcel->getActiveSheet()->setCellValue('AD1', 'Sender Country');
            $objPHPExcel->getActiveSheet()->setCellValue('AE1', 'Sender Postcode');
            $objPHPExcel->getActiveSheet()->setCellValue('AF1', 'Sender Phone');
            $objPHPExcel->getActiveSheet()->setCellValue('AG1', 'Receiver City');
            $objPHPExcel->getActiveSheet()->setCellValue('AH1', 'Receiver State');
            $objPHPExcel->getActiveSheet()->setCellValue('AI1', 'Receiver Country');

            $objPHPExcel->getActiveSheet()->getStyle('A1:AI1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:AI1')->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A1:AI1')->applyFromArray( array(
                                                                'fill' => array(
                                                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                                                    'color' => array('rgb' => 'D7E2D6')
                                                                )));

            try
            {
                foreach ($data as $key => $value)
                {
                    $index = $key + 2;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['tracking_id'])
                                                ->setCellValue('B' . $index, $value['receiver_name'])
                                                ->setCellValue('C' . $index, $value['receiver_address'])
                                                ->setCellValue('D' . $index, $value['receiver_phone'])
                                                ->setCellValue('E' . $index, $value['receiver_postcode'])
                                                ->setCellValue('F' . $index, $value['package_total_weight'])
                                                ->setCellValue('G' . $index, $value['package_description'])
                                                ->setCellValue('H' . $index, $value['note'])
                                                ->setCellValue('I' . $index, $value['order_date'])
                                                ->setCellValue('J' . $index, $value['flight_date'])
                                                ->setCellValue('K' . $index, $value['city_name'])
                                                ->setCellValue('L' . $index, $value['location'])
                                                ->setCellValue('M' . $index, $value['postage'])
                                                ->setCellValue('N' . $index, $value['insurance'])
                                                ->setCellValue('O' . $index, $value['tax'])
                                                ->setCellValue('P' . $index, $value['packing_charge'])
                                                ->setCellValue('Q' . $index, $value['total_price'])
                                                ->setCellValue('R' . $index, $value['agent_number'])
                                                ->setCellValue('S' . $index, $value['agent_price'])
                                                ->setCellValue('T' . $index, $value['courier_name'])
                                                ->setCellValue('U' . $index, $value['package_quantity'])
                                                ->setCellValue('V' . $index, $value['package_original_weight'])
                                                ->setCellValue('W' . $index, $value['package_claim_value'])
                                                ->setCellValue('X' . $index, $value['package_staff_signature'])
                                                ->setCellValue('Y' . $index, $value['sender_name'])
                                                ->setCellValue('Z' . $index, $value['sender_address'])
                                                ->setCellValue('AA' . $index, $value['sender_city'])
                                                ->setCellValue('AB' . $index, $value['sender_state'])
                                                ->setCellValue('AC' . $index, $value['sender_country'])
                                                ->setCellValue('AD' . $index, $value['sender_country'])
                                                ->setCellValue('AE' . $index, $value['sender_postcode'])
                                                ->setCellValue('AF' . $index, $value['sender_phone'])
                                                ->setCellValue('AG' . $index, $value['receiver_phone'])
                                                ->setCellValue('AH' . $index, $value['receiver_state'])
                                                ->setCellValue('AI' . $index, $value['receiver_country']);
                }
            }
            catch (Exception $e)
            {
                foreach ($data as $key => $value)
                {
                    $index = $key + 2;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['tracking_id'])
                                                ->setCellValue('B' . $index, '')
                                                ->setCellValue('C' . $index, '')
                                                ->setCellValue('D' . $index, '')
                                                ->setCellValue('E' . $index, '')
                                                ->setCellValue('F' . $index, '')
                                                ->setCellValue('G' . $index, '')
                                                ->setCellValue('H' . $index, '')
                                                ->setCellValue('I' . $index, '')
                                                ->setCellValue('J' . $index, '')
                                                ->setCellValue('K' . $index, '')
                                                ->setCellValue('L' . $index, $value['location'])
                                                ->setCellValue('M' . $index, '')
                                                ->setCellValue('N' . $index, '')
                                                ->setCellValue('O' . $index, '')
                                                ->setCellValue('P' . $index, '')
                                                ->setCellValue('Q' . $index, '')
                                                ->setCellValue('R' . $index, '')
                                                ->setCellValue('S' . $index, '')
                                                ->setCellValue('T' . $index, '')
                                                ->setCellValue('U' . $index, '')
                                                ->setCellValue('V' . $index, '')
                                                ->setCellValue('W' . $index, '')
                                                ->setCellValue('X' . $index, '')
                                                ->setCellValue('Y' . $index, '')
                                                ->setCellValue('Z' . $index, '')
                                                ->setCellValue('AA' . $index,'')
                                                ->setCellValue('AB' . $index,'')
                                                ->setCellValue('AC' . $index,'')
                                                ->setCellValue('AD' . $index,'')
                                                ->setCellValue('AE' . $index,'')
                                                ->setCellValue('AF' . $index,'')
                                                ->setCellValue('AG' . $index,'')
                                                ->setCellValue('AH' . $index,'')
                                                ->setCellValue('AI' . $index,'');
                }
            }
  
            $objPHPExcel->setActiveSheetIndex(0);

            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->setPreCalculateFormulas(false);
            $writer->save($file_path);

            return $file_path;
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function count()
    {
        return R::count('waybills');
    }

    // Get complete information from waybill
    private static function get_complete_waybill($ordinary_waybill)
    {
        $waybill = R::dispense('waybill');

        $waybill->id             = $ordinary_waybill->id;
        $waybill->tracking_id    = $ordinary_waybill->tracking_id;
        $waybill->order_date     = $ordinary_waybill->order_date;
        $waybill->flight_date    = $ordinary_waybill->flight_date;
        $waybill->location       = $ordinary_waybill->location;
        $waybill->postage        = $ordinary_waybill->postage;
        $waybill->insurance      = $ordinary_waybill->insurance;
        $waybill->tax            = $ordinary_waybill->tax;
        $waybill->packing_charge = $ordinary_waybill->packing_charge;
        $waybill->total_price    = $ordinary_waybill->total_price;
        $waybill->agent_number   = $ordinary_waybill->agent_number;
        $waybill->agent_price    = $ordinary_waybill->agent_price;
        $waybill->note           = $ordinary_waybill->note;
        $waybill->express_number = $ordinary_waybill->express_number;
        $waybill->waybill_status = $ordinary_waybill->waybill_status;

        $waybill->sender   = R::findOne('senders',   'id=?', array($ordinary_waybill->sender_id));
        $waybill->receiver = R::findOne('receivers', 'id=?', array($ordinary_waybill->receiver_id));
        $waybill->package  = R::findOne('packages',  'id=?', array($ordinary_waybill->package_id));
        $waybill->courier  = R::findOne('couriers',  'id=?', array($ordinary_waybill->courier_id));
        $waybill->city     = R::findOne('cities',    'id=?', array($ordinary_waybill->city_id));

        return $waybill;
    }

    // SEARCH
    private static function searched_waybills($tracking_id, $location, $order_date, 
                                              $courier_id, $flight_date, $city_id)
    {
        // if search flight date and city then the rest is ignored
        if ($flight_date || $city_id)
        {
            // Search flight date
            if ($flight_date && $city_id == NULL)
                return R::find('waybills', 'flight_date=?', array($flight_date));

            // Search city
            if ($flight_date == NULL && $city_id)
                return R::find('waybills', 'city_id=?', array($city_id));

            // Search flight date and city
            else if ($flight_date && $city_id)
                return R::find('waybills', 'flight_date=? AND city_id=?', array($flight_date, $city_id));
        }
        else
        {
            // Search tracking id
            if ($tracking_id && $location == NULL && $order_date == NULL && $courier_id == NULL)
                return R::find('waybills', 'tracking_id=?', array($tracking_id));

            // Search tracking id and location
            else if ($tracking_id && $location && $order_date == NULL && $courier_id == NULL)
                return R::find('waybills', 'tracking_id=? AND location=?', array($tracking_id, $location));

            // Search tracking id and order date
            else if ($tracking_id && $location == NULL && $order_date && $courier_id == NULL)
                return R::find('waybills', 'tracking_id=? AND order_date=?', array($tracking_id, $order_date));

            // Search tracking id and courier
            else if ($tracking_id && $location == NULL && $order_date == NULL && $courier_id)
                return R::find('waybills', 'tracking_id=? AND courier_id=?', array($tracking_id, $courier_id));

            // Search tracking id, location and order date
            else if ($tracking_id && $location && $order_date && $courier_id == NULL)
                return R::find('waybills', 'tracking_id=? AND location=? AND order_date=?', array($tracking_id, $location, $order_date));

            // Search tracking id, location and courier
            else if ($tracking_id && $location && $order_date == NULL && $courier_id)
                return R::find('waybills', 'tracking_id=? AND location=? AND courier_id=?', array($tracking_id, $location, $courier_id));

            // Search tracking id, order date and courier
            else if ($tracking_id && $location == NULL && $order_date && $courier_id)
                return R::find('waybills', 'tracking_id=? AND order_date=? AND courier_id=?', array($tracking_id, $order_date, $courier_id));

            // Search tracking id, location, order date and courier
            else if ($tracking_id && $location && $order_date && $courier_id)
                return R::find('waybills', 'tracking_id=? AND location=? AND order_date=? AND courier_id=?', array($tracking_id, $location, $order_date, $courier_id));

            // Search location
            else if ($location && $tracking_id == NULL && $order_date == NULL && $courier_id == NULL)
                return R::find('waybills', 'location=?', array($location));

            // Search location and order date
            else if ($location && $tracking_id == NULL && $order_date && $courier_id == NULL)
                return R::find('waybills', 'location=? AND order_date=?', array($location, $order_date));

            // Search location and courier
            else if ($location && $tracking_id == NULL && $order_date == NULL && $courier_id)
                return R::find('waybills', 'location=? AND courier_id=?', array($location, $courier_id));

            // Search location, courier, order date
            else if ($location && $tracking_id == NULL && $order_date && $courier_id)
                return R::find('waybills', 'location=? AND courier_id=? AND order_date=?', array($location, $courier_id, $order_date));

            // Search order date and courier
            else if ($location == NULL && $tracking_id == NULL && $order_date && $courier_id)
                return R::find('waybills', 'order_date=? AND courier_id=?', array($order_date, $courier_id));
            
            // Search courier
            else if ($location == NULL && $tracking_id == NULL && $order_date == NULL && $courier_id)
                return R::find('waybills', 'courier_id=?', array($courier_id));

            // Search order date 
            else if ($location == NULL && $tracking_id == NULL && $order_date && $courier_id == NULL)
                return R::find('waybills', 'order_date=?', array($order_date));
            else
                return NULL;
        }
    }
    
    /**
     * this method returns express details for client page
     */
    public static function find_courier($input)
    {
        $sender_phone = R::getRow('Select s.phone
            From waybills w join senders s on s.id = w.sender_id 
            Where w.tracking_id = "'.$input->tracking_id.'"');
        $receiver_phone = R::getRow('Select r.phone
            From waybills w join receivers r on r.id = w.sender_id 
            Where w.tracking_id = "'.$input->tracking_id.'"');

        $waybill = r::findone('waybills', 'tracking_id=?', array($input->tracking_id));
        // validate tracking ID 
        if(!$waybill)
            //if not found return false
            return 0;
        //validate phone number
        else if($input->phone_number == $sender_phone['phone'] || $input->phone_number == $receiver_phone['phone'])
        {
            //find express details upon tracking id
            $find_express = R::getRow('select c.id, c.name, 
                w.express_number from waybills w join couriers c on w.courier_id = c.id Where w.tracking_id = "'.$input->tracking_id.'"');
            //if tracking ID has no express details
            if(is_null($find_express))
                //return as no express details
                return 'false';
            else 
            {
                //return finded express details
                return json_encode($find_express);
            }
        }
        else
        {
            //invliad phone number
            return 1;
        }
    }

    /**
     * this method returns local waybill statuses for client page
     */
    public static function find_local_status($tracking_id)
    {
        //find local waybill status according to the trackind id
        $find_status = R::getAll('select  s.time, s.context
            from waybills w join statuses s on s.waybill_id = w.id Where w.tracking_id = "'.$tracking_id.'"');
        //if has no local status
        if(empty($find_status))
            return 'false';
        //else return finded local status
        else return json_encode($find_status);
    }

    /**
     *  this method update waybill status via tracking id for client page
     */
    public static function update_waybill_status($input)
    {
        $waybill = R::findOne('waybills', 'tracking_id=?', array($input->tracking_id));
        echo json_encode($input->status);
        if($waybill)
        {
            $waybill->waybill_status = $input->status;
            R::store($waybill);
        }
        else
        {
            return 'Waybill not found';
        }
    }

    /**
     * find courier for admin check status page
     */
    public static function find_admin_courier($tracking_id)
    {
        //find express details upon tracking id
        $find_express = R::getRow('select c.name, w.express_number 
            from waybills w join couriers c on w.courier_id = c.id Where w.tracking_id = "'.$tracking_id.'"');
        //return finded express details
        return json_encode($find_express);
    }

    /**
     * batch update waybill status for admin page
     */
    public static function batch_update_waybillstatus($status, $tracking_id)
    {
        $waybill = R::findOne('waybills', 'tracking_id=?', array($tracking_id));
        if($waybill)
        {
            $waybill->waybill_status = $status;
            R::store($waybill);
        }
        else
        {
            return 'Waybill not found';
        }
    }

}
