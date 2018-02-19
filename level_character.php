<?php
    define('TITLE', 'My Character');
    include('templates/header.html');

    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Level Up</h1>
        </div>
        <div class="page_content">';

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p></div>';
        include('templates/footer.html');
        exit();
    }

    include('connect/mysqli_connect.php');

    if (isset($_GET['character'])) {
        $character= $_GET['character'];
    } else if (isset($_POST['character'])) {
        $character= $_POST['character'];
    }

    //make sure the character being viewed belongs to the logged in user
    //get all the character information
    $char_query = "SELECT * FROM characters WHERE char_id=$character";
    $char_result = mysqli_query($dbc, $char_query);
    $char_row = mysqli_fetch_array($char_result);

    if ($character && $char_row['char_user'] == $_SESSION['user_id']) {

        //check if the method was POST: if so, will need to update the character sheet
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            //create an array of the character's current stats
            $stats = [
                'strength' => $char_row['strength'],
                'dexterity' => $char_row['dexterity'],
                'constitution' => $char_row['constitution'],
                'intelligence' => $char_row['intelligence'],
                'wisdom' => $char_row['wisdom'],
                'charisma' => $char_row['charisma']
            ];

            //get the character's new lvl
            $new_lvl = $char_row['lvl'] + 1;

            //get the character's hp increase
            $hp = $_POST['hp_increase'];
            $new_hp = $hp + $char_row['hp'];

            //add the character's constitution modifier
            $query = "SELECT modifier FROM modifiers WHERE ability_score={$stats['constitution']}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);

            $new_hp += $row['modifier'];

            //if there was an ability score increase, get which stats the user wanted to increase
            if (isset($_POST['stat1']) && isset($_POST['stat2'])) {
                $stat1 = $_POST['stat1'];
                $stat2 = $_POST['stat2'];

                //loop over the stats array and check if there is a match between a stat and what the user selected
                foreach ($stats as $stat => &$value) {
                    if ($stat == $stat1) {
                        $stats[$stat] = $value + 1;
                    }
                    if ($stat == $stat2) {
                        $stats[$stat] = $value + 1;
                    }
                }
            }

            //get the ids of the skills the character gets this lvl
            $skill_query = "SELECT skill_id FROM skills WHERE skill_class={$char_row['char_class']} AND skill_lvl={$new_lvl}";
            $skill_result = mysqli_query($dbc, $skill_query);

            $scs_query = "SELECT scs_id, scs_skill, scs_description, scs_lvl FROM sc_skills WHERE scs_subclass={$char_row['char_subclass']} AND skill_lvl={$new_lvl}";
            $scs_result = mysqli_query($dbc, $scs_query);

            //if the user was able to choose a subclass, get their selection
            if (isset($_POST['subclass'])) {
                $subclass = mysqli_real_escape_string($dbc, trim(strip_tags($_POST['subclass'])));
            }

            //check if there was a proficiency bonus increase

            /*Begin updating the character information*/

            //update the character's hp and lvl
            $query = "UPDATE characters SET lvl=$new_lvl, hp=$new_hp WHERE char_id=$character AND char_user={$char_row['char_user']}";
            $result = mysqli_query($dbc, $query);

            //add the subclass if one was set
            if (!empty($subclass)){
                $query = "UPDATE `characters` SET `char_subclass`=$subclass WHERE char_id=$character AND char_user={$char_row['char_user']}";
                $result = mysqli_query($dbc, $query);
            }

            //add the character's new class skills
            if ($skill_result) {
                while ($row = mysqli_fetch_array($skill_result)) {
                    $query = "INSERT INTO `char_skills`(`cskill_char`, `cskill_skill`) VALUES ($character, {$row['skill_id']})";
                    $result = mysqli_query($dbc, $query);
                }
            }

            //add the character's new subclass skills
            if ($scs_result) {
                while ($row = mysqli_fetch_array($scs_result)) {
                    $query= "INSERT INTO `char_skills`(`cskill_char`, `cskill_scskill`) VALUES ($character, {$row['scs_skill']})";
                    $result = mysqli_query($dbc, $query);
                }
            }

            //handle ability score increase: use the stats array we made earlier to update the character's stats
            if (isset($_POST['stat1']) && isset($_POST['stat2'])){
                $query = "UPDATE `characters` SET `strength`={$stats['strength']}, `dexterity`={$stats['dexterity']}, `constitution`={$stats['constitution']}, `intelligence`={$stats['intelligence']}, `wisdom`={$stats['wisdom']}, `charisma`={$stats['charisma']} WHERE char_id=$character AND char_user={$char_row['char_user']}";
                $result = mysqli_query($dbc, $query);
            }

            print '
            <h2>Success!</h2>
            <p class="center-text">Congrats! Click the button below to go back to your character.</p>
            <div class="cs_btn"><a href="view_character.php?character=' .$character. '" class="btn btn-default">Back to Character Sheet</a></div></div></div>';

        } else {

            //Check if the character has reached lvl 20
            if ($char_row['lvl'] == 20) {
                print '<h2>Congratulations!</h2><p class="welcome">You have already reached the maximum level for the game!</p>
                <div id="vc_button"><a href="view_character.php?character=' .$character. '" class="btn btn-default">Back to character sheet</a></div></div></div>';
            } else {

                //get the hit die associated with the character's class
                $query = "SELECT hit_die FROM classes WHERE class_id={$char_row['char_class']}";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);

                //use hit die + constitution mod to determine max entry
                $hit_die = $row['hit_die'];

                //get the character's upcoming level
                $next_lvl = $char_row['lvl'] + 1;

                //get a list of skills granted by the character's class at this level
                $query = "SELECT skill_id, skill_name, skill_descr, skill_lvl FROM skills WHERE skill_class={$char_row['char_class']} AND skill_lvl={$next_lvl}";
                $result = mysqli_query($dbc, $query);

                if ($result && ($result->num_rows !== 0)) {
                    print '<p>You gain the following skills:</p>';
                    while ($row = mysqli_fetch_array($result)) {
                        //print any skills granted by the character's class
                        print '
                        <h4>' .$row['skill_name']. '</h4>
                        <p>' .$row['skill_descr']. '</p>';
                    }
                }

                //get a list of skills granted by the character's subclass at this level
                $query = "SELECT scs_id, scs_skill, scs_description, scs_lvl FROM sc_skills WHERE scs_subclass={$char_row['char_subclass']} AND skill_lvl={$next_lvl}";
                $result = mysqli_query($dbc, $query);

                if ($result && ($result->num_rows !== 0)) {
                    print '<p>You gain the following skills:</p>';
                    while ($row = mysqli_fetch_array($result)) {
                        //print any skills granted by the character's subclass
                        print '
                        <h4>' .$row['skill_name']. '</h4>
                        <p>' .$row['skill_descr']. '</p>';
                    }
                }

                //create a form for updating the character
                print '
                <form action="level_character.php" method="post">
                    <div class="form-group lvl_up_form">
                        <label>Roll your character\'s hit die (or use the die\'s average) and enter the result below:</label>
                        <input type="number" name="hp_increase" class="form-control" required>
                    </div>';

                    //get any subclasses that become available at this level for the user to select
                    //get a list of subclasses granted this level, if any
                    $query = "SELECT sc_id, sc_name, sc_description FROM subclasses WHERE main_class_id={$char_row['char_class']} AND lvl_avail={$next_lvl}";
                    $result = mysqli_query($dbc, $query);

                    if ($result && ($result->num_rows !== 0)) {
                        print '
                        <div class="form-group lvl_up_form">
                            <label>Subclasses for you character\'s class are now available! Please choose one of the options below:</label>
                            <select name="subclass" class="form-control" onchange="subclassDescr(this)" required>
                            <option value="">Choose a subclass...</option>';

                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['sc_id']. '">' .$row['sc_name']. '</option>';
                            }
                            print '
                            </select>
                        </div>';
                    }

                //check to see if there is an ability score increase this level
                $as_query = "SELECT points FROM as_improvements WHERE asi_class={$char_row['char_class']} AND asi_lvl={$next_lvl}";
                $as_result = mysqli_query($dbc, $as_query);

                //get a list of ability scores
                $stats = [
                        'strength' => $char_row['strength'],
                        'dexterity' => $char_row['dexterity'],
                        'constitution' => $char_row['constitution'],
                        'intelligence' => $char_row['intelligence'],
                        'wisdom' => $char_row['wisdom'],
                        'charisma' => $char_row['charisma']
                ];

                //display the users current stats so they can make a better decision

                if ($as_result && ($as_result->num_rows !== 0)) {
                    //if so, create a multiselect so the user can choose up to two stats they would like to increase
                    print '
                    <div class="form-group lvl_up_form">
                        <fieldset required>
                            <label>Select two stats from the fields below that you would like to increase by 1 (Note: You can pick the same stat twice)</label>
                            <select name="stat1" class="form-control" required>
                            <option value="">Select a stat to increase...</option>';
                            foreach ($stats as $key => $value) {
                                print '<option value="' .$key. '">' .$key. '</option>';
                            }

                        print '</select>
                            <select name="stat2" class="form-control" required>
                            <option value="">Select a stat to increase...</option>';
                            foreach ($stats as $key => $value) {
                                print '<option value="' .$key. '">' .$key. '</option>';
                            }

                        print '</select>
                        </fieldset>
                    </div>';
                }

                print '
                <input type="hidden" name="character" value="' .$character. '" role="button">
                <button type="submit" name="submit" class="lvl_btn">Level Up!</button>
                </form>
                </div>
                </div>';
            }
        }
        mysqli_close($dbc);

        include('templates/footer.html');
    } else {
        print '<h2>Oops!</h2>
        <p>Please select one of your characters from <a href="character_select.php">here</a> to view this page.</p>';
    }
?>
