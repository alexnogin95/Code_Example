<?php
$accessinfo = array (
'username'=>$_POST['user_login'],
'password'=>$_POST['user_passvord']
);

$req = array (
'action' => 'Database',
'report' => 'City',
);

$access = json_encode($accessinfo,JSON_UNESCAPED_UNICODE);
$request = json_encode($req,JSON_UNESCAPED_UNICODE);

$opts = array('socket' => array('bindto' => '141.8.198.105:0'));
$context = stream_context_create($opts);

$access = json_encode($accessinfo,JSON_UNESCAPED_UNICODE);
$request = json_encode($req,JSON_UNESCAPED_UNICODE);


$client = new SoapClient('https://api.safekids.asterit.ru/api.php?wsdl', array('trace' => true, 'stream_context' => $context));

try{
  $result = $client->doRequest(['accessinfo' => $access, 'request' => $request]);

}catch (\Exception $e){
throw new \Exception("Soup Request Failed! Response:\n".$client->__getLastResponse());
}

foreach ($result as $key => $value) {
 $r=stristr($key . "  ".$value,'{');
}

$data_db = json_decode($r, true);
$cityes = $data_db['data'];

?>