<?php
// More like a struct. Member variables get updated by Person
class Problem {
    var $number; // set by contructor. not used yet.
    var $submissions;
    var $time; // time taken to complete since start of competition. 
               // 24 hour format hhmmss. "-" if not completed.
    
    function Problem($number) {
        $this->number = $number;
        $this->time = "-";
        $this->submissions = 0;
    }
}

class Person {
    var $user_id;
    var $name;
    var $problems;
    var $time_of_last_submission; // in 'His' format. empty string if no submissions processed
    var $last_status_code; // -1 if no submissions
    var $last_compile_log_filename;
    
    function Person($user_id) {
        global $NUM_PROBLEMS; 
        $this->user_id = $user_id;
        $this->problems = array();
        // HOW DO WE FIX THIS?
        for ($i = 1; $i <= $NUM_PROBLEMS; ++$i) {
            $this->problems["$i"] = new Problem($i);
        }
        $this->time_of_last_submission = "";
        $this->last_status_code = -1;
        $this->last_compile_log_filename = "";
    }
    
    // Called each time a line is read from the log file about this person
    // problem is the problem number
    // time is when this was submitted
    //   it is a string in 'H:i:s' format (hh:mm:ss with 24 hour time).
    function submitted_problem($problem, $time, $status_code) {
        if ($this->problems[$problem]->time == "-") { // no working submission yet for this problem
            ++$this->problems[$problem]->submissions;
            $this->time_of_last_submission = $time;
            if ($status_code == "0") {
                $this->problems[$problem]->time = time_from_start($time);
            }
            $this->last_status_code = $status_code;
        }
        // if there was already a working submission we ignore this submission
    }
    
    function total_problems_solved() {
        $total_problems = 0;
        foreach ($this->problems as $problem) {
            if ($problem->time != '-') {
                ++$total_problems;
            }
        }
        return $total_problems;
    }
    
    function total_seconds() {
        $total_time = 0; // in seconds
        foreach ($this->problems as $problem) {
            if ($problem->time != "-") {
                $total_time = $total_time + time_to_seconds($problem->time);
            }
        }
        return $total_time;
    }
}

function read_in_all_users() {
	global $users_filename; // input - filename of users file
	global $people;         // output - array of user ids to people
	

    global $contest_id, $db;

	$people = array();

    $result_set = $db->get_users($contest_id);

    foreach($result_set as $row) {
        $user_id = $row['user_id'];
		$people[$user_id] = new Person($user_id);
		$people[$user_id]->name = $row['name'];
    }
/*	$users = file($users_filename);
	if ($users === false) {
		app_log("ERROR: Opening users file failed.");
		echo "ERROR: Opening users file failed.";
		exit;
	}
	$people = array();
	foreach ($users as $user_entry) {
		$user_id = strtok($user_entry, ':');
		$name = strtok(':');
		$name = rtrim($name);
		if (array_key_exists($user_id, $people)) {
			app_log(sprintf("ERROR: Users file contains 2 entries with user id %d", $user_id));
			continue;
		}
		$people[$user_id] = new Person($user_id);
		$people[$user_id]->name = $name;
	}*/
}

// Fills in peoples' submission data from the submission results log file
function process_submission_results() {
	global $ROOT_DIR; // input
	global $people;   // input & output
	
	// Syntax of log file:
	// <hhmmss>,<user id>,<first name last name>,Problem<problem number>,<status code>,[text]
	// status codes:
	// "0" success
	// "1" compile failure
	// "2" test failure
	// "3" timeout while running
	// "4" crash
	// "15" unknown error while executing program
	// if status code is 1, there is another token before text which has the 
	// filename of a log of the compile
	// log file is assumed to be in chronological order
	

    global $contest_id, $curr_contest, $db;


    $result_set = $db->get_submitted($contest_id);

    foreach($result_set as $row) {
        $user_id = $row['user_id'];
        $problem_number = $row['problem'];
        $status_code = intval($row['message']);
        $time = $row['time'];//date("H:i:s", $row['time']);
        if($row['tested'] == 1)
        $people[$user_id]->submitted_problem($problem_number+1, $time, $status_code);
    }
/*	$log = file($ROOT_DIR.'/log', 1);
	if ($log === false) {
		app_log("ERROR: Opening submission results log file failed.");
		echo "ERROR: Opening submission results log file failed.";
		exit;
	}
	foreach ($log as $line) {        
	//echo "tokenized log entry as:<br />"; echo "<pre>"; print_r($entry); echo "</pre>";
		$time = strtok($line, ",");
		$user_id = strtok(",");
		$name = strtok(",");
		$problem_number = substr(strtok(","), 7);
		$status_code = strtok(",");
		//echo "tokenized line: time=$time, name=$name, problem number=$problem_number, code=$status_code<br />";
		
		if (!array_key_exists($user_id, $people)) {
			app_log(sprintf("ERROR: Submission results log refers to user id %d which is not in the users file.", $user_id));
			continue;
		}
		
		$people[$user_id]->submitted_problem($problem_number, $time, $status_code);
		
		if ($status_code == 1) {
			$people[$user_id]->last_compile_log_filename = strtok(",");
		}
	}*/
}

