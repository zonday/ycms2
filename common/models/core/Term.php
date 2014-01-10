<?php
/**
 * Term class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * @property integer $id
 * @property integer $taxonomy_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $weight
 * @property integer $depth
 * @property array $parents
 * @property array $parentIds
 * @property Taxonomy $taxonomy
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Term extends CActiveRecord
{
	/**
	 * 树中深度
	 * @var integer
	 */
	public $depth = 0;

	public $image;

	private $_parents;
	private $_parentIds;

	/**
	 * 获取模型
	 * @param string $className
	 * @return Term
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
		return '{{term}}';
	}

	/**
	 * 获取字段规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
			array('weight', 'numerical', 'integerOnly'=>true),
			array('name, slug', 'length', 'max'=>255),
			array('slug', 'match', 'pattern'=>'/^[a-z][a-z0-9\-]+$/', 'message'=>'{attribute} 只能包含小写英文和数字、-'),
			array('slug', 'unique', 'criteria'=>array('condition'=>'t.taxonomy_id=:taxonomy_id', 'params'=>array(':taxonomy_id'=>$this->taxonomy_id))),
			array('description', 'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
			array('parentIds', 'validateParentIds'),
			array('image', 'safe'),
			array('id', 'safe', 'on'=>'search'),
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
			'taxonomy_id' => '分类',
			'name' => '名称',
			'slug' => '别名',
			'description' => '描述',
			'weight' => '权重',
			'parentIds' => '父级',
			'image' => '图片'
		);
	}

	/**
	 * 行为
	 * @see CModel::behaviors()
	 */
	public function behaviors()
	{
		if (!empty(Yii::app()->params['termImage'])) {
			$termImage = Yii::app()->params['termImage'];
			return array(
				'YFileUsageBehavior' => array(
					'class' => 'YFileUsageBehavior',
					'fields' => array(
						'image' => array(
							'location' => 'public://term',
							'type' => 'image',
							'resize'=>isset($termImage['resize']) ? $termImage['resize'] : null,
						),
					),
				)
			);
		} else {
			return array();
		}
	}

	/**
	 * 获取模型关系列表
	 * @see CActiveRecord::relations()
	 */
	public function relations()
	{
		return array(
			'taxonomy'=>array(self::BELONGS_TO, 'Taxonomy', 'taxonomy_id'),
		);
	}

	/**
	 * 验证父节点关系
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateParentIds($attribute, $params)
	{
		if ($this->isNewRecord)
			return;

		$parentIds = (array) $this->$attribute;
		if (in_array($this->id, $parentIds)) {
			$this->addError('parentIds', '父级关系不正确');
			return;
		}

		foreach ($this->getChildrenAll() as $child) {
			if (in_array($child->id, $parentIds)) {
				$this->addError('parentIds', '父级关系不正确');
				break;
			}
		}
	}

	/**
	 * 根据分类获取术语的数目
	 * @param mixed $taxonomy_id
	 * @return integer
	 */
	public function getCountbyTaxonomy($taxonomy_id=null)
	{
		if ($taxonomy_id === null)
			$taxonomy_id = $this->taxonomy_id;

		$taxonomy_id = abs(intval($taxonomy_id));

		if (empty($taxonomy_id))
			return 0;

		$cacheKey = "term_{$taxonomy_id}_count";
		if (($count = Yii::app()->getCache()->get($cacheKey)) === false)
		{
			$count = $this->countByAttributes(array('taxonomy_id' => $taxonomy_id));
			Yii::app()->getCache()->set($cacheKey, $count);
		}
		return $count;
	}

	/**
	 * 获取权重列表 根据该分类下的术语数目
	 * @return array
	 */
	public function getWeightList()
	{
		$count = $this->getCountbyTaxonomy();
		$range = range(-$count, $count);
		return array_combine($range, $range);
	}

	/**
	 * 从缓存中获取术语
	 * @param array|integer $ids
	 * @return array|Term|null 如果$ids是数组则返回数组或者返回模型或者Null
	 */
	public static function findFromCache($ids)
	{
		$models = array();
		$uncached = array();

		foreach ((array) $ids as $id) {
			$cacheKey = self::getCacheKey($id);
			if (($model = Yii::app()->getCache()->get($cacheKey)) !== false) {
				$models[] = $model;
			} else {
				$uncached[] = $id;
			}
		}
		if ($uncached) {
			foreach (self::model()->findAllByPk($uncached) as $model) {
				$cacheKey = self::getCacheKey($model['id']);
				Yii::app()->getCache()->add($cacheKey, $model);
				$models[] = $model;
			}
		}

		return is_array($ids) ? $models : (isset($models[0]) ? $models[0] : null);
	}

	/**
	 * 根据术语别名和分类id获取术语
	 * @param string $slug
	 * @param integer $taxonomy_id
	 * @return Term|null
	 */
	public static function findFromCacheBySlug($slug, $taxonomy_id)
	{
		$cacheKey = 'term_' . $taxonomy_id . '_' . $slug;
		if (($model = Yii::app()->getCache()->get($cacheKey)) === false) {
			$model = self::model()->findByAttributes(array('slug' => $slug, 'taxonomy_id' => $taxonomy_id));
			Yii::app()->getCache()->add($cacheKey, $model);
		}
		return $model;
	}

	/**
	 * 根据id更新术语权重
	 * @param integer $id
	 * @param integer $weight
	 * @param integer $taxonomy_id
	 */
	public static function updateWeightByPk($id, $weight)
	{
		$weight = intval($weight);
		$id = intval($id);
		if ($term = self::findFromCache($id)) {
			$term->weight = $weight;
			if ($term->updateByPk($id, array('weight'=>$weight))) {
				Yii::app()->getCache()->set(self::getCacheKey($id), $term);
			}
		}
	}

	/**
	 * 设置父节点Ids
	 * @param array $parents
	 */
	public function setParentIds($parentIds)
	{
		$this->_parentIds = array();
		foreach ((array) $parentIds as $id) {
			$id = abs(intval($id));
			if (in_array($id, $this->_parentIds))
				continue;
			$this->_parentIds[] = $id;
		}

		if ($this->_parentIds === array() && $this->isNewRecord)
			$this->_parentIds = array(0); //保证术语有一个父级 0为根节点
	}

	/**
	 * 获取父节点ids
	 * @return array
	 */
	public function getParentIds()
	{
		if (!isset($this->_parentIds)) {
			$this->_parentIds = array();
			$parents = $this->getParents();
			foreach ($parents as $parent)
				$this->_parentIds[] = $parent->id;
		}

		if ($this->_parentIds === array() && !$this->isNewRecord)
			$this->_parentIds = array(0); //保证术语有一个父级 0为根节点

		return $this->_parentIds;
	}

	/**
	 * 获取父节点
	 * @return array 父节点模型列表
	 */
	public function getParents()
	{
		if (!isset($this->_parents)) {
			if ($this->isNewRecord)
				$this->_parents = array();
			else {
				$cacheKey = "term_{$this->id}_parents";
				if (($this->_parents = Yii::app()->getCache()->get($cacheKey)) === false) {
					$this->_parents = array();
					$query = 'SELECT t.* FROM {{term}} AS t INNER JOIN {{term_hierarchy}} t2 ON t.id = t2.parent_id WHERE t2.parent_id !=0 AND term_id=:term_id ';
					$result = $this->getDbConnection()
						->createCommand($query)
						->bindValue(':term_id', $this->id, PDO::PARAM_INT)
						->queryAll();
					$this->_parents = $this->populateRecords($result);
					Yii::app()->getCache()->set($cacheKey, $this->_parents);
				}
			}
		}

		return $this->_parents;
	}

	/**
	 * 获取所有父节点
	 * @return array
	 */
	public function getParentsAll()
	{
		if ($this->isNewRecord)
			return array();

		$ids = array(); //已存在的父节点ids
		$parents = $this->getParents();

		foreach ($parents as $parent) {
			$ids[] = $parent['id'];
		}

		reset($parents);
		while ($parent = current($parents)) {
			foreach ($parent->getParents() as $model) {
				if (!in_array($model->id, $ids)) {
					$parents[] = $model;
					$ids[] = $model->id;
				}
			}
			next($parents);
		}

		return $parents;
	}

	/**
	 * 获取后代子节点
	 * @return array
	 */
	public function getChildren()
	{
		if ($this->isNewRecord)
			return array();

		$cacheKey = "term_{$this->id}_children";
		if (($children = Yii::app()->getCache()->get($cacheKey)) === false) {
			$query = 'SELECT t.* FROM {{term}} AS t INNER JOIN {{term_hierarchy}} AS t2 ON t.id = t2.term_id WHERE t2.parent_id=:parent_id';
			$result = $this->getDbConnection()->createCommand($query)
				->bindValue(':parent_id', $this->id, PDO::PARAM_INT)
				->queryAll();
			$children = $this->populateRecords($result);
			Yii::app()->getCache()->set($cacheKey, $children);
		}

		return $children;
	}

	/**
	 * 获取后代所有子节点
	 * @return array
	 */
	public function getChildrenAll()
	{
		if ($this->isNewRecord)
			return array();

		$ids = array();
		$children = $this->getChildren();

		foreach ($children as $child)
			$ids[] = $child->id;

		reset($children);
		while($child = current($children)) {
			foreach ($child->getChildren() as $model) {
				if (!in_array($model->id, $ids)) {
					$children[] = $model;
					$ids[] = $model->id;
				}
			}
			next($children);
		}

		return $children;
	}

	/**
	 * 根据分类ID获取分类树
	 * @param integer|null $taxonomy_id
	 * @param integer $parent
	 * @param integer|null $maxDepth
	 * @return array
	 */
	public static function getTree($taxonomy_id, $parent=0, $maxDepth=null)
	{
		if (empty($taxonomy_id))
			return array();

		static $children = array();
		static $parents = array();
		static $terms = array();

		if (!isset($children[$taxonomy_id]))
		{
			$children[$taxonomy_id] = array();
			$parents[$taxonomy_id] = array();
			$terms[$taxonomy_id] = array();

			$cacheKey = 'term_tree_' . $taxonomy_id;
			if (($result = Yii::app()->getCache()->get($cacheKey)) === false) {
				$query = 'SELECT t.*, t2.parent_id FROM {{term}} AS t INNER JOIN {{term_hierarchy}} AS t2 ON t.id = t2.term_id WHERE t.taxonomy_id=:taxonomy_id ORDER BY t.weight, t.name';
				$result = self::model()->getDbConnection()
					->createCommand($query)
					->bindValue(':taxonomy_id', $taxonomy_id, PDO::PARAM_INT)
					->queryAll();
				Yii::app()->getCache()->add($cacheKey, $result);
			}

			if (empty($result))
				return array();

			foreach ($result as $row) {
				$children[$taxonomy_id][$row['parent_id']][] = $row['id'];
				$parents[$taxonomy_id][$row['id']][] = $row['parent_id'];
				$terms[$taxonomy_id][$row['id']] = self::model()->populateRecord($row);
			}
		}

		$maxDepth = (!isset($maxDepth)) ? count($children[$taxonomy_id]) : $maxDepth;
		$tree = array();
		$processParents = array();

		$processParents[] = $parent;
		while (count($processParents)) {
			$parent = array_pop($processParents);
			$depth = count($processParents);

			if ($maxDepth > $depth && !empty($children[$taxonomy_id][$parent])) {
				$hasChildren = false;
				$child = current($children[$taxonomy_id][$parent]);

				do {
					if (empty($child))
						break;

					$term = $terms[$taxonomy_id][$child];

					if (isset($parents[$taxonomy_id][$term->id]))
						$term = clone $term;

					$term->depth = $depth;
					$term->parentIds = $parents[$taxonomy_id][$term->id];

					$tree[] = $term;

					if (!empty($children[$taxonomy_id][$term->id])) {
						$hasChildren = true;
						$processParents[] = $parent;
						$processParents[] = $term->id;
						reset($children[$taxonomy_id][$term->id]);
						next($children[$taxonomy_id][$parent]);
						break;
					}
				} while ($child = next($children[$taxonomy_id][$parent]));

				if (!$hasChildren)
					reset($children[$taxonomy_id][$parent]);
			}
		}

		return $tree;
	}

	/**
	 * 生成树列表
	 * @param integer|null $taxonomy_id
	 * @param integer $parent
	 * @param integer|null $maxDepth
	 * @param string $spacer
	 * @return array
	 */
	public function generateTreeList($taxonomy_id=null, $parent=0, $maxDepth=null, $spacer=' — ')
	{
		if ($taxonomy_id === null)
			$taxonomy_id = $this->taxonomy_id;

		$tree = self::getTree($taxonomy_id, $parent, $maxDepth);

		$list = array();
		foreach ($tree as $term) {
			if (isset($list[$term->id]))
				continue;
			if ($this->id && ($term->id == $this->id || in_array($this->id, $term->parentIds)))
				continue;
			$list[$term->id] = str_repeat($spacer, $term->depth) . ' ' . $term->name;
		}
		return $list;
	}

	/**
	 * 根据分类名，动态插入术语
	 * @param array|string 条目名称
	 * @param integer|string 分类别名或id
	 * @return array 保存后的术语ids
	 */
	public static function custom($termNames, $taxonomyIdName, $maxCount=null)
	{
		if (is_string($termNames) && !trim($termNames))
			return array();

		if (!is_array($termNames))
			$termNames = array_unique(preg_split('/\s*,\s*/', $termNames, -1, PREG_SPLIT_NO_EMPTY));

		if ($termNames === array())
			return array();

		if (is_numeric($maxCount) && $maxCount > 0) {
			$termNews = array_slice($array, 0, $maxCount);
		}

		$connection = self::model()->getDbConnection();

		if (!$taxonomy = Taxonomy::findFromCache($taxonomyIdName))
			return array();
		else
			$taxonomy_id = $taxonomy->id;

		$placeholders = array();
		$i = 0;
		foreach ($termNames as $name) {
			$placeholders[':name_' . $i++] = $name;
		}

		$command = $connection->createCommand('SELECT id, name FROM {{term}} WHERE name IN ('. implode(',', array_keys($placeholders)) .') AND taxonomy_id=:taxonomy_id');
		$command->bindParam(':taxonomy_id', $taxonomy_id, PDO::PARAM_INT);
		foreach ($placeholders as $place => $value) {
			$command->bindValue($place, $value, PDO::PARAM_STR);
		}
		$terms = $command->queryAll();

		$savedNames = array();
		$savedIds = array();
		foreach ($terms as $term) {
			$savedNames[] = $term['name'];
			$savedIds[] = $term['id'];
		}

		$insertNames = array_diff($termNames, $savedNames);
		$newIds = array();

		foreach ($insertNames as $name) {
			$connection->createCommand()->insert('{{term}}', array(
				'name' => $name,
				'slug' => urlencode($name),
				'taxonomy_id' => $taxonomy_id
			));

			$lastInsertId = $connection->getLastInsertID();
			$newIds[] = $lastInsertId;
			$savedIds[] = $lastInsertId;
		}

		if ($insertNames) {
			$cacheKey = 'term_tree_' . $taxonomy_id;
			Yii::app()->getCache()->delete($cacheKey);
		}

		foreach ($newIds as $id) {
			$connection->createCommand()->insert('{{term_hierarchy}}', array(
				'term_id' => $id,
				'parent_id' => 0,
			));
		}

		return $savedIds;
	}

	/**
	 * 保存术语层级关系
	 * @return boolean|null
	 */
	protected function saveHierarchy()
	{
		if (empty($this->_parentIds)) {
			if ($this->isNewRecord)
				$this->_parentIds = array(0);
			else
				return;
		}

		$hierarchy = $this->taxonomy->hierarchy;
		if ($hierarchy == Taxonomy::HIERARCHY_DISABLED)
			$this->_parentIds = array(0);
		elseif ($hierarchy == Taxonomy::HIERARCHY_SINGLE)
			$this->_parentIds = array_slice($this->_parentIds, 0, 1);

		$this->_parentIds = array_map('intval', $this->_parentIds);

		$connection = $this->getDbConnection();
		if (!$transaction = $connection->getCurrentTransaction())
			$transaction = $connection->beginTransaction();

		try {
			$addParentIds = array();
			$delParentIds = array();

			if ($this->isNewRecord) {
				$addParentIds = $this->_parentIds;
			} else {
				$query = 'SELECT parent_id FROM {{term_hierarchy}} WHERE term_id=:term_id';
				$oldParentIds = $connection->createCommand($query)
					->bindValue(':term_id', $this->id, PDO::PARAM_INT)
					->queryColumn();

				$oldParentIds = array_map('intval', $oldParentIds);
				$delParentIds = array_diff($oldParentIds, $this->_parentIds);
				$addParentIds = array_diff($this->_parentIds, $oldParentIds);
			}

			if ($delParentIds) {
				$connection->createCommand()->delete('{{term_hierarchy}}', array(
					'and',
					'term_id=:term_id',
					array('in', 'parent_id', $delParentIds)
				), array(':term_id' => $this->id));
			}

			if ($addParentIds) {
				foreach ($addParentIds as $parentId) {
					$connection->createCommand()->insert('{{term_hierarchy}}', array(
						'term_id' => $this->id,
						'parent_id' => $parentId,
					));
				}
			}

			$transaction->commit();
			return true;
		} catch (CDbException $e) {
			$transaction->rollback();
			Yii::log(sprtif('保存术语关系失败%s', $e->getMessage()), CLogger::LEVEL_WARNING);
			return false;
		}
	}

	/**
	 * 删除节点关系
	 * @return boolean
	 */
	protected function deleteHierarchy()
	{
		$connection = $this->getDbConnection();
		if (!$transaction = $connection->getCurrentTransaction())
			$transaction = $connection->beginTransaction();

		try {
			$command = $connection->createCommand();
			$command->delete('{{term_hierarchy}}', 'term_id=:term_id', array(':term_id' => $this->id));

			foreach ($this->getChildren() as $model) {
				$result = $connection->createCommand('SELECT 1 FROM {{term_hierarchy}} WHERE term_id=:term_id AND parent_id=0 LIMIT 1')
					->bindValue(':term_id', $model->id, PDO::PARAM_INT)
					->queryRow();
				//如果术语子节点存在一个根节点关系，则删除该节点关系，否则更新关系为根节点关系
				if ($result !== false) {
					$command->delete(
						'{{term_hierarchy}}',
						'term_id=:term_id AND parent_id=:parent_id',
						array(':term_id' => $model->id, ':parent_id' => $this->id)
					);
				} else {
					$command->update(
						'{{term_hierarchy}}',
						array('parent_id' => 0),
						"term_id=:term_id AND parent_id=:id",
						array(':term_id' =>$model->id, ':id' => $this->id)
					);
				}
			}
			$transaction->commit();
			return true;
		} catch (CDbException $e) {
			$transaction->rollback();
			Yii::log(sprintf('删除术语关系失败%s', $e->getMessage()), CLogger::LEVEL_WARNING);
			return false;
		}
	}

	/**
	 * 删除术语对象关系
	 */
	protected function deleteObjectRelationship()
	{
		$this->getDbConnection()->createCommand()->delete('{{term_object}}', 'term_id=:term_id', array(':term_id' => $this->id));
	}

	/**
	 * 获取缓存key
	 * @param integer $id
	 * @return string
	 */
	protected static function getCacheKey($id)
	{
		return "term_{$id}";
	}

	/**
	 * 删除缓存
	 */
	protected function deleteCache()
	{
		$cache = Yii::app()->getCache();
		$cache->delete("term_tree_{$this->taxonomy_id}");
		$cache->delete("term_{$this->taxonomy_id}_{$this->slug}");
		$cache->delete("term_{$this->taxonomy_id}_count");
		$cache->delete("term_{$this->id}_parents");
		$cache->delete("term_{$this->id}_children");
		$cache->delete("term_{$this->id}");
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
		$this->saveHierarchy();
		$this->deleteCache();
	}

	/**
	 * 删除之前
	 * @see CActiveRecord::beforeDelete()
	 */
	protected function beforeDelete()
	{
		if (parent::beforeDelete()) {
			if ($this->deleteHierarchy() !== false)
				return true;
			else
				return false;
		} else
			return false;
	}

	/**
	 * 删除之后
	 * @see CActiveRecord::afterDelete()
	 */
	public function afterDelete()
	{
		parent::afterDelete();
		$this->deleteObjectRelationShip();
		$this->deleteCache();
	}
}
