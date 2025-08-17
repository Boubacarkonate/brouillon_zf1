<?php
$client_id = "PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622";
$client_secret = "995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598";
$scope = "api_offresdemploiv2 o2dsoffre"; // scope autorisé dans ton espace dev

// 1. Récupérer le token
$ch = curl_init("https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    "grant_type" => "client_credentials",
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "scope" => $scope
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    die("Erreur Token : " . curl_error($ch));
}
curl_close($ch);

$data = json_decode($response, true);
$access_token = $data["access_token"] ?? null;

if (!$access_token) {
    die("Impossible de récupérer le token. Réponse brute : " . $response);
}

// 2. Appel de l’API avec le token
$ch = curl_init("https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search?motsCles=php&range=0-9");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    die("Erreur API : " . curl_error($ch));
}
curl_close($ch);

echo $result;