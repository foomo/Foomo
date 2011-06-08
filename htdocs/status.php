<?php

namespace Foomo;

Frontend::setUpToolbox();

header('Content-Type: text/plain');

echo Yaml::dump(
	array(
		'runMode' => Config::getMode(),
		'modules' => Modules\Manager::getEnabledModules(),
	)
);