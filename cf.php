<?php

$api_key = 'global-apikey';

$email = 'mail';

$new_ip = 'yeni-ip';

$api_url = 'https://api.cloudflare.com/client/v4/';


function getAllZones($api_url, $api_key, $email) {
    $url = $api_url . 'zones';
    $headers = [
        'X-Auth-Email: ' . $email,
        'X-Auth-Key: ' . $api_key,
        'Content-Type: application/json',
    ];

    $response = makeApiRequest($url, 'GET', [], $headers);

    if ($response['success']) {
        return $response['result'];
    } else {
        echo 'Hata: ' . $response['errors'][0]['message'] . PHP_EOL;
        return [];
    }
}

function getDnsRecords($api_url, $api_key, $email, $zoneId) {
    $url = $api_url . 'zones/' . $zoneId . '/dns_records?type=A';
    $headers = [
        'X-Auth-Email: ' . $email,
        'X-Auth-Key: ' . $api_key,
        'Content-Type: application/json',
    ];

    $response = makeApiRequest($url, 'GET', [], $headers);

    if ($response['success']) {
        return $response['result'];
    } else {
        echo 'Hata: ' . $response['errors'][0]['message'] . PHP_EOL;
        return [];
    }
}

function updateDnsRecords($api_url, $api_key, $email, $zoneId, $dnsRecords, $new_ip) {
    foreach ($dnsRecords as $record) {
        $url = $api_url . 'zones/' . $zoneId . '/dns_records/' . $record['id'];
        $data = [
            'type' => $record['type'],
            'name' => $record['name'],
            'content' => $new_ip,
            'ttl' => $record['ttl'],
            'proxied' => $record['proxied'],
        ];

        $headers = [
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $api_key,
            'Content-Type: application/json',
        ];

        $response = makeApiRequest($url, 'PUT', $data, $headers);

        if ($response['success']) {
            echo 'DNS kaydı güncellendi: ' . $record['name'] . PHP_EOL;
        } else {
            echo 'Hata: ' . $response['errors'][0]['message'] . PHP_EOL;
        }
    }
}

function makeApiRequest($url, $method = 'GET', $data = [], $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'CURL Hatası: ' . curl_error($ch) . PHP_EOL;
    }

    curl_close($ch);

    return json_decode($response, true);
}

$zones = getAllZones($api_url, $api_key, $email);

foreach ($zones as $zone) {
    $zoneId = $zone['id'];

    $dnsRecords = getDnsRecords($api_url, $api_key, $email, $zoneId);

    updateDnsRecords($api_url, $api_key, $email, $zoneId, $dnsRecords, $new_ip);
}

?>
