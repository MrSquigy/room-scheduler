<? require_once('helper.php') ?>

<html>
    <head>
        <title>Event Scheduler</title>
    </head>

    <body>
        <a href="logout.php">Delete session</a><br>
        <a href="setup.php">Run setup</a><br>
        <a href="register.php">Register user</a><br>
        <a href="login.php">Login</a><br>
        <br>

        <?
            if (!$user->isGuest()) {
                echo "User details<br>";
                echo "Username: ".$user->getName();
                echo "<br>First name: ".$user->getFirst();
                echo "<br>Last name: ".$user->getLast();
                echo "<br>Email: ".$user->getEmail();
            } else {
                echo "Guest account<br>";
            }
        ?>
    </body>
</html>