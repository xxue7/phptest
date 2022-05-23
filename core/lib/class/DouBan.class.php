<?php

class DouBan {

	public static function getMatchs($tid, $maxpn = 1, $zduid = []) {
		$header = ['User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36'];

		$resmatchs = [];

		for ($i = 0; $i < $maxpn; $i++) {
			$res = http::R("https://www.douban.com/group/topic/{$tid}/?start=" . ($i * 100), '', $header);
			//echo $res;
			$re = '';
			if ($i == 0) {
				$c = intval(strMid('"commentCount": "', '",', $res));

				if ($c == 0) {
					break;
				}

				$page = ceil($c / 100);

				$maxpn = $maxpn > $page ? $page : $maxpn;

				$re = strstr($res, '<ul class="topic-reply" id="comments">');
			}
			$re = $re ? $re : $res;
			$len = preg_match_all('/data-author-id="(\d+)"\ndata-cid="(\d+)"[\s\S]+?alt="(.+?)"[\s\S]+?class="pubtime"\>(.+?)\<\/span\>[\s\S]+?\<p class=" reply-content"\>([\s\S]+?)\<\/p\>/', $re, $matches);
			for ($j = 0; $j < $len; $j++) {
				if (!empty($zduid) && !in_array($matches[1][$j], $zduid)) {
					continue;
				}
				array_push($resmatchs, ['uid' => $matches[1][$j], 'cid' => $matches[2][$j], 'name' => $matches[3][$j], 'time' => $matches[4][$j], 'content' => $matches[5][$j]]);
			}

		}
		return $resmatchs;

	}

	public static function report($cookie, $tid, $cid, $rtype) {
		$header = ['Cookie: ' . $cookie, "Referer: https://www.douban.com/group/topic/{$tid}/", 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36', 'X-Requested-With: XMLHttpRequest'];
		if (!in_array($rtype, ["3", '7', '0', '1', '13', '2', '6', '9', '10', '12', 'other'], true)) {
			$rtype = 'other';
		}
		//static $ck = '';

		$ck = strMid('ck=', ';', $cookie);

		if (empty($ck)) {
			throw new Exception("no ck");
		}

		// 3=辱骂攻击 7=引战 12=刷屏 9=泄露他人隐私 0=广告 1=色情低俗 other=其他
		$data = "url=https%3A%2F%2Fwww.douban.com%2Fgroup%2Ftopic%2F{$tid}%2F%3Fcomment_id%3D{$cid}&reason={$rtype}&extra_msg=&ck=" . $ck;
		$res = http::R('https://www.douban.com/misc/audit_report', $data, $header);

		if (strpos($res, '已经提交给管理员，我们会尽快处理') > 0) {
			$res = 'ok';
		}
		return $res;
		//return strMid("comment_id={$cid} </a>", '</p>', http::R('https://www.douban.com/misc/audit_report', $data, $header));

	}

	public static function keyReport($key, $cookie, $start = 1, $maxpn = 1, $zduid = []) {

		$header = ['User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36', 'Cookie: ' . $cookie];

		while (1) {

			echo "当前页数:{$start}" . PHP_EOL;
			$res = http::R('https://www.douban.com/group/search?start=' . (($start - 1) * 50) . '&cat=1013&sort=time&q=' . $key, '', $header);

			$len = preg_match_all('/sid: (\d+)\}\)" title="(.+?)"[\s\S]+?"td-time" title="(.+?)"[\s\S]+?\>(\d+)回应[\s\S]+?class=""\>(.+?)\<\/a\>/', $res, $matches);
			//dump($res, $len);
			for ($i = 0; $i < $len; $i++) {

				echo "当前举报主题,回车跳过,输入数字跳转:" . PHP_EOL;

				echo sprintf("%d\t%s\t%s\t%s\t%s", $i + 1, $matches[2][$i], $matches[3][$i], $matches[4][$i], $matches[5][$i]) . PHP_EOL;

				$handle = fopen('php://stdin', 'r');
				$input = trim(fgets($handle));
				fclose($handle);

				if ($input == '') {
					continue;
				}
				if (is_numeric($input)) {
					$i = intval($input) - 2;
					continue;
				}

				//

				$mares = self::getMatchs($matches[1][$i], $maxpn, $zduid);

				//dump($mares);

				foreach ($mares as $value) {
					echo sprintf("当前内容:%s", $value['content']) . PHP_EOL . '输入举报类型,输入break跳出:';
					$handle = fopen('php://stdin', 'r');
					$input = trim(fgets($handle));
					fclose($handle);

					if ($input == 'break') {
						break;
					}

					if (!in_array($input, ["3", '7', '0', '1', '13', '2', '6', '9', '10', '12', 'other'], true)) {
						continue;
					}

					echo "举报结果:" . self::report($cookie, $matches[1][$i], $value['cid'], $input) . PHP_EOL;

				}
			}

			$start++;

		}

	}

}

?>