<?php
/**
 * Copyright © 2015, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file Notifier.php
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

    public function __construct(&$CONF)
    {
        $this->CONF = $CONF;

        // Mail
        $this->transport = $this->getTransport();
        // Create the Mailer using your created Transport
        $this->mailer = \Swift_Mailer::newInstance($this->transport);
    }

    protected function getTransport()
    {
        if (isset($this->CONF['mailer']) &&
            isset($this->CONF['mailer']['type']) &&
            strtolower($this->CONF['mailer']['type']) == "smtp") {
            $transport = \Swift_SmtpTransport::newInstance();

            if (!empty($this->CONF['mailer']['host'])) {
                $transport->setHost($this->CONF['mailer']['host']);
            } else {
                $transport->setHost('localhost');
            }

            if (!empty($this->CONF['mailer']['port'])) {
                $transport->setPort((int) $this->CONF['mailer']['port']);
            } else {
                $transport->setPort(25);
            }

            if (!empty($this->CONF['mailer']['encryption']) &&
                (strtolower($this->CONF['mailer']['encryption']) == "tls" ||
                    strtolower($this->CONF['mailer']['encryption']) == "ssl")) {
                $transport->setEncryption($this->CONF['mailer']['encryption']);
            }

            if (!empty($this->CONF['mailer']['username'])) {
                $transport->setUsername($this->CONF['mailer']['username']);
            }

            if (!empty($this->CONF['mailer']['password'])) {
                $transport->setPassword($this->CONF['mailer']['password']);
            }

            return $transport;
        } else {
            return \Swift_MailTransport::newInstance();
        }
    }

    public function notifyDown($url)
    {
        $vars = array(
            'subject' => '[Website down] ' . $url . ' is not reachable.',
            'title' => $url . ' is down',
            'message' => $url . ' is not reachable at ' . date('Y-m-d H:i') . '.',
        );

        $template = $this->getEmailTemplate();
        foreach ($vars as $key => $value) {
            $template = str_replace('{{ ' . $key . ' }}', $value, $template);
        }

        // Mail
        $this->mailer->send($this->getSwiftMessage($vars, $template));
    }

    public function notifyUp($url)
    {
        $vars = array(
            'subject' => '[Website up] ' . $url . ' is reachable again.',
            'title' => $url . ' is up',
            'message' => $url . ' is reachable again at ' . date('Y-m-d H:i') . '.',
        );

        $template = $this->getEmailTemplate();
        foreach ($vars as $key => $value) {
            $template = str_replace('{{ ' . $key . ' }}', $value, $template);
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

                                     return $message;
                                 }

                                 public function getEmailTemplate()
    {
                                     return file_get_contents(BASE_FOLDER . '/resources/emails/alert.html');
                                 }
                             }
