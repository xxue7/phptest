<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
abstract class ProcessFork {

	protected $threadCount;

	protected $worksarr = [];

	public function __construct() {
		if (!function_exists('pcntl_fork')) {
			exit('不支持fork');
		}
	}

	abstract protected function work(...$param);

	protected function run() {
		for ($i = 0; $i < $this->threadCount; $i++) {

			$pid = pcntl_fork();

			if ($pid == -1) {
				die('进程创建失败');
			} elseif ($pid) {

				$this->worksarr[$pid] = $i;

			} else {

				//$this->work($i);
				call_user_func_array([$this, 'work'], ...$param);

				exit(0);
			}

		}
	}

	protected function join() {
		while (!empty($this->worksarr)) {
			$pid = pcntl_wait($status, WNOHANG);

			if ($pid > 0) {

				echo "进程-{$pid}-任务thread-" . $this->worksarr[$pid] . "完成" . PHP_EOL;
				unset($this->worksarr[$pid]);
			}

		}
	}

}

?>