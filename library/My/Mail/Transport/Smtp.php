<?php

require_once 'Zend/Mail/Transport/Smtp.php';
require_once 'My/Mail/Protocol/Smtp.php';

class My_Mail_Transport_Smtp extends Zend_Mail_Transport_Smtp
{
    public function _sendMail()
    {
        if (!($this->_connection instanceof My_Mail_Protocol_Smtp)) {
            $this->setConnection(new My_Mail_Protocol_Smtp($this->_host, $this->_port, $this->_config));
            $this->_connection->connect();
            $this->_connection->helo($this->_name);
        } else {
            $this->_connection->rset();
        }

        $this->_connection->mail($this->_mail->getReturnPath());

        foreach ($this->_mail->getRecipients() as $recipient) {
            $this->_connection->rcpt($recipient);
        }

        $this->_connection->data($this->header . Zend_Mime::LINEEND . $this->body);
    }
}
