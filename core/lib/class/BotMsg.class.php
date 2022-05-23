<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class BotMsg {
	public static function ftPusgMsg(string $text, string $desp, string $key): string {

		return Http::R('https://sctapi.ftqq.com/' . $key . '.send', ['text' => $text, 'desp' => $desp]);
	}

	public static function qMsg(string $msg, string $key): string {

		return Http::R('https://qmsg.zendee.cn/send/' . $key, "msg={$msg}");
	}

}

?>