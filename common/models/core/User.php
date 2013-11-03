<?php
/**
 * User Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * User Model
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property int $status
 * @property string $activation_key
 * @property string $create_time
 * @property string $update_time
 * @property string $login_time
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class User extends CActiveRecord
{
	/**
	 * 缺省状态
	 * @var integer
	 */
	const STATUS_DEFAULT = 0;

	/**
	 * 未激活认证
	 * @var integer
	 */
	const STATUS_NOT_ACTIVATED = 1;

	/**
	 * 锁定
	 * @var integer
	 */
	const STATUS_BLOCK = 2;

	/**
	 * 超级管理员id
	 * @var integer
	 */
	const SUPERADMIN_ID = 1;

	/**
	 * 确认密码
	 * @var string
	 */
	public $password_repeat;

	/**
	 * 角色名称列表
	 * @var array|null
	 */
	private $_roleNames;

	/**
	 * 角色列表
	 * @var array|null
	 */
	private $_roles;

	/**
	 * 获取模型
	 * @param string $className
	 * @return User
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 表名称
	 * @see CActiveRecord::tableName()
	 */
	public function tableName()
	{
		return '{{user}}';
	}

	/**
	 * 验证规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('username, email', 'required'),
			array('password, password_repeat', 'required', 'on' => 'insert'),
			array('password', 'compare'),
			array('password_repeat', 'safe'),
			array('username', 'match', 'pattern' => '|^[0-9a-z\-_]{5,16}|i', 'message'=>'{attribute} 只能包含字母和数字 、-、 _， 长度在6-16之间.'),
			array('nickname', 'length', 'max' => 16),
			array('email', 'length', 'max' => 128),
			array('email', 'email'),
			array('status', 'in', 'range'=>array(self::STATUS_DEFAULT, self::STATUS_NOT_ACTIVATED, self::STATUS_BLOCK)),
			array('username, email', 'unique'),
			array('roleNames', 'safe'),
			array('update_time, login_time, create_time', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => '用户名',
			'password' => '密码',
			'password_repeat' => '确认密码',
			'nickname' => '昵称',
			'email' => '电子邮件',
			'status' => '状态',
			'roleNames' => '角色',
			'create_time' => '创建时间',
			'update_time' => '更新时间',
			'login_time' => '最后登陆时间',
		);
	}

	/**
	 * 模型行为
	 * @see CModel::behaviors()
	 */
	public function behaviors()
	{
		return array(
			'CTimestampBehavior' => array(
				'class' => 'zii.behaviors.CTimestampBehavior',
				'createAttribute' => 'create_time',
				'updateAttribute' => 'update_time',
				'setUpdateOnCreate' => true,
			),
		);
	}

	/**
	 * 搜索
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('nickname',$this->nickname,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('update_time',$this->update_time);

		if ($this->create_time) {
			$condition = YUtil::generateTimeCondition($this->create_time, 't.create_time');
			if ($condition)
				$criteria->addCondition($condition);
		}

		if (isset($this->login_time)) {
			if (!$this->login_time) {
				$criteria->compare('login_time', $this->login_time);
			} else {
				$condition = YUtil::generateTimeCondition($this->login_time, 't.login_time');
				if ($condition)
					$criteria->addCondition($condition);
			}
		}

		if (isset($this->_roleNames) && is_array($this->_roleNames)) {
			$criteria->join = 'INNER JOIN ' . Yii::app()->getAuthManager()->assignmentTable . ' AS t2 ON t.id = t2.userid';
			$criteria->addInCondition('t2.itemname', $this->_roleNames);
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=> 't.id DESC'
			)
		));
	}

	/**
	 * 获取状态列表
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_DEFAULT => '正常',
			self::STATUS_NOT_ACTIVATED => '未激活验证',
			self::STATUS_BLOCK => '禁用',
		);
	}

	/**
	 * 生成key
	 * @return string
	 */
	public static function generateKey()
	{
		return sha1(uniqid(mt_rand(),true));
	}

	/**
	 * 验证key
	 * @param string $activation_key
	 * @param string $username
	 * @return User|null 成功后返回User
	 */
	public static function validateKey($activation_key, $username)
	{
		return self::model()->find('activation_key=:activation_key AND username=:username', array(
			':activation_key' => $activation_key,
			':username' => $username,
		));
	}

	/**
	 * 验证密码
	 * @param string $password
	 * @return boolean
	 */
	public static function validatePassword($password)
	{
		return CPasswordHelper::verifyPassword($password, $this->password); //iis php5.2不支持
	}

	/**
	 * Hash password
	 * @param string $password
	 * @return string
	 */
	public static function hashPassword($password)
	{
		return CPasswordHelper::hashPassword($password); //iis php5.2不支持
	}

	/**
	 * 获取角色列表
	 */
	public function getRoleList()
	{
		$list = CHtml::listData($this->getRoles(), 'name', 'description');
		return $list;
	}

	/**
	 * 设置角色名称列表
	 * @param array $names
	 */
	public function setRoleNames($names)
	{
		$this->_roleNames = array();
		foreach ((array) $names as $name)
			if ($name = trim($name))
			$this->_roleNames[] = $name;
		$this->setRoles($names);
	}

	/**
	 * 获取角色名称列表
	 * @return array
	 */
	public function getRoleNames()
	{
		if (!isset($this->_roleNames)) {
			$this->_roleNames = array();
			foreach ($this->getRoles() as $role)
				$this->_roleNames[] = $role->name;
		}
		return $this->_roleNames;
	}

	/**
	 * 是否拥有角色$name
	 * @param string $name
	 */
	public function hasRole($name)
	{
		$roles = $this->getRoles();
		foreach ($roles as $role)
			if ($role->getName() == $name)
			return true;
		return false;
	}

	/**
	 * 添加一个角色
	 * @param string $name 角色名
	 */
	public function addRole($name)
	{
		$roleNames = $this->getRoleNames();
		if (!in_array($name, $roleNames)) {
			$roleNames[] = $name;
			$this->setRoleNames($roleNames);
			$this->saveRoles();
		}
	}

	/**
	 * 删除一个角色
	 * @param string $name 角色名
	 */
	public function removeRole($name)
	{
		if (in_array($name, Role::systemRoles()))
			return;

		$roleNames = $this->getRoleNames();
		if (($index = array_search($name, $roleNames)) !== false) {
			unset($roleNames[$index]);
			$this->setRoleNames($roleNames);
			$this->saveRoles();
		}
	}

	/**
	 * 设置角色列表
	 * @param array $names 角色名称或者角色列表
	 */
	public function setRoles($names)
	{
		$this->_roles = array();
		$authManager = Yii::app()->getAuthManager();
		foreach ((array) $names as $name) {
			if ($name instanceof CAuthItem) {
				$this->_roles[] = $role;
			} else {
				if ($role = $authManager->getAuthItem($name)) {
					$this->_roles[] = $role;
				}
			}
		}
	}

	/**
	 * 获取角色列表
	 * @return array
	 */
	public function getRoles()
	{
		$authManager = Yii::app()->getAuthManager();
		if (!isset($this->_roles)) {
			if ($this->isNewRecord) {
				$this->_roles = array();
			} elseif (($this->_roles = Yii::app()->getCache()->get("user_{$this->id}_roles")) === false) {
				$this->_roles = $authManager->getRoles($this->id);
				Yii::app()->getCache()->set("user_{$this->id}_roles", $this->_roles, 3600);
			}
		}

		return $this->_roles;
	}

	/**
	 * 保存角色列表
	 */
	protected function saveRoles()
	{
		if (!isset($this->_roles) || !is_array($this->_roles))
			return;

		$addRoles = array();
		$delRoles = array();
		if ($this->isNewRecord) {
			$addRoles = $this->_roles;
		} else {
			$oldRoles = Yii::app()->getAuthManager()->getRoles($this->id);

			foreach ($this->_roles as $role) {
				foreach ($oldRoles as $oldRole) {
					if ($role->getName() == $oldRole->getName()) {
						continue 2;
					}
				}
				$addRoles[] = $role;
			}

			foreach ($oldRoles as $oldRole) {
				foreach ($this->_roles as $role) {
					if ($oldRole->getName() == $role->getName())
						continue 2;
				}
				$delRoles[] = $oldRole;
			}
		}

		foreach ($addRoles as $role)
			$role->assign($this->id);

		foreach ($delRoles as $role)
			$role->revoke($this->id);

		Yii::app()->getCache()->set("user_{$this->id}_roles", $this->_roles);
	}

	/**
	 * 删除用户所有角色
	 */
	protected function deleteRoles()
	{
		foreach ($this->getRoles() as $role)
			$role->revoke($this->id);
		Yii::app()->getCache()->delete("user_{$this->id}_roles");
	}

	/**
	 * 保存之前
	 * @see CActiveRecord::beforeSave()
	 */
	protected function beforeSave()
	{
		if (parent::beforeSave()) {
			if ($this->isNewRecord || ($this->getScenario() == 'update' && $this->password)) {
				$this->password = $this->hashPassword($this->password);
			} else {
				unset($this->password);
			}

			if (!trim($this->nickname)) {
				$this->nickname = $this->username;
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 删除之前
	 * @see CActiveRecord::beforeDelete()
	 */
	protected function beforeDelete()
	{
		if (parent::beforeDelete()) {
			if ($this->id == self::SUPERADMIN_ID) {
				return false;
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 保存之后
	 * @see CActiveRecord::afterSave()
	 */
	protected function afterSave()
	{
		parent::afterSave();
		$this->saveRoles();
	}

	/**
	 * 删除之后
	 * @see CActiveRecord::afterDelete()
	 */
	protected function afterDelete()
	{
		parent::afterDelete();
		$this->deleteRoles();
	}
}