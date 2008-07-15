<?php
chdir("..");
require_once('config.inc');

global $contest_id, $user_id;
$contest_id = -1;
if(array_key_exists("contest_id", $_GET)) {
    $contest_id = intval($_GET['contest_id']);
} else if(array_key_exists("contest_id", $_POST)) {
    $contest_id = intval($_POST['contest_id']);
}

$user_id = -1;
if(array_key_exists("user_id", $_GET)) {
    $user_id = intval($_GET['user_id']);
} else if(array_key_exists("user_id", $_POST)) {
    $user_id = intval($_POST['user_id']);
}
chdir('admin');
require_once('login.inc');

?>