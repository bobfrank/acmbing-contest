<?php
/*
Author: Daniel Benamy <dbenamy1@binghamton.edu>
License: To be determined
*/

require_once('fe.inc');
require('../web/logging.inc');

// Processes the submission and fills in some variables
// Should not write anything out
function process_submission() {
	// Variable that will be used to display the page
	global $message;
	global $user_id;
	global $contest_root;
	global $success;
	

	//$users_filename = $contest_root . '/manager/conf/users.txt';
	$name = $_GET['name'];
	// TODO check to make sure _GET has name
	
	// Make sure requested name is sane
	$allowable_characters = array_merge(range('a', 'z'), range('A', 'Z'));
	$allowable_characters = array_merge($allowable_characters, range(0, 9));
	$allowable_characters = array_merge($allowable_characters, array(' '));
	if(strlen($_GET['name']) > 49) {
		$message = "You requested a name that is too long. Please choose a different name.";
		return;
	} else if(strlen($name) < 2) {
	    $message = "You requested a name that is too short. Please choose a different name.";
		return;
	}
	for ($i = 0; $i < strlen($_GET['name']); $i++) {
		$character = substr($_GET['name'], $i, 1);
		if (!in_array($character, $allowable_characters, true)) {
			$message = "You requested a name that contains invalid characters. Please choose a different name.";
			return;
		}
	}
    global $contest_id, $db;

    $result_set = $db->get_users($contest_id);

    $names = array();
    $ids = array();

    foreach($result_set as $row) {
		$ids[] = $row['user_id'];
		$names[] = $row['name'];
    }
	
	// Make sure name is unique
	if (in_array($_GET['name'], $names)) {
		$message = "An account already exists for that name.";
		return;
	}
    $user_id = rand(1000,9999);
    while (in_array($user_id, $ids)) {
        $user_id = rand(1000,9999);
    }

    $db->add_user($contest_id, $user_id, $name);

    $status = 0;
	/*
	// Put in registration request
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890';
	$random_chars = '';
	for ($i = 0; $i < 15; $i++) {
		$random_chars .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	$registration_file = fopen($contest_root . '/temp_web/registration_request-' . $random_chars, 'w');
	if ($users_file === false) {
		$message = "ERROR: Opening registration_request file failed.";
		return;
	}
	fputs($registration_file, $_GET['name'] . ',' . get_ip() . "\n");
	fclose($registration_file);
	
	// Wait for reply for 15 seconds
	$start_time = time();
	$reply_file = null;
	while (1) { // TODO this loop is structured kinda funkily. Make it better?
		sleep(1);
		if (time() > $start_time + 15) {
			$message = 'ERROR: There was no response from the backend to a registration request.';
                        // Clean up the file we created over here
                        unlink($contest_root . '/temp_web/registration_request-' . $random_chars);
			return;
		}
		$reply_filename = $contest_root . '/temp_web/registration_status-' . $random_chars;
		clearstatcache();
		if (!is_readable($reply_filename)) {
			continue;
		}
		$reply_file = fopen($reply_filename, 'r');
		if ($reply_file === false) {
			$message = 'ERROR: Something went wrong while trying to open the registration reply file.';
			return;
		}
		break;
	}
	$reply = fgets($reply_file);
	fclose($reply_file);
	$status = strtok($reply, ',');
	$status_message = strtok(',');
	$user_id = strtok(',');*/
	if ($status == 0) {
		$message = "You have been successfully registered. Your user id is $user_id. Write this number down now! You may need it later to log in again.";
        $success = true;
	} else {
		$message = 'ERROR: Registration error. Code ' . $status . ': ' . $status_message;
		$success = false;
	}
	/*
	// Acknowledge to backend that we saw the information if possible so it can clean up
	$ack = fopen($contest_root . '/temp_web/registration_done-' . $random_chars, 'w');
	if ($ack === false) {
		// TODO log this
	} else {
		fclose($ack);
	}*/
}

process_submission();

app_log(get_ip() . ' attemped to register with name "' . $_GET['name'] . '". Result: ' . $message);
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
    <?php
    	echo $message;
    ?>
  </p>
  <p>
    <?php
    if ($success) {
    	echo "After you have written down your User ID, ";
    	echo '<a href="scoreboard.php?id=' . $user_id . '">';
    	echo "click here to continue</a>";
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
