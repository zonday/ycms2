<?php
/**
 * Node class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Node Model 所有内容模型必须继承该模型
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $tilte
 * @property string $excerpt
 * @property integer $status
 * @property integer $sticky
 * @property integer $promote
 * @property string $create_time
 * @property string $update_time
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
abstract class Node extends CActiveRecord
{
	/**
	 * 草稿
	 * @var integer
	 */
	const STATUS_DRAFT=0;

	/**
	 * 公开
	 * @var integer
	 */
	const STATUS_PUBLIC=1;


	/**
	 * 回收站 （暂时没有用到）
	 * @var integer
	 */
	const STATUS_TRASH=2;

	/**
	 * 用户id
	 * @var integer
	 */
	public $user_id;

	/**
	 * 栏目id
	 * @var integer 虚拟属性 根据表字段
	 */
	public $channel_id;

	/**
	 * @var integer 虚拟属性 根据表字段
	 */
	public $hits;

	/**
	 * @param string $className
	 * @return Node
	 */
	public static function model($className=__CLASS__)
	{
		$model = parent::model($className);
		if (!$model instanceof Node) {
			throw CException($className . ' 没有继承 Node');
		}
		return $model;
	}

	public static function className()
	{
		return 'Node';
	}

	/**
	 * 验证规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array_merge(
			array(
				array('title', 'required'),
				array('title', 'length', 'max'=>255),
				array('user_id, sticky, promote', 'numerical', 'integerOnly'=>true),
				array('excerpt', 'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
				array('status', 'in', 'range'=>array(self::STATUS_DRAFT, self::STATUS_PUBLIC)),
				array('create_time', 'date', 'format'=>'yyyy-MM-dd HH:mm:ss'),
			),$this->extraRules()
		);
	}

	/**
	 * 关联模型
	 * @see CActiveRecord::relations()
	 */
	public function relations()
	{
		return array_merge(array(
			'user'=>array(self::BELONGS_TO, 'User', 'user_id', 'select'=>'id, nickname, username'),
		), $this->extraRelations());
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array_merge(array(
			'id' => 'ID',
			'title' => '标题',
			'create_time' => '发布时间',
			'update_time' => '更新时间',
			'user_id' => '发布人',
			'excerpt'=>'摘要',
			'promote'=>'显示在首页',
			'sticky'=>'置顶',
			'status'=>'状态',
			'hits'=>'点击次数',
		), $this->extraLabels());
	}

	/**
	 * 模型行为
	 * @see CModel::behaviors()
	 */
	public function behaviors()
	{
		return $this->extraBehaviors();
		return array_merge(array(
			'CTimestampBehavior' => array(
				'class' => 'zii.behaviors.CTimestampBehavior',
				'createAttribute' => null,
				'updateAttribute' => 'update_time',
				'setUpdateOnCreate' => true,
			),
		), $this->extraBehaviors());
	}

	/**
	 * 扩展验证规则
	 * @return array
	 */
	public function extraRules()
	{
		return array();
	}

	/**
	 * 扩展标签
	 * @return array
	 */
	public function extraLabels()
	{
		return array();
	}

	/**
	 * 扩展行为
	 * @return array
	 */
	public function extraBehaviors()
	{
		return array();
	}

	/**
	 * 扩展模型关联
	 * @return array
	 */
	public function extraRelations()
	{
		return array();
	}

	/**
	 * 状态列表
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_DRAFT=>'草稿',
			self::STATUS_PUBLIC=>'公开',
		);
	}

	/**
	 * 获取内容模型
	 * @param string $modelClass
	 * @param mixed $channels
	 * @param boolean $static
	 * @param string $scenario
	 * @return Node
	 */
	public static function get($modelClass, $channels=null, $static=true)
	{
		$modelClass = ucfirst($modelClass);

		if ($static) {
			$model = Node::model($modelClass);
		} else {
			$model = new $modelClass();
		}

		if (!$model instanceof Node) {
			throw new CException(sprintf('%s没有继承Node', $modelClass));
		}

		if ($channels && $model->hasAttribute('channel_id')) {
			$applyChannels = array();
			foreach ((array) $channels as $index => $id) {
				if (($channel = Channel::get($id)) && $channel->model === $modelClass) {
					$applyChannels[] = $channel->id;
				}
			}

			if ($applyChannels) {
				$model->byChannel($applyChannels);
			}
		}

		return $model;
	}

	/**
	 * 根据栏目获取内容模型
	 * @param $mixed $channel 栏目 栏目对象 栏目别名 栏目id
	 * @param boolean $static 是否是静态对象
	 * @param boolean $applyChannel 是否要应用栏目
	 * @throws CException
	 * @return Node|null
	 */
	public static function contentModel($channel, $static=true, $applyChannel=true)
	{
		if ($channel = Channel::get($channel)) {
			$callClass = self::className();
			if (!strcasecmp($callClass, $channel->model) && is_subclass_of($callClass, $channel->model))
				throw new CException(sprintf('栏目模型%s没有继承%s', $channel->model, $callClass));

			$model =  $channel->getObjectModel($static, $applyChannel);
			if ($model instanceof Node)
				return $model;
			else
				throw new CException(sprintf('栏目模型%s没有继承Node', $modelClass));
		}
	}

	/**
	 * 根据栏目获取内容模型
	 * @deprecated
	 * @param mixed $channel
	 * @param boolean $applyChannel
	 * @return Node|null
	 */
	public static function getByChannel($channel, $static=true, $applyChannel=true)
	{
		if ($channel = Channel::get($channel)) {
			$model =  $channel->getObjectModel($static, $applyChannel);
			if ($model instanceof Node)
				return $model;
			else
				throw new CException(sprintf('栏目模型%s没有继承Node', $modelClass));
		}
	}

	/**
	 * 获取栏目
	 * @return Channel|null
	 */
	public function getChannel()
	{
		if (isset($this->channel_id))
			return Channel::get($this->channel_id);
		else if ($channels = Channel::getChannelsByModel(get_class($this)))
			return current($channels);
		else
			return null;
	}

	/**
	 * 最近的
	 * @param integer $limit
	 * @return Node
	 */
	public function recently($limit=-1)
	{
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'t.status=:status',
			'order'=>'t.sticky DESC, t.create_time DESC',
			'params'=>array(
				':status'=>self::STATUS_PUBLIC,
			),
			'limit'=>$limit,
		));

		return $this;
	}

	/**
	 * 显示在首页
	 * @param integer $limit
	 * @return Node
	 */
	public function promote($limit=-1)
	{
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'t.status=:status',
			'order'=>'t.promote DESC, t.sticky DESC, t.create_time DESC',
			'params'=>array(
				':status'=>self::STATUS_PUBLIC,
			),
			'limit'=>$limit,
		));

		return $this;
	}

	/**
	 * 已发布的
	 * @param integer $limit
	 * @return Node
	 */
	public function published($limit=-1)
	{
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'t.status=:status',
			'params'=>array(
				':status'=>self::STATUS_PUBLIC,
			),
			'limit'=>$limit,
		));

		return $this;
	}

	/**
	 * 按点击次数排行
	 * @param integer $limit
	 * @return Node
	 */
	public function top($limit=10)
	{
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'t.status=:status',
			'order'=>'t.hits DESC',
			'params'=>array(
				':status'=>self::STATUS_PUBLIC,
			),
			'limit'=>$limit,
		));

		return $this;
	}

	/**
	 * 更新点击次数
	 */
	public function updateHits()
	{
		$this->updateCounters(array('hits'=>1), 'id=:id', array(':id'=>$this->id));
	}

	/**
	 * 搜索
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.sticky',$this->sticky);
		$criteria->compare('t.promote',$this->promote);

		if ($this->create_time) {
			$condition = YUtil::generateTimeCondition($this->create_time, 't.create_time');

			if ($condition)
				$criteria->addCondition($condition);
		}

		$criteria->compare('t.title',$this->title,true);

		if (!empty($this->user_id)) {
			if (is_numeric($this->user_id)) {
				$criteria->compare('t.user_id',$this->user_id);
			} else {
				$this->with('user');
				$criteria->compare('user.nickname', $this->user_id, true);
			}
		}

		$this->afterSearch($criteria);

		if (!empty($this->channel_id)) {
			$criteria->compare('t.channel_id',$this->channel_id);
		}

		$criteria->compare('t.status', $this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'t.promote DESC, t.sticky DESC, t.create_time DESC'
			)
		));
	}

	/**
	 * 下一个
	 * @param array $newCondtion
	 * @return Node
	 */
	public function next($newCondtion=array())
	{
		$condition = 'status=:status AND id<:id';
		$params = array(':status'=>self::STATUS_PUBLIC, ':id'=>$this->id);

		if (!empty($this->channel_id))
		{
			$condition = 'channel_id=:channel_id AND ' . $condition;
			$params[':channel_id'] = $this->channel_id;
		}

		return $this->find(array_merge($newCondtion, array(
			'condition'=>$condition,
			'order'=>'id DESC',
			'params'=>$params,
		)));
	}

	/**
	 * 上一个
	 * @param array $newCondtion
	 * @return Node
	 */
	public function prev($newCondtion=array())
	{
		$condition = 'status=:status AND id>:id';
		$params = array(':status'=>self::STATUS_PUBLIC, ':id'=>$this->id);

		if (!empty($this->channel_id)) {
			$condition = 'channel_id=:channel_id AND ' . $condition;
			$params[':channel_id'] = $this->channel_id;
		}

		return $this->find(array_merge($newCondtion, array(
			'condition'=>$condition,
			'order'=>'id ASC',
			'params'=>$params,
		)));
	}

	/**
	 * 搜索之后
	 * @param CDbCriteria $criteria
	 */
	public function afterSearch(CDbCriteria $criteria)
	{
		if (isset($this->YTaxonomyBehavior))
			$this->searchByTaxonomy($criteria);
	}

	/**
	 * 改变状态
	 * @param integer $status
	 */
	public function changeStatus($status)
	{
		$statusList = array_keys($this->getStatusList());
		if (!isset($statusList[$status]))
			return;

		if ($status != $this->status) {
			$this->status = $status;
			$this->update('status');
		}
	}

	/**
	 * 批量置顶
	 * @param mixed $ids
	 */
	public function bulkSticky($ids)
	{
		$this->updateByPk($ids, array('sticky'=>1, 'update_time'=>time()));
	}

	/**
	 * 批量取消置顶
	 * @param mixed $ids
	 */
	public function bulkUnsticky($ids)
	{
		$this->updateByPk($ids, array('sticky'=>0, 'update_time'=>time()));
	}

	/**
	 * 批量显示在首页
	 * @param mixed $ids
	 */
	public function bulkPromote($ids)
	{
		$this->updateByPk($ids, array('promote'=>1, 'update_time'=>time()));
	}

	/**
	 * 批量取消推荐值首页
	 * @param mixed $ids
	 */
	public function bulkDemote($ids)
	{
		$this->updateByPk($ids, array('promote'=>0, 'update_time'=>time()));
	}

	/**
	 * 批量更新
	 * @param array $columns
	 * @param mixed $condition
	 * @param array $params
	 */
	public function bulkUpdate($columns, $condition='', $params=array())
	{
		$connection = $this->getDbConnection();
		$connection->createCommand()->update($this->tableName(), $columns, $condition, $params);
	}

	/**
	 * 批量删除
	 * @param mixed $condition
	 * @param array $params
	 */
	public function bulkDelete($condition='', $params=array())
	{
		$connection = $this->getDbConnection();
		$hasTaxonomy = isset($this->YTaxonomyBehavior);
		$hasMeta = isset($this->YMetaBehavior);

		if ($hasTaxonomy || $hasMeta) {
			$offset = 0;
			$limit = 500;
			while (
				$ids = $connection->createCommand()
					->select('id')
					->from($this->tableName())
					->where($condition, $params)
					->limit($limit, $offset)
					->queryColumn()
			) {
				$offset += $limit;
				foreach ($ids as $id) {
					$this->id = $id;
					$hasTaxonomy && $this->deleteTermRelationship();
					$hasMeta && $this->deleteMetaRelationship();
				}
			}
		}
		$this->unsetAttributes(); //释放属性
		$connection->createCommand()->delete($this->tableName(), $condition, $params);
	}

	/**
	 * 根据栏目
	 * @param mixed $channel
	 * @param mixed $limit
	 * @return Node
	 */
	public function byChannel($channel, $limit=null)
	{
		if (!$this->hasAttribute('channel_id'))
			return $this;

		if (is_array($channel)) {
			$channelIds = array();
			foreach ($channel as $value) {
				if ($value instanceof Channel)
					$channelIds[] = $value->id;
				elseif (is_numeric($value))
					$channelIds[] = (int) $value;
				else
					$channelIds[] = Channel::get($value)->id;
			}
		} elseif($channel instanceof Channel)
			$channelIds = array($channel->id);
		elseif (is_numeric($channel))
			$channelIds = array((int) $channel);
		else
			$channelIds = array(Channel::get($channel)->id);

		$criteria = new CDbCriteria(array(
			'limit' => $limit,
		));

		$criteria->compare('channel_id', $channelIds);
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	public function init()
	{
		parent::init();
		if ($this->isNewRecord) {
			$this->create_time = date('Y-m-d H:i:s');
		}
		$this->status = self::STATUS_PUBLIC;
	}

	/**
	 * 获取Raw标题
	 * @param boolean $editLink 是否显示编辑链接
	 * @return string
	 */
	public function getRawTitle($editLink=True)
	{
		$title = CHtml::encode($this->title);
		if ($editLink) {
			$url = array('/content/update', 'id'=>$this->id, 'channel'=>$this->channel->id);
			$htmlOptions = array('title'=>'更新 “' . $title . '” ');
			$title = CHtml::link($title, $url, $htmlOptions);
		}
		if ($this->sticky) {
			$title .= ' <span class="label label-info label-sticky">置顶</span> ';
		}
		if ($this->promote) {
			$title .= ' <span class="label label-info label-promote">显示在首页</span> ';
		}

		return '<div class="node-title">' . $title . '</div>';
	}

	/**
	 * 获取最后更新时间
	 * @return integer
	 */
	public function getLastUpdateTime()
	{
		$cacheKey = $this->tableName() . '_last_updatetime';
		if (($cache=Yii::app()->getCache()->get($cacheKey)) === false) {
			$lastTime = $this->getDbConnection()->createCommand()
				->select('update_time')
				->from($this->tableName())
				->order('update_time DESC')
				->limit(1)
				->queryScalar();
			Yii::app()->getCache()->set($cacheKey, $lastTime);
		}
		return $cache;
	}

	/**
	 * 更新最后更新时间
	 * @param integer $time
	 */
	public function updateLastUpdateTime($time=null)
	{
		$cacheKey = $this->tableName() . '_last_updatetime';
		if ($time === null)
			$time = time();
		Yii::app()->getCache()->set($cacheKey, $time);
	}

	/**
	 * 查找之后
	 * @see CActiveRecord::afterFind()
	 */
	protected function afterFind() {
		parent::afterFind();
		$this->create_time = date('Y-m-d H:i:s', $this->create_time);
	}

	/**
	 * 保存之前
	 * @see CActiveRecord::beforeSave()
	 */
	protected function beforeSave() {
		if (parent::beforeSave()) {
			if ($this->create_time) {
				$this->create_time = strtotime($this->create_time);
			} else {
				$this->create_time = time();
			}
			$this->update_time = time();
			$this->updateLastUpdateTime();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 删除之后
	 * @see CActiveRecord::afterDelete()
	 */
	protected function afterDelete()
	{
		parent::afterDelete();
		$this->updateLastUpdateTime();
	}
}