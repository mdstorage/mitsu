<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 5.2.17
 * Time: 17.33
 */

namespace Catalog\CommonBundle\Services;


class Mailer
{
    protected $mailer;
    public function __construct(\Swift_Mailer $mailer) {
        $this->mailer = $mailer;
    }
    public function send($name, $email, $message = "", $url){

        $message = \Swift_Message::newInstance()
            ->setSubject('Сообщение от ' . $name . ', с адреса: ' . $url)
            ->setFrom('vincatsupport@yandex.ru')
            ->setTo('vincatsupport@yandex.ru')
            ->setBody('Имя: ' . $name . ' Email: '.$email. ' Адрес: ' . $url . ' Сообщение: ' . $message);
        if ($this->mailer->send($message)) {
            return true;
        } else {
            return false;
        }
    }
}