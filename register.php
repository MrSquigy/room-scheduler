<?
    require_once('helper.php');

    if (isset($_POST['register'])) {
        // Check input
        $helper->makeConn();
        $username = $helper->escapeStr($_POST['username']);
        $password = password_hash($helper->escapeStr($_POST['password']), PASSWORD_DEFAULT);
        $email = $helper->escapeStr($_POST['email']);
        $first = $helper->escapeStr($_POST['first']);
        $last = $helper->escapeStr($_POST['last']);
        $helper->closeConn();
       
        // Register user
        $reg = $helper->registerUser($username, $password, $first, $last, $email);

        if ($reg) echo "Registered successfully<br>";
        else echo "Failed to register<br>";
        echo "<a href='index.php'>home</a>";
    } else {
        ?>

        <form action="register.php" method="post" id="RegisterForm">
            <table>
                <tr><td><label for="username">Username</label></td></tr>
                <tr><td><input type="text" maxlength="30" size="32" name="username"></td></tr>
                <tr><td><label for="password">Password</label></td></tr>
                <tr><td><input type="password" size="32" name="password"></td></tr>
                <tr><td><label for="email">Email</label></td></tr>
                <tr><td><input type="text" maxlength="50" size="32" name="email"></td></tr>
                <tr><td><label for="first">First Name</label></td></tr>
                <tr><td><input type="text" maxlength="30" size="32" name="first"></td></tr>
                <tr><td><label for="last">Last Name</label></td></tr>
                <tr><td><input type="text" maxlength="30" size="32" name="last"></td></tr>
                <tr><td><input type="submit" name="register" value="Register"></td></tr>
            </table>
        </form>

        <?
    }
?>