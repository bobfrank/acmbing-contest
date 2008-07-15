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
rank_users();
?>

  <table cellspacing="0" cellpadding="3" border="1">
    <thead>
    <tr align="center">
      <th rowspan="2">Name</th>
      <?php
	  for ($i = 1; $i <= $NUM_PROBLEMS; $i++) {
	      printf("<th colspan=\"2\">%s</th>\n", $i);
	  }
      ?>
      <th colspan="2">Total</th>
    </tr>
    <tr>
    <?php
	for ($i = 1; $i <= $NUM_PROBLEMS; $i++) {
	    echo "<td align=\"center\">T</td>";
	    echo "<td align=\"center\">N</td>";
	}
    ?>
    <td>Total Time</td>
    <td>Solved</td>
    </tr>
    </thead>
    <tbody>
<?php
$row= false;
$user_id=$_GET["id"];
foreach ($ranked_user_ids as $ranked_user_id) {
    $person = $people[$ranked_user_id];
    // change the color of alternating rows.

    if ($user_id == $ranked_user_id) { 
	echo "    <tr class=\"highlight\">";
    } else {
    if ($row == true) {
	echo "    <tr class=\"even\">";
	$row = false;
    } else {
	echo "    <tr class=\"odd\">";
        $row = true;
    }
    }

    printf("      <td>%s</td>", $person->name);
    for ($i = 1; $i <= $NUM_PROBLEMS; ++$i) {
	printf("      <td align=\"center\" class=\"timecol\">%s</td><td align=\"right\" class=\"numcol\">%s</td>", 
	$person->problems[$i]->time, $person->problems[$i]->submissions );
    }
    printf("      <td>%s</td>", seconds_to_time($person->total_seconds()));
    printf("      <td>%s</td>", $person->total_problems_solved());
    echo "    </tr>";
}
?>
  </tbody>
  </table>

