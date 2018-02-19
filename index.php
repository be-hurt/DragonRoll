<?php
    define('TITLE', 'Dragon Roll');
    include ('templates/header.html');
    include ('connect/mysqli_connect.php');

    print '
    <div id="text_area">
        <img class="logo" src="resources/drlogo.png" alt="dragonroll logo">
        <p>Welcome to DragonRoll, a Dungeons and Dragons 5e character creator and manager. In an effort to make the character creation process
        easier on both newbies and veterans, we have created this site to automate and eliminate as much of the work and calculations
        to get started so everyone can get to playing the game as quickly as possible.</p><hr>
        <img class="dice_divider" src="resources/dice.png" alt="A row of d20s">
    </div>
    ';

    mysqli_close($dbc);

    include('templates/footer.html');
?>
