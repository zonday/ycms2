<?php
/**
 * Banner class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Banner Model
 *
 * @property integer $id ID
 * @property string $name 名称
 * @property string $image 图片
 * @property string $link_href 链接地址
 * @property string $link_target 链接目标
 * @property string $description 描述
 * @property integer $weight 权重
 * @property integer $create_time 创建时间
 * @property Term $category 分类
 * @property integer visible 可见性
 * @property mixed update_time
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Banner extends CActiveRecord
{
	public $image;

	/**
	 * @param string $className
	 * @return Banner
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @see CActiveRecord::tableName()
	 * @return string
	 */
	public function tableName()
	{
		return '{{banner}}';
	}

	/**
	 * @see CModel::rules()
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('name, image', 'required'),
			array('link_href', 'url'),
			array('weight, visible', 'numerical', 'integerOnly'=>true),
			array('name, description, link_href', 'length', 'max'=>255),
			array('link_target', 'in', 'range'=>array_keys($this->getTargetList())),
			array('category_id', 'in', 'range'=>array_keys($this->getCategoryList())),
			array('create_time, visible, id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @see CModel::attributeLabels()
	 * @return array
	 */
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

	/**
	 * @see CModel::behaviors()
	 * @return array
	 */
	public function behaviors(){
		return array(
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
	 *
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
	 * @return array
	 */
	public function getCategoryList()
	{
		$taxonomy = Taxonomy::findfromCache('banner');
		$list = array(0 => '无');
		if ($taxonomy)
			return $list + CHtml::listData($taxonomy->getTree(), 'id', 'name');
		else
			return $list;
	}

	/**
	 * @return array
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
	 * 可见的
	 * @param integer $limit
	 * @return Banner
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
	 * 获取Banner 数目$limit 分类$category 排序$order
	 * @deprecated
	 * @param string $category
	 * @param mixed $limit
	 * @param string $order
	 * @return array
	 */
	public static function getBanners($category=null, $limit=-1, $order=null)
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
