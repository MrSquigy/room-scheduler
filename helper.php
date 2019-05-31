<?
    /**
     * Helper Class
     */

    require_once('config.php');
    require_once('classes/databaseConnection.php');
    require_once('user.php');

    // Session stuff
    session_start();
    if (!isset($_SESSION['helper'])) $_SESSION['helper'] = new Helper(); // Create helper instance if one does not exist
    if (!isset($_SESSION['user'])) $_SESSION['user'] = new User(); // Create guest user if one does not exist
    $helper = $_SESSION['helper']; // $helper is a shorthand to $_SESSION['helper']
    $user = $_SESSION['user']; // $user is a shorthand to $_SESSION['user']

    function debug($string) {
        // Function to print when debug mode is enabled

        $time = time();
        $msg = (date("[d-m-Y H:i:s] ", $time)).$string;

        if (DEBUG) echo $msg."<br>";

        if (LOGGING) {
            $logFile = fopen(LOGFILE, 'a');
            fwrite($logFile, $msg."\n");
            fclose($logFile);
        }
    }

    class Helper {
        /* Member vars */
        private $conn; // DatabaseConnection object

        /* Member functions */
        function __construct() {
            // Constructor to set up dependant objects
            $this->conn = new DatabaseConnection();
        }

        public function makeConn($connectDB = true) {
            // Function to make the mysql connection
            $this->conn = new mysqli(DBHOST, DBUSER, DBPWD);

            // Check connection
            if ($this->conn->connect_error) die("Connection failed: ".$this->conn->connect_error);
            debug("Connection open");

            // Connect to main db
            if ($connectDB) {
                if (!mysqli_select_db($this->conn, DBNAME)) {
                    debug("Database does not exist, running setup");
                    $this->setup();
                } else debug("Database selected");
            }
        }

        public function closeConn() {
            // Function to close mysql connection
            
            $this->conn->close();
            debug("Connection closed");
        }

        public function escapeStr($string) {
            return mysqli_real_escape_string($this->conn, $string);
        }

        public function setup($reinstall = false) {
            // Setup function
            $run;

            // Date stuff for testing
            $startDate = mktime(0, 0, 0, 1, 1, 1970); // January 1st 1970
            $startDate = date('Y-m-d H:i:s', $startDate);
            $endDate = mktime(0, 0, 0, 12, 31, 2030); // December 31st 2030
            $endDate = date('Y-m-d H:i:s', $endDate);

            // All sql commands for setup
            $createDB = "CREATE DATABASE IF NOT EXISTS ".DBNAME."";
            $createUsers = "CREATE TABLE IF NOT EXISTS ".USRTBL." (id INT(6) AUTO_INCREMENT PRIMARY KEY, username VARCHAR(30) NOT NULL, password VARCHAR(255) NOT NULL, firstName VARCHAR(30) NOT NULL, lastName VARCHAR(30) NOT NULL, email VARCHAR(50), regDate DATETIME DEFAULT CURRENT_TIMESTAMP)";
            $createEvents = "CREATE TABLE IF NOT EXISTS ".EVTTBL." (id BIGINT AUTO_INCREMENT PRIMARY KEY, roomId INT(6) NOT NULL, ownerId INT(6) NOT NULL, name VARCHAR(30) NOT NULL, description TEXT, startDate DATETIME NOT NULL, endDate DATETIME NOT NULL, dateCreated DATETIME DEFAULT CURRENT_TIMESTAMP)";
            $createRooms = "CREATE TABLE IF NOT EXISTS ".RMSTBL." (id INT(6) AUTO_INCREMENT PRIMARY KEY, name VARCHAR(30) NOT NULL, number INT(6) NOT NULL)";

            $deleteDB = "DROP DATABASE IF EXISTS ".DBNAME."";
            $adminPass = password_hash(ADMINPASS, PASSWORD_DEFAULT);
            $insertAdmin = "INSERT INTO ".USRTBL." (username, password, firstName, lastName) VALUES ('admin', '$adminPass', 'Administrator', '')";
            $insertConf = "INSERT INTO ".RMSTBL." (name, number) VALUES ('Conference Room', '1')";
            $insertEvt = "INSERT INTO ".EVTTBL." (roomId, ownerId, name, description, startDate, endDate) VALUES ('1', '1', 'Test event', 'Event made for testing purposes.', '$startDate', '$endDate')";

            // Reinstall
            if ($reinstall) {
                $this->makeConn(false); // Connect with no db selection
                $run = $this->conn->runQuery($deleteDB, "delete the database");
                if (!$run) {
                    $this->closeConn();
                    exit(); // Have to drop db to proceed
                }

                $this->closeConn();
            }

            // Make connection with no db selection
            $this->makeConn(false);

            $run = $this->conn->runQuery($createDB, "create the database");
            if (!$run) {
                $this->closeConn();
                exit(); // Have to create db to proceed
            }

            // Reconnect to mysql with db selection
            $this->closeConn();
            $this->makeConn();

            $this->conn->runQuery($createUsers, "create the users table");
            $this->conn->runQuery($createEvents, "create the events table");
            $this->conn->runQuery($createRooms, "create the rooms table");
            $this->conn->runQuery($insertAdmin, "insert admin account");
            $this->conn->runQuery($insertConf, "insert conference room");
            $this->conn->runQuery($insertEvt, "insert test event");

            // Close connection
            $this->closeConn();
        }

        public function registerUser($username, $pass, $first, $last, $email) {
            // Function to register a user
            $run;
            $ret = true;

            $this->makeConn();
            $insertUser = "INSERT INTO ".USRTBL." (username, password, firstName, lastName, email) VALUES ('$username', '$pass', '$first', '$last', '$email')";
            $chkUser = "SELECT * FROM ".USRTBL." WHERE email = '$email' or username = '$username'";

            // Check for duplicate user
            $run = $this->conn->runQuery($chkUser, "check if duplicate user for registration", false);
            $chk = $run->fetch_assoc();
            if ($chk) {
                $ret = false;
                if ($chk['username'] === $username) echo "Username already exists<br>";
                if ($chk['email'] === $email) echo "Email already exists<br>";

                $this->closeConn();
                exit(); // Stop running to prevent errors
            }

            // Register the user
            $run = $this->conn->runQuery($insertUser, "register user for email '$email'");
            if ($run === TRUE) {
                // Create user object
                $_SESSION['user'] = new User($username, $email, $first, $last);
                $user = $_SESSION['user'];
            }

            $this->closeConn();
            return $ret;
        }

        public function login($username, $pass) {
            // Function to login user
            $run;
            $ret = false;

            $this->makeConn();
            $chkUser = "SELECT * FROM ".USRTBL." WHERE username = '$username'";
            
            // Check for username
            $run = $this->conn->runQuery($chkUser, "check username for login '$username'", false);
            $chk = $run->fetch_assoc();

            if ($chk) {
                if (password_verify($pass, $chk['password'])) {
                    // Password is valid, log user in
                    $_SESSION['user'] = new User($username, $chk['email'], $chk['firstName'], $chk['lastName']);
                    echo "Logged user in successfully<br>";
                    $ret = true;
                } else echo "Incorrect password for user<br>";
            } else echo "No user exists with that username<br>";

            $this->closeConn();
            return $ret;
        }

        public function getRooms() {
            // Function to return an array of rooms
            $run;

            $this->makeConn();
            $getRooms = "SELECT * FROM ".RMSTBL." ORDER BY `name` ASC";

            // Get rooms
            $run = $this->conn->runQuery($getRooms, "get list of all rooms", false);
            
            $this->closeConn();
            return $run;
        }

        public function getRoomSchedule($roomID) {
            // Function to return a table of the room schedule
            $run;

            $ret = "<table>";
            $this->makeConn();
            $getEvents = "SELECT * FROM ".EVTTBL." WHERE roomId = '$roomID'";

            $run = $this->conn->runQuery($getEvents, "get list of events for room $roomID", false);
            
            $count = 0;
            if ($run->num_rows > 0) {
                $ret = $ret . "<tr><td><b>Event Name</b></td><td><b>Event Description</b></td><td><b>Start Date</b></td><td><b>End Date</b></td></tr>";
                while ($event = $run->fetch_assoc()) $ret = $ret . "<tr><td>$event[name]</td><td>$event[description]</td><td>$event[startDate]</td><td>$event[endDate]</td></tr>";
            }
            else $ret = $ret . "<tr><td>No results</td></tr>";

            $ret = $ret . "</table>";

            $this->closeConn();
            
            return $ret;
        }
    }
?>