<?php
/**
 * ResetPasswordForm class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * ResetPasswordForm 重置密码表单模型
 *
 * @author Yang <css3@qq.com>
 * @package backend.models
 */
class ResetPasswordForm extends CFormModel
{
	/**
	 * key
	 * @var string
	 */
	public $key;

	/**
	 * 账户
	 * @var string
	 */
	public $login;

	/**
	 * 新密码
	 * @var string
	 */
	public $password;

	/**
	 * 确认密码
	 * @var string
	 */
	public $password_repeat;

	/**
	 * 用户
	 * @var User
	 */
	private $_user;

	/**
	 * 验证规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('password, password_repeat, key, login', 'required'),
			array('password', 'compare'),
		);
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'password' => '新密码',
			'password_repeat' => '确认密码',
		);
	}

	/**
	 * 验证key
	 * @return User|null
	 */
	public function validateKey()
	{
		$this->_user = User::validateKey($this->key, $this->login);
		return $this->_user;
	}

	/**
	 * 重置密码
	 */
	public function resetPassword()
	{
		if (!isset($this->_user))
			return false;

		return $this->_user->saveAttributes(array(
			'activation_key' => '',
			'password' => User::hashPassword($this->password),
		));
	}

	/**
	 * 获取用户
	 * @return User
	 */
	public function getUser()
	{
		return $this->_user;
	}
}