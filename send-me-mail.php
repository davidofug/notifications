<?php
$from = 'Gagawala <hello@gagawala.com>';
$headers = [
    'Content-type' => 'text/html; charset=iso-8859-1',
    'IME-Version' => '1.0',
    'From' => $from,
    'Reply-To' => $from,
    'X-Mailer' => 'PHP/' . phpversion()
];

// the message
$msg = "First line of text\nSecond line of text";

// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);

// send email
if(mail("chat@david.ug","Testing send mail",$msg, $headers)) {
  echo 'Sent';
} else {
  echo 'Not sent<br/>';
  echo error_get_last()['message'];
}