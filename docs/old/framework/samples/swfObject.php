<?php

# this sample would typically be in a template

#add the nessecary js to your doc instance
RadSWFObject2::addJsToHTMLDoc();

# Define flash file
$swf  = 'my.swf';

# set flash params,
$params = new RadSWFObject2Params();
$params->menu(false);
$params->allowFullScreen(true);
$params->allowScriptAccess(RadSWFObject2Params::ALLLOW_SCRIPT_ACCESS_ALWAYS);

# attributes,
$attributes = new RadSWFObject2Attributes();

# and flashVars.
$flashVars = new RadSWFObject2FlashVars();
$flashVars->add('history', true);

# create the object itself
$so2 = new RadSWFObject2(
	$swf, 
	'flashcontent',
	'100%',
	'100%',
	'9.0.124',
	'/swf/expressInstall.swf',
	$flashVars,
	$params,
	$attributes
);

# add javascript to the document
Foomo\HTMLDocument::getInstance()->addJavascript($so2->embedSWF());

?><div id="flashcontent">
	<div id="alternate">
		<div id="alternateInfo">
			This site sucks whithout Flash Player and JavaScript. Please <a href="http://www.adobe.com/go/getflash/" style="color:#d40032;">get Flash Player</a> and enable your Javascript to view this content as full Flash Movie.
		</div>
		<div id="alternateContent">
			<h1>Html is beautiful and important</h1>
			<p>content goes here</p>
		</div>
	</div>
</div>