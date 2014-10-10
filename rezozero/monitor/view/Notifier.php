<?php
/*
 * Copyright REZO ZERO 2014
 *
 *
 *
 * @file Notifier.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\view;

/**
 * Handle email notifications.
 */
class Notifier
{
    private $CONF;
    private $transport;
    private $mailer;

    public function __construct(&$CONF){
        $this->CONF = $CONF;

        // Mail
        $this->transport = \Swift_MailTransport::newInstance();
        // Create the Mailer using your created Transport
        $this->mailer = \Swift_Mailer::newInstance($this->transport);
    }

    public function notifyDown($url)
    {
        $vars = array(
            'subject' => '[Website down] '.$url.' is not reachable.',
            'title' => $url.' is down',
            'message'=> $url.' is not reachable at '.date('Y-m-d H:i').'.'
        );

        $template = $this->getEmailTemplate();
        foreach ($vars as $key => $value) {
            $template = str_replace('{{ '.$key.' }}', $value, $template);
        }

        // Mail
        $this->mailer->send($this->getSwiftMessage($vars, $template));
    }

    public function notifyUp($url)
    {
        $vars = array(
            'subject' => '[Website up] '.$url.' is reachable again.',
            'title' => $url.' is up',
            'message'=> $url.' is reachable again at '.date('Y-m-d H:i').'.'
        );

        $template = $this->getEmailTemplate();
        foreach ($vars as $key => $value) {
            $template = str_replace('{{ '.$key.' }}', $value, $template);
        }


        // Mail
        $this->mailer->send($this->getSwiftMessage($vars, $template));
    }

    private function getSwiftMessage(&$vars, $body)
    {
        // Create the message
        $message = \Swift_Message::newInstance();
        $message->setSubject($vars['subject'])

                // Set the From address with an associative array
                ->setFrom(array($this->CONF['sender'] => 'RZ Monitor'))
                // Set the To addresses with an associative array
                ->setTo($this->CONF['mail'])
                // Give it a body
                ->setBody($body, 'text/html')
                // Indicate "High" priority
                ->setPriority(1);
        ;
        return $message;
    }

    public function getEmailTemplate()
    {
        return file_get_contents(BASE_FOLDER.'/resources/emails/alert.html');
    }
}
