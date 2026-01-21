<?php
/**
 * CanliTV API'den token alıp dosyaya kaydeden PHP scripti
 */

function get_canli_tv_token() {
    $url = "https://core-api.kablowebtv.com/api/channels";
    
    // Parametreleri ekle - Python kodundaki gibi checkip=false
    $url_with_params = $url . "?checkip=false";
    
    $headers = [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36",
        "Referer: https://tvheryerde.com",
        "Origin: https://tvheryerde.com",
        "Cache-Control: max-age=0",
        "Connection: keep-alive",
        "Accept-Encoding: gzip, deflate",
        "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJjZ2QiOiIwOTNkNzIwYS01MDJjLTQxZWQtYTgwZi0yYjgxNjk4NGZiOTUiLCJkaSI6IjBmYTAzNTlkLWExOWItNDFiMi05ZTczLTI5ZWNiNjk2OTY0MCIsImFwdiI6IjEuMC4wIiwiZW52IjoiTElWRSIsImFibiI6IjEwMDAiLCJzcGdkIjoiYTA5MDg3ODQtZDEyOC00NjFmLWI3NmItYTU3ZGViMWI4MGNjIiwiaWNoIjoiMCIsInNnZCI6ImViODc3NDRjLTk4NDItNDUwNy05YjBhLTQ0N2RmYjg2NjJhZCIsImlkbSI6IjAiLCJkY3QiOiIzRUY3NSIsImlhIjoiOjpmZmZmOjE0NC4yMC4xNDAuNSIsImNzaCI6IlRSS1NUIiwiaXBiIjoiMCJ9.dnAkqoQBQc8fTpJt40PHr8jXW8Y3WgnzZR1Zrz-TLQ8"
    ];

    echo "📡 CanliTV API'den veri alınıyor...\n";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_with_params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Gzip desteği ekle
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL hatası: " . $error);
        }
        
        if ($http_code !== 200) {
            throw new Exception("HTTP hatası: " . $http_code);
        }
        
        if (empty($response)) {
            throw new Exception("API boş yanıt döndü");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON çözme hatası: " . json_last_error_msg());
        }
        
        if (!$data || !isset($data['IsSucceeded']) || $data['IsSucceeded'] !== true) {
            throw new Exception("API başarısız yanıt döndü");
        }
        
        if (!isset($data['Data']['AllChannels']) || !is_array($data['Data']['AllChannels'])) {
            throw new Exception("Kanal verisi bulunamadı");
        }
        
        $channels = $data['Data']['AllChannels'];
        $channel_count = count($channels);
        
        echo "✅ {$channel_count} kanal bulundu\n";
        
        if ($channel_count === 0) {
            throw new Exception("Hiç kanal bulunamadı");
        }
        
        // İlk kanalın stream URL'sini kontrol et
        if (!isset($channels[0]['StreamData']['HlsStreamUrl'])) {
            throw new Exception("İlk kanalda HLS stream URL bulunamadı");
        }
        
        $streamUrl = $channels[0]['StreamData']['HlsStreamUrl'];
        
        // Token'ı stream URL'sinden çıkar
        if (preg_match('/wmsAuthSign=(.*?)(?:$|&)/', $streamUrl, $matches)) {
            $token = $matches[1];
            
            // Token'ı dosyaya yaz
            $result = file_put_contents('token.txt', $token);
            
            if ($result === false) {
                throw new Exception("Token dosyaya yazılamadı");
            }
            
            echo "✅ Token başarıyla güncellendi: " . substr($token, 0, 20) . "...\n";
            echo "📁 Token 'token.txt' dosyasına kaydedildi\n";
            
            return true;
        } else {
            throw new Exception('Token stream URL içinde bulunamadı!');
        }
        
    } catch (Exception $e) {
        echo "❌ Hata: " . $e->getMessage() . "\n";
        return false;
    }
}

// Script çalıştırma
if (php_sapi_name() === 'cli') {
    get_canli_tv_token();
} else {
    echo "<pre>";
    get_canli_tv_token();
    echo "</pre>";
}
?>
