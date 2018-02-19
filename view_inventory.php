<?php
    define('TITLE', 'My Character');
    include('templates/header.html');

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">register</a></p>';
        include('templates/footer.html');
        exit();
    }

    include('connect/mysqli_connect.php');

    $char_result = 0;

    if (isset($_POST['character'])) {
        $char_result = $_POST['character'];
    } else if (isset($_GET['character'])) {
        $char_result = $_GET['character'];
    }

    //make sure the character being viewed belongs to the logged in user
    if ($char_result) {

        //get the character's stats so we can calculate the characters' weapon bonuses
        include('connect/mysqli_connect.php');
        $stats_query = "SELECT * FROM characters WHERE char_id={$char_result} AND char_user={$_SESSION['user_id']}";
        $stats_result = mysqli_query($dbc, $stats_query);
        $stats_row = mysqli_fetch_array($stats_result);

        //make a query to get all the characters' items, weapons, armor and packs
        //begin with items
        $item_query = "SELECT ci_item, item_name FROM char_items INNER JOIN item ON
        char_items.ci_item = item.item_id WHERE ci_char={$char_result}";
        $item_result = mysqli_query($dbc, $item_query);

        //next, get packs
        //NOTE packs contain a list of items themselves. Maybe make the pack names clickable in the page and then have a popup with the list
        //of items included in the pack
        $pack_query = "SELECT cpack_pack, pack_name FROM char_packs INNER JOIN packs ON
        char_packs.cpack_pack = packs.pack_id WHERE cpack_char={$char_result}";
        $pack_result = mysqli_query($dbc, $pack_query);

        //as the page will be divided into 3 columns, use bootstrap classes to set up said columns

        //***************HTML content starts here********************//
        //display the character nav
        print '
        <nav class="subnav">
            <ul role="navigation">
                <li role="presentation"><a href="view_character.php?character=' .$char_result. '">Main</a></li>
                <li role="presentation"><a href="view_stats.php?character=' .$char_result. '">Stats</a></li>
                <li role="presentation"><a href="view_skills.php?character=' .$char_result. '">Skills</a></li>
                <li role="presentation"><a href="view_spellbook.php?character=' .$char_result. '">Spellbook</a></li>
                <li role="presentation" class="active"><a href="view_inventory.php?character=' .$char_result. '">Inventory</a></li>
                <li role="presentation"><a href="view_traits.php?character=' .$char_result. '">Traits</a></li>
            </ul>
        </nav>
        <div id="text_area">';
        print '
        <div>';
         //check if method is post
        //if this is true, the user has submitted one of the fields to the database
        //Note: this needs to go here (in the middle column of the page) so that it displays under the subnav
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //check if the user added a weapon
            if (isset($_POST['wpn_get'])) {
                //if so, add the weapon to the character's available weapons
                $query = "INSERT INTO `char_weapons`(`cw_char`, `cw_weapon`, `equipped`) VALUES ({$char_result}, {$_POST['wpn_select']}, 0)";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">The weapon was successfully added to your inventory.</p>';
                } else {
                    print '<p class="message">There was an error in adding your weapon to the database. Please try again.</p>';
                }
            } else if (isset($_POST['wpn_equip'])) { //check if the user equipped a weapon
                 //if so, update the weapon in the database
                $query = "UPDATE char_weapons SET equipped=1 WHERE cw_char={$char_result} AND cw_id={$_POST['equip_weapon']}";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully equipped the selected weapon.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['wpn_unequip'])) { //check if the user unequipped a weapon
                //if so, update the weapon in the database
                $query = "UPDATE char_weapons SET equipped=0 WHERE cw_char={$char_result} AND cw_id={$_POST['unequip_weapon']}";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully unequipped your weapon.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['get_armor'])) { //check if the user added new armor to their inventory
                //if so, update the database
                $query = "INSERT INTO char_armor (ca_char, ca_armor, equipped) VALUES ({$char_result}, {$_POST['add_armor']}, 0)";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">The armor was successfully added to your inventory.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            }else if (isset($_POST['armor_equip'])) { //check if the user equipped armor
                //if so, update the database
                $query = "UPDATE char_armor SET equipped=1 WHERE ca_char={$char_result} AND ca_id={$_POST['equip_armor']}";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully equipped the selected armor.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['armor_unequip'])) { //check if the user unequipped armor
                //if so, update the database
                $query = "UPDATE char_armor SET equipped=0 WHERE ca_char={$char_result} AND ca_id={$_POST['unequip_armor']}";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully unequipped the selected armor.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['item_get'])) { //check if the user added an item
                //if so, update the database
                $query = "INSERT INTO char_items (ci_char, ci_item) VALUES ({$char_result}, {$_POST['add_item_select']})";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully added the item to your inventory.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['item_remove'])) { //check if the user removed an item
                //if so, update the database
                $query = "DELETE FROM `char_items` WHERE ci_id={$_POST['remove_item_select']} AND ci_char={$char_result} LIMIT 1";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully removed the item from your inventory.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['armor_remove'])) { //check if the user removed armor
                //if so, update the database
                $query = "DELETE FROM `char_armor` WHERE ca_id={$_POST['remove_armor_select']} AND ca_char={$char_result} LIMIT 1";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully removed the armor from your inventory.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            } else if (isset($_POST['wpn_remove'])) { //check if the user removed a weapon
                //if so, update the database
                $query = "DELETE FROM char_weapons WHERE cw_id={$_POST['remove_wpn_select']} AND cw_char={$char_result} LIMIT 1";
                $result = mysqli_query($dbc, $query);

                //check to make sure the query ran:
                if ($result) {
                    print '<p class="message">You successfully removed the weapon from your inventory.</p>';
                } else {
                    print '<p class="message">There was an error updating the database. Please try again.</p>';
                }
            }
        }

        //utilize the same script as on view_character so we can see what bonuses the character will get to the weapon if it's used
        //for skills, need to get stat bonus, then add proficiency
        //get all stats in a list
        $stats = [
            'strength' => $stats_row['strength'],
            'dexterity' => $stats_row['dexterity'],
            'constitution' => $stats_row['constitution'],
            'intelligence' => $stats_row['intelligence'],
            'wisdom' => $stats_row['wisdom'],
            'charisma' => $stats_row['charisma']
        ];

        //Add race bonuses to stats
        $query = "SELECT rsb_stat, rsb_stat_increase FROM race_stat_bonus WHERE rsb_race={$stats_row['char_race']}";
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);

        foreach ($stats as $key => $value) {
            if ($row['rsb_stat'] == $key) {
                $stats[$key] = $value + $row['rsb_stat_increase'];
            }
        }

        //Add subrace bonuses to stats
        $query = "SELECT srsb_stat, srsb_stat_increase FROM sr_stat_bonus WHERE srsb_subrace={$stats_row['char_subrace']}";
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);

        foreach ($stats as $key => $value) {
            if ($row['srsb_stat'] == $key) {
                $stats[$key] = $value + $row['srsb_stat_increase'];
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

        //create a variable to hold weapon attack bonuses
        $atk_bonus = 0;
        $dmg_bonus = 0;

        //get all the character's weapons and modifiers from the database
        $wpn_query = "SELECT cw_weapon, equipped, wpn_name, wpn_die, wpn_multiplier, dmg_type, category, properties, ranged, wpn_prof, finesse FROM char_weapons
        INNER JOIN weapons ON char_weapons.cw_weapon = weapons.wpn_id WHERE cw_char={$char_result}";
        $wpn_result = mysqli_query($dbc, $wpn_query);

        //Check for weapon proficiencies: if the character has the weapon equipped and has proficiency in it, add the prof bonus
        $query = "SELECT cp_prof, prof_id, prof_name FROM char_proficiencies
        INNER JOIN proficiencies ON char_proficiencies.cp_prof = proficiencies.prof_id WHERE cp_char={$char_result} AND prof_stat='N/A'";
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);

        //Display the character's weapons in a table
        print '
        <table class="item_table">
            <tr class="stat">
                <th>Weapon</th>
                <th>Atk Bonus</th>
                <th>Dmg Type</th>
                <th>Dice</th>
                <th>Dmg Bonus</th>
                <th>Equipped</th>
            </tr>';

        while ($wpn_row = mysqli_fetch_array($wpn_result)) {
            //for attack bonus, need to check for proficiency and add either strength or dex (whichever is higher) if the weapon is versatile
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

            if ($wpn_row['wpn_prof'] == $row['prof_id']) {
                $atk_bonus += $pb;
            } else if ($row['prof_id'] == 31 && $wpn_row['category'] == 'Martial') {
                $atk_bonus += $pb;
            } else if ($row['prof_id'] == 29 && $wpn_row['category'] == 'Simple') {
                $atk_bonus += $pb;
            }

            print '
            <tr>
                <td>' .$wpn_row['wpn_name']. '</td>
                <td>' .$atk_bonus. '</td>
                <td>' .$wpn_row['dmg_type']. '</td>
                <td>' .$wpn_row['wpn_multiplier']. ' d' .$wpn_row['wpn_die']. '</td>
                <td>' .$dmg_bonus. '</td>
                <td>' .$wpn_row['equipped']. '</td>
            </tr>';
        }
        print '
            </table>';
        //Add a button that will allow the user to add a weapon to their inventory
        //get a list of all weapons in the database
        $query = "SELECT wpn_name, wpn_id FROM weapons ORDER BY wpn_name";
        $result = mysqli_query($dbc, $query);

        print '
        <div id="wpn_options">
            <button id="wpn_btn" class="btn btn-default wpn_popup_open">Add weapon</button>
            <!-- Add content to the popup -->
            <div id="wpn_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close wpn_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                        <label for="wpn_select">Add a weapon to your inventory:</label><br>
                        <select name="wpn_select" id="wpn_select" class="form-control">
                            <option value="">Select a weapon...</option>';

                            //add all weapons to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['wpn_id']. '">' .$row['wpn_name']. '</option>';
                            }
                    print '
                    </select>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="wpn_get" value="Get weapon">
                    </p>
                </form>
            </div>';

            //Add another button that will allow the user to equip a weapon (that is not currently equipped)
            $query = "SELECT cw_weapon, wpn_id, wpn_name, cw_id FROM char_weapons
            INNER JOIN weapons ON char_weapons.cw_weapon = weapons.wpn_id WHERE cw_char={$char_result} AND equipped=0";
            $result = mysqli_query($dbc, $query);

            print '
            <button id="wpn_equip_btn" class="btn btn-default wpn_equip_popup_open">Equip weapon</button>
            <!-- Add content to the popup -->
            <div id="wpn_equip_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close wpn_equip_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                    <p>
                        <label for="equip_weapon">Equip a weapon from your inventory:</label><br>
                        <select name="equip_weapon" id="equip_weapon" class="form-control">
                            <option value="">Select a weapon to equip...</option>';

                            //add all weapons to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['cw_id']. '">' .$row['wpn_name']. '</option>';
                            }
                    print '
                    </select>
                    </p>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="wpn_equip" value="Equip">
                    </p>
                </form>
            </div>';

            //Add a button for unequipping weapons
            $query = "SELECT cw_weapon, wpn_id, wpn_name, cw_id FROM char_weapons
            INNER JOIN weapons ON char_weapons.cw_weapon = weapons.wpn_id WHERE cw_char={$char_result} AND equipped=1";
            $result = mysqli_query($dbc, $query);

            print '
            <button id="wpn_unequip_btn" class="btn btn-default wpn_unequip_popup_open">Unequip weapon</button>
            <!-- Add content to the popup -->
            <div id="wpn_unequip_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close wpn_unequip_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                    <p>
                        <label for="unequip_weapon">Unequip a weapon:</label><br>
                        <select name="unequip_weapon" id="unequip_weapon" class="form-control">
                            <option value="">Select a weapon to unequip...</option>';

                            //add all equipped weapons to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['cw_id']. '">' .$row['wpn_name']. '</option>';
                            }
                    print '
                    </select>
                    </p>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="wpn_unequip" value="Unequip">
                    </p>
                </form>
            </div>';

            //Create a popup option for removing weapons
            print '<button id="remove_wpn_btn" class="btn btn-default remove_wpn_popup_open">Remove weapon</button>
            <!-- Add content to the popup -->
            <div id="remove_wpn_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close remove_wpn_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                        <label for="remove_wpn_select">Remove a weapon from your inventory (must be unequipped):</label><br>
                        <select name="remove_wpn_select" id="remove_wpn_select" class="form-control">
                            <option value="">Select weapon to be removed...</option>';

                            //Get the character's unequipped armor
                            $query = "SELECT cw_id, cw_weapon, wpn_id, wpn_name FROM char_weapons
                            INNER JOIN weapons ON char_weapons.cw_weapon = weapons.wpn_id WHERE cw_char={$char_result} AND equipped=0";
                            $result = mysqli_query($dbc, $query);

                            //add all items to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['cw_id']. '">' .$row['wpn_name']. '</option>';
                            }
                    print '
                    </select>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="wpn_remove" value="Remove weapon">
                    </p>
                </form>
            </div>
        </div>';

        //Get all the character's armor
        $armor_query = "SELECT ca_armor, armor_class, armor_mod, max_mod FROM char_armor
        INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$char_result} AND equipped=1";
        $armor_result = mysqli_query($dbc, $armor_query);

         //display the character's armor
        print '
        <div class="table-responsive">
            <table class="char-stats table-bordered">
                <tr class="stat">
                    <th>Armor</th>
                    <th>AC</th>
                    <th>Modifier</th>
                    <th>Max Bonus</th>
                    <th>Stealth Disadvantage</th>
                    <th>Equipped</th>
                </tr>';
        //Get the character's equipped armor
        $query = "SELECT ca_armor, armor_name, armor_class, armor_mod, max_mod, stealth_disadvantage, equipped FROM char_armor
        INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$char_result}";
        $result = mysqli_query($dbc, $query);

        while ($row = mysqli_fetch_array($result)) {
             print '
             <tr>
                <td>' .$row['armor_name']. '</td>
                <td>' .$row['armor_class']. '</td>
                <td>' .$row['armor_mod']. '</td>
                <td>' .$row['max_mod']. '</td>
                <td>' .$row['stealth_disadvantage']. '</td>
                <td>' .$row['equipped']. '</td>
            </tr>';
        }

        print '</table>
        </div>';
        //Add the ability to add new armor to inventory in a popup
        print '
        <div id="armor_options">
        <button id="armor_btn" class="btn btn-default armor_popup_open">Add armor</button>
        <!-- Add content to the popup -->
        <div id="armor_popup" class="inventory_opt">
            <!-- Add an optional button to close the popup -->
            <button type="button" class="close armor_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <form action="view_inventory.php" method="post">
                <p>
                    <label for="add_armor">Add armor to your inventory:</label><br>
                    <select name="add_armor" id="add_armor" class="form-control">
                        <option value="">Select armor to add...</option>';

                        //Get a list of all armor available in database
                        $query = "SELECT armor_id, armor_name FROM armor ORDER BY armor_name";
                        $result = mysqli_query($dbc, $query);

                        //add all armor to the dropdown list
                        while ($row = mysqli_fetch_array($result)) {
                            print '<option value="' .$row['armor_id']. '">' .$row['armor_name']. '</option>';
                        }
                print '
                </select>
                </p>
                <p>
                    <input type="hidden" name="character" value="' .$char_result. '">
                    <input type="submit" class="btn btn-default center-block" name="get_armor" value="Get armor">
                </p>
            </form>
        </div>';

        //Add the ability to equip armor
        print '<button id="armor_equip_btn" class="btn btn-default armor_equip_popup_open">Equip armor</button>
        <!-- Add content to the popup -->
        <div id="armor_equip_popup" class="inventory_opt">
            <!-- Add an optional button to close the popup -->
            <button type="button" class="close armor_equip_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <form action="view_inventory.php" method="post">
                <p>
                    <label for="equip_armor">Equip armor from your inventory:</label><br>
                    <select name="equip_armor" id="equip_armor" class="form-control">
                        <option value="">Select armor to equip...</option>';

                        //Get the character's unequipped armor
                        $query = "SELECT ca_armor, armor_id, armor_name, ca_id FROM char_armor
                        INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$char_result} AND equipped=0";
                        $result = mysqli_query($dbc, $query);

                        //add all armor to the dropdown list
                        while ($row = mysqli_fetch_array($result)) {
                            print '<option value="' .$row['ca_id']. '">' .$row['armor_name']. '</option>';
                        }
                print '
                </select>
                </p>
                <p>
                    <input type="hidden" name="character" value="' .$char_result. '">
                    <input type="submit" class="btn btn-default center-block" name="armor_equip" value="Equip">
                </p>
            </form>
        </div>';

        //Add the ability to unequip armor
        print '<button id="armor_unequip_btn" class="btn btn-default armor_unequip_popup_open">Unequip armor</button>
        <!-- Add content to the popup -->
        <div id="armor_unequip_popup" class="inventory_opt">
            <!-- Add an optional button to close the popup -->
            <button type="button" class="close armor_unequip_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <form action="view_inventory.php" method="post">
                <p>
                    <label for="unequip_armor">Unequip armor:</label><br>
                    <select name="unequip_armor" id="unequip_armor" class="form-control">
                        <option value="">Select armor to unequip...</option>';

                        //Get the character's unequipped armor
                        $query = "SELECT ca_armor, armor_id, armor_name, ca_id FROM char_armor
                        INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$char_result} AND equipped=1";
                        $result = mysqli_query($dbc, $query);

                        //add all armor to the dropdown list
                        while ($row = mysqli_fetch_array($result)) {
                            print '<option value="' .$row['ca_id']. '">' .$row['armor_name']. '</option>';
                        }
                print '
                </select>
                </p>
                <p>
                    <input type="hidden" name="character" value="' .$char_result. '">
                    <input type="submit" class="btn btn-default center-block" name="armor_unequip" value="Unequip">
                </p>
            </form>
        </div>';

        //Create a popup option for removing armor
        print '<button id="remove_armor_btn" class="btn btn-default remove_armor_popup_open">Remove armor</button>
            <!-- Add content to the popup -->
            <div id="remove_armor_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close remove_armor_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                        <label for="remove_armor_select">Remove armor from your inventory (must be unequipped):</label><br>
                        <select name="remove_armor_select" id="remove_armor_select" class="form-control">
                            <option value="">Select armor to be removed...</option>';

                            //Get the character's unequipped armor
                            $query = "SELECT ca_id, ca_armor, armor_id, armor_name FROM char_armor
                            INNER JOIN armor ON char_armor.ca_armor = armor.armor_id WHERE ca_char={$char_result} AND equipped=0";
                            $result = mysqli_query($dbc, $query);

                            //add all items to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['ca_id']. '">' .$row['armor_name']. '</option>';
                            }
                    print '
                    </select>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="armor_remove" value="Remove armor">
                    </p>
                </form>
            </div>
        </div>';

        print '</div>'; //end of right display
        
        //get all the characters' items and put them in a table
        $query = "SELECT ci_item, item_name, consumable FROM char_items INNER JOIN item ON char_items.ci_item = item.item_id WHERE ci_char={$char_result}";
        $result = mysqli_query($dbc, $query);

        //create the table for the item display
        print '
        <table class="item_table">
            <tr class="stat">
                <th>Item</th>
                <th>Consumable</th>
            </tr>';
        while ($row = mysqli_fetch_array($result)) {
            print '
            <tr>
                <td>' .$row['item_name']. '</td>
                <td>' .$row['consumable']. '</td>
            </tr>';
        }
        print '</table>';

        //add a popup button to enable the user to add or remove items from their inventory
        //first, query the database to get a list of all items
        $query = "SELECT item_name, item_id FROM item ORDER BY item_name";
        $result = mysqli_query($dbc, $query);

        //next, create the popup (done according to the plugin setup)
        print '
        <div id="item_options">
        <button id="add_item_btn" class="btn btn-default add_item_popup_open">Add item</button>
            <!-- Add content to the popup -->
            <div id="add_item_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close add_item_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                        <label for="add_item_select">Add an item to your inventory:</label><br>
                        <select name="add_item_select" id="add_item_select" class="form-control">
                            <option value="">Select an item...</option>';

                            //add all weapons to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['item_id']. '">' .$row['item_name']. '</option>';
                            }
                    print '
                    </select>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="item_get" value="Get item">
                    </p>
                </form>
            </div>';

        //Now create the use/remove item button (Note: in the future, will have to find a better way to keep count of the items. That way
        //the user can track consumables more easily. For now though, we'll just stick to removing.)
        //first, get all the user's items
        $query = "SELECT ci_item, ci_id, item_name FROM char_items INNER JOIN item ON char_items.ci_item = item.item_id WHERE ci_char={$char_result} ORDER BY item_name";
        $result = mysqli_query($dbc, $query);

        print '<button id="remove_item_btn" class="btn btn-default remove_item_popup_open">Remove item</button>
            <!-- Add content to the popup -->
            <div id="remove_item_popup" class="inventory_opt">
                <!-- Add an optional button to close the popup -->
                <button type="button" class="close remove_item_popup_close inventory_opt_close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <form action="view_inventory.php" method="post">
                        <label for="remove_item_select">Remove an item from your inventory:</label><br>
                        <select name="remove_item_select" id="remove_item_select" class="form-control">
                            <option value="">Select an item...</option>';

                            //add all items to the dropdown list
                            while ($row = mysqli_fetch_array($result)) {
                                print '<option value="' .$row['ci_id']. '">' .$row['item_name']. '</option>';
                            }
                    print '
                    </select>
                    <p>
                        <input type="hidden" name="character" value="' .$char_result. '">
                        <input type="submit" class="btn btn-default center-block" name="item_remove" value="Remove item">
                    </p>
                </form>
            </div>
        </div>';
        //end of first column containing items
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
