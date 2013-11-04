<?php
/**
 * Banner class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Banner Model
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Banner extends CActiveRecord
{
	public $image;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return '{{banner}}';
	}

	public function rules()
	{
		return array(
			array('name, image', 'required'),
			array('link_href', 'url'),
			array('weight, visible', 'numerical', 'integerOnly'=>true),
			array('link_target', 'in', 'range'=>array_keys($this->getTargetList())),
			array('category_id', 'in', 'range'=>array_keys($this->getCategoryList())),
			array('create_time, visible', 'safe', 'on'=>'search'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id'=>'ID',
			'image'=>'图片',
			'name'=>'名称',
			'category_id'=>'分类',
			'link_href'=>'链接地址',
			'link_target'=>'链接目标',
			'visible'=>'可见性',
			'weight'=>'权重',
			'create_time'=>'创建时间',
		);
	}

	public function behaviors(){
		return array(
			'timestamp' => array(
				'class' => 'zii.behaviors.CTimestampBehavior',
				'createAttribute' => 'create_time',
				'updateAttribute' => 'update_time',
				'setUpdateOnCreate' => true,
			),
			'fileUsage' => array(
				'class' => 'YFileUsageBehavior',
				'fields' => array(
					'image' => array(
						'location' => 'public://banner',
						'type' => 'image',
					),
				),
			),
		);
	}

	public function relations()
	{
		return array(
			'category'=>array(self::BELONGS_TO, 'Term', 'category_id'),
		);
	}

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

	public function getCategoryList()
	{
		$taxonomy = Taxonomy::findfromCache('banner');
		$list = array(0 => '无');
		if ($taxonomy)
			return $list + CHtml::listData($taxonomy->getTree(), 'id', 'name');
		else
			return $list;
	}

	public function getTargetList()
	{
		return array(
			'_none' => '同一窗口或标签',
			'_top' => '不包含框架的当前窗口或标签',
			'_blank' => '新窗口或新标签',
		);
	}

	/**
	 * 根据分类查找Banner
	 * @param string $category
	 * @param mixed $condition
	 * @param array $params
	 * @return array
	 */
	public function findAllByCategory($category=null, $condition=null, $params=array())
	{
		if ($category!== null)
		{
			$taxonomy = Taxonomy::findFromCache('banner');

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
	 * 获取Banner 数目$limit 分类$category 排序$order
	 * @param string $category
	 * @param mixed $limit
	 * @param string $order
	 * @return array
	 */
	public static function getBanners($category, $limit=1, $order=null)
	{
		if ($order === null)
			$order = 'weight, name';

		return self::model()->findAllByCategory($category, array('limit'=>$limit, 'order'=>$order, 'condition'=>'visible=1'));
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
}
