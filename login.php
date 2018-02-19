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

    if($error) {
        print '<p class="error">' . $error . '</p>';
    }

    if ($loggedin) {
        //redirect to character_select.php
        print '
        <div class="page_title">
            <h1>Login</h1>
            <h2>Success!</h2>
        </div>
        <div class="welcome">
            <h3>You are now logged in.</h3><br>';
            print '<p>Welcome back ' . $_SESSION['username'] . '!</p>
            <p>Click <a href="character_select.php">here</a> to view your characters</p>
        </div>';
    } else {
        print '
        <div class="page_title">
            <h1>Login</h1>
        </div>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username"><h2>Username:</h2></label>
                <div>
                    <input type="text" id="username" class="form-control" name="username" placeholder="Username">
                </div>
            </div>
            <div class="form-group">
                <label for="password"><h2>Password:</h2></label>
                <div>
                    <input type="password" id="password" class="form-control" name="password" placeholder="Password">
                </div>
            </div>
            <div class="form-group">
                <div>
                    <button type="submit" name="submit">Log In!</button>
                </div>
            </div>
        </form>
        <div class="register">
            <p>Not yet a member? <a class="btn btn-default" href="register.php" role="button">Sign Up!</a></p>
        </div>';
    }

    include('templates/footer.html');
?>
