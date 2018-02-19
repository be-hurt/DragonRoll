<?php

    define('TITLE', 'Logout');
    include('templates/header.html');

    $_SESSION = [];
    session_destroy();

    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Logout</h1>
            <h2>Success</h2>
        </div>
        <div class="welcome">
            <p>You are now logged out.</p>
        </div>
    </div>';

    include('templates/footer.html');
?>