function rank_users() {
	global $people;          // input
	global $ranked_user_ids; // output
	
	$problems_solved = array();
	$total_times = array(); // in seconds
	$ranked_user_ids = array();
	foreach ($people as $person) {
		$problems_solved[] = 0 - $person->total_problems_solved();
		$total_times[] = $person->total_seconds();
		$ranked_user_ids[] = $person->user_id;
	}
        // Have to pass arrays in by reference, otherwise the sort won't 
        // work properly. (ranked_user_ids will not be changed as it is 
        // a global variable.        
	array_multisort(&$problems_solved, &$total_times, &$ranked_user_ids);
	//echo "<pre>"; print_r($problems_solved); print_r($total_times); print_r($user_ids); echo "</pre><br />";
}

function process_specified_user() {
	global $_GET;
	global $people;
	//?global $specified_user;
	global $show_submission_result; // 0: no, 1: message, 2: message & compile log
	global $submission_result;
	global $compile_log;
	
	if (!array_key_exists("id", $_GET) || $_GET["id"] == "") { // Not showing anyone's submission status
		$show_submission_result = 0;
		return;
	}
	if (!array_key_exists($_GET["id"], $people)) {
		$show_submission_result = 1;
		$submission_result = "You are trying to display the status for an invalid user.";
		app_log('A user at ' . get_ip() . ' tried to display the status of user ' . $_GET['id'] . ' which is an invalid user.');
		return;
	}
	$specified_user = $people[$_GET['id']];
    if($specified_user->time_of_last_submission) {
	    $show_submission_result = 1;
        $last_processed = "Your most recent submission processed is the one submitted around " . date("H:i:s", $specified_user->time_of_last_submission) .". <br />";
    } else {
        $show_submission_result = 0;
        $last_processed = "";
    }
	//$submission_result = "Your most recent submission's status is: ";
	switch ($specified_user->last_status_code) {
		case "-1":
			$submission_msg = "You haven't submitted anything yet or your submission hasn't been processed.";
			$submission_result = "You haven't submitted anything yet or your submission hasn't been processed.";
			break;
		case "0":
			$submission_result = $last_processed . "Submission ok!";
			$submission_msg = "Submission ok!";
			break;
		case "1":
			$submission_result = $last_processed . "Compile failure.";
			$submission_msg = "Compile failure."; //. $specified_user->last_compiled_log_filename;
			$show_submission_result = 2;
			/*$compile_log = file($specified_user->last_compile_log_filename, 1);
			if ($compile_log === false) {
				$submission_msg = "Compile failure but compile log could not be read. Notify a proctor.";
//				$submission_result = "Compile failure but compile log could not be read. Notify a proctor.";
				$show_submission_result = 1;
				// TODO log
			}*/
			break;
		case "2":
			$submission_msg = "Program failed test";
//			$submission_result = $last_processed . "Program failed test.";
			break;
		case "3":
			$submission_msg = "Program timed out while running";
//			$submission_result = $last_processed . "Program timed out while running.";
			break;
		case "4":
			$submission_msg = "Program crashed while running.";
//			$submission_result = $last_processed . "Program crashed while running.";
			break;
		case "5":
			$submission_msg = "Unknown error while running program.";
//			$submission_result = "Unknown error while running program.";
			// TODO log
			break;
		case "6":
			$submission_msg = "Attempt to access files while running program.";
//			$submission_result = "Unknown error while running program.";
			// TODO log
			break;
		case "7":
			$submission_msg = "Processing...";
//			$submission_result = "Unknown error while running program.";
			// TODO log
			break;
        case "8":
            $submission_msg = "Attempt to use fork while running program.";
            break;
		default:
			$submission_msg = "Unknown error.";
			$submission_result = "Unknown error.";
			app_log(sprintf("ERROR: User %d has a last_status_code of %d which is invalid. (scoreboard status display)", $specified_user->user_id, $specified_user->last_status_code));
			break;
	}
	$submission_result = $last_processed."<br /><b>".$submission_msg."</b>";
        $submission_result = "<span>" . $submission_result . "</span>";
}
?>
