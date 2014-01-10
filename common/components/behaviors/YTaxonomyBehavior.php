<?php
/**
 * YTaxonomyBehavior class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YTaxonomyBehavior 分类行为
 *
 * @author Yang <css3@qq.com>
 * @package common.components.behaviors
 */
class YTaxonomyBehavior extends CActiveRecordBehavior
{
	/**
	 * 对象所有术语列表
	 * @var null|array
	 */
	private $_terms;

	private $_termIds;

	/**
	 * 对象的分类配置
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * 准备对象分类设置
	 * @return array
	 */
	public function prepareTaxonomies()
	{
		static $cache = array();
		$owner = $this->getOwner();
		$className = get_class($owner);

		if (!isset($cache[$className])) {
			$taxonomies = array();
			foreach ((array) $this->taxonomies as $key => $value) {
				$default = array(
					'many' => false, //是否可以拥有多个分类, 可以限制个数
					'input' => 'select', //input类型
					'custom' => false, //允许自定义，例如动态输入标签
					'allowEmpty' => true, //是否允许为空
					'cascade' => false, //级联性
					'channel' => false, //绑定对应的栏目
				);

				if (is_numeric($key)) {
					$taxSlug = $value;
					$params = $default;
				} else {
					$taxSlug = $key;
					$params = array_merge($default, (array) $value);
				}

				if (!$taxonomy = Taxonomy::findFromCache($taxSlug))
					continue;

				if (is_numeric($params['many'])) {
					$params['many'] = max(1, intval($params['many']));
				} else {
					$params['many'] = $params['many'] ? true : false;
				}

				if (!isset($params['label'])) {
					$labels = $owner->attributeLabels();
					if (isset($labels[$taxSlug])) {
						$params['label'] = $labels[$taxSlug];
					} else {
						$params['label'] = $taxonomy->name;
					}
				}

				if (!isset($params['attribute'])) {
					$params['attribute'] = $taxSlug . '_id';
				}
				$params['isSelf'] = $owner->hasAttribute($params['attribute']); //是否是模型自带字段（表中有这个字段）

				$params['taxonomy'] = $taxonomy;
				$taxonomies[$taxSlug] = $params;
			}
			$cache[$className] = $taxonomies;
		}
		return $cache[$className];
	}

	/**
	 * 设置术语ids
	 * @param mixed $taxonomy
	 * @param mixed $value
	 */
	public function setTermIds($taxonomy, $value=null)
	{
		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();

		if (is_array($taxonomy)) {
			foreach ($taxonomy as $taxSlug => $value) {
				$this->setTermIds($taxSlug, $value);
			}
			return;
		} else {
			if ($taxonomy instanceof Taxonomy) {
				$taxSlug = $taxonomy->slug;
			} else {
				$taxSlug = $taxonomy;
			}

			if (!isset($taxonomies[$taxSlug])) {
				return;
			}

			if (is_string($value)) {
				$value = trim($value);
			}

			$params = $taxonomies[$taxSlug];

			if ($params['many'] === true) {
				$count = null;
			} elseif ($params['many'] === false) {
				$count = 1;
			} else {
				$count = $params['many'];
			}

			if (!$params['custom']) {
				$ids = array_unique(array_map('intval', (array) $value));
				$ids = array_slice($ids, 0, $count);
				$value = array();
				foreach (Term::findFromCache($ids) as $term) {
					if ($term->taxonomy_id == $params['taxonomy']->id) {
						$value[] = $term->id;
					}
				}
			}

			if ($params['isSelf']) {
				$attribute = $taxonomies[$taxSlug]['attribute'];
				if ($params['many'] === false && is_array($value)) {
					$owner->$attribute = current($value);
				} else {
					$owner->$attribute = $value;
				}
			}

			$this->_termIds[$taxSlug] = $value;
		}
	}

	/**
	 * 获取术语ids
	 * @return array
	 */
	public function getTermIds() {
		if (!isset($this->_termIds)) {
			$taxonomies = $this->prepareTaxonomies();
			$owner = $this->getOwner();

			if ($owner->isNewRecord)
				return array();

			$this->_termIds = array();
			$terms = $this->getTerms();
			foreach ($taxonomies as $taxSlug => $params) {
				if ($params['isSelf']) {
					$attribute = $params['attribute'];
					$this->_termIds[$taxSlug] = $owner->$attribute;
					continue;
				}

				if (!isset($terms[$taxSlug])) {
					continue;
				}

				$ids = array();
				foreach ($terms[$taxSlug] as $term) {
					if ($params['custom']) {
						$ids[] = $term->name;
					} else {
						$ids[] = $term->id;
					}
				}
				if ($params['custom']) {
					$this->_termIds[$taxSlug] = implode(',', $ids);
				} else {
					$this->_termIds[$taxSlug] = $ids;
				}
			}
		}

		return $this->_termIds;
	}

