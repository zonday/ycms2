<?php
/**
 * Channel class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Channel Model
 *
 * @property integer $type
 * @property integer $parent_id
 * @property integer $weight
 * @property string $name
 * @property string $title
 * @property string $keywords
 * @property string $description
 * @property string $create_time
 * @property string $model
 * @property integer $status
 * @property string $update_time
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Channel extends CActiveRecord
{
	/**
	 * @var integer 状态缺省的
	 */
	const STATUS_DEFAULT = 0;

	/**
	 * @var integer 回收站中
	 */
	const STATUS_TRASH = 1;

	/**
	 * 类型空 封面
	 * @var integer
	 */
	const TYPE_EMPTY=0;

	/**
	 * 单页面
	 * @var integer
	 */
	const TYPE_PAGE = 1;

	/**
	 * 列表页面
	 * @var integer
	 */
	const TYPE_LIST = 2;

	/**
	 * 深度
	 * @var integer
	 */
	public $depth = 0;

	public $image;

	/**
	 * 获取模型
	 * @param string $className
	 * @return Channel
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 表名称
	 * @see CActiveRecord::tableName()
	 */
	public function tableName()
	{
		return '{{channel}}';
	}

	/**
	 * 行为
	 * @see CModel::behaviors()
	 */
	public function behaviors()
	{
		if (!empty(Yii::app()->params['channelImage'])) {
			$channelImage = Yii::app()->params['channelImage'];
			return array(
				'YFileUsageBehavior' => array(
					'class' => 'YFileUsageBehavior',
					'fields' => array(
						'image' => array(
							'location' => 'public://term',
							'type' => 'image',
							'resize'=>isset($channelImage['resize']) ? $channelImage['resize'] : null,
						),
					),
				)
			);
		} else {
			return array();
		}
	}

	/**
	 * 验证规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		return array(
			array('title, name', 'required'),
			array('title, name, keywords', 'length', 'max'=>255),
			array('name', 'match', 'pattern'=>'|^[0-9a-z\-]+$|i', 'message'=>'{attribute} 只能包含英文和 数字 、-.'),
			array('parent_id, weight', 'numerical', 'integerOnly'=>true),
			array('name', 'unique'),
			//array('name', 'unique', 'criteria'=>array('condition'=>'parent_id=:parent_id', 'params'=>array(':parent_id'=>$this->parent_id))),
			array('content', 'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
			array('type', 'in', 'range'=>array(self::TYPE_EMPTY, self::TYPE_LIST, self::TYPE_PAGE)),
			array('model', 'validateModel'),
			array('description, image', 'safe'),
			array('id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * 验证模型
	 */
	public function validateModel()
	{
		$modelList = $this->getModelList();
		if ($this->model && !isset($modelList[$this->model])) {
			$this->addError('mdoel', '模型选择不正确.');
			return;
		}

		if ($this->type == self::TYPE_LIST && empty($this->model)) {
			$this->addError('model', '类型为列表时，模型不能为空.');
			return;
		}
	}

	/**
	 * 验证之前
	 * @see CModel::beforeValidate()
	 */
	public function beforeValidate()
	{
		if (parent::beforeValidate())
		{
			$this->title = trim($this->title);
			$this->name = preg_replace('/\s+/i', '-', trim(strtolower($this->name)));
			$this->model = trim($this->model);
			$this->title = trim($this->title);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 属性标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'id'=>'ID',
			'image' => '图片',
			'title'=>'标题',
			'name'=>'名称',
			'keywords'=>'栏目关键字',
			'description'=>'栏目描述',
			'content'=>'内容',
			'type'=>'类型',
			'model'=>'模型',
			'weight'=>'权重',
			'parent_id'=>'父级',
			'create_time'=>'创建时间',
			'update_time'=>'更新时间',
		);
	}

	/**
	 * 类型列表
	 * @return array
	 */
	public static function getTypeList()
	{
		return array(
			self::TYPE_EMPTY=>'封面',
			self::TYPE_PAGE=>'单页面',
			self::TYPE_LIST=>'列表',
		);
	}

	/**
	 * 获取模型列表
	 * @return array
	 */
	public static function getModelList()
	{
		static $models;
		if (!isset($models)) {
			if (isset(Yii::app()->params['contentModels'])) {
				$models = Yii::app()->params['contentModels'];
			} else if (isset(Yii::app()->params['models'])) {
				$models = Yii::app()->params['models'];
			} else {
				$models = array(
					'Article'=>'文章',
				);
			}
		}
		return $models;
	}

	/**
	 * 根据模型获取栏目
	 * @param string $model
	 * @return array
	 */
	public static function getChannelsByModel($modelClass)
	{
		static $cache = array();
		if (!isset($cache[$modelClass])) {
			$children = self::model()->getChildren(-1);
			$channels = array();

			foreach ($children as $parentId => $models) {
				foreach ($models as $model) {
					if ($model->model === $modelClass) {
						$channels[] = $model;
					}
				}
			}
			$cache[$modelClass] = $channels;
		}
		return $cache[$modelClass];
	}

	/**
	 * 根据id更新权重
	 * @param integer $id
	 * @param integer $weight
	 */
	public static function updateWeightByPk($id, $weight) {
		$weight = intval($weight);
		$id = intval($id);
		self::model()->updateByPk($id, array('weight'=>$weight));
	}

	/**
	 * 获取父节点
	 * @return Channel|NULl
	 */
	public function getParent()
	{
		return self::get($this->parent_id);
	}

	/**
	 * 获取所有父节点
	 * @return array
	 */
	public function getParentsAll()
	{
		if ($this->isNewRecord)
			return array();

		$parents = array();
		$parentId = $this->parent_id;
		while($parent = self::get($parentId)) {
			$parents[] = $parent;
			$parentId = $parent->parent_id;
		}

		$parents = array_reverse($parents);
		return $parents;
	}

	/**
	 * 获取所有后代子节点
	 * @return array
	 */
	public function getChildrenAll()
	{
		if ($this->isNewRecord)
			return array();

		$children[] = $this;

		while($child = current($children)) {
			foreach ($this->getChildren($child->id) as $model) {
				$children[] = $model;
			}
			next($children);
		}

		array_shift($children);

		return $children;
	}

	/**
	 * 根据父ID获取子节点
	 * @param mixed $parent -1获取所有 null 获取自己的
	 * @return array
	 */
	public function getChildren($parent=null)
	{
		if ($parent === null) {
			if ($this->isNewRecord)
				return array();
			$parent = $this->id;
		}

		static $children;
		if (!isset($children) && ($children = Yii::app()->getCache()->get('channel_children')) === false) {
			$children = array();
			foreach ($this->findAll(array('order'=>'weight, title')) as $model) {
				$children[$model->parent_id][$model->id]= $model;
			}
			Yii::app()->getCache()->set('channel_children', $children);
		}
		return $parent === -1 ? $children : (isset($children[$parent]) ? $children[$parent] : array());
	}

	/**
	 * 是否有子节点
	 * @return boolean
	 */
	public function hasChildren()
	{
		return $this->getChildren() ? true : false;
	}

	/**
	 * 生成树 根据父ID, 最大深度
	 * @param integer $parent
	 * @param integer|NULl $maxDepth
	 * @return array
	 */
	public function getTree($parent=0, $maxDepth=null)
	{

		$children = $this->getChildren(-1);
		if ($maxDepth === null ) {
			$maxDepth = count($children);
		}

		$processParents = array();
		$tree = array();

		$processParents[] = $parent;
		while (count($processParents)) {
			$parent = array_pop($processParents);
			$depth = count($processParents);

			if ($maxDepth > $depth && !empty($children[$parent])) {
				$hasChildren = false;
				$child = current($children[$parent]);

				do {
					if (empty($child)) {
						break;
					}

					$child->depth = $depth;
					$tree[] = $child;

					if (!empty($children[$child->id])) {
						$hasChildren = true;
						$processParents[] = $parent;
						$processParents[] = $child->id;
						reset($children[$child->id]);
						next($children[$parent]);
						break;
					}
				} while ($child = next($children[$parent]));

				if (!$hasChildren) {
					reset($children[$parent]);
				}
			}
		}

		foreach ($tree as $index => $model) {
			if ($model->status != $this->status) {
				unset($tree[$index]);
			}
		}

		return $tree;
	}

	/**
	 * 生成树列表
	 * @param mixed $parent
	 * @param string $spacer 分隔符 会按照深度
	 * @return array
	 */
	public function getTreeList($parent=-1, $spacer=' — ')
	{
		$list = array();
		foreach ($this->getTree($parent) as $model) {
			$list[$model->id] = str_repeat($spacer, $model->depth) . ' ' . $model->title;
		}
		return $list;
	}

	/**
	 * 获取栏目 根据id或者栏目名
	 * @param mixed $value 数字则根据ID 字符串则根据name
	 * @return Channel|NUll
	 */
	public static function get($value)
	{
		static $cache = array();

		if (!$value)
			return;

		if ($value instanceof Channel)
			return $value;

		if (isset($cache[$value]))
			return $cache[$value];

		$children = self::model()->getChildren(-1);

		if (is_numeric($value))
			$attribute = 'id';
		else
			$attribute = 'name';

		foreach ($children as $parent => $models) {
			if ($attribute === 'id') {
				if (isset($models[$value])) {
					$channel = $models[$value];
					break;
				} else {
					continue;
				}
			} else {
				foreach ($models as $model) {
					if ($model->name == $value) {
						$channel = $model;
						break;
					}
				}
			}
		}

		if (isset($channel)) {
			$cache[$channel->id] = $channel;
			$cache[$channel->name] = &$cache[$channel->id];
			return $channel;
		}
	}

	/**
	 * 获取栏目路径
	 * @return string
	 */
	public function getPath()
	{
		static $cache = array();
		if (isset($cache[$this->id])) {
			return $cache[$this->id];
		}
		$parents = $this->getParentsAll();
		foreach ($parents as $parent)
			$path[] = $parent->name;

		$path[] = $this->name;
		$cache[$this->id] = implode('/', $path);
		return $cache[$this->id];
	}

	/**
	 * 获取顶层父节点
	 * @return mixed|Channel
	 */
	public function getTopLevelParent()
	{
		$parents = $this->getParentsAll();
		if ($parents)
			return current($parents);
		else
			return $this;
	}

	/**
	 * 获取模型名
	 * @return string
	 */
	public function getModelName($model=null)
	{
		if ($model === null) {
			$model = $this->model;
		}
		return isset($this->modelList[$model]) ? $this->modelList[$model] : $model;
	}

	/**
	 * 获取权重列表，根据分类数目
	 * @return array
	 */
	public function getWeightList()
	{
		$total = array(0);
		foreach ($this->getChildren(-1) as $children) {
			$total[] = count($children);
		}
		$count = max($total);
		$range = range(-$count, $count);
		return array_combine($range, $range);
	}

	/**
	 * 获取栏目内容操作链接
	 * @return string
	 */
	public function getContentActionLink()
	{
		$htmlOptions = array();;
		$links = array();
		$url = array('/content/index','channel'=>$this->id);
		if ($this->model) {
			$staticModel = CActiveRecord::model($this->model);
			if (!$staticModel instanceof Node) {
				$url = array('/other/' . strtolower($this->model));
			}
			$links[] = CHtml::link('<i class="icon-list"></i> 管理内容', $url, $htmlOptions);
		}

		if ($this->type == Channel::TYPE_LIST){
			if ($staticModel instanceof Node) {
				$links[] = CHtml::link(' <i class="icon-plus"></i> 创建内容', array('/content/create','channel'=>$this->id), $htmlOptions);
			} else {
				$links[] = CHtml::link(' <i class="icon-plus"></i> 创建内容', array('/other/' . strtolower($this->model) . '/create','channel'=>$this->id), $htmlOptions);
			}
		} elseif ($this->type == Channel::TYPE_PAGE) {
			$links[] = CHtml::link('<i class="icon-pencil"></i> 更新页面', array('/content/channel','channel'=>$this->id), $htmlOptions);
		}
		return implode("\t", $links);
	}

	/**
	 * 获取内容对象模型
	 * @param boolean $static
	 * @param boolean $applyChannel
	 * @return CActiveRecord|null
	 */
	public function getObjectModel($static=true, $applyChannel=true)
	{
		if ($this->isNewRecord || !$this->model)
			return;

		$modelClass = $this->model;

		if ($static) {
			$model = CActiveRecord::model($modelClass);
		} else {
			$model = new $modelClass('search');
		}

		if (($model instanceof Node) && $applyChannel) {
			$model->byChannel($this->id);
		}

		return $model;
	}

	/**
	 * 删除缓存
	 */
	public function deleteCache()
	{
		Yii::app()->getCache()->delete('channel_children');
	}

	/**
	 * 改变状态
	 * @param integer $status
	 * @return boolean
	 */
	public function changeStatus($status)
	{
		if (in_array($status, array(self::STATUS_DEFAULT, self::STATUS_TRASH))) {
			if ($this->status != $status) {
				$this->status = $status;
				foreach ($this->getChildrenAll() as $child) {
					$child->status = $status;
					$child->update('status');
				}
				$this->update('status');
				return;
			}
		}

		return false;
	}

	/**
	 * 保存之前
	 * @see CActiveRecord::beforeSave()
	 */
	public function beforeSave()
	{
		if (parent::beforeSave()) {
			if ($this->isNewRecord) {
				$this->create_time = time();
			}
			$this->update_time = time();

			foreach ($this->getChildrenAll() as $child) {
				if ($child->id == $this->parent_id) {
					unset($this->parent_id);
					break;
				}
			}

			if (trim($this->parent_id) === '') {
				$this->parent_id = 0;
			}

			if ($this->parent_id == $this->id) {
				unset($this->parent_id);
			}

			if ($this->keywords) {
				$keywords = preg_split('/[\s|,]+/', $this->keywords, -1, PREG_SPLIT_NO_EMPTY);
				$this->keywords = trim(implode(',', $keywords),',');
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 删除之前
	 * @see CActiveRecord::beforeDelete()
	 */
	protected function beforeDelete()
	{
		if (parent::beforeDelete()) {
			if ($this->hasChildren()) {
				return false;
			}
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
		$this->deleteCache();

		if ($this->type == self::TYPE_LIST && ($modelClass = $this->model)) {
			$staticModel = CActiveRecord::model($modelClass);
			if (!$staticModel instanceof Node) {
				return;
			}
			if ($staticModel->hasAttribute('channel_id')) {
				$staticModel->bulkDelete('channel_id=:channel_id', array(':channel_id'=>$this->id));
			}
		}
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
}