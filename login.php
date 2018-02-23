<?php
    $loggedin = false;
    $error = false;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            include('connect/mysqli_connect.php');

            $username = ($_POST['username']);
            $password = sha1(trim(strip_tags($_POST['password'])));

            $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
            $result = mysqli_query($dbc, $query);

            //check if the username and password exist in the database
            if ($row = mysqli_fetch_array($result)) {

                //if the password is correct, begin the session
                if ($row['password'] == $password) {
                    session_start();
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['is_admin'] = $row['admin']; //get the admin value from the users table and assign it here. Used to check if the user is an admin
                    $loggedin = true;
                } else {
                     $error = 'Incorrect password. Please try again.';
                }
            } else {
                //Incorrect email/password
                $error = 'Your username or password was incorrect. Please try again.';
            }
        } else {
            //forgot a field
            $error = 'Please make sure you enter both a username and a password!';
        }
    }

    define('TITLE', 'Login');
    include('templates/header.html');

    if ($loggedin) {
        //redirect to character_select.php
        print '
        <div id="text_area">
            <div class="page_title">
                <h1>Login</h1>
                <h2 class="red_text">Success!</h2>
            </div>
            <div class="welcome">
                <h3>You are now logged in.</h3><br>';
                print '<p>Welcome back ' . $_SESSION['username'] . '!</p>
                <p>Click <a href="character_select.php">here</a> to view your characters</p>
            </div>
        </div>';
    } else {
        print '
            <div id="text_area">';

        if($error) {
            print '
            <p class="error">' . $error . '</p>';
        }

        print '
            <div class="user_form_area">
                <img class="logo_medium" src="resources/drlogo.png" alt="dragonroll logo">
                <div class="page_title center_text">
                    <h1>Login</h1>
                </div>
                <form class="user_form" action="login.php" method="post">
                    <div class="form-group">
                        <div>
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control" name="username" placeholder="Username">
                        </div>
                    </div>
                    <div class="form-group">
                            <div>
                                <label for="password">Password</label>
                                <input type="password" id="password" class="form-control" name="password" placeholder="Password">
                            </div>
                    </div>
                    <div class="form-group">
                        <div>
                            <button class="user_form_button" type="submit" name="submit">Log In!</button>
                        </div>
                    </div>
                </form>
                <div class="center_text">
                    <p>Not yet a member? <a class="btn btn-default" href="register.php" role="button">Sign Up!</a></p>
                </div>
            </div>
        </div>';
    }

    include('templates/footer.html');
?>
