<?php
/**
 * Role Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * Role Model
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property int $weight
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Role extends CActiveRecord
{
	/**
	 * 旧名称
	 * @var string
	 */
	private $_oldName;

	/**
	 * 获取模型
	 * @param string $className
	 * @return Role
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 获取表名称
	 * @return string
	 */
	public function tableName()
	{
		return '{{role}}';
	}

	/**
	 * 字段规则
	 * @see CModel::rules()
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('name, description', 'required'),
			array('name', 'length', 'max' => 64),
			array('name', 'match', 'pattern'=>'/^[a-z]+$/', 'message'=>'{attribute} 只能包含小写英文'),
			array('name', 'unique'),
			array('description', 'length', 'max' => 255),
			array('weight', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * 字段标签
	 * @see CModel::attributeLabels()
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
			'name' => '名称',
			'description' => '描述',
			'weight' => '权重',
		);
	}

	/**
	 * 搜索
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('weight',$this->weight);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=> 'weight, name'
			)
		));
	}

	/**
	 * 获取权重列表，根据分类数目
	 * @return array
	 */
	public function getWeightList()
	{
		$count = $this->count();
		$range = range(-$count, $count);
		return array_combine($range, $range);
	}

	/**
	 * 获取名称列表
	 * @return array
	 */
	public static function allList()
	{
		if (($list = Yii::app()->getCache()->get('role_all_list')) === false) {
			$list = CHtml::listData(self::model()->findAll(array('order' => 'weight, name')), 'name', 'description');
			Yii::app()->getCache()->set('role_all_list', $list);
		}
		return $list;
	}

	/**
	 * 删除缓存
	 */
	public function deleteCache()
	{
		Yii::app()->getCache()->delete('role_all_list');
	}

	/**
	 * 根据id更新权重
	 * @param integer $id
	 * @param integer $weight
	 */
	public static function updateWeightByPk($pk, $weight) {
		$weight = intval($weight);
		self::model()->updateByPk($pk, array('weight'=>$weight));
	}

	/**
	 * 查找之后
	 * @see CActiveRecord::afterFind()
	 */
	protected function afterFind()
	{
		parent::afterFind();
		$this->_oldName = $this->name;
	}

	/**
	 * 保存之后
	 * @see CActiveRecord::afterSave()
	 */
	protected function afterSave()
	{
		parent::afterSave();
		$this->deleteCache();
		$authManager = Yii::app()->getAuthManager();
		if ($this->isNewRecord) {
			$authManager->createAuthItem($this->name, CAuthItem::TYPE_ROLE, $this->description);
		} else {
			$authItem = $authManager->getAuthItem($this->_oldName);
			if (!$authItem) {
				$authManager->createAuthItem($this->name, CAuthItem::TYPE_ROLE, $this->description);
			} else {
				$authItem->setDescription($this->description);
				$authItem->setName($this->name);
			}
		}
	}

	/**
	 * 删除之后
	 * @see CActiveRecord::afterDelete()
	 */
	protected function afterDelete()
	{
		parent::afterDelete();
		$this->deleteCache();
		Yii::app()->getAuthManager()->removeAuthItem($this->name);
	}
}
