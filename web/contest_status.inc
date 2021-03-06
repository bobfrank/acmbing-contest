<?php
require_once('fe.inc');

/*$times_file = $contest_root . '/manager/conf/times.txt';*/

//ini_set("include_path", ".:../");

require_once('logging.inc');

function process_contest_status() {
	global $times_file;     // input
	global $contest_status; // output - 0: hasn't started, 1: in progress, 2: finished, 3: hasn't started with countdown
	global $start_time;     // output
	global $time_left;      // output - formatted as a string of our normal time format (24 hour hh:mm:ss)
    global $curr_contest;   // input

//print_r($curr_contest);
    $start_time = (int)$curr_contest["start"];
    $left = (int)$curr_contest["start"] + (int)$curr_contest["length"] - time();
    $time_left = seconds_to_time($left);

    if($time_left < 0) {
        $contest_status = 2;
    }
    else if(time() < (int)$curr_contest["start"]) {
        $contest_status = 0;
    }
    else {
        $contest_status = 1;
    }

/*
	$times = file($times_file, 1);
	if ($times === false) {
		$contest_status = 0;
		app_log('ERROR: Could not open times.txt.');
		return;
	}
	if (count($times) < 2) { // leaving times.txt blank indicates contest hasn't started yet
		$contest_status = 0;
	} else {
		$start_time = strtotime($times[0]); // unix time format
		$contest_length_in_seconds = time_to_seconds($times[1]);
		$current_time = time(); // unix time format
		if ($current_time < $start_time) { // do countdown
			$contest_status = 3;
			$seconds_left = $start_time - $current_time;
			$time_left = seconds_to_time($seconds_left);
		} else {
			$end_time = $start_time + $contest_length_in_seconds; // unix time format (seconds since epoch)
			$seconds_left = $end_time - $current_time;
			if ($seconds_left <= 0) {
				$contest_status = 2;
			} else {
				$contest_status = 1;
				$time_left = seconds_to_time($seconds_left);
			}
		}
	}*/
}
?>
