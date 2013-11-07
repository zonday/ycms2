<?php
/**
 * Taxonomy class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $weight
 * @property array $terms
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Taxonomy extends CActiveRecord
{
	/**
	 * 术语不能继承
	 * @var integer
	 */
	const HIERARCHY_DISABLED=0;

	/**
	 * 术语单个继承
	 * @var integer
	 */
	const HIERARCHY_SINGLE=1;

	/**
	 * 术语多个继承
	 * @var integer
	 */
	const HIERARCHY_MULTIPLE=2;

	public $hierarchy = self::HIERARCHY_DISABLED;

	/**
	 * @param string $className
	 * @return Taxonomy
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 获取表名称
	 * @see CActiveRecord::tableName()
	 */
	public function tableName()
	{
		return '{{taxonomy}}';
	}

	/**
	 * 获取字段规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('name, slug', 'required'),
			array('hierarchy, weight', 'numerical', 'integerOnly'=>true),
			array('name, slug', 'length', 'max'=>255),
			array('slug', 'match', 'pattern'=>'/^[a-z][a-z0-9\-]+$/', 'message'=>'{attribute} 只能包含小写英文和数字、-'),
			array('hierarchy', 'in', 'range'=>array_keys($this->getHierarchyList())),
			array('slug', 'unique'),
			array('description', 'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
		);
	}

	/**
	 * 获取属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => '名称',
			'slug' => '别名',
			'description' => '描述',
			'hierarchy' => '术语继承',
			'weight' => '权重',
		);
	}

	/**
	 * 获取相关模型关系
	 * @see CActiveRecord::relations()
	 */
	public function relations()
	{
		return array(
			'terms'=>array(self::HAS_MANY, 'Term', 'taxonomy_id'),
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('slug',$this->slug,true);
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
	 * 获取术语层级性列表
	 * @return array
	 */
	public function getHierarchyList()
	{
		return array(
			self::HIERARCHY_DISABLED=>'不能继承',
			self::HIERARCHY_SINGLE=>'单继承',
			self::HIERARCHY_MULTIPLE=>'多继承',
		);
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
	 * 获取分类下的术语树
	 * @param integer $parent
	 * @param integer|null $maxDepth
	 * @return array
	 */
	public function getTree($parent=0, $maxDepth=null)
	{
		if ($this->isNewRecord)
			return array();
		else
			return Term::getTree($this->id, $parent, $maxDepth);
	}

	/**
	 * 根据id更新权重
	 * @param integer $id
	 * @param integer $weight
	 */
	public static function updateWeightByPk($id, $weight) {
		$weight = intval($weight);
		$id = intval($id);
		if ($taxonomy = self::model()->findFromCache($id)) {
			$taxonomy->weight = $weight;
			if ($taxonomy->updateByPk($id, array('weight'=>$weight)))
				Yii::app()->getCache()->set("taxonomy_id_{$id}", $taxonomy);
		}
	}

	/**
	 * 根据ID和别名从缓存中查找
	 * @param mixed $idName id或别名
	 * @return Taxonomy|NULL
	 */
	public static function findFromCache($idName)
	{
		static $cache = array();

		if (!isset($cache[$idName])) {
			if (is_numeric($idName))
				$attribute = 'id';
			else
				$attribute = 'slug';

			$cacheKey = "taxonomy_{$attribute}_{$idName}";
			if (($model = Yii::app()->getCache()->get($cacheKey)) === false) {
				$model = self::model()->findByAttributes(array($attribute => $idName));
				Yii::app()->getCache()->set($cacheKey, $model);
			}

			$cache[$idName] = $model;
		}

		return $cache[$idName];
	}

	/**
	 * 删除分类下的术语
	 */
	protected function deleteTerms()
	{
		foreach ($this->terms as $term)
			$term->delete();
	}

	/**
	 * 删除缓存
	 */
	protected function deleteCache()
	{
		$cache = Yii::app()->getCache();
		$cache->delete("taxonomy_id_{$this->id}");
		$cache->delete("taxonomy_slug_{$this->slug}");
	}

	/**
	 * 验证之前
	 * @see CModel::beforeValidate()
	 */
	protected function beforeValidate()
	{
		if (parent::beforeValidate())
		{
			$this->name = trim($this->name);
			$this->slug = preg_replace('/\s+/i', '-', trim(strtolower($this->slug)));
			return true;
		} else
			return false;
	}

	/**
	 * 保存之后
	 * @see CActiveRecord::afterSave()
	 */
	protected function afterSave()
	{
		parent::afterSave();
		$this->deleteCache();
	}

	/**
	 * 删除之后
	 * @see CActiveRecord::afterDelete()
	 */
	protected function afterDelete()
	{
		parent::afterDelete();
		$this->deleteCache();
		$this->deleteTerms();
	}
}