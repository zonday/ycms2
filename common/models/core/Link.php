<?php
/**
 * Link class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Link Model
 *
 * @property integer $id ID
 * @property string $name 名称
 * @property string $link_href 链接地址
 * @property string $link_target 链接目标
 * @property string $description 描述
 * @property integer $weight 权重
 * @property integer $create_time 创建时间
 * @property Term $category 分类
 * @property integer visible 可见性
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Link extends CActiveRecord
{
	/**
	 * 获取模型
	 * @param string $className
	 * @return Link
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 表名称
	 * @see CActiveRecord::tableName()
	 * @return array
	 */
	public function tableName()
	{
		return '{{link}}';
	}

	/**
	 * 验证规则
	 * @see CModel::rules()
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('name, link_href', 'required'),
			array('link_href', 'url'),
			array('weight, visible', 'numerical', 'integerOnly'=>true),
			array('name, description, link_href', 'length', 'max'=>255),
			array('link_target', 'in', 'range'=>array_keys($this->getTargetList())),
			array('category_id', 'in', 'range'=>array_keys($this->getCategoryList())),
			array('create_time, visible, id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @see CActiveRecord::relations()
	 * @return array
	 */
	public function relations()
	{
		return array(
			'category'=>array(self::BELONGS_TO, 'Term', 'category_id'),
		);
	}

	/**
	 * 获取属性标签
	 * @see CModel::attributeLabels()
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'image_id' => '图片',
			'name' => '名称',
			'description' => '描述',
			'link_target' => '目标',
			'visible' => '可见性',
			'link_href' => '链接地址',
			'category_id'=>'分类',
			'create_time'=>'创建时间',
			'weight'=>'权重',
		);
	}

	/**
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.category_id',$this->category_id);
		$criteria->compare('t.visible',$this->visible);

		if ($this->create_time)
		{
			$condition = YUtil::generateTimeCondition($this->create_time, 't.create_time');
			if ($condition)
				$criteria->addCondition($condition);
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=> 't.weight,t.name'
			)
		));
	}

	/**
	 * 获取分类列表
	 * @return array
	 */
	public function getCategoryList()
	{
		$taxonomy = Taxonomy::findfromCache('link');
		$list = array('0' => '无');
		if ($taxonomy)
			return $list + CHtml::listData($taxonomy->getTree(), 'id', 'name');
		else
			return $list;
	}

	/**
	 * 获取链接Target列表
	 * @return multitype:string
	 */
	public function getTargetList()
	{
		return array(
			'_none' => '同一窗口或标签',
			'_top' => '不包含框架的当前窗口或标签',
			'_blank' => '新窗口或新标签',
		);
	}

	/**
	 * 根据分类查找链接
	 * @param string $category
	 * @param mixed $condition
	 * @param array $params
	 * @return array
	 */
	public function findAllByCategory($category=null, $condition=null, $params=array())
	{
		if ($category !== null) {
			$taxonomy = Taxonomy::findFromCache('link');
			if (empty($taxonomy))
				return array();

			$term = Term::findFromCacheBySlug($category, $taxonomy->id);
			if (empty($term))
				return array();

			$criteria = new CDbCriteria();
			$criteria->addColumnCondition(array('category_id' => $term->id));
			$this->getDbCriteria()->mergeWith($criteria);
		}
		return $this->findAll($condition, $params);
	}

	/**
	 * 可见的
	 * @param integer $limit
	 * @return Link
	 */
	public function visible($limit=-1)
	{
		$this->getDbCriteria()->mergeWith(array(
			'limit'=>$limit,
			'order'=>'weight, name',
			'condition'=>'visible=1'
		));
		return $this;
	}

	/**
	 * 获取链接 数目$limit 分类$category 排序$order
	 * @deprecated
	 * @param string $category
	 * @param mixed $limit
	 * @param string $order
	 * @return array
	 */
	public static function getLinks($category=null, $limit=null, $order=null)
	{
		if ($order === null)
			$order = 'weight, name';

		return self::model()->cache(60)->findAllByCategory($category, array('limit'=>$limit, 'order'=>$order, 'condition'=>'visible=1'));
	}

	/**
	 * 根据id更新权重
	 * @param integer $id
	 * @param integer $weight
	 */
	public static function updateWeightByPk($id, $weight)
	{
		$weight = intval($weight);
		self::model()->updateByPk($id, array('weight'=>$weight));
	}

	/**
	 * @see CActiveRecord::beforeSave()
	 * @return boolean
	 */
	protected function beforeSave()
	{
		if (parent::beforeSave()) {
			if ($this->isNewRecord) {
				$this->create_time = time();
			}
			$this->update_time = time();
			return true;
		} else {
			return false;
		}
	}
}