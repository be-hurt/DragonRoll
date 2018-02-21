<?php
    define('TITLE', 'New Character');
    include('templates/header.html');
    print '
    <div id="text_area">
        <div class="page_title">
            <h1>Create Character</h1>
        </div>
        <div class="page_content">';

    if (!is_logged_in()) {
        print '<h2>Access Denied!</h2><p class="error">You do not have permission to access this page. Please <a href="login.php">Login</a> or <a href="register.php">Register</a></p></div>';
        include('templates/footer.html');
        exit();
    }

    //get the database connection
    include('connect/mysqli_connect.php');

    print '<form id="form1" action="new_character2.php" method="post">
        <div class="form-group">
            <label for="name"><h2>Character Name:</h2></label>
            <div>
                <input type="text" class="form-control" name="name" placeholder="Name" required>
            </div>
        </div>
        <div class="form-group">';

            //Get all the names of available races and populate a select list with the result
            $query = "SELECT race_name, race_id, description FROM races ORDER BY race_name";
            $result = mysqli_query($dbc, $query);

            if ($result) {
                print '
                <label for="race"><h2>Race:</h2></label>
                <div>
                    <select name="race" class="form-control" onchange="raceDescr(this)" required>
                    <option value="">Choose a race...</option>';

                    while ($row = mysqli_fetch_array($result)) {
                        print "<option value=\"{$row['race_id']}\">{$row['race_name']}</option>";
                    }

                     print '
                    </select>
                </div>
                <div>';

                mysqli_data_seek($result, 0);

                while ($row = mysqli_fetch_array($result)) {
                    print '<p id=' .$row['race_name']. ' class="hidden">'
                        .$row['description']. '</p>';
                }
                print '</div>';

            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</div>';
            }

    print '<p>';

        //Get all the names of available classes and populate a select list with the result
            $query = "SELECT class_name, class_id, description FROM classes ORDER BY class_name";
            $result = mysqli_query($dbc, $query);

            if ($result) {

                print '
                    <label for="class"><h2>Class:</h2></label>
                    <div>
                        <select id="class" name="class" class="form-control" onchange="classDescr(this)" required>
                        <option value="">Choose a class...</option>';

                    while ($row = mysqli_fetch_array($result)) {
                        print "<option value=\"{$row['class_id']}\">{$row['class_name']}</option>";
                    }

                    print '
                        </select>
                    </div>
                    <div>';

                    mysqli_data_seek($result, 0);

                    while ($row = mysqli_fetch_array($result)) {
                        print '<p id=' .$row['class_name']. ' class="hidden">'
                            .$row['description']. '</p>';
                    }
                print '</div>';

            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }
    print '<p>';
        //Get all the names of available backgrounds and populate a select list with the result
            $query = "SELECT bkgd_name, bkgd_id, bkgd_description FROM backgrounds ORDER BY bkgd_name";
            $result = mysqli_query($dbc, $query);

            if ($result) {

                print '
                    <label for="background"><h2>Background:</h2></label>
                    <div>
                        <select id="background" name="background" class="form-control" onchange="backgroundDescr(this)" required>
                        <option value="">Choose a background...</option>';

                    while ($row = mysqli_fetch_array($result)) {
                        print "<option value=\"{$row['bkgd_id']}\">{$row['bkgd_name']}</option>";
                    }
                    print '
                    </select>
                    </div>
                    <div>';

                    mysqli_data_seek($result, 0);

                    while ($row = mysqli_fetch_array($result)) {
                        print '<p id=' .$row['bkgd_name']. ' class="hidden">'
                            .$row['bkgd_description']. '</p>';
                    }
                    print '</div>';

            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }
    print '<p>';

        //Get all the names of available alignments and populate a select list with the result
        $query = "SELECT align_name, align_id, align_description FROM alignments ORDER BY align_name";

            if ($result = mysqli_query($dbc, $query)) {

                print '
                    <label for="alignment"><h2>Alignment:</h2></label>
                    <div>
                        <select name="alignment" class="form-control" onchange="alignmentDescr(this)" required>
                        <option value="">Choose an alignment...</option>';

                    while ($row = mysqli_fetch_array($result)) {
                        print "<option value=\"{$row['align_id']}\">{$row['align_name']}</option>";
                    }
                    print '
                        </select>
                    </div>
                    <div>';

                mysqli_data_seek($result, 0);

                while ($row = mysqli_fetch_array($result)) {
                    print '<p id=' .$row['align_id']. ' class="hidden">'
                        .$row['align_description']. '</p>';
                }
                print '</div>';
            } else {
                //Query didn't run
                print '<p class="error">Could not retrieve the data because:<br>' . mysqli_error($dbc) . '</p><p>The query being run was: ' . $query . '</p>';
            }

    print '
        <button type="submit" class="btn new_char_btn" name="submit">Next Step</button>
        </form>
        </div>
        </div>
        </div>';

    mysqli_close($dbc);
    include('templates/footer.html');
?>
