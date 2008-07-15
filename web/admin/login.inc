<?php
/*TODO make this a good login section.. */
/*
global $db;
require_once('be.inc');

if(isset($_POST['password'])) {
    if($_POST['password'] == 'test') {
        $value = rand(0,100000000);
        setcookie("session", $value);
        $db->update_cookie($value);
        echo "<script>top.location = '?';</script>";
die();
    }
}
//check for login requests, handle them...
//setcookie("TestCookie", $value);

if (isset($_COOKIE['session']) && $db->get_cookie() == $_COOKIE['session']) {
//ok... we're in
} else {
//show login page
?>
<form method='post'>
<input name='password'><input type='submit' value='login'>
</form>
<?php
die();
}
*/
?>
