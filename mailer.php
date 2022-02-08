<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';
require_once 'service.php';

class Mailer
{
    public PHPMailer $mail;
    public Service $service;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->service = new Service();
    }

    public function SendEmailReworkMonth($worker_id, $month, $message): string
    {
        $worker = $this->service->GetWorkerWithId($worker_id);
        try {                   //Enable verbose debug output
            $this->mail->isSMTP();
            $this->mail->CharSet = "UTF-8";
            $this->mail->Host       = 'mail.hostmaster.sk';                     //Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $this->mail->Username   = 'noreply@josgroup.sk';                     //SMTP username
            $this->mail->Password   = '000000';                               //SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $this->mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $this->mail->setFrom('noreply@josgroup.sk', 'Jan Osadsky');
            $this->mail->addAddress($worker->email, $worker->GetFullName());
            $this->mail->addReplyTo('osadsky.jan@josgroup.sk', 'Jan Osadsky');

            //Content
            $this->mail->isHTML(true);                                  //Set email format to HTML
            $this->mail->Subject = "Hodiny $month problém";
            $this->mail->Body    = "<p>Vaše hodiny boli vrátené na prepracovanie. Vysvetlenie:</p><p>$message</p>
                                    <p>Hodiny nájdete <a href='https://josgroup.sk/hodiny/month_view.php?id=$worker_id&m=$month'>
                                    tu</a> (opraviť sa dajú cez <a href='https://josgroup.sk/hodiny/'>úvodnú stránku</a>).</p>";
            $this->mail->AltBody = "Vaše hodiny boli vrátené na prepracovanie. Vysvetlenie: ".$message;

            $this->mail->send();
            $this->mail->smtpClose();
            $this->mail->clearAllRecipients();;
            $this->mail->clearReplyTos();
            return "Ok";
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function SendEmailCloseMonth($worker_id, $month): string
    {
        $worker = $this->service->GetWorkerWithId($worker_id);
        try {
            $this->mail->isSMTP();
            $this->mail->CharSet = "UTF-8";
            $this->mail->Host       = 'mail.hostmaster.sk';                     //Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $this->mail->Username   = 'noreply@josgroup.sk';                     //SMTP username
            $this->mail->Password   = '000000';                               //SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $this->mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $worker_full_name = $worker->GetFullName();
            $this->mail->setFrom('noreply@josgroup.sk', $worker_full_name);
            $this->mail->addAddress("osadsky.jan@josgroup.sk", "Jan Osadsky");
            $this->mail->addAddress($worker->email, $worker_full_name);
            $this->mail->addReplyTo($worker->email, $worker_full_name);

            //Content
            $this->mail->isHTML(true);                                  //Set email format to HTML
            $this->mail->Subject = "Hodiny $worker_full_name $month uzavreté";
            $this->mail->Body    = "<p>Hodiny používateľa $worker_full_name za mesiac $month boli uzavreté.
                                    <p>Hodiny nájdete <a href='https://josgroup.sk/hodiny/month_view.php?id=$worker_id&m=$month'>
                                    tu</a>.</p>";
            $this->mail->AltBody = "Hodiny používateľa $worker_full_name za mesiac $month boli uzavreté.";

            $this->mail->send();
            $this->mail->smtpClose();
            $this->mail->clearAllRecipients();;
            $this->mail->clearReplyTos();
            return "Ok";
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}