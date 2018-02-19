<?php
    define('TITLE', 'Dragon Roll');
    include ('templates/header.html');
    include ('connect/mysqli_connect.php');

    print 'Hello, World.';

    mysqli_close($dbc);

    include('templates/footer.html');
?>
