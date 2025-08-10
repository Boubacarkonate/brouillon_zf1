<?php
// Test sans vérification
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$fp = @stream_socket_client(
    "ssl://smtp.gmail.com:465",
    $errno,
    $errstr,
    30,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$fp) {
    echo "Connexion sans vérif SSL échouée : $errstr ($errno)\n";
} else {
    echo "Connexion sans vérif SSL réussie\n";
    fclose($fp);
}

// Test avec vérification
$context = stream_context_create([
    'ssl' => [
        'cafile' => 'C:\\wamp64\\bin\\php\\cacert.pem',
        'verify_peer' => true,
        'verify_peer_name' => true,
    ]
]);

$fp = @stream_socket_client(
    "ssl://smtp.gmail.com:465",
    $errno,
    $errstr,
    30,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$fp) {
    echo "Connexion avec vérif SSL échouée : $errstr ($errno)\n";
} else {
    echo "Connexion avec vérif SSL réussie\n";
    fclose($fp);
}
