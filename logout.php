<?php

    define('TITLE', 'Logout');
    include('templates/header.html');

    $_SESSION = [];
    session_destroy();

    print '
        <div id="text_area">
            <div class="page_title">
                <h1>Logout</h1>
                <h2 class="red_text">Success!</h2>
            </div>
            <div class="welcome">
                <p>You are now logged out. See you next time!</p>
                <p><a href="index.php">Back to home</a></p>
            </div>
        </div>
    </div>';

    include('templates/footer.html');
?>
