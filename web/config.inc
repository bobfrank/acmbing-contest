<?php
global $db, $dbtype;

$config = parse_ini_file("../config.ini", true);

$contest_root = "..";

$dbtype = $config['db']['type'];

if($dbtype == "mysql") {
    global $dbname, $dbhost, $dbuser, $dbpass;
    $dbhost = $config['db']['host'];
    $dbuser = $config['db']['user'];
    $dbpass = $config['db']['pass'];
    $dbname = $config['db']['name'];
}
require_once('db.inc');

?>