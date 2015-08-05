<?php
include_once('db.php');
$parts = explode('/', $_SERVER['QUERY_STRING']);
$callsign = array_shift($parts);
$request = implode('/', $parts);
$station = get_station(['callsign' => $callsign]);

if($station) {
  // Don't redirect unless needed
  $url = 'http://' . $station['base_url'] . '/' . implode("/", array_map("rawurlencode", explode("/", $request)));
  if (array_search($request, ['my_uuid', 'heartbeat', 'site-map', 'stats']) !== false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    header('Content-Type: ' . $info['content_type']);
    echo $data;
  } else {
    header('Location: ' . $url);
  }
}
