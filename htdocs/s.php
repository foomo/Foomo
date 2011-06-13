<?php

if(Foomo\Session::getEnabled()) {
	if(isset($_GET['w'])) {
		//sleep(10);
		Foomo\Session::lockAndLoad();
	}
	var_dump(Foomo\Session::getAge(), Foomo\Session::getSessionId());
} else {
	session_start();

	if(!isset($_SESSION['i'])) {
		$_SESSION['i'] = 0;
	} else {
		$_SESSION['i'] ++;
	}
	var_dump($_SESSION['i']);
	
}
$sessionConfig = Foomo\Config::getConf(
	Foomo\Module::NAME,
	Foomo\Session\DomainConfig::NAME
);

Foomo\Config::confExists(\Foomo\Module::NAME, Foomo\Session\DomainConfig::NAME);

var_dump($sessionConfig);
?><pre><? 
var_dump(\Foomo\Timer::getStats());
