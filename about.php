<?php
    define('TITLE', 'Dragon Roll');
    include ('templates/header.html');
    include ('connect/mysqli_connect.php');

    print '
    <div id="text_area">
    	<div class="page_title">
    		<h1>About</h1>
    	</div>
    	<div class="page_content">
    		<p>Dragonroll is a prototype of a Dungeons and Dragons 5e character creator and manager. It was originally created as a school project and this demo is by no means a complete product.</p>
    	</div>
    </div>';

    mysqli_close($dbc);

    include('templates/footer.html');
?>
