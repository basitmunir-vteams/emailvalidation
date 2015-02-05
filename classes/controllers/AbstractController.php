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

}