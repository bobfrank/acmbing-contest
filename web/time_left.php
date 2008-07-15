<?php 
require_once("time.inc");
require_once('config.inc');
require_once('logging.inc');
require('contest_status.inc');
process_contest_status();
?>
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
