<?php
    define ('TITLE', 'Register');
    include ('templates/header.html');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!empty($_POST['username']) && !empty($_POST['password1']) && !empty($_POST['email'])) {
            if (($_POST['password1']) == ($_POST['password2'])) {
                //get the database connection
                include('connect/mysqli_connect.php');

                //prepare the values for storing
                $username = mysqli_real_escape_string($dbc, trim(strip_tags($_POST['username'])));
                $password = mysqli_real_escape_string($dbc, sha1(trim(strip_tags($_POST['password1']))));
                $email = mysqli_real_escape_string($dbc, trim(strip_tags($_POST['email'])));

                //Check if the username already exists
                $query = "SELECT * FROM users WHERE username = '$username'";
                mysqli_query($dbc, $query);

                if (mysqli_affected_rows($dbc) == 0) {
                    //Check if email has been used
                    $query = "SELECT * FROM users WHERE email = '$email'";
                    mysqli_query($dbc, $query);

                    if (mysqli_affected_rows($dbc) == 0) {
                        $query = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
                        mysqli_query($dbc, $query);

                        if (mysqli_affected_rows($dbc) == 1) {
                            //print a success message
                            print '
                            <div id="text_area">
                                <div class="page_title">
                                    <h2>Success!</h2>
                                </div>
                                <p class="welcome">You have successfully been registered!</p>
                            </div>';
                        } else {
                            //failure
                            print '<p class="error">Could not register because:<br>' . mysqli_error($dbc) . '.</p><p>The query being run was: ' . $query . '</p>';
                        }
                    } else {
                        //email has already been used
                        print '<p class="error">The email you entered is already registered to a user. Please enter a unique email address.</p>';
                    }
                } else {
                    //username already exists in the database
                    print '<p class="error">The username you entered already exists. Please enter a unique username.</p>';
                }
            } else {
                //failed to enter a matching pw and confirmation pw
                print '<p class="error">Your Password and Password Confirmation did not match. Please try again.</p>';
            }
        } else {
            //Failed to enter a username, password or email
            print '<p class="error">One or more required fields was left empty. Please complete the form and try again.</p>';
        }
    }
?>
<div id="text_area">
    <div class="page_title">
        <h1>Register</h1>
    </div>
    <form action="register.php" method="post">
        <div class="form-group">
            <label for="username"><h2>Username:</h2></label>
            <div>
                <input type="text" class="form-control" name="username" placeholder="Username">
            </div>
        </div>
        <div class="form-group">
            <label for="password1"><h2>Password:</h2></label>
            <div>
                <input type="password" class="form-control" name="password1" placeholder="Password">
            </div>
        </div>
        <div class="form-group">
            <label for="password2"><h2>Confirm Password:</h2></label>
            <div>
                <input type="password" class="form-control" name="password2" placeholder="Confirm Password">
            </div>
        </div>
        <div class="form-group">
            <label for="email"><h2>E-mail:</h2></label>
            <div>
                <input type="email" class="form-control" name="email" placeholder="E-mail address">
            </div>
        </div>
        <div class="form-group">
            <div>
                <button type="submit" name="submit">Sign Up!</button>
            </div>
        </div>
    </form>
</div>
<?php
    include('templates/footer.html');
?>
