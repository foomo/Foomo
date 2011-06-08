<?php

namespace Foomo;

Frontend::setUpToolbox();

header('Content-Type: text/plain');

$sessionUtils = new Session\Utils();
$sessionUtils->collectGarbage();
