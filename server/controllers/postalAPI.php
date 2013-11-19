<?php
//enable to display chinese character
// header("Content-Type: text/html; charset=utf-8");
require_once 'models/waybill.php';
require_once 'helpers/json_helper.php';
/**
* this class call kuaidi100 API and return data from Kuaidi100 API
*/
class PostalAPI
{
   private $app;
   private $AppKey='63a8b4c04d8a9101';//Authentication key for kuaidi100

   public function __construct()
   {
      $this->app = \Slim\Slim::getInstance();
   }

   public function index()
   {
      echo json_encode(array("Message" => 'Postal API is healthy'));
   }

   /**
    * this method call postal API and validate tracking id(client page)
    */
   public function getPostAPI()
   {
      $data = $this->app->request()->post('tracking_detail');
      $input = json_decode(json_encode($data));
      try
      {
         $waybill = new Waybill();
         //check courier name and express number via tracking id and validate phone number
         $find_courier = $waybill->find_courier($input);
         $find_status = $waybill->find_local_status($input->tracking_id);
         //validate waybill has both courier name and express number
         // if trackind id is not found  
         if($find_courier === 0)
         {
            echo json_encode(array('tracking_id' => 'false'));
         }
         else if($find_courier === 1)
         {
            echo json_encode(array('tracking_id' => 'incorret_phone'));
         }
         //has no express detials 
         else if($find_courier === 'false')
         {
            //if has no local status
            if($find_status === 'false')
            {
               echo json_encode(array('tracking_id' => 'true', 'courier' => 'false','local_status' => 'false'));
            }
            else
            {
               $status = json_decode($find_status);
               echo json_encode(array('tracking_id' => 'true', 'courier' => 'false','local_status' => 'true', 'local_status' => $status));
            }
         }
         else
         {
            $courier = json_decode($find_courier);
            $status = json_decode($find_status);
            //if is yunda, huitong,yuantong
            if($courier->name === "yunda" || $courier->name === "huitongkuaidi" || $courier->name === "yuantong")
            {
               //prevent sending data to the client if has no or invalid courier data(json)
               if($courier->name == '' || $courier->name == ' ' || $courier->express_number == '')
               {
                  if($find_status === 'false')
                  {
                     //has only api_status
                     echo json_encode(array('tracking_id' => 'ture', 'courier' => 'false','local_status' => 'false'));
                  }
                  else
                  {
                     //has both api_status and local status
                     echo json_encode(array('tracking_id' => 'ture', 'courier' => 'false','local_status' => 'true', 'local_status' =>$status));
                  }
               }
               else
               {
                  //use json returned
                  $url ='http://api.kuaidi100.com/api?id='.$this->AppKey.'&com='.$courier->name.'&nu='.$courier->express_number.'&show=0&muti=1&order=asc';
                  //using the crul to send request
                  if (function_exists('curl_init') == 1)
                  {
                     $curl = curl_init();
                     curl_setopt ($curl, CURLOPT_URL, $url);
                     curl_setopt ($curl, CURLOPT_HEADER,0);
                     curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
                     curl_setopt ($curl, CURLOPT_TIMEOUT,5);
                     $get_content = curl_exec($curl);
                     curl_close ($curl);
                     $get_data =  json_decode($get_content);
                     //if has no local status 
                     if($find_status === 'false')
                     {
                        //has only api_status
                        echo json_encode(array('tracking_id' => 'ture', 'courier' => 'true','local_status' => 'false', 'api_status' => $get_data));
                     }
                     else
                     {
                        //has both api_status and local status
                        echo json_encode(array('tracking_id' => 'ture', 'courier' => 'true','local_status' => 'true', 'api_status' => $get_data, 'local_status' =>$status));
                     }
                  }
               }
            }
            else //use html returned
            {
               //prevent sending data to the client if has no or invalid courier data(html src)
               if($courier->name == '' || $courier->name == ' ' || $courier->express_number == '')
               {
                  //if has no local status 
                  if($find_status === 'false')
                  {
                     //has only api_status
                     echo json_encode(array('tracking_id' => 'ture', 'courier' => 'false', 'local_status' => 'false', 'api_status' => 'is_src'));
                  }
                  else
                  {
                     //has both api_status and local status
                     echo json_encode(array('tracking_id' => 'ture', 'courier' => 'false', 'local_status' => 'true', 'api_status' => 'is_src', 'local_status' =>$status));
                  }
               }
               else
               {

                  $url ='http://www.kuaidi100.com/applyurl?key='.$this->AppKey.'&com='.$courier->name.'&nu='.$courier->express_number.'';
                  //using the crul to send request
                  if (function_exists('curl_init') == 1)
                  {
                     $curl = curl_init();
                     curl_setopt ($curl, CURLOPT_URL, $url);
                     curl_setopt ($curl, CURLOPT_HEADER,0);
                     curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
                     curl_setopt ($curl, CURLOPT_TIMEOUT,5);
                     $get_content = curl_exec($curl);
                     curl_close ($curl);
                     //if has no local status 
                     if($find_status === 'false')
                     {
                        //has only api_status
                        echo json_encode(array('tracking_id' => 'ture', 'courier' => 'true','local_status' => 'false', 'api_status' => 'is_src', 'src' => $get_content));
                     }
                     else
                     {
                        //has both api_status and local status
                        echo json_encode(array('tracking_id' => 'ture', 'courier' => 'true','local_status' => 'true', 'api_status' => 'is_src', 'local_status' =>$status, 'src' => $get_content));
                     }
                  }
               }
            }
         }
      }
      catch (Exception $e)
      {
          response_json_error($this->app, 400, $e->getMessage());
      }
   }

