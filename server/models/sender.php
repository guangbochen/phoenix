<?php
use RedBean_Facade as R;

class Sender
{
    public $id;
    public $identity;
    public $name;
    public $phone;
    public $company;
    public $address;
    public $city;
    public $state;
    public $country;
    public $postcode;
    public $backside_photo;
    public $frontside_photo;
    public $signature_photo;

    // Find all senders -> return an array
    public static function show_all()
    {
        $senders = R::find('senders', 'name IS NOT NULL');
        try
        {
            foreach ($senders as $sender) 
                $sender->tracking_id = R::findOne('waybills', 'sender_id=?', array($sender->id))->tracking_id;
            return R::exportAll($senders);
        }
        catch (Exception $e)
        {
            return array();
        }
    }

    // Show single sender
    public static function find($id)
    {
        $waybill = R::findOne('waybills', 'tracking_id=?', array($id));
        if ($waybill)
        {
            $sender = R::findOne('senders', 'id=?', array($waybill->sender_id));
            if ($sender)
            {
                $sender->tracking_id = $waybill->tracking_id;
                return R::exportAll($sender);
            }
            else
            {
                return NULL;
            }
        }
        else
        {
            return NULL;
        }
    }

    // Create sender
    public static function create($input)
    {
        try
        {
            $sender = R::dispense('senders');

            $sender->name     = empty($input->sender_name)     ? NULL : $input->sender_name;
            $sender->phone    = empty($input->sender_phone)    ? NULL : $input->sender_phone;
            $sender->company  = empty($input->sender_company)  ? NULL : $input->sender_company;
            $sender->address  = empty($input->sender_address)  ? NULL : $input->sender_address;
            $sender->city     = empty($input->sender_city)     ? NULL : $input->sender_city;
            $sender->state    = empty($input->sender_state)    ? NULL : $input->sender_state;
            $sender->country  = empty($input->sender_country)  ? NULL : $input->sender_country;
            $sender->postcode = empty($input->sender_postcode) ? NULL : $input->sender_postcode;

            return R::store($sender);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    // Update sender
    public static function update($id, $input)
    {
        $sender = R::findOne('senders', 'id=?', array($id));
        if ($sender)
        {
            try
            {
                if ($input->identity)
                {
                    $sender->identity = $input->identity;

                    try 
                    {
                        // Create dir with tracking id as name if not exists
                        $dir_name = "assets/$input->tracking_id";
                        if (!file_exists($dir_name)) 
                            mkdir($dir_name, 0777, true);

                        if (strcmp($input->photo_type, 'front') == 0)
                        {
                            rename($input->photo_value, "$dir_name/front.png");
                            $sender->frontside_photo = "$dir_name/front.png";
                        }
                        else if (strcmp($input->photo_type, 'back') == 0)
                        {
                            rename($input->photo_value, "$dir_name/back.png");
                            $sender->backside_photo  = "$dir_name/back.png";
                        }
                        else
                        {
                            rename($input->photo_value, "$dir_name/signature.png");
                            $sender->signature_photo = "$dir_name/signature.png";
                        }
                    }
                    catch (Exception $e)
                    {
                        return $e->getMessage();
                    }
                }
                else
                {
                    $sender->name     = empty($input->sender_name)     ? NULL : $input->sender_name;
                    $sender->phone    = empty($input->sender_phone)    ? NULL : $input->sender_phone;
                    $sender->company  = empty($input->sender_company)  ? NULL : $input->sender_company;
                    $sender->address  = empty($input->sender_address)  ? NULL : $input->sender_address;
                    $sender->city     = empty($input->sender_city)     ? NULL : $input->sender_city;
                    $sender->state    = empty($input->sender_state)    ? NULL : $input->sender_state;
                    $sender->country  = empty($input->sender_country)  ? NULL : $input->sender_country;
                    $sender->postcode = empty($input->sender_postcode) ? NULL : $input->sender_postcode;
                }

            }
            catch (Exception $e)
            {
                $sender->name     = empty($input->sender_name)     ? NULL : $input->sender_name;
                $sender->phone    = empty($input->sender_phone)    ? NULL : $input->sender_phone;
                $sender->company  = empty($input->sender_company)  ? NULL : $input->sender_company;
                $sender->address  = empty($input->sender_address)  ? NULL : $input->sender_address;
                $sender->city     = empty($input->sender_city)     ? NULL : $input->sender_city;
                $sender->state    = empty($input->sender_state)    ? NULL : $input->sender_state;
                $sender->country  = empty($input->sender_country)  ? NULL : $input->sender_country;
                $sender->postcode = empty($input->sender_postcode) ? NULL : $input->sender_postcode;
            }
            return R::store($sender);
        }
        else
        {
            return NULL;
        }
    }

    // Delete sender
    public static function delete($id)
    {
        $sender = R::findOne('senders', 'id=?', array($id));
        if ($sender)
            R::trash($sender);
        else
            return NULL;
    }

    // Edit sender's photos
    public static function edit($photo)
    {
        // scaled values
        $targ_w = 240;
        $targ_h = 240;

        // set dimensions: x, y, height, width
        $x = 40;
        $y = 40;
        $h = 240;
        $w = 240;

        // decode base64 string image which is sent from client
        $data = base64_decode($photo);
        $file = 'assets/'.uniqid().'.png';

        try 
        {
            file_put_contents($file, $data);

            // create temporary image to handle
            $img_r  = imagecreatefrompng($file);

            // process image with true color from original source
            $dst_r  = imagecreatetruecolor( $targ_w, $targ_h );

            // crop and rotate image
            imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

            // write image back to original source
            imagepng($dst_r, $file, 9);

            // free memory
            imagedestroy($img_r);

            return $file;
        }
        catch (exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function not_exists ($input)
    {
        return ((Sender::update($input->sender_id, $input) == NULL) && ($input->sender_name 
                || $input->sender_phone || $input->sender_company || $input->sender_address 
                || $input->sender_city || $input->sender_state || $input->sender_country 
                || $input->sender_postcode));
    }
}
