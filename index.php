<? require_once('helper.php') ?>

<html>
    <head>
        <title>Event Scheduler</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    </head>

    <body>
        <a href="setup.php">Run setup</a><br>
        <a href="register.php">Register user</a><br>
        <?
            if ($user->isGuest()) echo "<a href='login.php'>Login</a><br>";
            else echo "<a href='logout.php'>Logout</a><br>";
        ?>

        <!-- Rooms info -->
        <table width="100%">
            <tr>
                <td style="min-width: 50px; width: 50px"><b>#</b></td>
                <td><b>Room Name</b></td>
            </tr>

            <?
                $rooms = $helper->getRooms();
                while ($room = $rooms->fetch_assoc()) {
                    debug($room['number']);
                    echo "<tr onclick=\"$('.room$room[number]Info').toggle()\">";
                    echo "<td>$room[number]</td><td>$room[name]</td>";
                    echo "</tr>";
                    echo "<tr class='room$room[number]Info' style='display:none'><td>&nbsp;</td><td>".$helper->getRoomSchedule($room['number'])."</td></tr>";
                }
            ?>

        </table>
    </body>
</html>