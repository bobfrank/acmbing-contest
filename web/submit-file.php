<?php
require_once('fe.inc');
require('logging.inc');
require('time.inc');
//ini_set("include_path", ".:../"); doesn't fix copy so I prepended ../ to $dir

// Processes the submission and fills in some variables
// Should not write anything out
function process_submission() {
	// Variable that will be used to display the page
	global $message;
	global $user_id;
	global $contest_root;
	$submission_time = date("H:i:s");

	/*
	$users_file = $contest_root . '/manager/conf/users.txt';
	
	if (!$users = file($users_file)) {
		$message = "Manager error: Opening users file failed.";
		return;
	}
	foreach ($users as $index => $user) {
		$users[$index] = strtok($user, ':');
	}*/
    global $contest_id, $db;

   /* $query  = "SELECT * FROM users WHERE contest_id=$contest_id;";
    $result = mysql_query($query);

    $users = array();

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$users[ $row['user_id'] ] = $row['name'];
    }*/
	
	if (!array_key_exists("id", $_POST)) {
		$message = 'You came here from a bad form.';
		return;
	}
	$user_id = $_POST["id"];
	
	/*if (!in_array($user_id, $users)) { //test this...
		$message = "You tried to submit a file using an invalid user id.";
		unset($GLOBALS['user_id']); // unset $user_id would only destroy it in this function's scope
		return;
	}*/
	
	$upload = $_FILES["program"];
	$allowable_types = array("text/x-c++src", "text/x-csrc", "text/x-java", "text/plain", "application/octet-stream", "text/x-csharp", "text/java");
	if ($upload["error"] != 0) { // TODO this doesn't seem to be working
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
	
	global $contest_status;
	global $times_file;
	require('contest_status.inc');
	process_contest_status();
	//echo "<pre>"; print_r($GLOBALS); echo "</pre>";
	if ($contest_status != 1) {
		$message = "You can not submit a file now because the contest is not in progress.";
		return;
	}
	
	// Ok to upload file
	//if ($_POST["lang"] == "auto") {
		if (strpos($upload['name'], '.') === false) {
			$message = "File name doesn't have an extension so the type could not be automatically detected.";
			return;
		}
		$extension = strrchr($upload['name'], '.');
	/*} else {
		$extension = '.' . $_POST['lang'];
	}*/
	$allowable_extensions = array('.java', '.c', '.cpp', '.cs'); // TODO move to config file
	if (!in_array($extension, $allowable_extensions)) {
		$message = "Invalid language specified.";
		return;
	}
	
       	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890';
	$random_chars = '';
	for ($i = 0; $i < 15; $i++) {
		$random_chars .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	$tempfile = $upload["tmp_name"];
    $program = intval($_POST['progno']);
	$filename = 'submission-' . $user_id . '-' . $submission_time . '-' . $program . '-' . $random_chars . $extension;
	$filename_with_path = $contest_root . '/files/' . $filename;
	$result = copy($tempfile, $filename_with_path);
	if ($result === false) {
		$message = "ERROR: Could not copy submitted file to files/.";
		return;
	}

    $db->add_submitted($contest_id, $user_id, $program, $filename, time());

	$message = "The program was successfully uploaded. Thanks!";
}

process_submission();

app_log(sprintf("User %d submitted a file. IP address: %s. Problem: %s. Result: %s", $user_id, get_ip(), $_POST['progno'], $message));
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
  <title>ACM Coding Contest Submission</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
  <p>
    <?php echo $message; ?>
  </p>
  <p>
    <?php
  	if (isset($user_id)) {
		echo '<a href="scoreboard.php?id=' . $user_id . '">Scoreboard</a>';
	} else {
		echo '<a href="scoreboard.php">Scoreboard</a>';
	}
    ?>
  </p>
  <hr />
  
  <p>
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Transitional" height="31" width="88" /></a>
  </p>

</body>
</html>
