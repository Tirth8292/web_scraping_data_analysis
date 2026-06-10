<?php
$otp = rand(1000,9999);
$to = $email_id;
$sub = "Otp";
$message = "The OTP is : " . $otp;

$result = mail($to,$sub,$message);

?>