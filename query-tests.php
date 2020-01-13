<?php
require_once 'Helpers.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$connection = dbConnect('mysql:host=localhost;dbname=gagraphi_demo', 'gagraphi_uza', 'bmehjBc9');

$sqlInstructions = "SELECT * FROM payment_instructions WHERE status = 1";

$instructionRows = getRows( $connection, $sqlInstructions, 'query');
$instructions = [];

foreach( $instructionRows as $instructionRow ):
    $instructions[] = sprintf("<p><b>%s</b><br/> %s</p>",$instructionRow['method'], $instructionRow['instructions']);
endforeach;

$payStr = join($instructions,'');

echo $payStr;

$connection = null;