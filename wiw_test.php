<?php
include 'wheniwork-api-php/src/Wheniwork.php';

$response = Wheniwork::login('developer_key', 'michael.morrow@mavs.uta.edu', 'rLyeZ321');

print_r($response);

?>