	/**
	 * 解析ids
	 * @param mixed $field
	 * @return array
	 */
	public function parseIds($ids)
	{
		if ($ids && is_string($ids)) {
			$ids = preg_split('/\s*,\s*/', trim($ids), -1, PREG_SPLIT_NO_EMPTY);
		} elseif (!is_array($ids)) {
			$ids = array();
		}

		return array_unique(array_map('intval', $ids));
	}

	/**
	 * 获取分类
	 * @param mixed $taxSlug
	 * @return mixed
	 */
	public function getTaxonomy($taxSlug=null)
	{
		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();

		if ($taxSlug !== null && !isset($taxonomies[$taxSlug])) {
			throw new CException(sprintf('%s 没有分类 %s', get_class($owner), $taxSlug));
		}

		$terms = $this->getTerms();

		if ($taxSlug === null)
			return $terms;
		$params = $taxonomies[$taxSlug];
		if ($params['isSelf']) {
			$attribute = $params['attribute'];
			return Term::findFromCache($owner->$attribute);
		}
		$terms = isset($terms[$taxSlug]) ? $terms[$taxSlug] : array();
		return $params['many'] ? $terms : current($terms);
	}

	/**
	 * 获取对象所有术语列表
	 * @return array
	 */
	public function getTerms()
	{
		$owner = $this->getOwner();

		if (!isset($this->_terms)) {
			if ($owner->isNewRecord)
				return array();

			$terms = Term::findFromCache($this->getTermIdsFromDb());
			foreach ($terms as $term) {
				if (!$taxonomy = Taxonomy::findFromCache($term->taxonomy_id))
					continue;
				$term->taxonomy = $taxonomy;
				$this->_terms[$taxonomy->slug][] = $term;
			}
		}

		return $this->_terms;
	}

	/**
	 * 从数据库中获取术语ids
	 * @return array
	 */
	public function getTermIdsFromDb()
	{
		$owner = $this->getOwner();
		$cacheKey = $this->getTermIdsCacheKey();
		if (($termIds = Yii::app()->getCache()->get($cacheKey)) === false) {
			$termIds = $owner->getDbConnection()
				->createCommand(" SELECT term_id FROM {{term_object}} WHERE bundle=:bundle AND object_id = :object_id")
				->bindValue(':bundle', get_class($owner))
				->bindValue(':object_id', $owner->getPrimaryKey())
				->queryColumn();
			Yii::app()->getCache()->add($cacheKey, $termIds, Setting::get('system', '_object_term_ids_expire', 2592000)); //30天
		}

		return $termIds;
	}

	/**
	 * 获取对象所有术语id缓存key
	 * @return string
	 */
	public function getTermIdsCacheKey()
	{
		$className = get_class($this->getOwner());
		$primaryKey = $this->getOwner()->getPrimaryKey();
		return "{$className}_{$primaryKey}_termIds";
	}

	/**
	 * 分类相关查询
	 * @param integer $limit
	 * @return array
	 */
	public function related($limit=-1)
	{
		$owner = $this->getOwner();
		$criteria = new CDbCriteria(array(
			'limit' => $limit,
			'condition' => 'id !=:id',
			'params' => array(':id'=>$owner->getPrimaryKey()),
		));

		$termIds = $this->getTermIds();

		$owner->getDbCriteria()->mergeWith($criteria);
		return $owner->byAndTerm($termIds, $limit);
	}

	/**
	 * by Term IN 操作
	 * @param mxied $term
	 * @param integer $limit
	 * @return CActiveRecord
	 */
	public function byTerm($term, $limit=-1)
	{
		$owner = $this->getOwner();
		$termIds = array();

		if (is_array($term) && ($term[0] instanceof Term)) {
			foreach ($term as $model) {
				$termIds[] = $model->id;
			}
		} elseif ($term instanceof Term) {
			$termIds[] = $term->id;
		} elseif (is_array($term)) {
			$termIds = $term;
		} else {
			$termIds[] = (int) $term;
		}

		$criteria = new CDbCriteria(array(
			'join'=>" INNER JOIN {{term_object}} AS term ON term.object_id = t.id ",
			'limit'=> $limit,
			'condition'=>'term.bundle=:bundle',
			'params'=>array(':bundle'=>get_class($owner)),
		));

		$criteria->compare('term.term_id', $termIds);
		$owner->getDbCriteria()->mergeWith($criteria);
		return $owner;
	}

