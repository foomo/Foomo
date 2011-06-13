<?php

namespace Foomo;

Frontend::setUpToolbox();

header('Content-Type: text/plain');

Session\GC::run();