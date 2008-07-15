<?
require_once('be.inc');
global $db;

echo "<b>Contest List</b><br/><br/>";

if(array_key_exists('hour',$_POST)) {
    $dt = mktime($_POST['hour'], $_POST['minute'], 0, $_POST['month'], $_POST['day'], $_POST['year']);
    $db->add_contest($dt, 7200);
    echo "<script>top.location='./';</script>";
}

$contests = $db->get_contests();
foreach($contests as $row)
{
    echo "<a href='contest.php?contest_id=".$row['contest_id']."'>".date('l dS \of F Y h:i:s A',$row['start'])."</a><br/>\n";
}
?>
<br/><br/>
<b>New Contest</b>
<form method='post' action='index.php'>
<table>
<tr><td>MM/DD/YYYY</td><td><input name='month' size='2'>/<input name='day' size='2'>/<input name='year' size='4'></td></tr>
<tr><td>HH:MM</td><td><input name='hour' size='2'>:<input name='minute' size='2'></td></tr>
<tr><td> </td><td><input type='submit' value='Create'></td></tr>
</table>
</form>
