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

            //get proficiency bonus for character's level
            $pb_query = "SELECT bonus FROM prof_bonus WHERE lvl={$char_row['lvl']}";
            $pb_result = mysqli_query($dbc, $pb_query);
            $pb_row = mysqli_fetch_array($pb_result);
            $pb = $pb_row['bonus'];

            //Display the character's stats and proficiencies together in one div/section
            //have some sort of indicator for if the character has proficiency

            //for skills, need to get stat bonus, then add proficiency
            //get all stats in a list
            $stats = [
                'strength' => $char_row['strength'],
                'dexterity' => $char_row['dexterity'],
                'constitution' => $char_row['constitution'],
                'intelligence' => $char_row['intelligence'],
                'wisdom' => $char_row['wisdom'],
                'charisma' => $char_row['charisma']
            ];

            //Add race bonuses to stats
            $query = "SELECT rsb_stat, rsb_stat_increase FROM race_stat_bonus WHERE rsb_race={$char_row['char_race']}";
            $result = mysqli_query($dbc, $query);
            $row = mysqli_fetch_array($result);

            foreach ($stats as $key => $value) {
                if ($row['rsb_stat'] == $key) {
                    $stats[$key] = $value + $row['rsb_stat_increase'];
                }
            }

            //Add subrace bonuses to stats
            $query = "SELECT srsb_stat, srsb_stat_increase FROM sr_stat_bonus WHERE srsb_subrace={$char_row['char_subrace']}";
            $result = mysqli_query($dbc, $query);

            if($result) {
                
                $row = mysqli_fetch_array($result);

                foreach ($stats as $key => $value) {
                    if ($row['srsb_stat'] == $key) {
                        $stats[$key] = $value + $row['srsb_stat_increase'];
                    }
                }
            }

            //get all the modifiers for each stat
            $stat_mods = [];

            foreach ($stats as $value) {
                $mod_query = "SELECT modifier FROM modifiers WHERE ability_score=$value";
                $mod_result = mysqli_query($dbc, $mod_query);
                $mod_row = mysqli_fetch_array($mod_result);
                array_push($stat_mods, $mod_row['modifier']);
            }

            //initialize variable to hold AC
            $ac = 0;

            //get the character's AC from the armor they have equipped
            $armor_query = "SELECT ca_armor, armor_class, armor_mod, max_mod FROM char_armor
            INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$character} AND equipped=1";
            $armor_result = mysqli_query($dbc, $armor_query);

            while ($armor_row = mysqli_fetch_array($armor_result)) {
                $ac += $armor_row['armor_class'];
                if ($armor_row['armor_mod'] && $armor_row['max_mod']) { //check if the armor accepts a bonus modifier
                    if ($stat_mods[1] < $armor_row['max_mod']) { //check if the character's dex mod meets or exceeds the max_mod: if so, apply it to AC
                        $ac += $stat_mods[1];
                    } else if ($stat_mods[1] >= $armor_row['max_mod']) {
                        $ac += $armor_row['max_mod'];
                    }
                } else if ($armor_row['armor_mod']) {
                    $ac += $stat_mods[1];
                }
            }

            //make an array of all proficiencies that rely on the main stats and set their values to the appropriate modifier
            $profs = [
                'Strength' => $stat_mods[0],
                'Athletics' => $stat_mods[0],
                'Dexterity' => $stat_mods[1],
                'Acrobatics' => $stat_mods[1],
                'Sleight of hand' => $stat_mods[1],
                'Stealth' => $stat_mods[1],
                'Constitution' => $stat_mods[2],
                'Intelligence' => $stat_mods[3],
                'Arcana' => $stat_mods[3],
                'History' => $stat_mods[3],
                'Investigation' => $stat_mods[3],
                'Nature' => $stat_mods[3],
                'Religion' => $stat_mods[3],
                'Wisdom' => $stat_mods[4],
                'Animal handling' => $stat_mods[4],
                'Insight' => $stat_mods[4],
                'Medicine' => $stat_mods[4],
                'Perception' => $stat_mods[4],
                'Survival' => $stat_mods[4],
                'Charisma' => $stat_mods[5],
                'Deception' => $stat_mods[5],
                'Intimidation' => $stat_mods[5],
                'Performance' => $stat_mods[5],
                'Persuasion' => $stat_mods[5]
            ];

            //get the character's proficiencies
            $query = "SELECT cp_prof, prof_name FROM char_proficiencies
            INNER JOIN proficiencies ON char_proficiencies.cp_prof = proficiencies.prof_id WHERE cp_char={$character} AND prof_stat != 'N/A'";
            $result = mysqli_query($dbc, $query);

            //if the character has proficiency in one of the main stat profs, add proficiency to the value
            while ( $row = mysqli_fetch_array($result)) {
                foreach ($profs as $key => $value) {
                    if ($row['prof_name'] == $key) {
                        $profs[$key] = $value + $pb;
                    }
                }
            }

            //as the page will be divided into 3 columns, use bootstrap classes to set up said columns

            //***************HTML content starts here********************//
            print '
            <nav class="subnav">
                <ul role="navigation">
                    <li role="presentation" class="active"><a href="view_character.php?character=' .$character. '">Main</a></li>
                    <li role="presentation"><a href="view_stats.php?character=' .$character. '">Stats</a></li>
                    <li role="presentation"><a href="view_skills.php?character=' .$character. '">Skills</a></li>
                    <li role="presentation"><a href="view_spellbook.php?character=' .$character. '">Spellbook</a></li>
                    <li role="presentation"><a href="view_inventory.php?character=' .$character. '">Inventory</a></li>
                    <li role="presentation"><a href="view_traits.php?character=' .$character. '">Traits</a></li>
                </ul>
            </nav>
            <div id="text_area">
            <div class="row">';
            //Display the character's hp, ac, initiative, and speed

            //check if the user updated the character's current hp through the popup form. If not, set current hp to the character's max.
            //if so, take the previous hp and update it according to which option the user selected
            if (!isset($_POST['previous_hp'])) {
                $current_health = $char_row['hp'];
            } else {
                $current_health = $_POST['previous_hp'];
            }

            if (!isset($_POST['previous_temp_hp'])) {
                $temp_hp = 0;
            } else {
                $temp_hp = $_POST['previous_temp_hp'];
            }

            //check if $_POST['hp_change'] is set: if so, assign its value to $hp_change
            //if not, there is no change.
            if (isset($_POST['hp_change'])) {
                $hp_change = $_POST['hp_change'];
            } else {
                $hp_change = 0;
            }

            //Check to see if the character took damage, healed, or gained temporary hp and adjust the values accordingly
            if (isset($_POST['damage_hp'])) {
                //if the character has temp hp and the damage taken meets or exceeds it, remove temp hp
                if (isset($_POST['previous_temp_hp']) && ($_POST['damage_hp'] >= $_POST['previous_temp_hp'])) {
                    $temp_hp = 0;
                }

                $current_health -= $hp_change;

            } else if (isset($_POST['heal_hp'])) {

                //check if the character has temp hp: if so, check that the healing does not exceed temp hp + max hp
                if (isset($_POST['previous_temp_hp']) && ($current_health + $hp_change) <= ($_POST['previous_temp_hp'] + $char_row['hp'])) {
                    $current_health += $hp_change;
                } else if (isset($_POST['previous_temp_hp']) && ($current_health + $hp_change) > ($_POST['previous_temp_hp'] + $char_row['hp'])){
                    //if it does, set the character's health to temp_hp + max hp
                    $current_health = $_POST['previous_temp_hp'] + $char_row['hp'];
                } else if (($current_health + $hp_change) <= $char_row['hp'] ) {
                    //if there is no temp hp, make sure the amount healed does not exceed max hp
                    $current_health += $hp_change;
                } else {
                    //if the healing amount does exceed the hp max, put the character back to full health
                    $current_health = $char_row['hp'];
                }

            } else if (isset($_POST['temp_hp'])) {
                $temp_hp = $hp_change;
                $current_health += $temp_hp;
            }

            print '
            <div class="char_display">
                <h4>Character Name:</h4>
                <p>' .$char_row['name']. '</p><br>
                <h4>Level:</h4>
                <p>' .$char_row['lvl']. '</p>
                <a href="level_character.php?character=' .$character. '" id="lvl-btn"><button>Level Up</button></a>
            </div>
            <hr>
            <div class="display_margin">
                <i class="ra ra-hearts ra-5x icon_margin"></i>
                <table class="battle_table">
                    <tr class="stat">
                        <th>Max HP</th>
                        <th>Current HP</th>
                        <th>Temp HP</th>
                    </tr>
                    <tr>
                        <td>' .$char_row['hp']. '</td>
                        <td>' .$current_health. '</td>
                        <td>' .$temp_hp. '</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button id="hp-btn" class="my_popup_open btn_small">Current HP</button></td>
                        <td></td>
                    </tr>
                </table>
            </div>
            
            <!-- Add content to the popup -->
            <div id="my_popup" class="hp_tracker">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close my_popup_close hp_tracker_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form class="popup_form" action="view_character.php" method="post">
                    <p>
                        <label for="hp_change">Update Health:</label><br><input type="number" id="hp_change" name="hp_change" class="form-control">
                    </p>
                    <p>
                        <input type="hidden" name="previous_hp" value="' .$current_health. '">
                        <input type="hidden" name="previous_temp_hp" value="' .$temp_hp. '">
                        <input type="hidden" name="character" value="' .$character. '">
                        <button type="submit" class="hp_form_btn" name="damage_hp">Take Damage</button>
                        <button type="submit" class="hp_form_btn" name="heal_hp">Heal</button>
                        <button type="submit" class="hp_form_btn" name="temp_hp">Add temp HP</button>
                    </p>
                </form>
            </div>';

             print '
            <div class="display_margin">
                <i class="ra ra-heavy-shield ra-5x icon_margin"></i>
                <table class="battle_table">
                    <tr class="stat">
                        <th>Armor Class</th>
                        <th>Initiative</th>
                        <th>Speed</th>
                    </tr>
                    <tr>
                        <td>' .$ac. '</td>
                        <td>' .$stat_mods[1]. '</td>
                        <td>' .$char_row['speed']. '</td>
                    </tr>
                </table>
            </div>';

            //Display the character's hit die and death saves in a collective div
            //and clicks the add button, it will display. Can be updated by re-entering a number in the field

            //Death saves should be a checklist just so it's possible to keep track of successes/failures
            print '
            <div class="display_margin">
                <i class="ra ra-bleeding-hearts ra-5x icon_margin"></i>
                <table class="battle_table">
                    <tr>
                        <th class="stat">Hit Dice</th>
                        <th class="stat">Death saves</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>';

            //Display the character's weapons
            print '
            <div class="display_margin">
                <i class="ra ra-sword ra-5x icon_margin"></i>
                <table class="battle_table">
                    <tr class="stat">
                        <th>Weapon</th>
                        <th>Attack Bonus</th>
                        <th>Damage Type</th>
                        <th>Dice</th>
                        <th>Damage Bonus</th>
                    </tr>';
            //create a variable to hold weapon attack bonuses
            $atk_bonus = 0;
            $dmg_bonus = 0;

            //get all the character's weapons and modifiers from the database
            $wpn_query = "SELECT cw_weapon, wpn_name, wpn_die, wpn_multiplier, dmg_type, category, properties, ranged, wpn_prof, finesse FROM char_weapons
            INNER JOIN weapons ON char_weapons.cw_weapon = weapons.wpn_id WHERE cw_char={$character} AND equipped=1";
            $wpn_result = mysqli_query($dbc, $wpn_query);

            //Check for weapon proficiencies: if the character has the weapon equipped and has proficiency in it, add the prof bonus
            $query = "SELECT cp_prof, prof_id, prof_name FROM char_proficiencies
            INNER JOIN proficiencies ON char_proficiencies.cp_prof = proficiencies.prof_id WHERE cp_char={$character} AND prof_stat='N/A'";
            

            //iterate through the list of weapons the character has equipped
            while ($wpn_row = mysqli_fetch_array($wpn_result)) {
                 print '
                 <tr>
                    <td>' .$wpn_row['wpn_name']. '</td>';
                    //check which stat the weapon should use as its modifier
                    if ($wpn_row['finesse']) {
                        if ($stats['dexterity'] >= $stats['strength']) {
                            $atk_bonus = $stat_mods[1];
                            $dmg_bonus = $stat_mods[1];
                        } else {
                            $atk_bonus = $stat_mods[0];
                            $dmg_bonus = $stat_mods[0];
                        }
                    } else if ($wpn_row['ranged']) {
                        $atk_bonus = $stat_mods[1];
                        $dmg_bonus = $stat_mods[1];
                    } else {
                        $atk_bonus = $stat_mods[0];
                        $dmg_bonus = $stat_mods[0];
                    }

                $result = mysqli_query($dbc, $query);
                while ($row = mysqli_fetch_array($result)) {
                    //for attack bonus, need to check for proficiency and add either strength or dex (whichever is higher) if the weapon is finesse
                    //if the weapon type or name matches one of the character's proficiencies, add the prof bonus
                    if ($wpn_row['wpn_prof'] == $row['prof_id']) {
                        $atk_bonus += $pb;
                    } else if ($row['prof_id'] == 31 && $wpn_row['category'] == 'Martial') {
                        $atk_bonus += $pb;
                    } else if ($row['prof_id'] == 29 && $wpn_row['category'] == 'Simple') {
                        $atk_bonus += $pb;
                    }
                }
                
                print '
                    <td>' .$atk_bonus. '</td>
                    <td>' .$wpn_row['dmg_type']. '</td>
                    <td>' .$wpn_row['wpn_multiplier']. ' d' .$wpn_row['wpn_die']. '</td>
                    <td>' .$dmg_bonus. '</td>
                    </tr>';
                
                $atk_bonus = 0;
                $dmg_bonus = 0;
            }
            //display the character's armor
            print '</table>
            </div>
            <div class="display_margin">
                <i class="ra ra-helmet ra-5x"></i>
                <table class="battle_table">
                    <tr class="stat">
                        <th>Armor</th>
                        <th>AC</th>
                        <th>Modifier</th>
                        <th>Max Bonus</th>
                        <th>Stealth Disdvantage</th>
                    </tr>';
            //Get the character's equipped armor
            $armor_query = "SELECT ca_armor, armor_name, armor_class, armor_mod, max_mod, stealth_disadvantage FROM char_armor
            INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$character} AND equipped=1";
            $armor_result = mysqli_query($dbc, $armor_query);

            while ($armor_row = mysqli_fetch_array($armor_result)) {
                 print '
                 <tr>
                    <td>' .$armor_row['armor_name']. '</td>
                    <td>' .$armor_row['armor_class']. '</td>
                    <td>' .$armor_row['armor_mod']. '</td>
                    <td>' .$armor_row['max_mod']. '</td>
                    <td>' .$armor_row['stealth_disadvantage']. '</td>
                </tr>';
            }

            print '
            </table>
            </div>'; //end of center display
            print '<div class="column_small">';

            //Display the character's fighting style or other passive skills

            //check if the character has the fighter class
            //Need to make skills query

            if ($char_row['char_class'] == 1) {
                $query = "SELECT cfs_fight_style, fs_name, fs_descr FROM char_fight_style
                INNER JOIN fighting_styles ON char_fight_style.cfs_fight_style = fighting_styles.fs_id WHERE cfs_char={$character}";
                $result = mysqli_query($dbc, $query);

                if($result && mysqli_num_rows($result) > 0) {
                    print '<table class="skills_table">';

                    while ($row = mysqli_fetch_array($result)) {
                        print '
                         <tr>
                            <th>Fighting Style</th>
                        </tr>
                        <tr>
                            <td>' .$row['fs_name']. '</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                        </tr>
                        <tr>
                            <td>' .$row['fs_descr']. '</td>
                        </tr>';
                    }

                    print '</table>';
                }
            }

            //Check if the character has passive skills
            //make the skills query
            $query = "SELECT cskill_skill, skill_name, skill_descr, skill_lvl FROM char_skills
            INNER JOIN skills ON char_skills.cskill_skill = skills.skill_id WHERE cskill_char={$character} AND skill_type='p'";
            $result = mysqli_query($dbc, $query);

            print '<table class="skills_table">';
            while ($row = mysqli_fetch_array($result)) {
                 print '
                 <tr>
                    <th class="stat">Skill Name</th>
                    <td>' .$row['skill_name']. '</td>
                </tr>
                <tr>
                    <th class="stat">Level</th>
                    <td>' .$row['skill_lvl']. '</td>
                </tr>
                <tr>
                    <th class="stat">Description</th>
                    <td>' .$row['skill_descr']. '</td>';
            }

            print '
            </table>
            </div>'; //end of skills display
            //end of row and content
            print '
                </div>
            </div>';

            mysqli_close($dbc);

            include('templates/footer.html');
        } else {
            print '<h2>Oops!</h2>
            <p>Please select one of your characters from <a href="character_select.php">here</a> to view this page.</p>';
        }
?>
