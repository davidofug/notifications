<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo http_build_query([
  'ApiKey' => '',
   'ExpiringOnly' => 45,
   'CompactList' => 'no',
   'Password' => '5683Gr@ph1cs',
   'ResponseFormat' => 'JSON'
]);