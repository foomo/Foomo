<?php

if(file_exists(\Foomo\BasicAuth::getDefaultAuthFilename())) {
	Foomo\BasicAuth::auth('foomo-toolbox');
}
echo Foomo\MVC\ControllerHelper::runAsHtml(new Foomo\Setup\Controller());