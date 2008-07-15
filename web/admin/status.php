<?
include('be.inc');
function last_update_time()
{
    global $db;
    $rows = $db->get_update_time();
    return (int)$row[0]['value'];
}

function system_status()
{
    return last_update_time() != 0;
}

echo system_status() + 0;
?>