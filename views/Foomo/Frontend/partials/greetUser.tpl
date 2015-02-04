<?

/* @var $view Foomo\MVC\View */

$hour = date('H');
$user = Foomo\BasicAuth::getCurrentUser();
switch(true) {
	case ($hour < 10 && $hour > 6):
		$key = 'GREET_GOOD_MORNING';
		break;
	case ($hour > 12 && $hour < 13):
		$key = 'GREET_LUNCH';
		break;
	case ($hour > 20 && $hour < 24):
		$key = 'GREET_LATE';
		break;
	case ($hour > 0 && $hour < 6):
		$key = 'GREET_LAUNCH';
		break;
	default:
		$key = 'GREET_DEFAULT';
}
printf($view->_($key), $view->escape($user));

