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
		$terms = isset($terms[$taxSlug]) ? $terms[$taxSlug] : array();
		return $params['many'] ? $terms : current($terms);
	}

	/**
	 * 获取对象所有术语列表
	 * @return array
	 */
	public function getTerms()
	{
		$taxonomies = $this->prepareTaxonomies();
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
		$cacheKey = $this->getTermIdsCacheKey();
		if (($termIds = Yii::app()->getCache()->get($cacheKey)) === false) {
			$owner = $this->getOwner();
			$termIds = $owner->getDbConnection()
				->createCommand(" SELECT term_id FROM {{term_object}} WHERE bundle=:bundle AND object_id = :object_id")
				->bindValue(':bundle', get_class($owner))
				->bindValue(':object_id', $owner->getPrimaryKey())
				->queryColumn();
			Yii::app()->getCache()->add($cacheKey, $termIds);
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
	 * @param mixed $limit
	 * @return array
	 */
	public function related($limit=null)
	{
		$owner = $this->getOwner();
		$criteria = new CDbCriteria(array(
			'limit' => $limit,
			'condition' => 'id !=:id',
			'params' => array(':id'=>$owner->getPrimaryKey()),
		));

		$termIds = $this->getTermIds(false);

		if ($termIds) {
			$criteria->join = "INNER JOIN {{term_object}} AS term ON term.object_id = t.id";
			$criteria->addInCondition('term.term_id', $termIds);
			$criteria->addCondition('term.bundle="' . get_class($this->getOwner()) . '"');
		}

		$owner->getDbCriteria()->mergeWith($criteria);
		return $owner;
	}

	/**
	 * by Term IN 操作
	 * @param mxied $term
	 * @param mxied $limit
	 * @return CActiveRecord
	 */
	public function byTerm($term, $limit=null)
	{
		$owner = $this->getOwner();

		if (empty($term))
			return $owner;

		if (is_array($term) && ($term[0] instanceof Term)) {
			foreach ($term as $model) {
				$termIds[] = $term->id;
			}
		} elseif ($term instanceof Term) {
			$termIds = $term->id;
		} else {
			$termIds = $term;
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
	 * @param mxied $limit
	 * @return CActiveRecord
	 */
	public function byAndTerm($termIds, $limit=null)
	{
		$owner = $this->getOwner();

		if (empty($termIds))
			return $owner;

		$join = '';
		$criteria = new CDbCriteria(array(
			'distinct' => true,
			'limit' => $limit,
		));

		$bundle = get_class($owner);
		foreach((array) $termIds as $index => $termId) {
			$alias = "`term{$index}`";
			$join .= " INNER JOIN {{term_object}} AS {$alias} ON {$alias}.object_id=t.id ";
			$criteria->compare("{$alias}.term_id", $termId);
			$criteria->compare("{$alias}.bundle", $bundle);
		}
		$criteria->join = $join;

		$owner->getDbCriteria()->mergeWith($criteria);
		return $owner;
	}

	/**
	 * by TermSlug
	 * @param mxied $taxonomy
	 * @param mxied $termSlug
	 * @param mxied $limit
	 * @return CActiveRecord
	 */
	public function byTermSlug($taxonomy, $termSlug, $limit=null)
	{
		$owner = $this->getOwner();

		if (empty($taxonomy) || empty($termSlug))
			return $owner;

		if (!$taxonomy instanceof Taxonomy) {
			$taxonomy = Taxonomy::findFromCache($taxonomy);
		}

		if (!$taxonomy)
			return $owner;

		$terms = array();
		foreach ((array) $termSlug as $slug) {
			if ($term = Term::findFromCacheBySlug($slug, $taxonomy->id)) {
				$terms[] = $term;
			}
		}

		return $this->byTerm($terms, $limit);
	}

	/**
	 * by Taxonomy IN 操作
	 * @param mxied $term
	 * @param mxied $limit
	 * @return CActiveRecord
	 */
	public function byTaxonomy($taxonomy, $limit=null)
	{
		$taxonomy = Taxonomy::model()->findFromCache($taxonomy);
		if ($taxonomy) {
			$terms = $taxonomy->getTree();
		} else {
			$terms = array();
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
	 * 根据术语ids获取对象数目
	 * @param mixed $termIds
	 * @return integer
	 */
	public function getCountByTermIds($termIds)
	{
		$owner = $this->getOwner();
		$termIds = array_map('intval', (array) $termIds);
		$count = $owner->getDbConnection()->createCommand()
			->select('COUNT(*)')
			->from('{{term_object}}')
			->where(array('and', 'bundle=:bundle', array('in', 'term_id', $termIds)), array(':bundle'=>get_class($owner)))
			->queryScalar();
		return $count === false ? 0 : $count;
	}

	/**
	 * 获取对象ids
	 * @param mixed $termIds 术语ids
	 * @param string $operator
	 * @param mixed $limit
	 * @param mixed $offset
	 * @return array
	 */
	public function getObjectIdsByTermId($termIds, $operator='OR', $limit=null, $offset=null)
	{
		$owner = $this->getOwner();
		$termIds = array_map('intval', (array) $termIds);

		if (strtoupper($operator) === 'AND') {
			$command = $owner->getDbConnection()->createCommand()
				->selectDistinct('t.object_id')
				->from('{{term_object}} AS t');
				$where[] = 't.bundle=:bundle';
			foreach ($termIds as $index => $termId) {
				$alias = "t" . ($index + 1) ;
				$command->join("{{term_object}} AS {$alias}", "{$alias}.object_id = t.object_id");
				$where[] = "{$alias}.term_id={$termId}";
			}
			$command->where(implode(' AND ', $where), array(':bundle'=>get_class($owner)));
		} else {
			$command = $owner->getDbConnection()->createCommand()
				->select('t.object_id')
				->from('{{term_object}} AS t')
				->where(array('and', 'bundle=:bundle', array('in', 'term_id', $termIds)), array(':bundle'=>get_class($owner)));
		}
		if ($limit !== null)
			$command->limit($limit, $offset);
		return $command->queryColumn();
	}

	/**
	 * 验证之前
	 * @see CModelBehavior::beforeValidate()
	 */
	public function beforeValidate($event)
	{
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
	 * 对象保存之后
	 * @see CActiveRecordBehavior::afterSave()
	 */
	public function afterSave($event)
	{
		$owner = $this->getOwner();
		if (!isset($this->_termIds))
			return;

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
				$ids = array_unique(array_map('intval', (array) $value));
				$ids = array_slice($ids, 0, $count);
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

		Yii::app()->getCache()->set($this->getTermIdsCacheKey(), $newTermIds);
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