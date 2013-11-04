<?php
/**
 * UserIdentity class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * UserIdentity
 * @author Yang <css3@qq.com>
 * @package backend.components
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * @var integer 未激活认证
	 */
	const ERROR_USER_NOT_ACTIVATED = 101;

	/**
	 * @var integer 禁用
	 */
	const ERROR_USER_BLOCK = 102;

	/**
	 * @var integer 角色不被允许
	 */
	const ERROR_ROLE_NOT_ALLOW = 103;

	/**
	 * @var mixed 认证id
	 */
	private $_id;

	/**
	 * 认证
	 * @see CUserIdentity::authenticate()
	 */
	public function authenticate()
	{
		$user = User::model()->find('username=:username', array(':username'=>$this->username));

		if (!isset($user)) {
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		} elseif (!$user->validatePassword($this->password)) {
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		} elseif ($user->id == User::SUPERADMIN_ID) {
			$this->errorCode = self::ERROR_NONE;
		} elseif ($user->status == User::STATUS_BLOCK) {
			$this->errorCode = self::ERROR_USER_BLOCK;
		} elseif ($user->status == User::STATUS_NOT_ACTIVATED) {
			$this->errorCode = self::ERROR_USER_NOT_ACTIVATED;
		} elseif (!$user->hasRole('admin')) {
			$this->errorCode = self::ERROR_ROLE_NOT_ALLOW;
		} else {
			$this->errorCode = self::ERROR_NONE;
		}

		if ($this->errorCode == self::ERROR_NONE) {
			$this->_id = $user->id;
			$this->setPersistentStates(array(
				'nickname' => $user->nickname
			));
		}

		return !$this->errorCode;
	}

	/**
	 * 获取认证id
	 * @see CUserIdentity::getId()
	 * @return mixed
	 */
	public function getId()
	{
		return $this->_id;
	}
}