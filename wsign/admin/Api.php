<?php
/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsignBase {

	protected $cachet = ['welcome', 'member', 'tl', 'mlist'];

	//protected $cachefalg = true;

	public function __construct() {

		//echo "admin";

		//$this->needLogin('/wsign-login-login.html');

		parent::__construct();

		//$this->cacheitem(['time' => 144000, 'qflag' => G('qflag', false)]);

	}

	public function index() {
		//dump($_SERVER);exit;

		$this->view();
	}

	public function welcome() {

		//dump($_SERVER);

		$this->slist('i.id,i.ip,i.time,i.ua,i.login_type,l.name', 'login_info i inner join login l on i.uid=l.id order by i.id desc limit 10', 'welcome');

	}

	public function member() {

		$this->slist('id,name,weixin as wid,wuid,status', 'user', 'member-list');

	}
	public function madd() {
		if (isGetPostAjax('post')) {
			$cookie = G('cookie');

			$params = $this->checkParams(['id' => 'int', 'wuid' => 'int', 'status' => 'regex:^[01]$']);

			try {
				if ($cookie != '') {
					$wb = new Weibo($cookie);
					$uidname = $wb->getUidName();

					if (isset($uidname['id']) && $params['wuid'] == $uidname['id']) {
						//var_dump('update user set cookie="' . $cookie . '",name="' . $uidname['name'] . '" where id=' . $id);exit;
						Db::getInstance()->exec('update user set cookie=:cookie,name=:name,status=:status where id=:id', [':id' => $params['id'], ':cookie' => $cookie, ':name' => $uidname['name'], ':status' => $params['status']]);
						exitMsg(ErrorConst::API_SUCCESS_ERRNO, '修改成功');

					}
				} else {
					Db::getInstance()->exec('update user set status=:status where id=:id', [':id' => $params['id'], ':status' => $params['status']]);
					exitMsg(ErrorConst::API_SUCCESS_ERRNO, '修改成功');
				}

				exitMsg(2, '修改失败,修改用户和提交用户不匹配');
			} catch (PDOException $ee) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
			} catch (Exception $e) {
				exitMsg($e->getCode(), $e->getMessage());
			}
		}
		$this->view('member-add');
	}

	public function mdel() {
		$this->comdel('user');

	}

	public function mlist() {
		$this->slist('*', 'wcount', 'mlist');

	}
	public function mgadd() {
		if (isGetPostAjax('post')) {

			$params = $this->checkParams(['id' => 'int', 'count' => 'int']);
			try {
				Db::getInstance()->exec('update wcount set count=:count where id=:id', [':id' => $params['id'], ':count' => $params['count']]);
				exitMsg(ErrorConst::API_SUCCESS_ERRNO, '修改成功');
			} catch (PDOException $e) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
			}
		}
		$this->view('m-add');
	}

	public function gdel() {
		$this->comdel('wcount');
	}

	public function tl() {

		$this->slist('id,name,time,status,`order`', 'tb_zh', 'tlist');
	}

	public function torder() {
		//dump(Request::Csrf());exit;

		$params = $this->checkParams(['id' => 'int', 'order' => 'int']);

		$this->db('tb_zh')->where('`order`=:order', [':order' => $params['order']])->save($params['id']);

		exitMsg(ErrorConst::API_SUCCESS_ERRNO, '修改成功');
	}

	public function ts() {
		$this->statuscomm('tb_zh');
	}

	public function logout() {

		$_SESSION = array();
		session_destroy();
		//Session('name', null);
		Cookie('auth', null, -1);
		Cookie(session_name(), null, -1);

		echo "<script>alert('退出成功');location.href='/wsign-login-login.html';</script>";
		//header("location: /wsign/login/login");
		exit;
	}

}

?>