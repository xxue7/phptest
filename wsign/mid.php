<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

session_name('wsign');

session_start();

Middle::Mid('get', '/wsign/tsign/cron', function () {

	// $redis = Mredis::getInstance();

	// //$lasttime = $redis->getVal('cronlasttime');

	// //$curtime = time();
	// $key = 'cronlasttime' . Request::Host();

	// if ($redis->exists($key)) {
	// 	die('频繁');
	// }

	// $redis->setVal($key, true, 59);

}, function () {

	Middle::Mid('get|post', '/wsign/(admin|info)/[a-z]+', function () {

		WsignBase::needLoginS('/wsign-login-login.html');

	});

});

?>