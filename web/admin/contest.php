<?php

require_once('be.inc');
global $db;
if($contest_id == -1) die("Error");

if(array_key_exists('output', $_POST)) {
    $problem = intval($_POST['problem'])-1;
    $input = $_POST['input'];
    $output = $_POST['output']; //this needs to be cleaned up with regard to spaces
    $db->add_testcase($contest_id, $problem, $input, $output);
    echo "<script>top.location='contest.php?contest_id=$contest_id';</script>";
}

if(array_key_exists('statement', $_FILES)) {
    $upload = $_FILES["statement"];

	if ($upload["error"] != 0) {
		$message = sprintf("An error occured while uploading your file. Error: %d", $upload["error"]);
		return;
	} /*else if (!in_array($upload["type"], $allowable_types)) {
		$message = sprintf("Error uploading file. We don't accept %s files.", $upload["type"]);
		return; //I seemed to have some problems while submitting things...
	} */else if ($upload["size"] == 0) {
		$message = "You tried to upload a blank file.";
		return;
	} else if ($upload["size"] > 100 * 1024) {
		$message = "You tried to upload a file that is too big.";
		return;
	}

    $problem = intval($_POST['problem'])-1;

		if (strpos($upload['name'], '.') === false) {
			$message = "File name doesn't have an extension so the type could not be automatically detected.";
			return;
		}
		$extension = strrchr($upload['name'], '.');

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890';
	$random_chars = '';
	for ($i = 0; $i < 15; $i++) {
		$random_chars .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	$tempfile = $upload["tmp_name"];
	$filename = 'problem-' . '-' . ($problem+1) . '-' . $random_chars . $extension;
	$filename_with_path = $contest_root . '/../files/problems/' . $filename;
	$result = copy($tempfile, $filename_with_path);
	if ($result === false) {
		$message = "ERROR: Could not copy submitted file to files/.";
		return;
	}

    $db->add_problem_file($contest_id, $problem, $filename);

    echo "<script>top.location='contest.php?contest_id=$contest_id';</script>";
}

function get_testcase_list()
{
    global $testcases, $contest_id, $user_id, $db;
    $rows = $db->get_testcases($contest_id);

    $testcases = array();

    foreach($rows as $row)
    {
        $testcases[ $row['problem'] ][] = $row;
    }
    ksort($testcases);
}

function get_user_list()
{
    global $users, $contest_id, $db;
    $users = $db->get_users($contest_id);
}

function get_problem_list() {
    global $pproblems, $contest_id, $db;
    $rows = $db->get_problem_files($contest_id);

    $pproblems = array();

    foreach($rows as $row)
    {
        $pproblems[ $row['problem'] ][] = $row;
    }
    ksort($pproblems);
}

?>
<h2>Edit contest</h2><br/>
<b>User List</b><br/><br/>
<?
get_user_list();
foreach($users as $row)
{
    echo "<a href='user.php?contest_id=$contest_id&user_id=".$row['user_id']."'>".$row['name']."</a><br/>\n";
}
?><br/><br/>
<hr/>
<b>Problem Statement List</b><br/><br/>
<?
get_problem_list();
$top = 1;
foreach($pproblems as $problem)
{
    if( ($problem[0]['problem']+1) > $top)
        $top = ($problem[0]['problem']+1);

    echo "Problem " . ($problem[0]['problem']+1) . "<br/>";
    echo "<table border=1>";
    echo "<tr><td width=300><b>filename</b></td></tr>\n";
    foreach($problem as $row)
    {
        echo "<tr><td><a href='../problems/".$row['filename']."'>".$row['filename']."</a></td></tr>\n";
    }
    echo "</table>";
}
?><br/>
<b>Add Problem Statement</b>
<form enctype="multipart/form-data" method='post' action='contest.php?contest_id=<?=$contest_id?>'>
<table><tr><td>Problem #:</td><td><input name='problem' value='<?=$top?>' size='2'></td></tr>
<tr><td>File:</td><td><input type='file' name='statement'></td></tr>
<input type="hidden" name="MAX_FILE_SIZE" value="102400" />
<tr><td> </td><td><input type='submit' value='Add Problem Statement'></td></tr>
</table>
</form>
<hr/>
<b>Testcases List</b><br/><br/>
<?
get_testcase_list();
$top = 1;
foreach($testcases as $problem)
{
    if( ($problem[0]['problem']+1) > $top)
        $top = ($problem[0]['problem']+1);

    echo "Problem " . ($problem[0]['problem']+1) . "<br/>";
    echo "<table border=1>";
    echo "<tr><td width=300><b>input</b></td><td width=300><b>output</b></td></tr>\n";
    foreach($problem as $row)
    {
        echo "<tr><td><i>" . $row['input'] . "</i></td><td><i>" . $row['output'] .  "</i></td></tr>\n";
    }
    echo "</table><br/>\n";
}
?><br/>
<b>Add Testcase</b>
<form method='post' action='contest.php?contest_id=<?=$contest_id?>'>
<table><tr><td>Problem #:</td><td><input name='problem' value='<?=$top?>' size='2'></td></tr>
<tr><td>Input:</td><td><input name='input'></td></tr>
<tr><td>Output:</td><td><input name='output'></td></tr>
<tr><td> </td><td><input type='submit' value='Add Testcase'></td></tr>
</table>
</form>
