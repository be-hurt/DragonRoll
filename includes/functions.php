<?php
    //This page defines custom functions
    //Check if the user is an administrator.

    function is_administrator() {
        //check for the session and check its value:
        if ($_SESSION && ($_SESSION['is_admin']) == '1') {
            return true;
        } else {
            return false;
        }
    }

    function is_logged_in() {
        if ($_SESSION && ($_SESSION['username'])) {
            return true;
        } else {
            return false;
        }
    }
?>
