<?php
include('be.inc');

function get_output_list()
{
    global $outputs, $contest_id, $user_id, $db;
    $submitted_id = intval($_GET['submitted_id']);

    $outputs = $db->get_output($submitted_id);
}

get_output_list();

echo "<table border=1>";
echo "<tr><td><b>id</b></td><td><b>input</b></td><td><b>returned</b></td><td><b>expected</b></td></tr>\n";
$id = 0;
foreach($outputs as $row)
{
    $id+=1;
    echo "<tr><td>$id</td><td>{$row['input']}</td><td>{$row['returned']}</td><td>{$row['output']}</td></tr>\n";
}
echo "</table><br/>\n";

?>
