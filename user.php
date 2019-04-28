<?
    // User class file
    require_once('helper.php');

    class User {
        /* Member variables */
        private $name;
        private $email;
        private $first;
        private $last;
        private $guest;

        /* Member functions */
        function __construct() {
            // Constructor function
            $argv = func_get_args();
            switch(func_num_args()) {
                case 0:
                    self::__constructGuest();
                    break;
                case 4:
                    self::__constructUser($argv[0], $argv[1], $argv[2], $argv[3]);
                    break;
            }

            $_SESSION['user'] = $this;
        }

        function __constructGuest() {
            // Guest user constructor function
            $this->name = "Guest";
            $this->guest = true;
            debug("Created guest user session");
        }

        function __constructUser($name, $email, $first, $last) {
            // Normal user constructor function
            $this->name = $name;
            $this->email = $email;
            $this->first = $first;
            $this->last = $last;
            $this->guest = false;
            debug("Created normal user session");
        }

        public function isGuest() {
            return $this->guest;
        }

        public function showDetails() {
            if (!$this->guest) {
                echo "User details<br>";
                echo "Username: ".$this->name;
                echo "<br>First name: ".$this->first;
                echo "<br>Last name: ".$this->last;
                echo "<br>Email: ".$this->email;
            } else {
                echo "Guest account<br>";
            }
        }

        /* Getter functions */
        public function getName() {
            return $this->name;
        }

        public function getEmail() {
            return $this->email;
        }

        public function getFirst() {
            return $this->first;
        }

        public function getLast() {
            return $this->last;
        }

        /* Setter functions */
        public function setName($name) {
            $this->name = $name;
        }

        public function setEmail($email) {
            $this->email = $email;
        }

        public function setFirst($first) {
            $this->first = $first;
        }

        public function setLast($last) {
            $this->last = $last;
        }
    }
?>