   /**
    * this method returns html src to the admin page
    */
   public function get_html_status($tracking_id)
   {
      try
      {
         $waybill = new Waybill();
         $find_courier = $waybill->find_admin_courier($tracking_id);
         $express_info = json_decode($find_courier);
         $AppKey='63a8b4c04d8a9101';//Authentication key for kuaidi100
         $url ='http://www.kuaidi100.com/applyurl?key='.$AppKey.'&com='.$express_info->name.'&nu='.$express_info->express_number.'';
         if (function_exists('curl_init') == 1)
         {
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, $url);
            curl_setopt ($curl, CURLOPT_HEADER,0);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
            curl_setopt ($curl, CURLOPT_TIMEOUT,5);
            $get_content = curl_exec($curl);
            curl_close ($curl);
            echo json_encode(array('src' =>$get_content));
         }
      }
      catch (Exception $e)
      {
          response_json_error($this->app, 400, $e->getMessage());
      }
   }

   /**
    * this method update waybill status from admin page
    */
   public function update_waybill_status()
   {
      $data = $this->app->request()->post('data');
      $input = json_decode(json_encode($data));
      try
      {
         // $find = json_decode($data);
         Waybill::update_waybill_status($input);
      }
      catch (Exception $e)
      {
          response_json_error($this->app, 400, $e->getMessage());
      }
   }

   public function batch_check_status()
   {
      try {
         $waybills = Waybill::show_all();
         foreach ($waybills as $value) {
            if(!empty($value['courier']) && !empty($value['express_number']) && $value['waybill_status'] != "已签收")
            {
               $tracking_id    = $value['tracking_id'];
               $waybill_status = $value['waybill_status'];
               $express_number = $value['express_number'];
               $courier_name = $value['courier']['name'];
               //valide the courier name
               if($courier_name != ' ' && $courier_name != 'ems' && $courier_name != 'shentong' && $courier_name != 'shunfeng')
               {
                  // echo $express_number."\xA";
                  $url ='http://api.kuaidi100.com/api?id='.$this->AppKey.'&com='.$courier_name.'&nu='.$express_number.'&show=0&muti=1&order=asc';
                  // using the crul to send request
                  if (function_exists('curl_init') == 1)
                  {
                     $curl = curl_init();
                     curl_setopt ($curl, CURLOPT_URL, $url);
                     curl_setopt ($curl, CURLOPT_HEADER,0);
                        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
                     curl_setopt ($curl, CURLOPT_TIMEOUT,5);
                     $get_content = curl_exec($curl);
                     curl_close ($curl);
                     $get_data =  json_decode($get_content);
                  }
                  if($get_data->status === '1')
                  {
                     $checked_status = '';
                     if($get_data->state == 0)
                      $checked_status = '在途中'; 
                     else if($get_data->state == 1)
                      $checked_status = '已发货';
                     else if($get_data->state == 2)
                      $checked_status = '疑难件'; 
                     else if($get_data->state == 3) 
                       $checked_status  = '已签收';
                     Waybill::batch_update_waybillstatus($checked_status,$tracking_id);
                  }
               }//end if
               
            }//end if
         }//end foreach
         echo json_encode(array("Message" => '更新状态成功'));
      }
      catch (Exception $e)
      {
          response_json_error($this->app, 400, $e->getMessage());
      }
   }//end function

}//end class
