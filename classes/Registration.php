<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
/**
 * Handles the user registration
 * @author Panique
 * @link http://www.php-login.net
 * @link https://github.com/panique/php-login-advanced/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Registration
{
	/**
	 * @var object $db_connection The database connection
	 */
	private $db_connection            = null;
	/**
	 * @var bool success state of registration
	 */
	public  $registration_successful  = false;
	/**
	 * @var array collection of error messages
	 */
	public  $errors                   = array();
	/**
	 * @var array collection of success / neutral messages
	 */
	public  $messages                 = array();

	/**
	 * the function "__construct()" automatically starts whenever an object of this class is created,
	 * you know, when you do "$login = new Login();"
	 */
	public function __construct()
	{
		session_start();

		// if we have such a POST request, call the registerNewUser() method
		if (isset($_POST["register"])) {
			$this->registerNewUser($_POST['user_email']);
		// if we have such a GET request, call the verifyNewUser() method
		}
	}

	/**
	 * Checks if database connection is opened and open it if not
	 */
	private function databaseConnection()
	{
		// connection already opened
		if ($this->db_connection != null) {
			return true;
		} else {
			// create a database connection, using the constants from config/config.php
			try {
				// Generate a database connection, using the PDO connector
				// @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
				// Also important: We include the charset, as leaving it out seems to be a security issue:
				// @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
				// "Adding the charset to the DSN is very important for security reasons,
				// most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
				$this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
				return true;
			// If an error is catched, database connection failed
			} catch (PDOException $e) {
				$this->errors[] = MESSAGE_DATABASE_ERROR;
				return false;
			}
		}
	}

	/**
	 * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
	 * everything is fine
	 */
	public function registerNewUser($user_email)
	{
		// we just remove extra space on email
		$user_email = trim($user_email);

		// check provided data validity
		// TODO: check for "return true" case early, so put this first
		if (empty($user_email)) {
			$this->errors[] = MESSAGE_EMAIL_EMPTY;
		} elseif (strlen($user_email) > 64) {
			$this->errors[] = MESSAGE_EMAIL_TOO_LONG;
		} elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
			$this->errors[] = MESSAGE_EMAIL_INVALID;

		// finally if all the above checks are ok
		} else if ($this->databaseConnection()) {
			// check if email already exists
			$query_check_user_name = $this->db_connection->prepare('SELECT user_email FROM users WHERE user_email=:user_email');
			$query_check_user_name->bindValue(':user_email', $user_email, PDO::PARAM_STR);
			$query_check_user_name->execute();
			$result = $query_check_user_name->fetchAll();

			// if email find in the database
			// TODO: this is really awful!
			if (count($result) > 0) {
				$this->errors[] = MESSAGE_EMAIL_ALREADY_EXISTS;
			} else {
				// Create a user name from the first part of the email address
				$user_name = $this->createUserName($user_email);
				// Create a random 10 character password for the user
				$user_password = $this->createRandomPassword();
				
				// check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
				// if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
				$hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

				// crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
				// the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
				// compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
				// want the parameter: as an array with, currently only used with 'cost' => XX.
				$user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
				
				// write new users data into database
				$query_new_user_insert = $this->db_connection->prepare('INSERT INTO users (user_name, user_password_hash, user_email, user_registration_datetime) VALUES(:user_name, :user_password_hash, :user_email, now())');
				$query_new_user_insert->bindValue(':user_name', $user_name, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
				$query_new_user_insert->bindValue(':user_email', $user_email, PDO::PARAM_STR);
				$query_new_user_insert->execute();
				
				// id of new user
				$user_id = $this->db_connection->lastInsertId();
				
				if($query_new_user_insert->rowCount()) {
					// Send an email with the user name and password for the account
					if($this->sendNewAccountEmail($user_name, $user_email, $user_password)) {
						// Mail sent successfully
						$this->messages[] = MESSAGE_NEW_ACCOUNT_MAIL_SENT;
						$this->registration_successful = true;
					} else {
						// Delete the account immediately if the info could not be sent correctly
						$query = "DELETE FROM users WHERE user_id=:user_id";
						$query_delete_user = $this->db_connection->prepare($query);
						$query_delete_user->bindValue(':user_id', $user_id, PDO::PARAM_INT);
						$query_delete_user->execute();
						
						$this->errors[] = MESSAGE_NEW_ACCOUNT_MAIL_ERROR;
					}
				} else {
					$this->errors[] = MESSAGE_REGISTRATION_FAILED;
				}
			}
		}
	}
	
	private function createUserName($user_email)
	{
		// Grabs the substr of the email before the '@' as a temporary user name
		$temp_user_name = substr($user_email, 0, strpos($user_email, "@"));
		
		/**
		 * Still need to create a user name if the temp user name exists.
		 * The following code will add an auto_incrementing number to the end of the user name if it already exists.  
		 */
		
		// Checks the database for user names similar to the temp user name
		// Orders the result by descending so that the first user name has the largest number at the end
		$query = "SELECT user_name FROM `users` WHERE user_name LIKE :user_name ORDER BY `user_name` DESC;";
		$query_check_user_name = $this->db_connection->prepare($query);
		$query_check_user_name->bindValue(":user_name", $temp_user_name."%", PDO::PARAM_STR);
		$query_check_user_name->execute();
		$result = $query_check_user_name->fetchAll();
		
		// If there is already a similar user name in the database (with or without an auto_incremented number)
		if(count($result) > 0)
		{
			// Gets the first user name (has the greatest auto_increment based on the query)
			$greatest_user_name = $result[0]['user_name'];
			
			// Acquires the number and adds 1 to create a unique user name
			$new_user_name_number = intval(substr($greatest_user_name, strlen($temp_user_name))) + 1;
			
			// Combines the original user name substr and auto_incremented number to create the final user name
			$user_name = $temp_user_name . strval($new_user_name_number);
		}
		
		// No similar user names in the database already (temp user name is set as the unique user name)
		else
		{
			$user_name = $temp_user_name;
		}
		
		return $user_name;
	}
	
	/**
	 * Creates a cryptographically random password
	 */
	private function createRandomPassword()
	{
		// Creates a cryptographically random set of bytes for a random password
		$crypt_strong = false;
		while(!$crypt_strong)
		{
			$random_password_bytes = openssl_random_pseudo_bytes(5, $crypt_strong);
		}
		
		// Converts those bytes to hexadecimals (letter & numbers) for the actual password
		$random_password_str = bin2hex($random_password_bytes);
		
		
		return $random_password_str;
	}
	
	/**
	 * Sends an email for a new account
	 */
	public function sendNewAccountEmail($user_name, $user_email, $user_password)
	{
		$mail = new PHPMailer;
		
		// please look into the config/config.php for much more info on how to use this!
		// use SMTP or use mail()
		if(EMAIL_USE_SMTP) {
			// Set mailer to use SMTP
			$mail->IsSMTP();
			//useful for debugging, shows full SMTP errors
			//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
			// Enable SMTP authentication
			$mail->SMTPAuth = EMAIL_SMTP_AUTH;
			// Enable encryption, usually SSL/TLS
			if(defined(EMAIL_SMTP_ENCRYPTION)) {
				$mail->SMPTSecure = EMAIL_SMTP_ENCRYPTION;
			}
			// Specify host server
			$mail->Host = EMAIL_SMTP_HOST;
			$mail->Username = EMAIL_SMTP_USERNAME;
			$mail->Password = EMAIL_SMTP_PASSWORD;
			$mail->Port = EMAIL_SMTP_PORT;
		} else {
			$mail->IsMail();
		}
		
		$mail->From = EMAIL_NEW_ACCOUNT_FROM;
		$mail->FromName = EMAIL_NEW_ACCOUNT_FROM_NAME;
		$mail->AddAddress($user_email);
		$mail->Subject = EMAIL_NEW_ACCOUNT_SUBJECT;
		
		// Body of the email
		$mail->Body = EMAIL_NEW_ACCOUNT_BODY . "User Name: $user_name\nPassword: $user_password" . EMAIL_FOOTER_AND_SIGNATURE;

		if(!$mail->Send()) {
			$this->errors[] = MESSAGE_NEW_ACCOUNT_MAIL_NOT_SENT . $mail->ErrorInfo;
			return false;
		} else {
			return true;
		}
	}
}
