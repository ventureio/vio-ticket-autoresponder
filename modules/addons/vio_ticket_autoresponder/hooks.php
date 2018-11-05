<?php

use Illuminate\Database\Capsule\Manager as DB;

require_once dirname(__FILE__) . '/include/ticket_autoresponder_addon.php';

function ticket_autoresponder_hook($params) {
    $ticketid = $params['ticketid'];
    $ticket = DB::table('tbltickets')->find($ticketid);
    $sender = $ticket->email;
    if(empty($sender) && $ticket->userid > 0) {
        $client = DB::table('tblclients')->find($ticket->userid);
        $sender = $client->email;
    }
    //$sender = 'test@ventureio.com';
    $addon = new ticket_autoresponder_addon($vars);
    $emailTemplate = $addon->autoreplyCheck();
    if(!empty($emailTemplate)) {
        autoreply_helper($sender, $emailTemplate->subject, $emailTemplate->message);
    }
}

function autoreply_helper($email, $subject, $body) {
    global $CONFIG;
    global $whmcs;
    $mail = new \PHPMailer();
    $mail->From = $CONFIG['SystemEmailsFromEmail'];
    $mail->FromName = html_entity_decode($CONFIG['SystemEmailsFromName'], ENT_QUOTES);
    $mail->CharSet = $CONFIG['Charset'];
    if ($CONFIG['MailType'] == "mail") {
        $mail->Mailer = "mail";
    } else {
        if ($CONFIG['MailType'] == "smtp") {
            $mail->IsSMTP();
            $mail->Host = $CONFIG['SMTPHost'];
            $mail->Port = $CONFIG['SMTPPort'];
            $mail->Hostname = $_SERVER['SERVER_NAME'];

            if ($CONFIG['SMTPSSL']) {
                $mail->SMTPSecure = $CONFIG['SMTPSSL'];
            }


            if ($CONFIG['SMTPUsername']) {
                $mail->SMTPAuth = true;
                $mail->Username = $CONFIG['SMTPUsername'];
                $mail->Password = decrypt($CONFIG['SMTPPassword']);
            }

            $mail->Sender = $mail->From;
        }
    }

    $mail->isHTML(true);
    $mail->XMailer = "WHMCS v" . $whmcs->get_config("Version");
    $mail->Body = '<html><head></head><body>' . html_entity_decode($body) . '</body></html>';
    $mail->Subject = $subject;
    $mail->addAddress($email);
    return $mail->send();
}

add_hook('TicketOpen', 1, 'ticket_autoresponder_hook');