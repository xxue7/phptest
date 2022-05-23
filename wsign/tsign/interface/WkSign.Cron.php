<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class WkSign implements CronInterface {
	function sql() {
		return 'SELECT s.id,s.uid,z.cookie,z.name from wk_sign s INNER JOIN tb_zh z on s.uid=z.id and s.status=0  LIMIT 1';
	}

	function run($resi, &$condition, &$info) {

		try {

			$info = [$resi['uid'], '文库签到', $resi['name'], '', ''];

			//$info = [$resi['name'], '文库签到', ''];

			$rss = WenKu::sign('BDUSS=' . Tieba::fromKeyCookie($resi['cookie'], 'BDUSS'));

			$rs = json_decode($rss, true);

			//dump($rs);

			$condition = isset($rs['errno']) && $rs['errno'] == 0;

			if (!$condition) {
				$info[4] = isset($rs['errno']) ? $rs['errno'] . '-' . $rs['errmsg'] : $rss;
			}
		} catch (Exception $e) {
			$info[4] = $e->getMessage();
		}

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update wk_sign set status={$status} where id in({$idstr})");

	}
}

?>