<?php
/**
 * LostPasswordForm class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * LostPasswordForm 忘记密码表单模型
 *
 * @author Yang <css3@qq.com>
 * @package backend.models
 */
class LostPasswordForm extends CFormModel
{
	/**
	 * 用户名或密码
	 * @var string
	 */
	public $login;

	/**
	 * 验证码
	 * @var string
	 */
	public $verifyCode;

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
			array('login, verifyCode', 'required'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
			array('login', 'validateLogin'),
		);
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'login' => '用户名或电子邮件',
			'verifyCode'=> '验证码',
		);
	}

	/**
	 * 验证账户或电子邮件
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateLogin($attribute, $params)
	{
		if ($this->hasErrors())
			return;

		$value = $this->$attribute;
		if (strpos($value, '@') !== false) {
			$label = '邮箱';
			$condition = 'email=:email';
			$params = array(':email'=>$value);
		} else {
			$label = '用户名';
			$condition = 'username=:username';
			$params = array(':username'=>$value);
		}

		$this->_user = User::model()->find($condition, $params);
		if (!isset($this->_user))
		{
			$this->addError('login', '无效的' . $label);
		} elseif ($this->_user->status == User::STATUS_NOT_ACTIVATED)
		{
			$this->addError('login', '邮箱未激活验证！');
		} elseif ($this->_user->status == User::STATUS_BLOCK)
		{
			$this->addError('login', '用户已被禁用，无法进行操作！');
		}
	}

	/**
	 * 丢失密码 更新activation_key
	 */
	public function lostpassword()
	{
		if ($this->hasErrors() || !isset($this->_user))
			return false;

		return $this->_user->saveAttributes(array('activation_key' => $this->_user->generateKey()));
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