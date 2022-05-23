<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class SendError implements CronInterface {

	function sql() {

		return "SELECT id from send_error where status=0";
	}

	function run($resi, &$condition, &$info) {

		$info = [0, '通知信息', '', '', ''];

		try {

			$now = mktime(0, 0, 0);
			//dump($now);
			$res = Db::getInstance()->exec("SELECT tb_wb_type,`name`,error from tb_wb_error  where {$now}<=`time` GROUP BY tb_wb_type,name")->getAll();

			if (!empty($res)) {
				$desp = '';
				foreach ($res as $value) {
					$desp .= $value['tb_wb_type'] . '-' . $value['name'] . '-' . $value['error'] . "\r\n\r\n";
				}

				//$data = ['text' => '签到执行失败信息', 'desp' => $desp];

				$rssstr = ftPusgMsg('签到执行失败信息', $desp); // Http::R('https://sc.ftqq.com/' . C('ftqq')['key'] . '.send', $data);

				$rss = json_decode($rssstr, true);

				$condition = isset($rss['code']) && $rss['code'] == 0;
				if (!$condition) {
					$info[4] = isset($rss['message']) ? $rss['message'] : $rssstr;
				}
			} else {

				$condition = true;
			}

		} catch (Throwable $e) {
			$info[4] = $e->getMessage();
		}

		//$info = [$resi['name'] . '-' . $resi['value'], '贴吧封禁:' . $resi['kw'], $rs['error_msg']];

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update send_error set status={$status} where id in({$idstr})");

	}

}

?>