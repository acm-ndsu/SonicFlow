<?php

include("../http.class.php");

# our HTTP lib lets us keep a presistant connection
$http = new http();
$html = $http->get("http://www.shazam.com/");

$dom = new DOMDocument();

# hide some XML errors.  
libxml_use_internal_errors(true);
$dom->loadHTML($html);

$xp = new domxpath($dom);
$elements = $xp->query("/html/head/title");

#$elements = $xp->query("/html/body/div/div/div[2]/div[2]/ul");

if (!is_null($elements)) {
  foreach ($elements as $element) {
    echo  $element->nodeName;

    $nodes = $element->childNodes;
    foreach ($nodes as $node) {
      echo $node->nodeValue. "\n";
    }
  }
}

?>