<?
    // User class file
    require_once('helper.php');

    class User {
        /* Member variables */
        var $name;
        var $email;
        var $first;
        var $last;

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
        }

        function __constructGuest() {
            // Guest user constructor function
            $name = "Guest";
        }

        function __constructUser($name, $email, $first, $last) {
            // Normal user constructor function
            $this->name = $name;
            $this->email = $email;
            $this->first = $first;
            $this->last = $last;
        }
    }
?>