	/**
	 * by Term AND 操作
	 * @param mxied $term
	 * @param integer $limit
	 * @return CActiveRecord
	 */
	public function byAndTerm($termIds, $limit=-1)
	{
		$owner = $this->getOwner();

		if (empty($termIds))
			return $owner;

		$join = '';
		$criteria = new CDbCriteria(array(
			'limit' => $limit,
		));

		$bundle = get_class($owner);
		$taxonomies = $this->prepareTaxonomies();
		foreach((array) $termIds as $taxSlug => $termId) {
			if (!empty($taxonomies[$taxSlug]['isSelf'])) {
				$criteria->compare($taxonomies[$taxSlug]['attribute'], $termId);
				continue;
			}

			$alias = "`term{$taxSlug}`";
			$join .= " INNER JOIN {{term_object}} AS {$alias} ON {$alias}.object_id=t.id ";
			$criteria->compare("{$alias}.term_id", $termId);
			$criteria->compare("{$alias}.bundle", $bundle);
			$criteria->distinct = true;
		}
		$criteria->join = $join;

		$owner->getDbCriteria()->mergeWith($criteria);
		return $owner;
	}

	/**
	 * by TermSlug
	 * @param mxied $taxonomy
	 * @param mxied $termSlug
	 * @param integer $limit
	 * @return CActiveRecord
	 */
	public function byTermSlug($taxonomy, $termSlug, $limit=-1)
	{
		$owner = $this->getOwner();

		if (!$taxonomy instanceof Taxonomy) {
			$taxonomy = Taxonomy::findFromCache($taxonomy);
		}

		if (!$taxonomy) {
			throw new CException(sprintf('没有找到别名为s%分类', $taxonomy));
		}

		$termIds = array();
		$taxonomies = $this->prepareTaxonomies();

		if ($termSlug instanceof Term) {
			$termIds[] = $termSlug->id;
		} else {
			foreach ((array) $termSlug as $slug) {
				if ($term = Term::findFromCacheBySlug($slug, $taxonomy->id)) {
					$termIds[] = $term->id;
				} else {
					$termIds[] = null;
				}
			}
		}

		if (isset($taxonomies[$taxonomy->slug]) && $taxonomies[$taxonomy->slug]['isSelf']) {
			$attribute = $taxonomies[$taxonomy->slug]['attribute'];
			$criteria = new CDbCriteria();
			$criteria->compare('t.' . $attribute, $termIds);
			$owner->getDbCriteria()->mergeWith($criteria);
			return $owner;
		}

		return $this->byTerm($termIds, $limit);
	}

	/**
	 * by Taxonomy IN 操作
	 * @param mxied $term
	 * @param mxied $limit
	 * @return CActiveRecord
	 */
	public function byTaxonomy($taxonomy, $limit=-1)
	{
		$owner = $this->getOwner();

		$taxonomy = Taxonomy::model()->findFromCache($taxonomy);

		if (!$taxonomy) {
			throw new CException(sprintf('没有找到别名为s%分类', $taxonomy));
		}

		$terms = $taxonomy->getTree();
		$taxonomies = $this->prepareTaxonomies();

		if (isset($taxonomies[$taxonomy->slug]) && $taxonomies[$taxonomy->slug]['isSelf']) {
			$attribute = $taxonomies[$taxonomy->slug]['attribute'];
			$criteria = new CDbCriteria();
			$criteria->limit = $limit;
			$termIds = array();
			foreach ($terms as $term) {
				$termIds[] = $term->id;
			}
			$criteria->compare('t.' . $attribute, $termIds);
			$owner->getDbCriteria()->mergeWith($criteria);
			return $owner;
		}

		return $this->byTerm($terms, $limit);
	}

	/**
	 * 搜索ByTaxonomy
	 * @param mixed $criteria
	 */
	public function searchByTaxonomy($criteria=null)
	{
		return $this->byAndTerm($this->getTermIds());
	}

	/**
	 * 验证之前
	 * @see CModelBehavior::beforeValidate()
	 */
	public function beforeValidate($event)
	{
		if (!isset($this->_termIds))
			return;

		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();

		$termIds = $this->getTermIds();
		foreach ($taxonomies as $taxSlug => $params) {
			if (!$params['allowEmpty']) {
				if (!isset($termIds[$taxSlug]) || ($params['custom'] && empty($termIds[$taxSlug]))) {
					$owner->addError("termIds[{$taxSlug}]", $params['label'] . ' 不能为空白.');
				}
			}
		}
	}

