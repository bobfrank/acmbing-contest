<?php

require_once('be.inc');

if($contest_id == -1) die("Error");
if($user_id == -1) die("Error");

function get_submitted_list()
{
    global $submitted, $contest_id, $user_id, $db;
    $resultset = $db->get_user_submissions($contest_id, $user_id);

    $submitted = array();

    foreach($resultset as $row)
    {
        $submitted[ $row['problem'] ][] = $row;
    }
    ksort($submitted);
}

function istrue($var, $bold=false)
{
    if($var == 1 && $bold == true) { return "<b>true</b>"; }
    else if($var == 1) { return "true"; }
    else { return "false"; }
}

function message($msg, $success)
{
    if($msg == 0 && $success) {
        return "Success";
    } elseif($msg == 0) {
        return "Queued";
    } elseif($msg == 1) {
        return "Compile Fail";
    } elseif($msg == 2) {
        return "Testcase Failed";
    } elseif($msg == 3) {
        return "Timeout";
    } elseif($msg == 4) {
        return "Program Crashed";
    } elseif($msg == 5) {
        return "Unknown";
    } elseif($msg == 6) {
        return "File Access Used";
    } elseif($msg == 7) {
        return "Processing";
    } elseif($msg == 8) {
        return "Fork Attempted";
    }
}

?>
<h3>Edit user</h3><br/>
<b>Submissions List</b><br/><br/>
<?
get_submitted_list();
foreach($submitted as $problem)
{
    echo "Problem " . ($problem[0]['problem']+1) . "<br/>";
    echo "<table border=1>";
    echo "<tr><td width=400><b>Filename</b></td><td><b>Tested</b></td><td><b>Success</b></td><td><b>Time</b></td><td><b>message</b></td></tr>\n";
    foreach($problem as $row)
    {
        echo "<tr><td><i><a href='view_file.php?user_id=$user_id&contest_id=$contest_id&file=" . $row['filename'] . "'>" . $row['filename'] . "</a></i></td><td><i><a href='view_output.php?user_id=$user_id&contest_id=$contest_id&submitted_id=" . $row['submitted_id'] . "'>" . istrue($row['tested']) .  "</i></td><td><i>" . istrue($row['success'],true) . "</i></td><td><i>" . date("H:i:s",$row['time']) .  "</i></td><td><i>" . message($row['message'], $row['success']) . "</i></td></tr>\n";
    }
    echo "</table><br/>\n";
}
?>