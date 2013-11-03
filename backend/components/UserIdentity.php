<?php
class BackendUserIdentity extends CUserIdentity
{
	const ERROR_USER_NOT_ACTIVATED = 101;
	const ERROR_USER_BLOCK = 102;
	const ERROR_ROLE_NOT_ALLOW = 103;

	private $_id;

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

	public function getId()
	{
		return $this->_id;
	}
}