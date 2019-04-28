<?
    /**
     * Helper functions class
     */

    require_once('config.php');
    require_once('user.php');

    // Session stuff
    session_start();
    if (empty($_SESSION['helper'])) {
        $_SESSION['helper'] = new Helper(); // Create helper instance if one does not exist
    }

    if (empty($_SESSION['user'])) {
        $_SESSION['user'] = new User(); // Create guest user
    }

    $helper = $_SESSION['helper'];
    $user = $_SESSION['user'];

    function debug($string) {
        // Function to print when debug mode is enabled

        $time = time();
        $msg = (date("[d-m-Y H:i:s] ", $time)).$string;

        if (DEBUG) {
            echo $msg."<br>";
        }

        if (LOGGING) {
            $logFile = fopen(LOGFILE, 'a');
            fwrite($logFile, $msg."\n");
            fclose($logFile);
        }
    }

    class Helper {
        /* Member vars */
        var $conn;

        /* Member functions */
        function makeConn($connectDB = true) {
            // Function to make the mysql connection
            $this->conn = new mysqli(DBHOST, DBUSER, DBPWD);

            // Check connection
            if ($this->conn->connect_error) {
                die("Connection failed: ".$this->conn->connect_error);
            }

            debug("Connection open");

            // Connect to main db
            if ($connectDB) {
                if (!mysqli_select_db($this->conn, DBNAME)) {
                    debug("Database does not exist, running setup");
                    $this->setup();
                } else {
                    debug("Database selected");
                }
            }
        }

        function closeConn() {
            // Function to close mysql connection
            $this->conn->close();
            debug("Connection closed");
        }

        function runQuery($sql, $desc = "run the query") {
            // Function to run query on the current connection
            $out = $this->conn->query($sql);
            if ($out === TRUE) {
                debug("Succeeded to ".$desc);
            } else {
                debug("Failed to ".$desc.". Technical details: ".$this->conn->error);
            }

            return $out;
        }

        function escapeStr($string) {
            return mysqli_real_escape_string($this->conn, $string);
        }

        function setup($reinstall = false) {
            // Setup function
            $run;

            // All sql commands for setup
            $createDB = "CREATE DATABASE IF NOT EXISTS ".DBNAME."";
            $createUsers = "CREATE TABLE IF NOT EXISTS ".USRTBL." (id INT(6) AUTO_INCREMENT PRIMARY KEY, username VARCHAR(30) NOT NULL, password VARCHAR(30) NOT NULL, firstName VARCHAR(30) NOT NULL, lastName VARCHAR(30) NOT NULL, email VARCHAR(50), regDate DATETIME DEFAULT CURRENT_TIMESTAMP)";
            $createEvents = "CREATE TABLE IF NOT EXISTS ".EVTTBL." (id BIGINT AUTO_INCREMENT PRIMARY KEY, ownerId INT(6) NOT NULL, name VARCHAR(30) NOT NULL, description TEXT, date DATETIME DEFAULT CURRENT_TIMESTAMP)";
            $deleteDB = "DROP DATABASE IF EXISTS ".DBNAME."";
            $insertAdmin = "INSERT INTO ".USRTBL." (username, password, firstName, lastName) VALUES ('admin', '', 'Administrator', '')";

            // Reinstall
            if ($reinstall) {
                $this->makeConn(false); // Connect with no db selection
                $run = $this->runQuery($deleteDB, "delete the database");
                if (!$run) {
                    $this->closeConn();
                    exit(); // Have to drop db to proceed
                }

                $this->closeConn();
            }

            // Make connection with no db selection
            $this->makeConn(false);

            $run = $this->runQuery($createDB, "create the database");
            if (!$run) {
                $this->closeConn();
                exit(); // Have to create db to proceed
            }

            // Reconnect to mysql with db selection
            $this->closeConn();
            $this->makeConn();

            $run = $this->runQuery($createUsers, "create the users table");
            $run = $this->runQuery($createEvents, "create the events table");
            $run = $this->runQuery($insertAdmin, "insert admin account");

            // Close connection
            $this->closeConn();
        }

        function registerUser($username, $pass, $first, $last, $email) {
            // Function to register a user
            $run;

            $this->makeConn();
            $insertUser = "INSERT INTO ".USRTBL." (username, password, firstName, lastName, email) VALUES ('$username', '$pass', '$first', '$last', '$email')";
            $chkUser = "SELECT * FROM ".USRTBL." WHERE email = '$email' or username = '$username'";

            // Check for duplicate user
            $run = $this->runQuery($chkUser, "check if duplicate user for registration");
            $chk = mysqli_fetch_assoc($run);
            if ($chk) {
                if ($chk['username'] === $username) {
                    echo "Username already exists<br>";
                }

                if ($chk['email'] === $email) {
                    echo "Email already exists<br>";
                }

                $this->closeConn();
                exit(); // Stop running to prevent errors
            }

            // Register the user
            $run = $this->runQuery($insertUser, "register user for email '$email'");
            if ($run === TRUE) {
                // Create user object
                $_SESSION['user'] = new User($username, $email, $first, $last);
                $user = $_SESSION['user'];
            }

            $this->closeConn();
        }

        function login($username, $pass) {
            // nothing
        }
    }
?>