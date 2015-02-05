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
        $ipAddress = $_SERVER['REMOVE_ADDR'];
        $email = $email;
        $status = '';
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
        var_dump($content);
        curl_close($ch);
        var_dump(preg_match("/\b(a|A)".$domain." is already registered\b/",$content));
            exit();
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


        //array of blacklisted domains
        $blackList = array('testing@test.com','anotheremail@mail.com','myemail@gmail.com','hellow@gmail.com');//sql call here

      //  foreach($blackList as $email){    
            if(in_array($email, $blackList) )
            {
                return true;
                //this email is in blacklist
            } else {
                return false;
            }
      //  }

    }

}