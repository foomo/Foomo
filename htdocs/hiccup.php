<?php

namespace Foomo;

Frontend::setUpToolbox();

echo MVC\ControllerHelper::runAsHtml(new Hiccup\Controller());