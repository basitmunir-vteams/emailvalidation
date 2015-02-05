<?php
/**
 * Validate controller.
 * 
 * @package api-framework
 * @author  Muhammad Basit Munir <basit.munir@nxb.com.pk>
 */
class ValidateController extends AbstractController
{
    /**
     * News file.
     *
     * @var variable type
     */
    protected $articles_file = './data/news.txt';
    
    public function __construct(){

        $this->makeConnection();
    }

    /**
     * GET method.
     * 
     * @param  Request $request
     * @return string
     */
    public function email($request)
    {
        $email = isset($request->url_elements[2]) && !empty($request->url_elements[2]) ? true : false;
        if($email){
            //check if it is not spammer if we receive 5 requests in a minute we will consider him as spammer and add that ip to black list.
            $isBlackList = $this->checkIpAddress($_SERVER['REMOTE_ADDR']);

            if(!$isBlackList) { // check if more than 5 times have been attempted in last one minute then add to black list and return error.
                return array('status'=>'error', 'message'=>'Too Much Attempts, Blocked');
            }

            // keep record of request
            $this->saveRequestResource($request->url_elements[2]);

            // validate email address
            $response = $this->validateEmail($request->url_elements[2]);
            return $response;
        } else {
            return array('status'=>'error', 'message'=>'email is required to validate');
        }
    }

    /**
     * saveRequestResource method saves request in database for future purposes.
     * 
     * @param  email $email 
     * @return json string
     */
    private function saveRequestResource($email){
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $email = $email;
        $status = '';
        $stmt = $this->db->prepare("insert into validation_requests (email,ip_address, date_created) values(:EMAIL, :IPADDRESS, NOW())");
        $stmt->bindParam(':EMAIL', $email, PDO::PARAM_STR);
        $stmt->bindParam(':IPADDRESS', $ipAddress, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }

    /**
     * validateEmail method validates email syntax and called other functions to validate
     * its host as well as blacklist
     * 
     * @param  email $email -> email variable user want to validate it.
     * @return json string
     */
    private function validateEmail($email){

       $exp = "^[a-z\'0-9]+([._-][a-z\'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$";

       if(eregi($exp,$email)){
         $hostName= array_pop(explode("@",$email)); 
         
         if($this->checkBlackList($email)){
          return  $responseString = array('status'=>'error', 'message'=>'email is black listed');
         }
         //if(gethostbyname($hostName) != $hostName) {
         //if(filter_var(gethostbyname($hostName), FILTER_VALIDATE_IP)) {
         else if(checkdnsrr($hostName,"MX")){
         //if($this->checkDomain($hostName)){

            return $responseString = array('status'=>'success', 'message'=> 'Address is valid');

        } else {

           return $responseString = array('status'=>'warning', 'message'=>'invalid Email Address, Host Doesn\'t exists');
        }
         

       }else{

          return $responseString = array('status'=>'error', 'message'=> 'Address is invalid');

       }   

    }

    /**
     * method checkDomain: used to verify host of the email address either exists or not.
     * 
     * @param  email $domain
     * @return bolean
     * @status Depreciated not being used at all
     */
    private function checkDomain($domain, $iscocc = false)
    {
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "mail_checker",
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );
        if($iscocc){
            $url = "http://".$domain;
        }else{
            $url = "http://www.whois.net/getNB.cfm?domain_name=".$domain;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = strip_tags(curl_exec($ch) );
        curl_close($ch);
        
        
        if(!preg_match("/\b(a|A)vailable\b/",$content)):
            exit("there 1") ;
            return true;
        else:
            return false;
        endif;
    }

    /**
     * private method check black list used to verify if the email is not in black list
     * at our end.
     * 
     * @param  email $email -> 
     * @return json string
     */
    private function checkBlackList($email){


        $stmt = $this->db->prepare("SELECT * FROM `blacklist_IPs` WHERE ip_address = :IPADDRESS ");
        $stmt->bindParam(':IPADDRESS', $ipAddress, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($stmt->rowCount() > 0 )
            {
                return true;
                //this email is in blacklist
            } else {
                return false;
            }

    }



}