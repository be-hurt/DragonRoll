<?php
    //This page takes the submitted form from page 3 and gives an overview of the character sheet that will be submitted to the database
    define('TITLE', 'New Character');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>New Character</h1>
            <h2>Submit Character</h2>
        </div>
        <div class="page_content">';

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

            //Here we will check if any spells are set. Casters aren't allowed right now though :)

            //prepare the values for storing
            $name = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_name'])));
            $race = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_race'])));
            $class = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_class'])));
            $subrace = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_subrace'])));
            //check if a subclass was set
            if (isset($char_data['char_subclass'])) {
                $subclass = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_subclass'])));
            }
            $background = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_bkgd'])));
            $strength = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_str'])));
            $dexterity = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_dex'])));
            $constitution = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_con'])));
            $intelligence = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_int'])));
            $wisdom = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_wis'])));
            $charisma = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_cha'])));
            $level = 1;

            //get the characters' starting gold (from background)
            $query = "SELECT bkgd_gold FROM backgrounds WHERE bkgd_id={$background}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            $gold = $row['bkgd_gold'];

            $alignment = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_align'])));

            //get the character's speed (from race)
            $query = "SELECT speed FROM races WHERE race_id={$race}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            $speed = $row['speed'];

            //get the character's base hp (from class)
            $query = "SELECT base_health FROM classes WHERE class_id={$class} LIMIT 1";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);
            //get the character's constitution modifier
            $mod_query = "SELECT modifier FROM modifiers WHERE ability_score={$char_data['char_con']} LIMIT 1";
            $mod_result = mysqli_query($dbc, $mod_query);
            $mod_row = mysqli_fetch_array($mod_result);
            //add base hp and constitution to get the character's hp
            $hp = $row['base_health'] + $mod_row['modifier']; //****HAVE TO USE ROW TO GET THESE VALUES

            //get user id from session
            $user = $_SESSION['user_id'];

            //get the user's fighting style if one is set
            if (isset($char_data['char_fs'])) {
                $fight_style = mysqli_real_escape_string($dbc, trim(strip_tags($char_data['char_fs'])));
            };


            $prof_list = []; // used to hold all granted proficiencies

            //prepare user-selected proficiencies for storing: will need to iterate through each array and strip any malicious stuff
            //all chosen profs
            foreach($char_data['char_profs'] as $value)
            {
                $value = mysqli_real_escape_string($dbc, trim(strip_tags($value)));
                array_push($prof_list, $value);
            }

            //get proficiences automatically granted by class, subclass, background, race, and subrace
            foreach ($char_data['char_auto_pl'] as $value) {
                $query = "SELECT prof_id FROM proficiencies WHERE prof_id=$value";
                $result = mysqli_query($dbc, $query);
                while ($row = mysqli_fetch_array($result)) {
                    array_push($prof_list, $row['prof_id']);
                }
            }

            //get languages automatically granted by race, subrace, class and subclass
            $lang_list = []; // used to hold all granted languages

            //prepare selected languages for storing: will need to iterate through the array and do an insert statement for individul choices
            foreach($char_data['char_langs'] as $value)
            {
                $value = mysqli_real_escape_string($dbc, trim(strip_tags($value)));
                array_push($lang_list, $value);
            }

            //race languages
            $query = "SELECT rl_lang FROM race_langs WHERE rl_race=$race AND auto_gain=1";
            $result = mysqli_query($dbc, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    array_push($lang_list, $row['rl_lang']);
                }
            }

            //subrace languages
            $query = "SELECT srl_lang FROM sr_langs WHERE srl_subrace=$subrace AND auto_gain=1";
            $result = mysqli_query($dbc, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    array_push($lang_list, $row['rl_lang']);
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

            //add all items contained in the chosen pack to the items array
            foreach ($char_packs as $pack) {
                //make a query to get all items
                $query = "SELECT pi_item, quantity FROM pack_items WHERE pi_pack=$pack";
                $result = mysqli_query($dbc, $query);

                while ($row = mysqli_fetch_array($result)) {
                    //add the item to the array a number of times equal to its quantity
                    for ($i=0; $i < $row['quantity']; $i++){
                        array_push($char_items, $row['pi_item']);
                    }
                }
            }

            //*******NOTE: Will also need to add items granted by the chosen background to the items array*****//

            //prepare skills for storing
            $skill_list = []; //initialize array to hold skill ids
            $query = "SELECT skill_id FROM skills WHERE skill_class=$class AND skill_lvl=$level";
            $result = mysqli_query($dbc, $query);

            while ($row = mysqli_fetch_array($result)) {
                array_push($skill_list, $row['skill_id']);
            }

            //prepare available skills from subclass for storing
            if (isset($subclass)){
                $query = "SELECT skill_id FROM skills WHERE skill_class=$class AND skill_sc=$subclass AND skill_lvl=$level";
                $result = mysqli_query($dbc, $query);

                while ($row = mysqli_fetch_array($result)) {
                    array_push($skill_list, $row['skill_id']);
                }
            }

            //prepare spells for storing (will be done at a later time)

            //insert all information into the database
            //due to dependencies, have another query for when subclass is not null
            if (!empty($subclass)){
                $query = "INSERT INTO `characters`(`name`, `char_race`, `char_class`, `char_subrace`, `char_subclass`, `char_bkgd`, `strength`, `dexterity`, `constitution`, `intelligence`,
                `wisdom`, `charisma`, `lvl`, `gold`, `alignment`, `speed`, `hp`, `exp`, `char_user`) VALUES ('$name', $race, $class, $subrace, $subclass, $background, $strength,
                $dexterity, $constitution, $intelligence, $wisdom, $charisma, $level, $gold, $alignment, $speed, $hp, 0, $user)";
                $result = mysqli_query($dbc, $query);

            } else {
                $query = "INSERT INTO `characters`(`name`, `char_race`, `char_class`, `char_subrace`, `char_subclass`, `char_bkgd`, `strength`, `dexterity`, `constitution`, `intelligence`,
                `wisdom`, `charisma`, `lvl`, `gold`, `alignment`, `speed`, `hp`, `exp`, `char_user`) VALUES ('$name', $race, $class, $subrace, null, $background, $strength,
                $dexterity, $constitution, $intelligence, $wisdom, $charisma, $level, $gold, $alignment, $speed, $hp, 0, $user)";
                $result = mysqli_query($dbc, $query);
            }

            if ($result) {
                //print a success message
                print '<p class="success">Your character was successfully created!</p>
                <br><div class="center"><a class="btn btn-default" href="character_select.php" role="button">Character Select</a></div>';

                //new problem here...
                //get the newly created char_id and use it to insert selected proficiencies into char_proficiencies
                $char_query = "SELECT char_id FROM characters WHERE name='$name'";
                mysqli_query($dbc, $char_query);
                $char_result = mysqli_query($dbc, $char_query);
                $char_row = mysqli_fetch_array($char_result);

                //get prof_list and for each item in it, insert it into the char_profs table
                foreach ($prof_list as $value) {
                    $query = "INSERT INTO `char_proficiencies`(`cp_char`, `cp_prof`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //use char_result to insert languages
                foreach ($lang_list as $value) {
                    $query = "INSERT INTO `char_langs`(`cl_char`, `cl_lang`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //use char result to insert weapons
                foreach ($char_weapons as $value) {
                    $query = "INSERT INTO `char_weapons`(`cw_char`, `cw_weapon`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //use char result to insert armor
                foreach ($char_armor as $value) {
                    $query = "INSERT INTO `char_armor`(`ca_char`, `ca_armor`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                   //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //user char result to insert items
                foreach ($char_items as $value) {
                    $query = "INSERT INTO `char_items`(`ci_char`, `ci_item`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //use char result to insert packs
                foreach ($char_packs as $value) {
                    $query = "INSERT INTO `char_packs`(`cpack_char`, `cpack_pack`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //use char result to insert skills
                foreach ($skill_list as $value) {
                    $query = "INSERT INTO `char_skills`(`cskill_char`, `cskill_skill`) VALUES ({$char_row['char_id']}, $value)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

                //use char result to insert fighting style
                if (isset($fight_style)) {
                    $query = "INSERT INTO `char_fight_style`(`cfs_char`, `cfs_fight_style`) VALUES ({$char_row['char_id']}, $fight_style)";
                    mysqli_query($dbc, $query);
                }
                if (mysqli_affected_rows($dbc) == 1) {
                    //success! Do nothing.
                } else {
                    print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                }

            } else {
                //failure
                print '<p class="error">Error.<br>Could not create your character because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
            }
        }
        print '</div>
        </div>';
    } else {
            //Failed to roll stats or arrive here from the correct page
            print '
            <h2>Oops!</h2>
            <p>Please make sure you roll your stats on the previous page and try again.</p>
            </div>
            </div>';
    }
     mysqli_close($dbc);
    include('templates/footer.html');
?>
