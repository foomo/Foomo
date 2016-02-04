<?php

namespace Foomo;

Frontend::setUpToolbox();

$html = MVC\ControllerHelper::runAsHtml(new Hiccup\Controller());

$headers = getallheaders();
if(empty($headers['Accept']) || strpos($headers['Accept'], 'text/html') === false) {
    header('Content-Type: text/plain; charset=utf-8');
    echo strip_tags($html);
} else {
    echo $html;    
}
