<?php
// Copyright 2006 Daniel Benamy <dbenamy1@binghamton.edu>
// License to be determined

require_once('fe.inc');
require_once('logging.inc');
require('scoreboard-functions.inc');
require('contest_status.inc');
require('time.inc');

$NUM_PROBLEMS=$num_problems;
$ROOT_DIR = $contest_root . '/logs/';
$users_filename = $contest_root . '/manager/conf/users.txt';


// ***Program flow starts here***

process_contest_status();
read_in_all_users();
process_submission_results();
rank_users();
process_specified_user();

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <title>ACM Contest Manager</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
  <script src="acm.js" type="text/javascript"></script>

<script language="Javascript" type="text/javascript"><!--

<?php
if(array_key_exists("id",$_GET)) {
    echo "var user_id = \"";
    echo $_GET["id"];
    echo "\";";
} else {
    echo "var user_id= '-1';";
}
?>

var refStatus; // Refresh status vartiable, used for clearing the autoreload
var refScores; // Refresh scores variable, used for clearing the autoreload
function refreshScores() {
    refresh("scoreboard","displayscores.php?id="+user_id+"&sid="+Math.random());
    refScores = setTimeout("refreshScores()",5000);
}
addLoadEvent(refreshScores);

function refreshStatus() {
    refresh("status","displaystatus.php?id="+user_id+"&sid="+Math.random());
    refStatus = setTimeout("refreshStatus()",5000);
}
addLoadEvent(refreshStatus);

function getCurrentTime() {
    var t = new Date();
    var h = t.getHours();
    var ap = "AM";
    if (h >= 12) {
        h = h-12;
        ap = "PM";
    }
    if (h == 0) {
        h = 12;
    }
    var time_text = h + ":";
    var m = t.getMinutes();
    if (m < 10) time_text = time_text + "0";
    time_text = time_text + m + ":";
    var s = t.getSeconds();
    if (s < 10) time_text = time_text + "0";
    time_text = time_text + s + " " + ap;

    return time_text;
}

function refreshOfficialTime() {
    document.getElementById("official_time").innerHTML = getCurrentTime();	
    setTimeout("refreshOfficialTime()",1000);
}

function refreshTimeLeft() {
    refresh("time_left", "time_left.php");
    setTimeout("refreshTimeLeft()", 5000);
}

function show(boxid, refresh) {
    if (document.getElementById) 
	box = document.getElementById(boxid).style;
    else if (document.all) 
	box = document.all[boxid];
   
    if (box.visibility == "visible" || box.visibility == "show") {
	box.visibility = "hidden";
	refreshStatus();
    } else {
    	clearTimeout(refStatus);	
	box.visibility = "visible";
    }
}
addLoadEvent(refreshTimeLeft);
//addLoadEvent(refreshOfficialTime);

//--></script>

</head>
<body id="document">
<div id="MenuLinks">
<ul> 
<li><a href="scoreboard.php?id=<?php echo $_GET["id"]; ?>"> Scoreboard</a></li>
<!-- <li><a href="clarifications.php?id=<?php echo $_GET["id"]; ?>">Clarifications</a></li> -->
<li><a href="problems.php?id=<?php echo $_GET["id"]; ?>">Problems</a></li>
</ul>
</div>
<hr />

<div id="main">  
  <table border="0" width="100%">
    <tr>
      <td valign="top" align="left">
<!-- <body> -->
  <div id="content">
<!-- title and login name -->
  <h1>ACM Coding Contest Scoreboard</h1>
<?php
if (array_key_exists("id", $_GET) && array_key_exists($_GET["id"], $people)) {
  $user_id = $_GET["id"];

  echo "<h2>You are logged in as: <i>";
  echo $people[$user_id]->name;
  echo "</i></h2>";
}
?>
  

  <!--Contest Time & Status-->

  <span id="official_time"></span>
  <span id="time_left"></span>

  <!--
  <p>
    The official time is <?php echo date('H:i:s') ?> (24 hour format).<br />
    <?php
    if ($contest_status == 0) {
	echo "The contest has not yet started.";
    } else if ($contest_status == 3) {
	echo "Time until contest start: " . $time_left;
    } else if ($contest_status == 1) {
	echo "Time left in contest: " . $time_left;
    } else if ($contest_status == 2) {
	echo "The contest is over. Thanks for participating!";
    } else {
	app_log("ERROR: \$contest_status is set to $contest_status which is an invalid value. (scoreboard)");
	echo "Error.";
    }
    ?>

  </p>
-->
  
  <!--Scoreboard-->
  <div id="scoreboard">
  </div>
  </div>
  </td>
<!-- Side Bar -->
<td valign="top">



<!--Submission status-->
<div id="status">
</div>


<!-- Submit file form -->

<div class="sidebar">
<?php
require('file-submission-form.inc');
?>
        </div>
      </td>
    </tr>
  </table>

<?php 
require('footer.inc'); 
?>

