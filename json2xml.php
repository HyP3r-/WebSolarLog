<?php
// Require Array2XML class which takes a PHP array and changes it to XML
  require_once('utils/array2xml.php');

  // Gets JSON file
    $curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $_GET['url']);
    curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($curlSession);
    curl_close($curlSession);

  // Decodes JSON into a PHP array
  $php_array['container'] = json_decode($json, true);

  // adding Content Type
  header("content-type: text/xml");

  // Converts PHP Array to XML with the root element being 'root-element-here'

$xml = Array2XML::createXML('websolarlog', $php_array);
echo $xml->saveXML();
?>
