<?php
    define('TITLE', 'My Character');
    include('templates/header.html');

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p>';
        include('templates/footer.html');
        exit();
    }

    include('connect/mysqli_connect.php');
    $character= $_GET['character'];
    //make sure the character being viewed belongs to the logged in user
    if ($character) {
        //as the page will be divided into 3 columns, use bootstrap classes to set up said columns

        //***************HTML content starts here********************//
        print '
        <nav class="subnav">
            <ul role="navigation">
                <li role="presentation"><a href="view_character.php?character=' .$character. '">Main</a></li>
                <li role="presentation"><a href="view_stats.php?character=' .$character. '">Stats</a></li>
                <li role="presentation"><a href="view_skills.php?character=' .$character. '">Skills</a></li>
                <li role="presentation" class="active"><a href="view_spellbook.php?character=' .$character. '">Spellbook</a></li>
                <li role="presentation"><a href="view_inventory.php?character=' .$character. '">Inventory</a></li>
                <li role="presentation"><a href="view_traits.php?character=' .$character. '">Traits</a></li>
            </ul>
        </nav>
        <div id="text_area">
            <img class="logo" src="resources/drconstruction.png" alt="dragonroll logo - unprepared variation">
            <h2>Oops!</h2>
            <p class="center-text">Looks like this page isn\'t quite ready yet. Know that we\'re working on it and will have it ready for use soon!</p>
        </div>';

        mysqli_close($dbc);

        include('templates/footer.html');
    } else {
        print '<h2>Oops!</h2>
        <p>Please select one of your characters from <a href="character_select.php">here</a> to view this page.</p>';
    }
?>
