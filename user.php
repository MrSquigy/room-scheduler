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

        function getName() {
            return $this->name;
        }

        function getEmail() {
            return $this->email;
        }

        function getFirst() {
            return $this->first;
        }

        function getLast() {
            return $this->last;
        }

        function isGuest() {
            return $this->guest;
        }
    }
?>