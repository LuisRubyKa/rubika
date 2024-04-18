<?php
require "crypto.php";
class rubika_bot {
private $auth;
private $rsakey;
private $encryption;
private $url = "https://messengerg2c58.iranlms.ir/";
private $auth_send;
public function __construct($auth, $private_key) {
$this->auth = $auth;
$this->rsakey = $private_key;
$this->encryption = new Encryption($auth, $private_key);
$this->auth_send = $this->encryption->changeAuthType($auth);
}

public function makeMethod($method, $temp_code, $data) {
$data = [
                "input" => $data,
                "client" => [
                    "app_name" => "Main",
                    "app_version" => "3.4.3",
                    "lang_code" => "fa",
                    "package" => "app.rbmain.a",
                    "temp_code" => $temp_code,
                    "platform" => "Android"
                ],
                "method" => $method
            ];
            $data = json_encode($data);
            return $data;
            }
public function makeData($name,$input){
$method = $this->makeMethod($name,"20",$input);
$tmp = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
$this->rsakey
-----END RSA PRIVATE KEY-----
EOD;
$bot = new Encryption($this->auth,$tmp);
$data_enc = $bot->encrypt($method);
$sign = $bot->sign($data_enc);
$data = [
                "api_version" => "6",
                "auth" => $this->auth_send,
                "data_enc" => $data_enc,
                "sign" => $sign
            ];
            
            $response = $this->POST($this->url,json_encode($data));
            $enc = json_decode($response,true);
            $enc = $enc['data_enc'];
            $json = $bot->decrypt($enc);
            return $json;
}
public function POST($url, $data){
    while (true) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = ['content-type: application/json; charset=UTF-8',];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        global $ngix_error_bypass;
        if ($response != null & strpos($response, "The page you are looking for is temporarily unavailable") == false)
            {
                return $response;
            }
        else {
            continue;
        }
    }
}
public function getChats(){
return $this->makeData("getChats",["start_id" => "null"]);
}
public function getMe(){
return $this->makeData("getUserInfo",[]);
}

}
?>
