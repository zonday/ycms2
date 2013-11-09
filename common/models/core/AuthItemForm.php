<?php
/**
 * AuthItemForm Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * AuthItemForm Model 权限表单模型
 *
 * @see CAuthItem
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class AuthItemForm extends CFormModel
{
	public $name;
	public $description;
	public $bizRule;
	public $data;

	public $task;

	public $isNewRecord = true;

	private $_type = CAuthItem::TYPE_OPERATION;

	/**
	 * 验证规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('name, description', 'required'),
			array('name', 'length', 'max' => 64),
			array('name', 'validateName'),
			array('description', 'length', 'max' => 128),
			array('bizRule, task', 'safe'),
		);
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'name' => '名称',
			'description' => '描述',
			'bizRule' => '业务规则',
			'data' => '数据',
			'task' => '所属任务',
		);
	}

	/**
	 * 验证名称
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateName($attribute, $params)
	{
		if (!$this->isNewRecord)
			return;

		if (Yii::app()->getAuthManager()->getAuthItem($this->name))
			$this->addError('name', $this->getAttributeLabel('name') . ' 不是唯一的.');
	}

	/**
	 * 获取任务列表
	 * @return array
	 */
	public function getTaskList()
	{
		$authItems = Yii::app()->getAuthManager()->getAuthItems(CAuthItem::TYPE_TASK);
		return CHtml::listData($authItems, 'name', 'description');
	}

	/**
	 * 获取类型名称列表
	 * @return array
	 */
	public function getTypeNames()
	{
		return array(
			CAuthItem::TYPE_OPERATION => '操作',
			CAuthItem::TYPE_TASK => '任务',
		);
	}

	/**
	 * 设置类型
	 * @param integer $type
	 */
	public function setType($type)
	{
		if (in_array($type, array(CAuthItem::TYPE_TASK, CAuthItem::TYPE_OPERATION)))
			$this->_type = $type;
	}

	/**
	 * 获取类型
	 */
	public function getType()
	{
		return $this->_type;
	}
}