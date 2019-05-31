<?
    /**
     * Database Connection Class
     */

    require_once('../config.php');

    class DatabaseConnection {
        /* Member vars */
        private $conn;

        /* Member functions */
        private function makeConn($connectDB = true) {
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

        public function runQuery($sql, $desc = "run the query", $check = true) {
            // Function to run query on the current connection
            
            $out = $this->conn->query($sql);
            if (!($out === TRUE) && $check) debug("Failed to ".$desc.". Technical details: ".$this->conn->error);
            else debug("Succeeded to ".$desc);
            
            return $out;
        }

        public function closeConn() {
            // Function to close mysql connection
            
            $this->conn->close();
            debug("Connection closed");
        }

        public function escape($string) {
            return mysqli_real_escape_string($this->conn, $string);
        }
    }

?>