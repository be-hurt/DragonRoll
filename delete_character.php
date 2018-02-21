<?php

    //This page takes the chosen character to delete from character_select.php and takes the user through the character deletion process
    define('TITLE', 'Delete Character');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Delete Character</h1>
        </div>';

    //check if the user is logged in
    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p></div>';
        include('templates/footer.html');
        exit();
    }

    //check to make sure the server method is POST (to ensure that the user got here from character_select.php)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $character = 0;

        if (isset($_POST['character'])) {
            $character = $_POST['character'];
        }

        //include the database connection
        include('connect/mysqli_connect.php');

        //query the database to get all the necessary character information for deletion
        $char_query = "SELECT * FROM characters WHERE char_id={$character} AND char_user={$_SESSION['user_id']}";
        $char_result = mysqli_query($dbc, $char_query);

        //make sure the character being viewed belongs to the logged in user
        if ($char_result && $char_result->num_rows !== 0) {
            
            $char_row = mysqli_fetch_array($char_result);

            //if the user has already clicked the 'delete' button on this page, handle it here
            if (isset($_POST['remove']) && $_POST['remove'] == 1) {
                /*Have to delete all dependencies before we can delete the character itself*/
                //delete character armor
                $query = "DELETE FROM `char_armor` WHERE ca_char={$character}";
                mysqli_query($dbc, $query);

                //delete fight style, if the character has the fighter class
                if ($char_row['char_class'] == 1) {    
                    $query="DELETE FROM `char_fight_style` WHERE cfs_char={$character}";
                    mysqli_query($dbc, $query);
                }

                //delete all the character's items
                $query = "DELETE FROM `char_items` WHERE ci_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's known languages
                $query = "DELETE FROM `char_langs` WHERE cl_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's packs
                $query = "DELETE FROM `char_packs` WHERE cpack_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's proficiencies
                $query = "DELETE FROM `char_proficiencies` WHERE cp_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's skills
                $query = "DELETE FROM `char_skills` WHERE cskill_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's spells
                $query = "DELETE FROM `char_spells` WHERE cs_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's spell slots
                $query = "DELETE FROM `char_spell_slots` WHERE css_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's tools
                $query = "DELETE FROM `char_tools` WHERE ct_char={$character}";
                mysqli_query($dbc, $query);

                //delete all the character's weapons
                $query = "DELETE FROM `char_weapons` WHERE cw_char={$character}";
                mysqli_query($dbc, $query);

                /*Finally, delete the character*/
                $query = "DELETE FROM `characters` WHERE char_id={$character} AND char_user={$_SESSION['user_id']} LIMIT 1";
                $result = mysqli_query($dbc, $query);

                //check if there was a result. If so, the character was deleted
                if ($result) {
                    //print a success message to the user: have a back button to return to character_select.php
                    print '<h2>Success!</h2><p>Your character was successfully deleted.</p>
                    <a href="character_select.php" class="btn btn-default" role="button">Character Select</a></div>';
                } else {
                    //Query didn't run
                    print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p></div>';
                }

            } else {
            //get the character delete confirmation

            print '<div class="page_content">
            <p>Are you sure you want to delete this character?</p>';

            //display an overview of the character: name, level, class, race...
            $class_query = "SELECT class_name FROM classes WHERE class_id={$char_row['char_class']}";
            $class_result = mysqli_query($dbc, $class_query);
            $class_row = mysqli_fetch_array($class_result);
            print '<div class="view_char">
                <table class="table table-condensed cs_table">
                    <tr>
                        <th>Name</th>
                        <td>' .$char_row['name']. '</td>
                    </tr>
                    <tr>
                        <th>Class</th>
                        <td>' .$class_row['class_name']. '</td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td>' .$char_row['lvl']. '</td>
                    </tr>
                </table>
                <form class="cs_form" action="delete_character.php" method="post">
                    <input type="hidden" name="character" value="' .$char_row['char_id']. '">
                    <input type="hidden" name="remove" value="1">
                    <p><input type="submit" class="btn btn-default" name="submit" value="Delete Character"></p>
                </form>
                </div>
                <img class="dice_divider" src="resources/dice.png" alt="A row of d20s">
            </div>';
            }
        } else {
            //Query didn't run
            print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
        }

        print '</div>';
}

    mysqli_close($dbc);

    include('templates/footer.html');
?>
