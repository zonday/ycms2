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
	 * @var integer
	 */
	public $channel_id;

	public $hits;

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
			'user_id' => '作者',
			'excerpt'=>'摘要',
			'promote'=>'推荐至首页',
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
	 * @return multitype:string
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_DRAFT=>'待审核',
			self::STATUS_PUBLIC=>'公开',
		);
	}

	/**
	 * 最近的
	 * @param mixed $limit
	 * @return Node
	 */
	public function recently($limit=null)
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
	 * @param mixed $limit
	 * @return Node
	 */
	public function promote($limit=null)
	{
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'t.status=:status AND t.promote != 0',
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
	 * @param mixed $limit
	 * @return Node
	 */
	public function published($limit=null)
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
	 * @param mixed $limit
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

		$criteria->compare('id',$this->id,true);

		$criteria->compare('promote',$this->promote);
		$criteria->compare('sticky',$this->sticky);
		$criteria->compare('status',$this->status);

		if ($this->create_time) {
			$condition = YUtil::generateTimeCondition($this->create_time, 't.create_time');

			if ($condition)
				$criteria->addCondition($condition);
		}

		$criteria->compare('title',$this->title,true);

		if (!empty($this->user_id)) {
			if (is_numeric($this->user_id)) {
				$criteria->compare('user_id',$this->user_id);
			} else {
				$this->with('user');
				$criteria->compare('user.nickname', $this->user_id, true);
			}
		}

		if (!empty($this->channel_id)) {
			$criteria->compare('t.channel_id',$this->channel_id);
		}

		$this->afterSearch($criteria);

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
	 * @param mixed $criteria
	 */
	public function afterSearch($criteria)
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
		self::updateByPk($ids, array('sticky'=>1));
	}

	/**
	 * 批量取消置顶
	 * @param mixed $ids
	 */
	public function bulkUnsticky($ids)
	{
		self::updateByPk($ids, array('sticky'=>0));
	}

	/**
	 * 批量推荐至首页
	 * @param mixed $ids
	 */
	public function bulkPromote($ids)
	{
		self::updateByPk($ids, array('promote'=>1));
	}

	/**
	 * 批量取消推荐值首页
	 * @param mixed $ids
	 */
	public function bulkDemote($ids)
	{
		self::updateByPk($ids, array('promote'=>0));
	}

	public function init()
	{
		parent::init();
		if ($this->isNewRecord) {
			$this->create_time = date('Y-m-d H:i:s');
		}
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
			return true;
		} else {
			return false;
		}
	}
}