	/**
	 * 根据模型属性查找分类对象
	 * @param string $attribute
	 * @return Taxonomy|null
	 */
	public function findTaxonomyByAttribute($attribute)
	{
		foreach ($this->prepareTaxonomies() as $taxSlug => $params)
		{
			if (isset($params['attribute']) && $params['attribute'] = $attribute)
				return $params['taxonomy'];
		}
	}

	/**
	 * 保存之前
	 * @see CActiveRecordBehavior::beforeSave()
	 */
	public function beforeSave($event)
	{
		if (!isset($this->_termIds))
			return;

		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();

		$termIds = $this->getTermIds();
		foreach ($taxonomies as $taxSlug => $params) {
			if ($params['isSelf']) {
				$attribute = $params['attribute'];
				if (!empty($termIds[$taxSlug])) {
					$owner->$attribute = implode(',', $this->parseIds($termIds[$taxSlug]));
				} else {
					$owner->$attribute = 0;
				}
			}
		}
	}

	/**
	 * 对象保存之后
	 * @see CActiveRecordBehavior::afterSave()
	 */
	public function afterSave($event)
	{
		if (!isset($this->_termIds))
			return;

		$owner = $this->getOwner();

		$newTermIds = array();
		$taxonomies = $this->prepareTaxonomies();
		foreach ($this->_termIds as $taxSlug => $value) {
			if (!isset($taxonomies[$taxSlug]))
				continue;

			$params = $taxonomies[$taxSlug];

			if ($params['many'] === true) {
				$count = null;
			} elseif ($params['many'] === false) {
				$count = 1;
			} else {
				$count = $params['many'];
			}

			if ($params['custom']) {
				$ids = Term::custom($value, $taxSlug, $count);
			} else {
				$ids = (array) $value;
			}

			$newTermIds = array_merge($newTermIds, $ids);
		}

		foreach ($newTermIds as $index => $id) {
			//$id为0的删除
			if (!$id) {
				unset($newTermIds[$index]);
			}
		}

		$oldTermIds = $addTermIds = $delTermIds = array();
		if ($owner->isNewRecord) {
			$addTermIds = $newTermIds;
		} else {
			$oldTermIds = $this->getTermIdsFromDb();
			$delTermIds = array_diff($oldTermIds, $newTermIds);
			$addTermIds = array_diff($newTermIds, $oldTermIds);
		}

		$connection = $owner->getDbConnection();
		if (!$transaction = $connection->getCurrentTransaction())
			$transaction = $connection->beginTransaction();

		try {
			if ($delTermIds !== array()) {
				$connection->createCommand()->delete('{{term_object}}', array('and',
					'object_id=:object_id',
					array('in', 'term_id', $delTermIds)
				), array(':object_id'=>$owner->getPrimaryKey()));
			}

			if ($addTermIds !== array()) {
				$objectId = $owner->getPrimaryKey();
				$bundle = get_class($owner);
				foreach($addTermIds as $termId) {
					$values[] = "('{$bundle}', {$objectId}, {$termId}, '" . time() . "')";
				}
				$command = $connection->createCommand("INSERT INTO {{term_object}} (bundle, object_id, term_id, create_time) VALUES " . implode(',', $values));
				$command->execute();
			}
			$transaction->commit();
		} catch (Exception $e) {
			Yii::log(get_class($this) . ' 保存对象分类失败. 错误信息:' . $e->getMessage(), CLogger::LEVEL_ERROR);
			$transaction->rollBack();
			return;
		}

		Yii::app()->getCache()->set($this->getTermIdsCacheKey(), $newTermIds, Setting::get('system', '_object_term_ids_expire', 2592000)); //30天
	}

	public function deleteTermRelationship()
	{
		$owner = $this->getOwner();
		$owner->getDbConnection()->createCommand()->delete('{{term_object}}', 'bundle=:bundle AND object_id=:object_id', array(
			':bundle' => get_class($owner),
			':object_id' => $owner->getPrimaryKey(),
		));
		Yii::app()->getCache()->delete($this->getTermIdsCacheKey());
	}

	/**
	 * 对象删除之后
	 * @see CActiveRecordBehavior::afterDelete()
	 */
	public function afterDelete($event)
	{
		$this->deleteTermRelationship();
	}
}