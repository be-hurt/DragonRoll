<?php
    define('TITLE', 'My Character');
    include('templates/header.html');

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p>';
        include('templates/footer.html');
        exit();
    }

        $character = 0;

        if (isset($_POST['character'])) {
            $character = $_POST['character'];
        } else if (isset($_GET['character'])) {
            $character = $_GET['character'];
        }

        include('connect/mysqli_connect.php');
        $char_query = "SELECT * FROM characters WHERE char_id={$character} AND char_user={$_SESSION['user_id']}";
        $char_result = mysqli_query($dbc, $char_query);

        //make sure the character being viewed belongs to the logged in user
        if ($char_result && $char_result->num_rows !== 0) {
            //get the logged in user's selected character (passed in from character_select.php) and build the character sheet with the retrieved information
            $char_row = mysqli_fetch_array($char_result);

            //***************HTML content starts here********************//
            print '
            <nav class="subnav">
                <ul role="navigation">
                    <li role="presentation"><a href="view_character.php?character=' .$character. '">Main</a></li>
                    <li role="presentation"><a href="view_stats.php?character=' .$character. '">Stats</a></li>
                    <li role="presentation" class="active"><a href="view_skills.php?character=' .$character. '">Skills</a></li>
                    <li role="presentation"><a href="view_spellbook.php?character=' .$character. '">Spellbook</a></li>
                    <li role="presentation"><a href="view_inventory.php?character=' .$character. '">Inventory</a></li>
                    <li role="presentation"><a href="view_traits.php?character=' .$character. '">Traits</a></li>
                </ul>
            </nav>
            <div id="text_area">';
                //Display the character's skills/feats
                //Need to make skills query
                $query = "SELECT cskill_skill, skill_name, skill_descr, skill_lvl FROM char_skills
                INNER JOIN skills ON char_skills.cskill_skill = skills.skill_id WHERE cskill_char={$character}";
                $result = mysqli_query($dbc, $query);

                while ($row = mysqli_fetch_array($result)) {
                     print '
                     <table class="skills_table">
                         <tr>
                            <th>Skill Name</th>
                            <td>' .$row['skill_name']. '</td>
                        </tr>
                        <tr>
                            <th>Level</th>
                            <td>' .$row['skill_lvl']. '</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>' .$row['skill_descr']. '</td>
                        </tr>
                    </table>
                    <hr>';
                }

                print '
                </div>'; //end of skills display

            mysqli_close($dbc);

            include('templates/footer.html');
        } else {
            print '<h2>Oops!</h2>
            <p>Please select one of your characters from <a href="character_select.php">here</a> to view this page.</p>';
        }
?>
