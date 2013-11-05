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
					'multiple' => false,
					'input' => 'select',
					'dynamic' => false,
					'allowEmpty' => true,
					'cascade' => false,
					'channel' => false,
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

				if ($params['dynamic']) {
					if (is_numeric($params['multiple']))
						$params['multiple'] = max(1, abs(intval($params['multiple'])));
					else
						$params['multiple'] = true;
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
				$params['isSelf'] = $owner->hasAttribute("{$taxSlug}_id"); //是否是模型自带字段（表中有这个字段）
				$taxonomies[$taxSlug] = $params;
			}
			$cache[$className] = $taxonomies;
		}
		return $cache[$className];
	}

	/**
	 * 设置分类
	 * @param string $value
	 */
	public function setTaxonomy($taxonomy, $value=null)
	{
		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();
		if (is_array($taxonomy)) {
			foreach ($taxonomy as $taxSlug => $terms) {
				$this->setTaxonomy($taxSlug, $terms);
			}
			return;
		} else {
			if ($taxonomy instanceof Taxonomy) {
				$taxSlug = $taxonomy->slug;
			} else {
				$taxSlug = $taxonomy;
			}

			if (!isset($taxonomies[$taxSlug]))
				throw new CException(sprintf('%s 没有分类 %s', get_class($owner), $taxSlug));

			$params = $taxonomies[$taxSlug];
			$value = (array) $value;

			if ($params['isSelf']) {
				$attribute = "{$taxSlug}_id";
				$owner->$attribute = current($value);
				$this->_terms[$taxSlug] = $value;
				return;
			}

			if ($params['multiple'] !== true) {
				$count = is_numeric($params['multiple']) ? $params['multiple'] : 1;
				$value = array_slice($value, 0, $count);
			}

			if ($params['dynamic']) {
				if (!isset($count))
					$count = is_numeric($params['multiple']) ? $params['multiple'] : null;
				$value = Term::dynamicTerms(current($value), $taxSlug, $count);
			}

			if (!isset($this->_terms[$taxSlug]))
				$this->_terms[$taxSlug] = array();
			$this->_terms[$taxSlug] = array_merge($this->_terms[$taxSlug], $value);
		}
	}

	/**
	 * 获取分类
	 * @param mixed $taxSlug
	 * @return array|false|Term
	 */
	public function getTaxonomy($taxSlug=null, $edit=true)
	{
		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();

		if ($taxSlug !== null && !isset($taxonomies[$taxSlug]))
			throw new CException(sprintf('%s 没有分类 %s', get_class($owner), $taxSlug));

		$terms = $this->getTerms();

		foreach ($taxonomies as $tax => $params) {
			if ($params['dynamic'] && isset($terms[$tax])) {
				$values = $terms[$tax];

				foreach ($values as $index => $term) {
					if (!$term instanceof Term && $term = Term::findFromCache($term)) {
						$values[$index] = $term;
					}
				}
				if ($edit) {
					$termNames = array();
					foreach ($values as $term) {
						$termNames[] = $term->name;
					}
					$terms[$tax] = implode(',', $termNames);
				}
			}
		}

		if ($taxSlug === null)
			return $terms;
		$params = $taxonomies[$taxSlug];
		$terms = isset($terms[$taxSlug]) ? $terms[$taxSlug] : array();
		return $params['multiple'] ? $terms : current($terms);
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

			$termIds = $this->getTermIds();
			$terms = Term::findFromCache($termIds);
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
	 * 获取对象所有术语id
	 * @return array
	 */
	public function getTermIds($includeSelf=true)
	{
		$taxonomies = $this->prepareTaxonomies();
		$owner = $this->getOwner();
		if ($owner->isNewRecord)
			return array();

		$cacheKey = $this->getTermIdsCacheKey();
		if (($termIds = Yii::app()->getCache()->get($cacheKey)) === false) {
			$termIds = $owner->getDbConnection()
				->createCommand(" SELECT term_id FROM {{term_object}} WHERE bundle=:bundle AND object_id = :object_id")
				->bindValue(':bundle', get_class($owner))
				->bindValue(':object_id', $owner->getPrimaryKey())
				->queryColumn();
			Yii::app()->getCache()->add($cacheKey, $termIds);
		}

		if ($includeSelf) {
			foreach ($taxonomies as $taxSlug => $params) {
				$attribute = "{$taxSlug}_id";
				if ($params['isSelf']) {
					$termIds[] = $owner->$attribute;
				}
			}
		}

		return $termIds;
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
		$owner = $this->getOwner();
		$termIds = array();
		foreach ($this->getTerms() as $taxSlug => $terms) {
			$attribute = $taxSlug . '_id';
			if ($owner->hasAttribute($attribute)) {
				foreach ((array) $terms as $term) {
					if (is_object($term)) {
						$value = $term->id;
					} else {
						$value = intval($term);
					}

					$owner->$attribute = $value;
					if ($criteria && $value)
						$criteria->compare( 't.'. $attribute, $value);
				}
				continue;
			}

			foreach ($terms as $term) {
				if ($term instanceof Term) {
					$termIds[$taxSlug][] = $term->id;
				} else {
					$termIds[$taxSlug][] = intval($term);
				}
			}
		}

		return $this->byAndTerm($termIds);
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
	 * 对象保存之后
	 * @see CActiveRecordBehavior::afterSave()
	 */
	public function afterSave($event)
	{
		$owner = $this->getOwner();
		if (!isset($this->_terms))
			return;

		$newTermIds = array();
		$taxonomies = $this->prepareTaxonomies();
		foreach ($this->_terms as $taxSlug => $termIds) {
			if (!isset($taxonomies[$taxSlug]) || $taxonomies[$taxSlug]['isSelf'] || !is_array($termIds))
				continue;

			$newTermIds = array_merge($newTermIds, array_unique(array_map('intval', $termIds)));
		}

		foreach ($newTermIds as $index => $id) {
			if (!$id) {
				unset($newTermIds[$index]);
			}
		}

		$oldTermIds = $addTermIds = $delTermIds = array();
		if ($owner->isNewRecord) {
			$addTermIds = $newTermIds;
		} else {
			$oldTermIds = $this->getTermIds(false);
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

	/**
	 * 对象删除之后
	 * @see CActiveRecordBehavior::afterDelete()
	 */
	public function afterDelete($event)
	{
		$owner = $this->getOwner();
		$owner->getDbConnection()->createCommand()->delete('{{term_object}}', 'bundle=:bundle AND object_id=:object_id', array(
			':bundle' => get_class($owner),
			':object_id' => $owner->getPrimaryKey(),
		));
		Yii::app()->getCache()->delete($this->getTermIdsCacheKey());
	}
}