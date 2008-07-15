<?php

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
process_specified_user();

?>

<?php
if ($show_submission_result != 0 && $_GET['id'] != "-1") {
        echo "<div class=\"sidebar\">"; 
        echo "<div id=\"result-box\">";
	echo "  <h3>Submission Status</h3>";
	if ($show_submission_result > 2) {
		$submission_result = "Error";
		// TODO log
	}
	if ($show_submission_result >= 1) {
		printf("  <p>%s</p>", $submission_result);
		if ($show_submission_result == 2) {
			echo "  <div>";
			
			echo "<div class=\"overlay\" id=\"codebox\">";
//			echo "    Compile log:<br />";
			echo "<p align=\"center\">
			<a href=\"#\" onclick=\"show('codebox')\">Back</a>
			</p>";
			echo "    <pre>";
			foreach ($compile_log as $line) {
				echo htmlentities($line);
			}
			echo "    </pre>";
			echo "<hr /><p align=\"center\">
			<a href=\"#\" onclick=\"show('codebox')\">Back</a>
			</p>";
			echo "</div>";
			/*echo "<a href=\"#\" onclick=\"return show('codebox')\">";
			echo "Show Compile Log</a>";*/
//			echo "    </pre>";
			echo "  </div>";
		}
	}
	echo "<p>";
	echo "  <i>Please note that it may take a couple of minutes for your ";
	echo "  submissions to be processed.</i>";
	echo "</p>";
        echo "</div>";
        echo "</div>";
}
?>

