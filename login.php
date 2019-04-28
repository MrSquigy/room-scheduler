<?
    require_once('helper.php');

    if (isset($_POST['login'])) {
        // Check input
        $helper->makeConn();
        $username = $helper->escapeStr($_POST['username']);
        $password = $helper->escapeStr($_POST['password']);
        $helper->closeConn();

        // Login user
        $log = $helper->login($username, $password);
        if (!$log) echo "Failed to log user in<br>";
        echo "<a href='index.php'>home</a>";
    } else {
        ?>

        <form action="login.php" method="post" name="LoginForm">
            <table>
                <tr><td><label for="username">Username</label></td></tr>
                <tr><td><input type="text" maxlength="30" size="32" name="username"></td></tr>
                <tr><td><label for="password">Password</label></td></tr>
                <tr><td><input type="password" size="32" name="password"></td></tr>
                <tr><td><input type="submit" name="login" value="Login"></td></tr>
            </table>
        </form>

        <?
    }
?>