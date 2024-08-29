<?php

namespace App\Helpers;

class SoapHelper {

  public function arrayToXml($xmlArray, &$xml)
  {
    foreach ($xmlArray as $key => $value) {
      if (is_array($value)) {
          if (!is_numeric($key)) {
              $subnode = $xml->addChild("$key");
              $this->arrayToXml($value, $subnode);
          } else {
              $this->arrayToXml($value, $xml);
          }
      } else {
          $xml->addChild("$key");
          $xml->$key = "$value";
      }
    }

    return $xml;
  
  }

  public function soap()
  {
    $opts = [
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      ]
    ];
    $url = "https://tpsonline.beacukai.go.id/tps/service.asmx";
    $params = [
      'encoding' => 'UTF-8',
      'verifypeer' => false,
      'verifyhost' => false,
      'soap_version' => SOAP_1_2,
      'trace' => 1,
      'exceptions' => 1,
      "connection_timeout" => 180,
      'stream_context' => stream_context_create($opts)
    ];

    $soap = new \SoapClient($url . "?WSDL", $params);

    return $soap;
  }

  public function getResults($service, $string)
  {
    // preg_match('/<'.$service.'>(.*)<\/'.$service.'>/', $string, $match);
    preg_match('~<'.$service.'>([^{]*)</'.$service.'>~i', $string, $match);

    return $match[1] ?? "-";
  }
  
}

