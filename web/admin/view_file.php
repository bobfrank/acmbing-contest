<?php

include('be.inc');
header("Content-type: text/plain");

echo file_get_contents ("../../files/" . str_replace("..","",$_GET['file']), "r");

?>