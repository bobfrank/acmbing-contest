<?php
global $curr_contest, $db, $num_problems;

$contest_id = -1;
if(array_key_exists('contest_id', $_GET)) {
    $contest_id = $_GET['contest_id'];
//TODO set $curr_contest
}
else {
    $contest_list = array();
    $result_set = $db->get_contests();

    $mrt = time();

    foreach($result_set as $row)
    {
        //get active contests...
        $sz = (int)$row['start'] + (int)$row['length'];
        if((int)$row['start'] < time() && (int)$row['start']+(int)$row['length'] > time()) {
            $contest_list[] = $row;
        } else if($sz < time() && $mrt < $sz) { // if we don't have anything, then we want the next best...
           $mrt = $sz;
           $curr_contest = $row;
           $contest_id = $row['contest_id'];
        }
    }

    if(count($contest_list) == 1) {
        $contest_id = $contest_list[0]['contest_id'];
        $curr_contest = $contest_list[0];
    }
    else {
        echo "<b>Active Contests:</b><br/>"; //more details can be easily added by adding more fields to the contests table
        foreach($contest_list as $row) {
            echo "<a href='?contest_id=" . $row['contest_id'] . "'>" . date(DATE_RFC822, $row['start']) . "</a><br/>";
        }
    }
}

$num_problems = 0;

if($contest_id >= 0) {
    $result_set = $db->get_testcases($contest_id);

    $problems = array();

    foreach($result_set as $row)
    {
        if(intval($row['problem']) > $num_problems) {
            $num_problems = intval($row['problem']);
        }
    }

    $num_problems += 1;
}
?>