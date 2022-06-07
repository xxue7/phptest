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

	public static function teleGramMsg(string $msg, string $token): string{

		$res = json_decode(Http::R("https://api.telegram.org/bot{$token}/getUpdates"), true);
		$chat_id = $res['result'][0]['my_chat_member']['chat']['id'] ?? 0;
		if ($chat_id) {
			return Http::R("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&text={$msg}");
		}
		return 'chat_id error';
	}

}

?>