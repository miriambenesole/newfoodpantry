<?php
set_include_path('/Applications/PHPMailer-master');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '/Applications/PHPMailer-master/src/PHPMailerAutoload.php';
require '/Applications/PHPMailer-master/src/Exception.php';
require '/Applications/PHPMailer-master/src/PHPMailer.php';
require '/Applications/PHPMailer-master/src/SMTP.php';

ini_set("pcre.jit", "0");

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'mbenesole@gmail.com';                     //SMTP username
    $mail->Password   = 'xxxxxxxxxxxxxx';                               //SMTP password
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;     
    $mail->SMTPSecure = 'tls';
    //Enable implicit TLS encryption
    $mail->Port       = 587;      
    
    $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
      )
  );
  
  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('mbenesole@gmail.com', 'Mailer');
    $mail->addAddress('benesole@gmail.com', 'Joe User');     //Add a recipient
    // $mail->addAddress('ellen@example.com');               //Name is optional
    $mail->addReplyTo('mbenesole@gmail.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $