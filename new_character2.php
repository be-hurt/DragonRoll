<?php
    //this page processes the submitted info from new_character.php and grants additional character options accordingly (proficiencies and languages)
    define('TITLE', 'New Character');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Create Character</h1>
            <h2>Languages and Proficiencies</h2>
        </div>
        <div class="page_content">';

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p>';
        include('templates/footer.html');
        exit();
    }

    //TODO: check to make sure name field is set: if not, produce an error and back button
    if (isset($_POST['name'])){

        //get the database connection
        include('connect/mysqli_connect.php');

        //begin an array to hold all the form information and carry it across multiple pages
        if (!isset($char_object)){
            $char_object = [
                'char_name' => "{$_POST['name']}",
                'char_class' => "{$_POST['class']}",
                'char_race' => "{$_POST['race']}",
                'char_bkgd' => "{$_POST['background']}",
                'char_align' => "{$_POST['alignment']}"
            ];
        }

        $char_data = serialize($char_object);
        $char = htmlentities($char_data);

        $option_count = 0;

        print '
            <form id="new_char2" action="new_character3.php" method="post">';

        //If there are subraces available for the chosen race, create a select field so the user can choose which they want
        $query = "SELECT subrace_name, subrace_id, subrace_description FROM subraces WHERE main_race_id={$_POST['race']} ORDER BY subrace_name";
        $result = mysqli_query($dbc, $query);

        if ($result) {
            if($result->num_rows !== 0) {
                print '
                <div class="form-group">
                    <label for="subrace"><h2>Sub-Race:</h2></label>
                    <div>
                        <select name="subrace" id="subrace" class="form-control" onchange="subraceDescr(this)" required>
                        <option value="">Choose a subrace...</option>';
                while ($row = mysqli_fetch_array($result)) {
                    print '<option value="' .$row['subrace_id'].'">' .$row['subrace_name']. '</option>';
                }
                print '
                        </select>
                    </div>
                </div>
                <div>';

                mysqli_data_seek($result, 0);

                    while ($row = mysqli_fetch_array($result)) {
                        print '<p id=' .$row['subrace_id']. ' class="hidden">'
                            .$row['subrace_description']. '</p>';
                    }
                print '</div>';
            }
        } else {
            //Query didn't run
            print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
        }

        //If there are subclasses available for the chosen class at the current level, create a select field so the user can choose which they want. If the result
        //is 0, there is no sub-class (or the query didn't run, but that seems unlikely)
        $query = "SELECT sc_name, sc_id, sc_description FROM subclasses WHERE main_class_id={$_POST['class']} AND lvl_avail=1 ORDER BY sc_name";
        $result = mysqli_query($dbc, $query);

        if ($result && ($result->num_rows !== 0)) {
            print '
            <div class="form-group">
                <label for="subclass"><h2>Sub-Class:</h2></label>
                <select name="subclass" required>';

            while ($row = mysqli_fetch_array($result)) {
                print "<option value=\"{$row['sc_name']}\">{$row['sc_name']}</option>";
            }

            print '
            </select>
            </div>';
        }

        //List all class proficiencies that are automatically granted
        //Note: this one might be able to be reduced by a join statement. Look into refactoring later. The autogain may be problematic though.

        $char_auto_pl = []; //initialize array to store all profs granted automatically
        $query = "SELECT clp_prof FROM class_profs WHERE clp_class={$_POST['class']} AND auto_gain=1";
        $result = mysqli_query($dbc, $query);

        if ($result && ($result->num_rows !== 0)) {
            print '
            <label><h2>Granted Proficiencies:</h2></label>
            <ul>';

            while ($row = mysqli_fetch_array($result)) {
                $prof_query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$row['clp_prof']}";
                $prof_result = mysqli_query($dbc, $prof_query);

                while ($prof_row = mysqli_fetch_array($prof_result)) {
                    print '<li>' . $prof_row['prof_name'] . '</li>';
                    array_push($char_auto_pl, $prof_row['prof_id']);
                }
            }
            print '</ul>';
        }

        //List all background proficiencies that are automatically granted
        $query = "SELECT bp_prof FROM bkgd_profs WHERE bp_bkgd={$_POST['background']} AND auto_gain=1";
        $result = mysqli_query($dbc, $query);

        if ($result && ($result->num_rows !== 0)) {
            print '<ul>';

            while ($row = mysqli_fetch_array($result)) {
                $prof_query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$row['bp_prof']}";
                $prof_result = mysqli_query($dbc, $prof_query);

                while ($prof_row = mysqli_fetch_array($prof_result)) {
                    print '<li>' . $prof_row['prof_name'] . '</li>';
                    array_push($char_auto_pl, $prof_row['prof_id']);
                }
            }
            print '</ul>';
        }

        //List all racial proficiencies that are automatically granted
        $query = "SELECT rp_prof FROM race_profs WHERE rp_race={$_POST['race']} AND auto_gain=1";
        $result = mysqli_query($dbc, $query);

        if ($result && ($result->num_rows !== 0)) {
            print '<ul>';

            while ($row = mysqli_fetch_array($result)) {
                $prof_query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$row['rp_prof']}";
                $prof_result = mysqli_query($dbc, $prof_query);

                while ($prof_row = mysqli_fetch_array($prof_result)) {
                    print '<li>' . $prof_row['prof_name'] . '</li>';
                    array_push($char_auto_pl, $prof_row['prof_id']);
                }
            }
            print '</ul>';
        }

        print '
        <label><h2>Class Proficiencies:</h2></label>';
        //Make a checkbox list of proficiencies granted by the character's class and allow the user to select up to the max number of profs allowed by the class
        //(limiting choices will have to be handled in javascript and when Posting the info to the next page)
        $cp_query = "SELECT clp_prof FROM class_profs WHERE clp_class={$_POST['class']} AND auto_gain=0";
        $cp_result = mysqli_query($dbc, $cp_query);

        if ($cp_result) {
            //Get the max number of proficiencies that can be chosen according to the characters' class
            $prof_num_query = "SELECT max_proficiencies FROM classes WHERE class_id={$_POST['class']}";
            $pn_result = mysqli_query($dbc, $prof_num_query);
            $pn_row = mysqli_fetch_array($pn_result);
            print '<p>Choose ' . $pn_row['max_proficiencies'] . ' additional proficiencies from the list below:</p>';

            while ($cp_row = mysqli_fetch_array($cp_result)) {
                $query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$cp_row['clp_prof']}";
                $result = mysqli_query($dbc, $query);

                if ($result) {

                    while ($row = mysqli_fetch_array($result)) {
                        print '<input type="checkbox" name="cl_profs[]" value="' . $row['prof_id'] . '" />' . $row['prof_name'] . '<br>';
                    }
                }
            }
        } else {
            //Query didn't run
            print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
        }

        //will also need to get subclass and subrace proficiencies on next page. Might need to reprint all proficiencies to prevent doubles.

        //Get optional race proficiencies if they exist
        $rp_query = "SELECT rp_prof FROM race_profs WHERE rp_race={$_POST['race']} AND auto_gain=0";
        $rp_result = mysqli_query($dbc, $rp_query);

        if ($rp_result && $rp_result->num_rows !== 0) {
            print '
            <label><h2>Race Proficiencies:</h2></label>';
            //Get the max number of proficiencies that can be chosen according to the characters' class
            $prof_num_query = "SELECT max_proficiencies FROM races WHERE race_id={$_POST['race']}";
            $pn_result = mysqli_query($dbc, $prof_num_query);
            $pn_row = mysqli_fetch_array($pn_result);
            print '<p>Choose ' . $pn_row['max_proficiencies'] . ' additional proficiencies from the list below:</p>';

            while ($cp_row = mysqli_fetch_array($cp_result)) {
                $query = "SELECT prof_name, prof_id FROM proficiencies WHERE prof_id={$rp_row['rp_prof']}";
                $result = mysqli_query($dbc, $query);

                if ($result) {
                    while ($row = mysqli_fetch_array($result)) {
                        print '<input type="checkbox" name="race_profs[]" value="' . $row['prof_id'] . '" />' . $row['prof_name'] . '<br>';
                    }
                }
            }
        } else if ($rp_result) {

        } else {
            //Query didn't run
            print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
        }

        print '
            <label><h2>Languages Known:</h2></label>';
            //Display a list of languages granted by the character's race
            $query = "SELECT rl_lang, lang_name FROM race_langs INNER JOIN languages ON race_langs.rl_lang = languages.lang_id WHERE rl_race={$_POST['race']} AND auto_gain=1";
            $result = mysqli_query($dbc, $query);

            if ($result) {
                print '<ul>';
                while ($row = mysqli_fetch_array($result)) {
                    print '<li>' . $row['lang_name'] . '</li>';
                }
                print '</ul>';

            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }
        print '
            <label><h2>Languages:</h2></label>';
            //Allow the user to select a number of languages according to the character's background
                $bp_query = "SELECT langs_granted FROM bkgd_langs WHERE bl_bkgd={$_POST['background']}";
                $bp_result = mysqli_query($dbc, $bp_query);
                $row = mysqli_fetch_array($bp_result);

                if ($bp_result) {
                    print '<p>Choose ' . $row['langs_granted'] . ' additional languages from the list below:</p>';

                    //Generate list of available languages
                    $query = "SELECT lang_name, lang_id FROM languages";
                    $result = mysqli_query($dbc, $query);

                    while ($row = mysqli_fetch_array($result)) {
                        print '<input type="checkbox" name="bkgd_langs[]" value="' . $row['lang_id'] . '" />' . $row['lang_name'] . '<br>';
                    }
                } else {
                    //Query didn't run
                    print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
                }

        //Create a table to display a list of stats along with racial bonuses and allow the user to either roll or use 'point buy' to determine the character's stats
        //When the user clicks the 'Let's Roll' button, call the roll_stat function from functions.js to generate base stats
        print '
        <label><h2>Stats:</h2></label>
        <div>
            <p>Click the button to roll your stats:</p>
            <button type="button" class="stats_btn" onclick="roll_stat()">Let\'s Roll</button>
        </div>
        <div class="table-responsive">
            <table id="stats-table">
                    <tr>
                        <th>Stat</th>
                        <td>Str</td>
                        <td>Dex</td>
                        <td>Con</td>
                        <td>Int</td>
                        <td>Wis</td>
                        <td>Cha</td>
                    </tr>
                    <tr id="stats">
                        <th>Score</th>
                        <td class="base_stat"></td>
                        <td class="base_stat"></td>
                        <td class="base_stat"></td>
                        <td class="base_stat"></td>
                        <td class="base_stat"></td>
                        <td class="base_stat"></td>
                    </tr>
                    <tr>
                        <th>Race Bonus</th>';
                        //Get the character's racial bonus
                        $query = "SELECT rsb_stat, rsb_stat_increase FROM race_stat_bonus WHERE rsb_race={$_POST['race']}";
                        $result = mysqli_query($dbc, $query);
                        $row = mysqli_fetch_array($result);
                        //create an array of the stat names to loop over: has to be in the same order as the stats in the table
                        $stats = array("strength", "dexterity", "constitution", "intelligence", "wisdom", "charisma");

                            for ($i = 0; $i < count($stats); $i++) {
                                //if there is a match between the racial stat in the database table and the current stat in the array, print the stat bonus
                                if ($row['rsb_stat'] == $stats[$i]) {
                                    print '<td>' . $row['rsb_stat_increase'] . '</td>';
                                } else {
                                    print '<td>-</td>';
                                }
                            }

                    print '</tr>
                    <tr>
                        <th>Subrace Bonus</th>';
                        //Get the character's subrace bonus: NOTE: THIS SHOULD GO ON NEXT PAGE DUE TO THE SUBRACE BONUS
                        //The only reason this currently works is because there is only 1 subrace per race. Demo purposes only.
                        $query = "SELECT srsb_stat, srsb_stat_increase FROM sr_stat_bonus WHERE srsb_subrace={$_POST['race']}";
                        $result = mysqli_query($dbc, $query);
                        $row = mysqli_fetch_array($result);
                        //reuse stats array
                            for ($i = 0; $i < count($stats); $i++) {
                                //if there is a match between the subrace stat in the database table and the current stat in the array, print the stat bonus
                                if ($row['srsb_stat'] == $stats[$i]) {
                                    print '<td>' . $row['srsb_stat_increase'] . '</td>';
                                } else {
                                    print '<td>-</td>';
                                }
                            }

                    print '</tr>
                </table>
            </div><br>';

            //Get the equipment options for the character according to their chosen class: place options in a radio group

            print '
            <label><h2>Equipment</h2></label>
            <p>Select your starting equipment from the options below:</p>';
            //Query the database to get equipment options
            $query = "SELECT ceo_id, ceo_num FROM class_equip_option WHERE ceo_class={$_POST['class']}";
            $result = mysqli_query($dbc, $query);
            if ($result) {
                //Create a radiogroup for each option available
                while ($option = mysqli_fetch_array($result)) {
                    //Maybe get the number of equip options for the selected class - that way, on the next page, we can deduce the number
                    //of options and therefore the fields we need to get (equip_option1, equip_option2...)
                    $option_count += 1;

                    print '
                    <fieldset class="radiogroup">';
                    //For each unique ceo_id, make a radio button and select and print the choice's contents
                    //When adding character, will need the ceo_id (to get the option) and the ce_choice (choice 1 or 2) - this will allow
                    //us to get the weapons, armor, and items associated with said choice.

                    $choice_query = "SELECT DISTINCT (ce_choice) AS ce_choice FROM class_equip WHERE ce_ceo_id={$option['ceo_id']}";
                    $choice_result = mysqli_query($dbc, $choice_query);

                    while ($choice_row = mysqli_fetch_array($choice_result)) {
                        print '<br><input type="radio" name="equip_option[' . $option['ceo_num'] . ']" value="' . $choice_row['ce_choice'] . '">';

                        //Within each radio group, get all equipment that falls under the current choice
                        $equip_query = "SELECT ce_equip_type, ce_id, ce_equip_id FROM class_equip WHERE ce_ceo_id={$option['ceo_id']} AND ce_choice ={$choice_row['ce_choice']}";
                        $equip_result = mysqli_query($dbc, $equip_query);

                        while ($equip_row = mysqli_fetch_array($equip_result)) {
                            //check which category the choice falls under to determine where to get the item name
                            $equip_type = $equip_row['ce_equip_type'];
                            switch($equip_type) {
                                case 1:
                                    $e_query = "SELECT wpn_name, wpn_id FROM weapons WHERE wpn_id={$equip_row['ce_equip_id']}";
                                    $e_result = mysqli_query($dbc, $e_query);
                                    $e_row = mysqli_fetch_array($e_result);
                                    print $e_row['wpn_name'] . ' ';
                                    break;
                                case 2:
                                    $e_query = "SELECT armor_name, armor_id FROM armor WHERE armor_id={$equip_row['ce_equip_id']}";
                                    $e_result = mysqli_query($dbc, $e_query);
                                    $e_row = mysqli_fetch_array($e_result);
                                    print $e_row['armor_name'] . ' ';
                                    break;
                                case 3:
                                    $e_query = "SELECT item_name, item_id FROM item WHERE item_id={$equip_row['ce_equip_id']}";
                                    $e_result = mysqli_query($dbc, $e_query);
                                    $e_row = mysqli_fetch_array($e_result);
                                    print $e_row['item_name'] . ' ';
                                    break;
                                case 4:
                                    $e_query = "SELECT wpn_name, wpn_id FROM weapons WHERE category='Martial'";
                                    $e_result = mysqli_query($dbc, $e_query);
                                    //Print a select statement so the user can choose a martial weapon
                                    //note that each select statement will need a unique name
                                    /*DO THE SELECTION ON THE NEXT PAGE*/
                                    print '<select class="equip_select" name="martial[]" multiple="multiple">';
                                    while ($e_row = mysqli_fetch_array($e_result)) {

                                        print '<option value="' . $e_row['wpn_id'] . '">' . $e_row['wpn_name'] . '</option>' . ' ';
                                    }
                                    print '</select>';
                                    break;
                                case 5:
                                    $e_query = "SELECT wpn_name, wpn_id FROM weapons WHERE category='Simple'";
                                    $e_result = mysqli_query($dbc, $e_query);
                                    $e_row = mysqli_fetch_array($e_result);
                                    //Print a select statement so the user can choose a simple weapon
                                    //note that each select statement will need a unique name
                                    /*DO THE SELECTION ON THE NEXT PAGE*/
                                    print '<select class="equip_select" name="simple[]" multiple="multiple">';
                                    while ($e_row = mysqli_fetch_array($e_result)) {

                                        print '<option value="' . $e_row['wpn_id'] . '">' . $e_row['wpn_name'] . '</option>' . ' ';
                                    }
                                    print '</select>';
                                    break;
                                case 6:
                                    $e_query = "SELECT pack_name, pack_id FROM packs WHERE pack_id={$equip_row['ce_equip_id']}";
                                    $e_result = mysqli_query($dbc, $e_query);
                                    $e_row = mysqli_fetch_array($e_result);
                                    print $e_row['pack_name'] . ' ';
                                    break;
                            }
                        }
                    }
                    print '</fieldset>';
                }
            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }

            $char_auto_pl = serialize($char_auto_pl);
            $char_auto_pl = htmlentities($char_auto_pl);

            print '
            <input type="hidden" name="char" value="' . $char . '">
            <input type="hidden" name="char_auto_pl" value="' . $char_auto_pl . '">
            <input type="hidden" name="opt_count" value="' . $option_count . '">
            <button type="submit" class="btn" name="submit">Next Step</button>
            </form>
            </div>
            </div>';

        mysqli_close($dbc);
        include('templates/footer.html');
    } else {
        print 'Please go back and be sure to fill out all fields to proceed.';
    }
?>
