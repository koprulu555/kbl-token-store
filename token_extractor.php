<?php
$url = "https://core-api.kablowebtv.com/api/channels";
$headers = [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36",
    "Authorization: Bearer eyJlbnYiOiJMSVZFIiwiaXBiIjoiMCIsImNnZCI6IjA5M2Q3MjBhLTUwMmMtNDFlZC1hODBmLTJiODE2OTg0ZmI5NSIsImNzaCI6IlRSS1NUIiwiZGN0IjoiRTFDNjQiLCJkaSI6Ijg5MTlmNjYwLTBhZGUtNGYwMS1hMTVlLTc2MDZjNjI4ZTc5MyIsInNnZCI6IjM5MTY0ZjIwLTZlZjUtNDRlZS04ZjAyLWEzODRjOTg1ZTY5MyIsInNwZ2QiOiI5ZjJlYWE1NC01NDM2LTQ0ZTgtYTkyNy00MzQ2NjlkMTU1MWEiLCJpY2giOiIwIiwiaWRtIjoiMCIsImlhIjoiOjpmZmZmOjEwLjAuMC41IiwiYXB2IjoiMS4wLjAiLCJhYm4iOiIxMDAwIiwibmJmIjoxNzQzNDY1MzY5LCJleHAiOjE3NDM0NjU0MjksImlhdCI6MTc0MzQ2NTM2OX0.YWdVfOL5hEZTrd4f4qkmPCPmUUlaiG7I2REW5H0p6Gw"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!$data || !isset($data['Data']['AllChannels'][0]['StreamData']['HlsStreamUrl'])) {
    die('API yanıtı geçersiz veya kanal bulunamadı');
}

// Token'ı stream URL'sinden çıkar
$streamUrl = $data['Data']['AllChannels'][0]['StreamData']['HlsStreamUrl'];
if (preg_match('/wmsAuthSign=(.*?)(?:$|&)/', $streamUrl, $matches)) {
    $token = $matches[1];
    file_put_contents('token.txt', $token);
    echo "Token güncellendi: " . substr($token, 0, 20) . "...";
} else {
    die('Token bulunamadı!');
}
?>
