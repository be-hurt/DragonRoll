<?php
    define('TITLE', 'Character Select');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Character Select</h1>
            <h2>Choose Your Character...</h2>
        </div>';

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p>';
        include('templates/footer.html');
        exit();
    }

    print '<div class="page_content">
      <button class="new_char_btn" role="button"><a href="new_character.php">New Character</a></button>';

    include('connect/mysqli_connect.php');

    //get the logged in user's stored characters

    $query = "SELECT char_id, name, char_class, lvl FROM characters WHERE char_user={$_SESSION['user_id']}";

    if ($result = mysqli_query($dbc, $query)) {
        while ($row = mysqli_fetch_array($result)) {
            $class_query = "SELECT class_name FROM classes WHERE class_id={$row['char_class']}";
            $class_result = mysqli_query($dbc, $class_query);
            $class_row = mysqli_fetch_array($class_result);
            print '
            <div class="view_char">
                <form class="view_char_form" action="delete_character.php" method="post">
                    <input type="hidden" name="character" value="' .$row['char_id']. '">
                    <button type="submit" class="btn_small" name="submit">Delete</button>
                </form>
	            <table class="view_char_table">
	                <tr>
	                    <th>Name:</th>
	                    <td>' .$row['name']. '</td>
	                </tr>
	                <tr>
	                    <th>Class:</th>
	                    <td>' .$class_row['class_name']. '</td>
	                </tr>
	                <tr>
	                    <th>Level:</th>
	                    <td>' .$row['lvl']. '</td>
	                </tr>
	            </table>
	            <form class="view_char_form" action="view_character.php" method="post">
	            	<input type="hidden" name="character" value="' .$row['char_id']. '">
	            	<button type="submit" class="btn_select" name="submit">Select</button>
	            </form>
            </div>';
        }

    } else {
        //Query didn't run
        print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
    }

            print '
            <img class="dice_divider" src="resources/dice.png" alt="A row of d20s">
        </div>
    </div>';

    mysqli_close($dbc);

    include('templates/footer.html');
?>
