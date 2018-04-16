<?php

//this is class module 


    class Database

    {


        public $con;
        public function __construct(){

            $this->con = new mysqli("localhost","root","pa55cod5","iotmono");
            if($this->con->connect_error){

             die("Connection failed: " . $this->con->connect_error);
            }


             




          


        }




    }


   class OccupancyFilter extends Database

   {


    public $threshold;
    public $mac;
    public $time_on;
    public $time_off;
    public $adc;
    public $adc_num;




    public function getThreshold($mac,$adc){
    $this->mac = $mac;
    $this->adc = $adc;
     
     $sql_get_threshold = "SELECT threshold,$this->adc,dash_id,dash_name FROM dashboard where gal_mac = '$this->mac'";
                       $sql_get_threshold = $this->con->query($sql_get_threshold);
                       if ($sql_get_threshold->num_rows > 0){
                            while($sql_get_threshold = $sql_get_threshold->fetch_assoc()) {

                                       return array(
                                       'threshold' => $sql_get_threshold['threshold'],
                                       'room_details' => $sql_get_threshold[$this->adc],
                                       'dash_name' => $sql_get_threshold['dash_name'],
                                       'dash_id' =>  $sql_get_threshold['dash_id']

                                       );


                                }

                         

                        }



    }

    public function checkTimeOn($time_on,$adc,$adc_num,$mac){
     $this->time_on = $time_on;
     $this->adc = $adc;
     $this->adc_num = $adc_num;
     $this->mac = $mac;

        $occupancy = new OccupancyFilter;



        $room_details = $occupancy->getThreshold($this->mac,$this->adc_num);

        $threshold = $room_details["threshold"];
        $get_room_details = explode(",",$room_details['room_details']);
        $dash_name = $room_details['dash_name'];
        $dash_id = $room_details['dash_id'];
        $room_name = $get_room_details[0];



        $check_from = date("Y-m-d H:i:s",strtotime($this->time_on . "-5 minutes"));
        
      
       
        $sql_check_timeOn = "SELECT $this->adc_num,mac,date_time FROM raw_data where mac = '$this->mac' and date_time < '$this->time_on' and date_time >= '$check_from' and  $this->adc_num > '$threshold' ";
                       $sql_result_timeOn = $this->con->query($sql_check_timeOn);
                       if ($sql_result_timeOn->num_rows > 0){


                        return "No";

                          

                        }
                       else
                        {
                     
                          
                      

                                 $sql_check_duplicate = "SELECT time_on FROM log_data where dash_name = '$dash_name' and dash_id = '$dash_id' and room_name='$room_name' and mac ='$this->mac' and time_off IS NULL and time_on <='$this->time_on'";
                                            $sql_check_duplicate = $this->con->query($sql_check_duplicate);
                                                  if ($sql_check_duplicate->num_rows > 0){

                                                      
                                                    return "No";

                                                  }
                                                  else{


                                                  	return "Yes";
                                                  }






                         }





    }

    public function getRoomDetails($adc,$mac){
          $this->adc = $adc;
          $this->mac = $mac;



        $sql_room_details = "SELECT $this->adc,dash_name,dash_id FROM dashboard where gal_mac = '$this->mac'";
                       $sql_room_details = $this->con->query($sql_room_details);
                       if ($sql_room_details->num_rows > 0){
                            while($sql_room_details = $sql_room_details->fetch_assoc()) {

                                 return array(


                                       'room_details' => $sql_room_details[$this->adc],
                                       'dash_name' => $sql_room_details['dash_name'],
                                       'dash_id' =>  $sql_room_details['dash_id']

                                       );



                                }

                         

                        }





    }


    public function checkTimeOff($adc,$time_off,$mac,$threshold){
           $this->adc = $adc;
           $this->time_off = $time_off;
           $this->mac = $mac;
           $this->threshold = $threshold;


                            $check_to= date("Y-m-d H:i:s",strtotime($this->time_off . "-5 minutes"));
        
      

                           $sql_check_last_minute = "SELECT id FROM raw_data where mac = '$this->mac' and date_time >= '$check_to' ";

                                    $sql_result_last_minute = $this->con->query($sql_check_last_minute);

                                            if($sql_result_last_minute->num_rows > 0){


       
                                                         $sql_check_timeOn = "SELECT id FROM raw_data where mac = '$this->mac' and date_time > '$this->time_off'  and $adc > '$this->threshold' ";
                                                                  $sql_result_timeOn = $this->con->query($sql_check_timeOn);
                                                                        if ($sql_result_timeOn->num_rows > 0){


                                                                                   return "No";

                          

                                                                          }
                                                                        else
                                                                          {

                                                                                     return "Yes";


                                                                           }
                     


                                             }
                                               else{

                                                      return "No";


                                               }





        }


    public function checkTimeOff_custom($adc,$time_off,$mac,$threshold,$date_to){
         $this->adc = $adc;
         $this->time_off = $time_off;
         $this->mac = $mac;
         $this->threshold = $threshold;
         $this->date_to = $date_to;
            

        

                          $check_to= date("Y-m-d H:i:s",strtotime($this->time_off . "+5 minutes"));


                           $sql_check_last_minute = "SELECT id FROM raw_data where mac = '$this->mac' and date_time >= '$check_to' ";

                                    $sql_result_last_minute = $this->con->query($sql_check_last_minute);

                                            if($sql_result_last_minute->num_rows > 0){

                                                             $sql_check_timeOn = "SELECT id FROM raw_data where mac = '$this->mac' and date_time > '$this->time_off'  and date_time <= '$check_to' and $this->adc > '$this->threshold' ";
                                                                          $sql_result_timeOn = $this->con->query($sql_check_timeOn);
                                                                                       if ($sql_result_timeOn->num_rows > 0){

                                                                                          
                                                                                                    
                                                                                             return "No";
                          

                                                                                            }
                                                                                        else
                                                                                           {
                                                                                                if($this->time_off <= $this->date_to){


                                                                                               
                                                                                                   return "Yes";

                                                                                                }


                                                                                            }
                                             }
                                             else{


                                             	return "No";

                                             }






    }






   }












   





























?>