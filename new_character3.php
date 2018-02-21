<?php
    //This page takes the values from new_character2.php, checks to see what skills the chosen class has at first level and displays them here
    define('TITLE', 'New Character');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Create Character</h1>
            <h2>Skills and Spells</h2>
        </div>';

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p>';
        include('templates/footer.html');
        exit();
    }

    //TODO: check to make sure char is set, stats were rolled, proficiencies were selected and languages were selected

    if (isset($_POST['char'])) {

        $char_data = unserialize($_POST['char']);
        $char_auto_pl = unserialize($_POST['char_auto_pl']);

        //Add the information submitted from new_character2 to the char_data array

        //check if a subrace was set
        if(isset($_POST['subrace'])) {
            $char_data['char_subrace'] = $_POST['subrace'];
        }

        $char_data['char_profs'] = $_POST['cl_profs'];
        $char_data['char_auto_pl'] = $char_auto_pl;
        $char_data['char_langs'] = $_POST['bkgd_langs'];
        $char_data['char_str'] = $_POST['str'];
        $char_data['char_dex'] = $_POST['dex'];
        $char_data['char_con'] = $_POST['con'];
        $char_data['char_int'] = $_POST['intel'];
        $char_data['char_wis'] = $_POST['wis'];
        $char_data['char_cha'] = $_POST['cha'];
        $char_data['char_equip_opt'] = $_POST['equip_option'];

        //get the database connection
        include('connect/mysqli_connect.php');

        //check if a subclass was set
        if (isset($_POST['subclass'])) {
            $char_data['subclass'] = $_POST['subclass'];
        }

        //check if race proficiencies were selected. If so, add to the char_profs list
        if (isset($_POST['race_profs'])) {
            foreach ($_POST['race_profs'] as $value) {
                array_push($char_data['char_profs'], $value);
            }
        }

        //check if the user selected a martial or simple weapon on the previous page: if so, add those to the array as well (probably need their id)
        if (isset($_POST['martial'])) {
            $char_data['martial_weaps'] = $_POST['martial'];
        }

        if (isset($_POST['simple'])) {
            $char_data['simple_weaps'] = $_POST['simple'];
        }

        //Allow the user to select their subclass/race profs if available
        //Get optional subclass proficiencies if they exist
        if(isset($char_data['subclass'])) {

            $scp_query = "SELECT scp_prof FROM sc_profs WHERE scp_subclass={$_POST['subclass']} AND auto_gain=0";
            $scp_result = mysqli_query($dbc, $scp_query);

            if ($scp_result ) {
                print '
                <label for="subclass_profs">Subclass Proficiencies:</label><br>';
                //Get the max number of proficiencies that can be chosen according to the characters' class
                $prof_num_query = "SELECT max_proficiencies FROM subclasses WHERE sc_id={$_POST['subclass']}";
                $pn_result = mysqli_query($dbc, $prof_num_query);
                $pn_row = mysqli_fetch_array($pn_result);
                print '<p>Choose ' . $pn_row['max_proficiencies'] . ' additional proficiencies from the list below:</p>';

                while ($scp_row = mysqli_fetch_array($scp_result)) {
                    $query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$scp_row['scp_prof']}";
                    $result = mysqli_query($dbc, $query);

                    if ($result) {

                        while ($row = mysqli_fetch_array($result)) {
                            print '<input type="checkbox" name="subclass_profs[]" value="' . $row['prof_id'] . '" />' . $row['prof_name'] . '<br>';
                        }
                    }
                }
            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }
        }

        //Get optional subrace proficiencies if they exist
        if(isset($char_data['subrace'])) {
            $srp_query = "SELECT srp_prof FROM sr_profs WHERE srp_subrace={$_POST['subrace']} AND auto_gain=0";
            $srp_result = mysqli_query($dbc, $srp_query);

            if ($srp_result) {
                print '
                <label for="subrace_profs">Subrace Proficiencies:</label><br>';
                //Get the max number of proficiencies that can be chosen according to the characters' class
                $prof_num_query = "SELECT max_proficiencies FROM subraces WHERE race_id={$_POST['subrace']}";
                $pn_result = mysqli_query($dbc, $prof_num_query);
                $pn_row = mysqli_fetch_array($pn_result);
                print '<p>Choose ' . $pn_row['max_proficiencies'] . ' additional proficiencies from the list below:</p>';

                while ($srp_row = mysqli_fetch_array($srp_result)) {
                    $query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$srp_row['srp_prof']}";
                    $result = mysqli_query($dbc, $query);

                    if ($result) {

                        while ($row = mysqli_fetch_array($result)) {
                            print '<input type="checkbox" name="subrace_profs[]" value="' . $row['prof_id'] . '" />' . $row['prof_name'] . '<br>';
                        }
                    }
                }
            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }
        }

        if (isset($char_data['subclass']) && $scp_result && $scp_result->num_rows !== 0 && $srp_result && $srp_result->num_rows !== 0) {
            //print all profs granted so far, as well as any granted by the subclass and subrace chosen on the previous page
            //granted proficiencies
            print '<ul>Current Proficiencies:';
            foreach ($char_auto_pl as $value) {
                $query = "SELECT prof_name FROM proficiencies WHERE prof_id=$value";
                $result = mysqli_query($dbc, $query);

                while ($row = mysqli_fetch_array($result)) {
                    print '<li>' . $row['prof_name'] . '</li>';
                }
            }

            //proficiencies selected by user
            foreach ($char_data['char_profs'] as $value) {
                $query = "SELECT prof_name FROM proficiencies WHERE prof_id=$value";
                $result = mysqli_query($dbc, $query);

                while ($row = mysqli_fetch_array($result)) {
                    print '<li>' . $row['prof_name'] . '</li>';
                }
            }

            //List all subclass proficiencies that are automatically granted
            $query = "SELECT scp_prof, prof_id, prof_name FROM sc_profs INNER JOIN proficiencies
            ON sc_profs.scp_prof = proficiencies.prof_id WHERE scp_subclass={$_POST['subrace']} AND auto_gain=1";
            $result = mysqli_query($dbc, $query);
            if ($result && ($result->num_rows !== 0)) {
                while ($row = mysqli_fetch_array($result)) {
                    print '<li>' . $row['prof_name'] . '</li>';
                    array_push($char_data['char_auto_pl'], $row['prof_id']);
                }
            }

            //List all subrace proficiencies that are automatically granted
            $query = "SELECT srp_prof, prof_id, prof_name FROM sr_profs INNER JOIN proficiencies
            ON sr_profs.srp_prof = proficiencies.prof_id WHERE srp_subrace={$_POST['subrace']} AND auto_gain=1";
            $result = mysqli_query($dbc, $query);
            if ($result && ($result->num_rows !== 0)) {
                while ($row = mysqli_fetch_array($result)) {
                    print '<li>' . $row['prof_name'] . '</li>';
                    array_push($char_data['char_auto_pl'], $row['prof_id']);
                }
            }

            print '</ul>';

        }

        print '<form id="new_char3" action="new_character4.php" method="post">';

        //if the character has the fighter class, let them choose their fighting style
        if ($char_data["char_class"] == 1) {
            print '<label>Select Your Fighting Style:</label><br><select name="fight_style">';
            $query = "SELECT fs_id, fs_name, fs_descr FROM fighting_styles";
            $result = mysqli_query($dbc, $query);

            while ($row = mysqli_fetch_array($result)) {
                print "<option value=\"{$row['fs_id']}\">{$row['fs_name']}</option>";
            }
            print '</select>';

            //print all the fighting style descriptions here and use javascript to change their class to visible when the corresponding option is selected
        }

        //Get and display all available level 1 skills for the chosen class
        $query = "SELECT skill_id, skill_name, skill_descr FROM skills WHERE skill_class={$char_data['char_class']} AND skill_lvl=1";
        $result = mysqli_query($dbc, $query);

        print '<h4 class="note">Here are your character\'s starting skills:</h4>';

        while ($row = mysqli_fetch_array($result)) {
            print '
            <h4>' . $row['skill_name'] . '</h4>
            <p>' . $row['skill_descr'] . '</p>';
        }

        //In the future when caster classes are added, this page will check the user's class to see if it falls under that category.
        //If so, they will be able to select their spells and cantrips
        //serilize the character info
        $char_data = serialize($char_data);
        $char = htmlentities($char_data);
        print '
            <input type="hidden" name="char" value="' . $char . '">
            <button type="submit" class="btn" name="submit">Next Step</button>
            </form>
            </div>';

        include('templates/footer.html');
    } else {
        //did not enter values
        print '<p>Please enter a name before continuing.</p></div>';
        include('templates/footer.html');
    }
?>
