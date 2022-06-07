<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class IqiSign implements CronInterface {

	function sql() {

		return "SELECT id,P00001,name from iqi_sign where status=0";
	}

	function run($resi, &$condition, &$info) {

		$info = [0, 'iqidign', $resi['name'], '', ''];

		$desp = '';

		try {

			$header = ['Content-Type: application/json;charset=UTF-8 application/json;charset=UTF-8'];
			$data = '{"natural_month_sign":{"agentType":1,"agentversion":1,"authCookie":"95oFL4PTzVaVXuHnrNgwKno8LsFeBz9um2Nzn8OfwoMYJdGQdeoTUgJ6NvGSAhvVryc2a","qyid":"2d0382499ac1cd5963e90abf738fb913","verticalCode":"iQIYI","taskCode":"iQIYI_mofhr"}}';
			$wcurl = new Mcurl('https://community.iqiyi.com/openApi/task/execute?agentType=1&agentversion=1.0&appKey=basic_pcw&authCookie=95oFL4PTzVaVXuHnrNgwKno8LsFeBz9um2Nzn8OfwoMYJdGQdeoTUgJ6NvGSAhvVryc2a&qyid=2d0382499ac1cd5963e90abf738fb913&task_code=natural_month_sign&timestamp=1643213311305&typeCode=point&userId=1448892489&sign=38be89d5cd861b1acf8a3aa631a86e90', $header);
			$r = $wcurl->post($data, 1);
			$condition = isset($r['code']) && $r['code'] == 'A00000';
			if (!$condition) {
				$info[4] = isset($r['msg']) ? $r['msg'] : '解析错误';
				$desp = $info[4];
			} else {
				$cumulateSignDaysSum = $r["data"]["data"]["signDays"];
				$vipinfotime = json_decode(Http::R("https://vinfo.vip.iqiyi.com/external/vip_users?P00001={$resi['P00001']}&platform=01080031010000000000&version=3.0&appVersion=1.0&bizSource=vip_web_player&messageId=13AB123C-E346-E86D-2C0F-0A4AA94F4726&vipTypes=1%2C3%2C4%2C5%2C7%2C8%2C10%2C13%2C14%2C16%2C18"), true)['data']['vip_info']['deadline']['date'];
				$desp = "用户：{$resi['name']}\r\n\r\n累计签到：{$cumulateSignDaysSum}天\r\n\r\n到期日期：{$vipinfotime}";

				//dump($data);

			}

		} catch (Throwable $e) {
			$info[4] = $e->getMessage();
		}

		ftPusgMsg('iqi签到信息', $desp);
		//Http::R('https://sc.ftqq.com/' . C('ftqq')['key'] . '.send', $data);
		//$info = [$resi['name'] . '-' . $resi['value'], '贴吧封禁:' . $resi['kw'], $rs['error_msg']];

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update iqi_sign set status={$status} where id in({$idstr})");

	}

}

?>