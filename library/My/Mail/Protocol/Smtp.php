<?php

require_once 'Zend/Mail/Protocol/Smtp.php';

class My_Mail_Protocol_Smtp extends Zend_Mail_Protocol_Smtp
{
    /**
     * Overriding connect to disable SSL certificate verification (self-signed)
     */
    public function connect()
    {
        // Crée un contexte SSL personnalisé qui désactive la vérification
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ]);

        // Ouvre la connexion socket avec ce contexte SSL personnalisé
        $this->_socket = stream_socket_client(
            $this->_host . ':' . $this->_port,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->_socket) {
            throw new Zend_Mail_Protocol_Exception("Could not connect to SMTP host: $errstr ($errno)");
        }

        // Attend la réponse 220 du serveur SMTP
        $this->_expect(220);

        // Si TLS est demandé, lance la commande STARTTLS puis active le chiffrement
        if ($this->_config['ssl'] === 'tls') {
            $this->_send("EHLO " . gethostname());
            $this->_expect(250);

            $this->_send("STARTTLS");
            $this->_expect(220);

            $cryptoEnabled = stream_socket_enable_crypto(
                $this->_socket,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT,
                $context
            );

            if (!$cryptoEnabled) {
                throw new Zend_Mail_Protocol_Exception("Failed to enable TLS encryption");
            }
        }
    }
}
