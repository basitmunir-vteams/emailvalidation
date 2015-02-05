<?php
/**
 * @package  Email Validation API
 * @author   Muhammad Basit Munir <basit.munir@nxb.com.pk>
 * @abstract
 */
abstract class AbstractController {
	protected $dbName = 'fcm_demo'; // database name at api server
	protected $dbUser = 'root';		// user name for database
	protected $dbHost = 'localhost';	// database host most probably it is localhost
	protected $dbPassword = 'password';  // password of the database.
	protected $db; // prtoected variable used to initliaze pdo db.

	/**
	*	Constructure Method
	*	used to initialize database connection of api to keep record of requests.
	*/

	protected function makeConnection(){
		/*** set up the database connection ***/
        $this->db = new PDO("mysql:host=".$this->dbHost.";dbname=".$this->dbName."", $this->dbUser, $this->dbPassword);

	}

	/**
     * protected  method used to check how many requests received from this ip address in last 60 seconds.
     * 
     * @param  $ipAddress
     * @return bool true or false
     */
	protected function checkIpAddress($ipAddress){
		$stmt = $this->db->prepare("SELECT count(*) as count FROM `validation_requests` WHERE ip_address = :IPADDRESS AND TIMESTAMPDIFF( SECOND , date_created, NOW( ) ) <=60");       
        $stmt->bindParam(':IPADDRESS', $ipAddress, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        if($count['count'] >= 5 ) {

            $stmt = $this->db->prepare("INSERT INTO `blacklist_IPs` (ip_address, date_added) values (:IPADDRESS, NOW()) ON DUPLICATE KEY UPDATE date_added = NOW() ");
            $stmt->bindParam(':IPADDRESS', $ipAddress, PDO::PARAM_STR);
            $stmt->execute();
            return false;

        } else {
            return true;
        }
	}

}