<?php


namespace App\Application\Mail;

use App\Components\Mail\Mail;
use App\Interfaces\Mailable;

/**
 * Class UserMail
 * @package App\Application\Mail
 */
class UserMail implements Mailable
{
    private ?string $subject=null;

    /**
     * UserMail constructor.
     * @param string $subject
     */
    public function __construct(?string $subject=null)
    {
        $this->subject = $subject;
    }

    /**
     * @param Mail $mail
     * @return Mail
     */
    public function __invoke(Mail $mail): Mail
    {
        $mail->setFrom('dilsizkaval@windowslive.com');
        $mail->setSubject($this->subject);
        $mail->setBody('lorem upsem dolar');
        $mail->setView(view('index'));
        return $mail;
    }
}
