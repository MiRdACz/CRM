<?php
namespace App\Model;

use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;


class EmailModel
{
    private $content;
    private $email;
    private $subject;
    private $emailTo;
    private $sslHost;
    private $sslUser;
    private $sslPass;
    private $sslsec;
    //navic pro databaze
    use Nette\SmartObject;
    /** @var Nette\Database\Explorer */
    private $database;

    public function __construct($sslHost = 'smtp.onebit.cz' ,$sslUser = 'noreply@apartmany-bily-kostel.cz',$sslPass = 'rG5lO#dd35Vh#@vWs',$sslsec = 'ssl',Nette\Database\Explorer $database)
    {
        $this->sslHost = $sslHost;
        $this->sslUser = $sslUser;
        $this->sslPass = $sslPass;
        $this->sslsec = $sslsec;

        $this->database = $database;
    }

    public function messageFrom($email){
        $this->email = $email;
        return $this;
    }

    public function messageContent($content){
        $this->content = $content;
        return $this;
    }

    public function messageSubject($subject){
        $this->subject = $subject;
        return $this;
    }

    public function messageTo($email){
        $this->emailTo = $email;
        return $this;
    }


    public function sendEmail(){
        $mail = new Message;

        $latte = new \Latte\Engine;
        $params = [
            'body' => $this->content,
            'predmet' => $this->subject,
        ];

        $mail->setFrom('noreply@apartmany-bily-kostel.cz', 'Apartmány Bílý Kostel');
        $mail->addTo($this->emailTo);
        $mail->setSubject($this->subject);
        $mail->setHtmlBody(
            $latte->renderToString(dirname(__DIR__) . '/Services/email.latte', $params),'./img/'
            );

        $mailer = new Nette\Mail\SmtpMailer(['host' => $this->sslHost, //  pokud není nastaven, použijí se hodnoty z php.ini
            'username' => $this->sslUser,
            'password' => $this->sslPass,
            'secure' => $this->sslsec,
        ]);

        return $mailer->send($mail);
    }

    public function userEmail(){
        $mail = new Message;

        $latte = new \Latte\Engine;
        $params = [
            'body' => $this->content,
            'predmet' => $this->subject,
        ];

        $mail->setFrom('noreply@apartmany-bily-kostel.cz', 'Apartmány Bílý Kostel');
        $mail->addTo($this->emailTo);
        $mail->setSubject($this->subject);
        $mail->setHtmlBody(
            $latte->renderToString(dirname(__DIR__) . '/Services/email.latte', $params),'./img/'
        );
        $mailer = new Nette\Mail\SmtpMailer(['host' => $this->sslHost, //  pokud není nastaven, použijí se hodnoty z php.ini
            'username' => $this->sslUser,
            'password' => $this->sslPass,
            'secure' => $this->sslsec,]);
        return $mailer->send($mail);
    }
}