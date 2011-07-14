<?php

header('Content-Type: text/plain');

$a = array('a' => 'one', 'b' => 'two');
$b = array('a' => 'three', 'c' => 'four');

$c = array_merge($a, $b);
$d = array_merge($b, $a);

#$view->link("[YES, proceed]", 'setupone');
#$view->link("[YES, proceed]", 'setupone', array($model->currentResourceName));
#$view->link("[YES, proceed]", 'setupone', array($model->currentResourceName), 'test');

#\Foomo\Zugspitze\Module::getm;

#echo '1 = ' . gettype(1) . PHP_EOL;
#echo '1.3 = ' . gettype(1.3) . PHP_EOL;
#echo 'array = ' . gettype(array('oubg')) . PHP_EOL;
#echo 'true = ' . gettype(true) . PHP_EOL;

#echo \Foomo\Config::getHtdocsDir('Foomo.Test');

#get_called_class()


#\Foomo\Config::getModuleDir($module) . '/docs';

#\Foomo\MVC::redirect($action, $parameters);

#$test = json_decode('{"action":"showModuleDocs","parameters":["Foomo.Docs"]}', true);
#$test = json_decode('{"a":1,"b":2,"c":3,"d":4,"e":5}', true);
#var_dump(htmlentities('{"action":"showModuleDocs","parameters":["Foomo.Docs"]}', ENT_QUOTES, UTF-8));



/*
function DoHTMLEntities ($string) {
    $trans_tbl[chr(145)] = '&#8216;';
    $trans_tbl[chr(146)] = '&#8217;';
    $trans_tbl[chr(147)] = '&#8220;';
    $trans_tbl[chr(148)] = '&#8221;';
    $trans_tbl[chr(142)] = '&eacute;';
    $trans_tbl[chr(150)] = '&#8211;';
    $trans_tbl[chr(151)] = '&#8212;';
    return strtr ($string, $trans_tbl);
}
*/

#var_dump(DoHTMLEntities('{&#8220;action&#8221;:&#8221;showModuleDocs&#8221;,&#8221;parameters&#8221;:["Foomo.Docs"]}'));

#$reflection = new ReflectionClass('Foomo\\Docs\\Frontend');
#$method = $reflection->getMethod('__construct');
#$method->getParameters();

#var_dump(true);
#var_dump(13);
#var_dump(13.3);
#var_dump('My string');
#var_dump(array('foo', 'bar'));
#var_dump(array('foo' => 'bar', 'bar' => 'foo'));


echo \Foomo\Wordpress\Module::getBaseDir() . PHP_EOL;
echo \Foomo\Wordpress\Module::getPluginsDir() . PHP_EOL;
echo \Foomo\Wordpress\Module::getPluginsPath() . PHP_EOL;
echo \Foomo\Wordpress\Module::getThemesDir() . PHP_EOL;
echo \Foomo\Wordpress\Module::getThemesPath() . PHP_EOL;
echo \Foomo\Wordpress\Module::getWordpressDir() . PHP_EOL;
echo \Foomo\Wordpress\Module::getWordpressPath() . PHP_EOL;

echo PHP_EOL;
echo PHP_EOL;

echo \Foomo\Site\Module::getBaseDir() . PHP_EOL;
echo \Foomo\Site\Module::getCacheDir() . PHP_EOL;
echo \Foomo\Site\Module::getCachePath() . PHP_EOL;
echo \Foomo\Site\Module::getContentDir() . PHP_EOL;
echo \Foomo\Site\Module::getContentPath() . PHP_EOL;
echo \Foomo\Site\Module::getDbDir() . PHP_EOL;
echo \Foomo\Site\Module::getHtdocsDir() . PHP_EOL;
echo \Foomo\Site\Module::getHtdocsPath() . PHP_EOL;
echo \Foomo\Site\Module::getHtdocsVarDir() . PHP_EOL;
echo \Foomo\Site\Module::getHtdocsVarPath() . PHP_EOL;
echo \Foomo\Site\Module::getPluginsDir() . PHP_EOL;
echo \Foomo\Site\Module::getPluginsPath() . PHP_EOL;
echo \Foomo\Site\Module::getThemesDir() . PHP_EOL;
echo \Foomo\Site\Module::getThemesPath() . PHP_EOL;
echo \Foomo\Site\Module::getUploadDir() . PHP_EOL;
echo \Foomo\Site\Module::getUploadPath() . PHP_EOL;
echo \Foomo\Site\Module::getVarDir() . PHP_EOL;
