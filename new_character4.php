<?php
    //This page takes the submitted form from page 3 and gives an overview of the character sheet that will be submitted to the database
    define('TITLE', 'New Character');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>New Character</h1>
            <h2>Submission Overview</h2>
        </div>
        <div class="overview">';

    //Should also be making sure that the user got here from new_character2. Could cause problems if they just navigate here on their own.
    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p></div>';
        include('templates/footer.html');
        exit();
    }

    //Check if the method is post. If not, the user got here some way they shouldn't have.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['char'])) {
            //get the database connection
            include('connect/mysqli_connect.php');

            $char_data = unserialize($_POST['char']);
            //Add the information submitted from new_character2 to the char_data array
            //check if a fight style is set. if so, add it to the array
            if (isset($_POST['fight_style'])) {
                $char_data['char_fs'] = $_POST['fight_style'];
            }

            //add the subclass and subrace profs to char_data['char_profs']
            if (isset($_POST['char_subclass_profs'])){
                foreach ($_POST['char_subclass_profs'] as $value) {
                    array_push($char_data['char_profs'], $value);
                }
            }

            if (isset($_POST['char_subrace_profs'])){
                foreach ($_POST['char_subrace_profs'] as $value) {
                    array_push($char_data['char_profs'], $value);
                }
            }

            //Here we will check if any spells are set. Casters aren't allowed right now though :)

            //add the values into variables for preview
            $name = mysqli_real_escape_string($dbc, strip_tags($char_data['char_name']));
            print "<h4>Name:</h4><p>$name</p>";

            //Get the race name
            $race = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_race'])));
            $query = "SELECT race_name FROM races WHERE race_id=$race";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            print '<h4>Race:</h4><p>' . $row['race_name'] . '</p>';

            //Get the subrace name if it exists
            if(isset($char_data['char_subrace'])) {
    
                $subrace = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_subrace'])));
                $query = "SELECT subrace_name FROM subraces WHERE subrace_id=$subrace";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<h4>Subrace:</h4><p>' . $row['subrace_name'] . '</p>';
            }

            //Get the class name
            $class = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_class'])));
            $query = "SELECT class_name FROM classes WHERE class_id=$class";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            print '<h4>Class:</h4><p>' . $row['class_name'] . '</p>';

            //check if a subclass was set
            if (isset($char_data['char_subclass'])) {
                $subclass = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_subclass'])));
                $query = "SELECT sc_name FROM subclasses WHERE sc_id=$subrace";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<h4>Subclass:</h4><p>' . $row['sc_name'] . '</p>';
            }

            //get the character's background
            $background = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_bkgd'])));
            $query = "SELECT bkgd_name FROM backgrounds WHERE bkgd_id=$background";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            print '<h4>Background:</h4><p>' . $row['bkgd_name'] . '</p>';

            //get the character's stats
            $strength = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_str'])));
            print "<h4>Strength:</h4><p>$strength</p>";


            $dexterity = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_dex'])));
            print "<h4>Dexterity:</h4><p>$dexterity</p>";

            $constitution = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_con'])));
            print "<h4>Constitution:</h4><p>$constitution</p>";

            $intelligence = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_int'])));
            print "<h4>Intelligence:</h4><p>$intelligence</p>";

            $wisdom = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_wis'])));
            print "<h4>Wisdom:</h4><p>$wisdom</p>";

            $charisma = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_cha'])));
            print "<h4>Charisma:</h4><p>$charisma</p>";

            //display character level
            print "<h4>Level:</h4><p>1</p>";

            //get the characters' starting gold (from background)
            $query = "SELECT bkgd_gold FROM backgrounds WHERE bkgd_id={$background}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            $gold = $row['bkgd_gold'];
            print "<h4>Gold:</h4><p>$gold</p>";

            //get the alignment
            $alignment = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_align'])));
            $query = "SELECT align_name FROM alignments WHERE align_id={$alignment}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            print '<h4>Alignment:</h4><p>' . $row['align_name'] . '</p>';

            //get the character's speed (from race)
            $query = "SELECT speed FROM races WHERE race_id={$race}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            $speed = $row['speed'];
            print "<h4>Speed:</h4><p>$speed</p>";

            //get the character's base hp (from class)
            $query = "SELECT base_health FROM classes WHERE class_id={$class} LIMIT 1";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);

            //get the character's constitution modifier
            $mod_query = "SELECT modifier FROM modifiers WHERE ability_score={$char_data['char_con']} LIMIT 1";
            $mod_result = mysqli_query($dbc, $mod_query);
            $mod_row = mysqli_fetch_array($mod_result);
            //add base hp and constitution to get the character's hp
            $hp = $row['base_health'] + $mod_row['modifier'];
            print "<h4>Base HP:</h4><p>$hp</p>";

            //get the user's fighting style if one is set
            if (isset($char_data['char_fs'])) {
                $fight_style = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_fs'])));
                $query = "SELECT fs_name FROM fighting_styles WHERE fs_id={$fight_style}";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<h4>Fighting Style:</h4><p>' . $row['fs_name'] . '</p>';
            };


            $prof_list = []; // used to hold all granted proficiencies

            //get proficiences automatically granted by class, subclass, background, race, and subrace
            foreach($char_data['char_auto_pl'] as $value) {
                array_push($prof_list, $value);
            }

            //iterate through the array of user selected class proficiencies and add them to the list
            foreach ($char_data['char_profs'] as $value) {
                $value = mysqli_real_escape_string($dbc, trim(strip_tags($value)));
                array_push($prof_list, $value);
            }

            print "
            </div>
            <label><h4>Proficiencies:</h4></label>
            <ul>";
            foreach ($prof_list as $value) {
                //Get and print the names of all granted proficiencies
                $query = "SELECT prof_name FROM proficiencies WHERE prof_id={$value};";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<li>' . $row['prof_name'] . '</li>';
            }
            print '</ul>';

            //get languages automatically granted by race, subrace, class and subclass
            $lang_list = []; // used to hold all granted languages

            //race languages
            $query = "SELECT rl_lang FROM race_langs WHERE rl_race=$race AND auto_gain=1";
            $result = mysqli_query($dbc, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    array_push($lang_list, $row['rl_lang']);
                }
            }

            //subrace languages
            if (isset($_POST['char_subrace'])){
                $query = "SELECT srl_lang FROM sr_langs WHERE srl_subrace=$subrace AND auto_gain=1";
                $result = mysqli_query($dbc, $query);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        array_push($lang_list, $row['rl_lang']);
                    }
                }
            }

            //class languages
            $query = "SELECT cll_lang FROM class_langs WHERE cll_class=$class AND auto_gain=1";
            $result = mysqli_query($dbc, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    array_push($lang_list, $row['cll_lang']);
                }
            }

            //subclass languages
            //Only get if subclass is defined
            if (isset($char_data['char_subclass'])) {
                $query = "SELECT scl_lang FROM sc_langs WHERE scl_subclass=$subclass AND auto_gain=1";
                $result = mysqli_query($dbc, $query);
                if (mysqli_num_rows($result) != 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        array_push($lang_list, $row['scl_lang']);
                    }
                }
            }

            //get selected languages
            foreach($char_data['char_langs'] as $value)
            {
                $value = mysqli_real_escape_string($dbc, trim(strip_tags($value)));
                array_push($lang_list, $value);
            }

            print "
            <label><h4>Languages:</h4></label>
            <ul>";
            foreach ($lang_list as $value) {
                //Get and print the names of all granted proficiencies
                $query = "SELECT lang_name FROM languages WHERE lang_id={$value};";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<li>' . $row['lang_name'] . '</li>';
            }
            print '</ul>';

            //prepare equip/items for storing. This will be messy: most of the code will be a repeat of the code in new_character2 until I can find a better way to optimize
            //Is this doable in new_character2??? Only issue I see is that this is dependant on the choices previously selected by the user.
            //get the equip option ids for each available option
            //initialize variables to hold weapons, armor, items, and packs for easier storing
            $char_weapons = [];
            $char_armor = [];
            $char_items = [];
            $char_packs = [];

            foreach($char_data['char_equip_opt'] as $key =>$value) {
                $query = "SELECT ceo_id FROM class_equip_option WHERE ceo_class={$char_data['char_class']} AND ceo_num={$key}";
                $result = mysqli_query($dbc, $query);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        $equip_query = "SELECT ce_choice, ce_equip_type, ce_equip_id FROM class_equip WHERE ce_ceo_id={$row['ceo_id']} AND ce_choice={$value}";
                        $equip_result = mysqli_query($dbc, $equip_query);

                        while ($equip_row = mysqli_fetch_array($equip_result)) {
                        //check which category the choice falls under to determine where to get the item name
                        $equip_type = $equip_row['ce_equip_type'];
                        switch($equip_type) {
                            case 1:
                                $e_query = "SELECT wpn_id FROM weapons WHERE wpn_id={$equip_row['ce_equip_id']}";
                                $e_result = mysqli_query($dbc, $e_query);
                                $e_row = mysqli_fetch_array($e_result);
                                array_push($char_weapons, $e_row['wpn_id']);
                                break;
                            case 2:
                                $e_query = "SELECT armor_id FROM armor WHERE armor_id={$equip_row['ce_equip_id']}";
                                $e_result = mysqli_query($dbc, $e_query);
                                $e_row = mysqli_fetch_array($e_result);
                                array_push($char_armor, $e_row['armor_id']);
                                break;
                            case 3:
                                $e_query = "SELECT item_id FROM item WHERE item_id={$equip_row['ce_equip_id']}";
                                $e_result = mysqli_query($dbc, $e_query);
                                $e_row = mysqli_fetch_array($e_result);
                                array_push($char_items, $e_row['item_id']);
                                break;
                            case 4:
                                //can get simple and martial weapons from the arrays created for them in new_character2
                                break;
                            case 5:
                                break;
                            case 6:
                                $e_query = "SELECT pack_id FROM packs WHERE pack_id={$equip_row['ce_equip_id']}";
                                $e_result = mysqli_query($dbc, $e_query);
                                $e_row = mysqli_fetch_array($e_result);
                                array_push($char_packs, $e_row['pack_id']);
                                break;
                            }
                        }
                    }
                }
            }

            //add all chosen martial weapons to the weapons array
            if (isset($char_data['martial_weaps'])) {
                foreach ($char_data['martial_weaps'] as $value) {
                    array_push($char_weapons, $value);
                }
            }

            //add all chosen simple weapons to the weapons array
            if (isset($char_data['simple_weaps'])) {
                foreach ($char_data['simple_weaps'] as $value) {
                    array_push($char_weapons, $value);
                }
            }

            print '
            <label><h4>Weapons:</h4></label>
            <ul>';
            //get and print the names of all selected weapons
            foreach ($char_weapons as $value) {
                $query = "SELECT wpn_name FROM weapons WHERE wpn_id={$value};";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<li>' . $row['wpn_name'] . '</li>';
            }
            print '</ul>';

            //get and print the names of all selected armor
            print '
            <label><h4>Armor:</h4></label>
            <ul>';
            foreach ($char_armor as $value) {
                $query = "SELECT armor_name FROM armor WHERE armor_id={$value};";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<li>' . $row['armor_name'] . '</li>';
            }
            print '</ul>';

            //get and print the names of all selected items
            print '
            <label><h4>Items:</h4></label>
            <ul>';
            foreach ($char_items as $value) {
                $query = "SELECT item_name FROM item WHERE item_id={$value};";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<li>' . $row['item_name'] . '</li>';
            }
            print '</ul>';

            //get and print the names of all selected packs
            print '
            <label><h4>Packs:</h4></label>
            <ul>';
            foreach ($char_packs as $value) {
                $query = "SELECT pack_name FROM packs WHERE pack_id={$value};";
                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                print '<li>' . $row['pack_name'] . '</li>';
            }
            print '</ul>';

            /*NOTE: equipment/items are also granted by background. Ugh.*/

            //Missing tools at this point

            //prepare spells for storing (will be done at a later time)

            mysqli_close($dbc);

            //serialize character data
            $char_data = serialize($char_data);
            $char = htmlentities($char_data);

            //create submit form/button so this can be processed on the next page
            print '
            <form action="new_character_complete.php" method="post">
                <input type="hidden" name="char" value="' . $char . '">
                <button type="submit" class="btn" name="submit">Create Character</button>
            </form>
            </div>';
        } else {
            //Failed to roll stats or arrive here from the correct page
            print '
            <h2>Oops!</h2>
            <p>Please make sure you roll your stats on the previous page and try again.</p>
            </div>
            </div>';
        }

    } else {

        //you shouldn't be here, fool.
        print '
        <h2>Oops!</h2>
        <p>This page was accessed in error.</p>
        </div>';
    }

    include('templates/footer.html');
?>
