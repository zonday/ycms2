<?php
/**
 * LoginForm  File
 * @author Yang <css3@qq.com>
 */
class LoginForm extends CFormModel
{
	/**
	 * 用户名
	 * @var string
	 */
	public $username;

	/**
	 * 密码
	 * @var string
	 */
	public $password;

	/**
	 * 记住我
	 * @var boolean
	 */
	public $rememberMe;

	/**
	 * 验证码
	 * @var string
	 */
	public $verifyCode;

	/**
	 * 认证id
	 * @var CUserIdentity
	 */
	private $_identity;

	/**
	 * 验证规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('username, password, verifyCode', 'required'),
			array('rememberMe', 'boolean'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
			array('password', 'authenticate'),
		);
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'username' => '用户名',
			'password' => '密码',
			'rememberMe' => '记住我',
			'verifyCode' => '验证码',
		);
	}

	public function authenticate($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$this->_identity = new UserIdentity($this->username, $this->password);
			if(!$this->_identity->authenticate()) {
				if ($this->_identity->errorCode == UserIdentity::ERROR_ROLE_NOT_ALLOW) {
					$this->addError('username', '无效的用户角色');
				} elseif ($this->_identity->errorCode == UserIdentity::ERROR_USER_NOT_ACTIVATED) {
					$this->addError('password', '用户未激活验证');
				} elseif ($this->_identity->errorCode == UserIdentity::ERROR_USER_BLOCK) {
					$this->addError('password', '用户已被禁用');
				} else {
					$this->addError('password','无效的用户名或密码');
				}
			}
		}
	}

	public function login()
	{
		if ($this->_identity === null) {
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();
		}

		if ($this->_identity->errorCode === BackendUserIdentity::ERROR_NONE) {
			$duration = $this->rememberMe ? 3600*24*30 : 0; //30天
			Yii::app()->getUser()->login($this->_identity, $duration);
			return true;
		} else {
			return false;
		}
	}
}