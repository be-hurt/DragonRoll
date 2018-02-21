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

            //***************HTML content starts here********************//
            print '
            <nav class="subnav">
                <ul role="navigation">
                    <li role="presentation"><a href="view_character.php?character=' .$character. '">Main</a></li>
                    <li role="presentation" class="active"><a href="view_stats.php?character=' .$character. '">Stats</a></li>
                    <li role="presentation"><a href="view_skills.php?character=' .$character. '">Skills</a></li>
                    <li role="presentation"><a href="view_spellbook.php?character=' .$character. '">Spellbook</a></li>
                    <li role="presentation"><a href="view_inventory.php?character=' .$character. '">Inventory</a></li>
                    <li role="presentation"><a href="view_traits.php?character=' .$character. '">Traits</a></li>
                </ul>
            </nav>';

           $mod_query = "SELECT modifier FROM modifiers WHERE ability_score="; //=stat score

            print '
            <div id="text_area">
                <table class="proficiency_table">
                    <tr>
                        <th>Proficiency Bonus</th>
                        <td>' .$pb. '</td>
                    </tr>
                </table>
                <hr>
                <table class="char_stats">
                    <tr>
                        <th class="stat">Strength</th>
                        <td class="stat">' .$stats['strength']. '</td>
                    </tr>
                    <tr>
                        <th>Modifier</th>
                        <td>' .$stat_mods[0]. '</td>
                    </tr>
                    <tr>
                        <th>Saving Throws</th>
                        <td>' .$profs['Strength']. '</td>
                    </tr>
                    <tr>
                        <th>Athletics</th>
                        <td>' .$profs['Athletics']. '</td>
                    </tr>
                </table>
                <table class="char_stats">
                    <tr>
                        <th class="stat">Dexterity</th>
                        <td class="stat">' .$stats['dexterity']. '</td>
                    </tr>
                    <tr>
                        <th>Modifier</th>
                        <td>' .$stat_mods[1]. '</td>
                    </tr>
                    <tr>
                        <th>Saving throws</th>
                        <td>' .$profs['Dexterity']. '</td>
                    </tr>
                    <tr>
                        <th>Acrobatics</th>
                        <td>' .$profs['Acrobatics']. '</td>
                    </tr>
                    <tr>
                        <th>Sleight of Hand</th>
                        <td>' .$profs['Sleight of hand']. '</td>
                    </tr>
                    <tr>
                        <th>Stealth</th>
                        <td>' .$profs['Stealth']. '</td>
                    </tr>
                </table>
                <table class="char_stats">
                    <tr>
                        <th class="stat">Constitution</th>
                        <td class="stat">' .$stats['constitution']. '</td>
                    </tr>
                    <tr>
                        <th>Modifier</th>
                        <td>' .$stat_mods[2]. '</td>
                    </tr>
                    <tr>
                        <th>Saving throws</th>
                        <td>' .$profs['Constitution']. '</td>
                    </tr>
                </table>
                <table class="char_stats">
                    <tr>
                        <th class="stat">Intelligence</th>
                        <td class="stat">' .$stats['intelligence']. '</td>
                    </tr>
                    <tr>
                        <th>Modifier</th>
                        <td>' .$stat_mods[3]. '</td>
                    </tr>
                    <tr>
                        <th>Saving throws</th>
                        <td>' .$profs['Intelligence']. '</td>
                    </tr>
                    <tr>
                        <th>Arcana</th>
                        <td>' .$profs['Arcana']. '</td>
                    </tr>
                    <tr>
                        <th>History</th>
                        <td>' .$profs['History']. '</td>
                    </tr>
                    <tr>
                        <th>Investigation</th>
                        <td>' .$profs['Investigation']. '</td>
                    </tr>
                    <tr>
                        <th>Nature</th>
                        <td>' .$profs['Nature']. '</td>
                    </tr>
                    <tr>
                        <th>Religion</th>
                        <td>' .$profs['Religion']. '</td>
                    </tr>
                </table>
                <table class="char_stats">
                    <tr>
                        <th class="stat">Wisdom</th>
                        <td class="stat">' . $stats['wisdom'] . '</td>
                    </tr>
                    <tr>
                        <th>Modifier</th>
                        <td>' .$stat_mods[4]. '</td>
                    </tr>
                    <tr>
                        <th>Saving throws</th>
                        <td>' .$profs['Wisdom']. '</td>
                    </tr>
                    <tr>
                        <th>Animal Handling</th>
                        <td>' .$profs['Animal handling']. '</td>
                    </tr>
                    <tr>
                        <th>Insight</th>
                        <td>' .$profs['Insight']. '</td>
                    </tr>
                    <tr>
                        <th>Medicine</th>
                        <td>' .$profs['Medicine']. '</td>
                    </tr>
                    <tr>
                        <th>Perception</th>
                        <td>' .$profs['Perception']. '</td>
                    </tr>
                    <tr>
                        <th>Survival</th>
                        <td>' .$profs['Survival']. '</td>
                    </tr>
                </table>
                <table class="char_stats">
                    <tr>
                        <th class="stat">Charisma</th>
                        <td class="stat">' . $stats['charisma'] . '</td>
                    </tr>
                    <tr>
                        <th>Modifier</th>
                        <td>' .$stat_mods[5]. '</td>
                    </tr>
                    <tr>
                        <th>Saving throws</th>
                        <td>' .$profs['Charisma']. '</td>
                    </tr>
                    <tr>
                        <th>Deception</th>
                        <td>' .$profs['Deception']. '</td>
                    </tr>
                    <tr>
                        <th>Intimidation</th>
                        <td>' .$profs['Intimidation']. '</td>
                    </tr>
                    <tr>
                        <th>Performance</th>
                        <td>' .$profs['Performance']. '</td>
                    </tr>
                    <tr>
                        <th>Persuasion</th>
                        <td>' .$profs['Persuasion']. '</td>
                    </tr>
                </table>
            </div>';  //end of skills display

            mysqli_close($dbc);

            include('templates/footer.html');
        } else {
            print '<h2>Oops!</h2>
            <p>Please select one of your characters from <a href="character_select.php">here</a> to view this page.</p>';
        }
?>
