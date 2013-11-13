<?php
/**
 * YMetaBehavior class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YMetaBehavior 分类行为
 *
 * @author Yang <css3@qq.com>
 * @package common.components.behaviors
 */
class YMetaBehavior extends CModelBehavior
{
	/**
	 * meta表
	 * @var string
	 */
	public $metaTable;

	/**
	 * 对象的Meta
	 * @var array|null
	 */
	private $_meta;

	/**
	 * 需要保存的Meta
	 * @var array
	 */
	private $_saveMeta = array();

	/**
	 * 获取缓存Key
	 * @return string
	 */
	public function getCacheKey()
	{
		$owner = $this->getOwner();
		return get_class($owner) . '_meta_' . $owner->getPrimaryKey();
	}

	/**
	 * 获取Meta 如果$key为null 则返回全部
	 * @param mixed $key
	 * @return mixed
	 */
	public function getMeta($key=null)
	{
		if ($key !== null && isset($this->_saveMeta[$key]))
			return $this->_saveMeta[$key];

		if (!isset($this->_meta)) {
			$this->_meta = array();
			$owner = $this->getOwner();
			if (!$owner->getIsNewRecord()) {
				if (($this->_meta = Yii::app()->getCache()->get($this->getCacheKey())) === false) {
					$rows = $owner->getDbConnection()
						->createCommand()
						->select(array('meta_key', 'meta_value'))
						->from($this->getTable())
						->where('object_id=:object_id', array(':object_id'=>$owner->getPrimaryKey()))
						->queryAll();
					foreach ($rows as $row)
						$this->_meta[$row['meta_key']] = $row['meta_value'];

					Yii::app()->getCache()->set($this->getCacheKey(), $this->_meta);
				}
			}
		}

		if ($key !== null)
			return isset($this->_meta[$key]) ? $this->_meta[$key] : null;
		else
			return $this->_meta;
	}

	/**
	 * 添加一个meta
	 * @param string $key
	 * @param mixed $value
	 */
	public function addMeta($key, $value)
	{
		$this->_saveMeta[$key] = $value;
	}

	/**
	 * 设置一个meta
	 * @param string $key
	 * @param mixed $value
	 */
	public function setMeta($key, $value)
	{
		$this->_meta[$key] = $value;
	}

	/**
	 * 删除一个meta
	 * @param string $key
	 */
	public function delMeta($key)
	{
		if ($this->getOwner()->getIsNewRecord())
			return;

		$meta = $this->getMeta();
		if (isset($meta[$key])) {
			$connection = $this->getOwner()->getDbConnection();
			$connection->createCommand()->delete($this->getTable(), 'object_id=:object_id AND meta_key=:meta_key', array(
				':object_id' => $this->getOwner()->getPrimaryKey(),
				':meta_key' => $key,
			));
			unset($meta[$key]);
			Yii::app()->getCache()->setCache($this->getCacheKey(), $meta);
		}
	}

	/**
	 * 对象保存之后
	 * @param CModelEvent $event
	 */
	public function afterSave($event)
	{
		$connection = $this->getOwner()->getDbConnection();
		if (!$transaction = $connection->getCurrentTransaction())
			$transaction = $connection->beginTransaction();

		try {
			$meta = $this->getMeta();
			$table = $this->getTable();
			foreach ($this->_saveMeta as $key => $value) {
				if (isset($meta[$key])) {
					if ($meta[$key] == $value)
						continue;
					else
						$query = "UPDATE {$talbe} SET meta_value=:value WHERE object_id=:object_id AND meta_key=:key";
				} else
					$query = "INSERT INTO {$table} (object_id, meta_key, meta_value) VALUES (:object_id, :key, :value)";

				$command = $connection->createCommand($query)
					->bindValue(':object_id', $this->getOwn()->getPrimaryKey())
					->bindValue(':key', $key, PDO::PARAM_STR)
					->bindValue(':value', $key, PDO::PARAM_STR);
				if ($command->execute() !== false)
					$this->setMeta($key, $value);
			}
			$transaction->commit();
		} catch (CDbException $e) {
			$transaction->rollback();
		}

		Yii::app()->getCache()->set($this->getCacheKey(), $this->getMeta());
	}

	/**
	 * 删除Meta关系
	 */
	public function deleteMetaRelationship()
	{
		Yii::app()->getCache()->delete($this->getCacheKey());
		$connection = $this->getOwner()->getDbConnection();
		$connection->createCommand()->delete($this->getTable(), 'object_id=:object_id', array(
			':object_id' => $this->getOwner()->getPrimaryKey(),
		));
	}

	/**
	 * 对象删除之后
	 * @param CModelEvent $event
	 */
	public function afterDelete($event)
	{
		$this->deleteMetaRelationship();
	}

	/**
	 * 获取meta表
	 * @return string
	 */
	protected function getTable()
	{
		if (isset($this->metaTable)) {
			return $this->metaTable;
		} else {
			$owner = $this->getOwner();
			return '{{' . strtolower(get_class($owner)) . '_meta}}';
		}
	}
}