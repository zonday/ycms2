<?php
/**
 * YContentCacheDependency class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YContentCacheDependency
 *
 * @author Yang <css3@qq.com>
 * @package common.components
 */
class YContentCacheDependency extends CCacheDependency
{
	public $modelList = array();

	public function generateDependentData()
	{
		$lastUpdateTime = array();

		if (isset($_GET['path'])) {
			$parts = explode('/', $_GET['path']);
			if ($channel = Channel::get($parts[count($parts)-1])) {
				if ($channel->type == Channel::TYPE_PAGE) {
					return $channel->update_time;
				} elseif ($channel->type == Channel::TYPE_LIST && !in_array($channel->model, $this->modelList)) {
					$this->modelList[] = $channel->model;
				} else {
					foreach ($channel->getChildren() as $child) {
						if ($child->type == Channel::TYPE_LIST && !in_array($channel->model, $this->modelList)) {
							$this->modelList[] = $child->model;
						}
					}
				}
			}
		}

		if (!$this->modelList) {
			$this->modelList = array_keys(Channel::getModelList());
		}

		foreach ($this->modelList as $modelClass) {
			$model = CActiveRecord::model($modelClass);
			$table = $model->tableName();
			if (!isset($lastUpdateTime[$table]) && $model->hasAttribute('update_time')) {
				if ($model instanceof  Node) {
					$lastUpdateTime[$table] = $model->getLastUpdateTime();
				} elseif ($value = Yii::app()->getDB()->createCommand()
					->select('update_time')
					->from($table)
					->order('update_time DESC')
					->limit(1)
					->queryScalar()) {
					$lastUpdateTime[$table] = $value;
				}
			}
		}

		if ($lastUpdateTime)
			return max($lastUpdateTime);
	}
}