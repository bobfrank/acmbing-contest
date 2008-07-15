<?php
// Copyright 2006 Daniel Benamy <dbenamy1@binghamton.edu>
// License to be determined

require_once('fe.inc');
require_once('logging.inc');
require_once('scoreboard-functions.inc');
require('contest_status.inc');
require('time.inc');

/*$NUM_PROBLEMS=$num_problems;
$ROOT_DIR = $contest_root . '/logs/';
$users_filename = $contest_root . '/manager/conf/users.txt';
*/
function read_problems() {
	global $problems, $contest_id, $db;

    $problems = array();

    $result_set = $db->get_problem_files($contest_id);

    foreach($result_set as $row) {
        $problems[ $row['filename'] ] = $row['problem'];
    }

}

read_in_all_users();
process_contest_status();
read_problems();

// ***Output starts here***
require('header.inc');
?>
<div id="main">  
<!-- <body> -->
  <div id="content">
  <h1>ACM Coding Contest Problems</h1>
<!-- title and login name -->

  <!--Contest Time & Status-->
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
  <div id="problems">
  <ul>
  <?php
  ksort($problems);
  foreach($problems as $fname => $prob) {
  	  //$stats = stat($prob);
	  echo "<li><a href=\"problems/".$fname."\">";
	  //echo basename($prob);
      echo "Problem ".$prob." (" . strrchr($fname, '.') . ")";
	  //include($clar);
	  echo "</a></li>";
  }
  if (count($problems) == 0) {
  	echo "<li>There are no problems at this time.</li>";
  }
  ?>
  </ul>
  </div>
</div>
<?php
//echo "<hr /><pre>"; print_r($people); echo "</pre>";
require('footer.inc');
?>

