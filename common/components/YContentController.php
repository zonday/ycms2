<?php
class YContentController extends CController
{
	/**
	 * @var array 栏目映射
	 */
	public $channelMap = array();

	/**
	 * @var array 视图映射
	 */
	public $viewMap = array();

	/**
	 * @var string
	 */
	public $baseUrl;

	/**
	 * @var array 面包屑
	 */
	public $breadcrumbs = array();

	/**
	 * @var Channel 栏目
	 */
	private $_channel;

	/**
	 * @var string 栏目路径
	 */
	private $_path;

	/**
	 * 初始化
	 * 获取栏目路径 根据栏目映射设置栏目
	 * @see CController::init()
	 */
	public function init()
	{
		parent::init();
		$this->baseUrl = Yii::app()->getTheme()->getBaseurl();

		if (empty($_GET['path'])) {
			throw new CHttpException(404, '栏目路径没有找到');
		} else {
			$this->_path =  trim($_GET['path']);
		}

		$parts = explode('/', $this->_path);

		if (!$channel = Channel::get($parts[count($parts) - 1])) {
			throw new CHttpException(404, '栏目没有找到');
		}

		while (isset($this->channelMap[$channel->name])) {
			$value = $this->channelMap[$channel->name];
			if ($value === '_CHILD') {
				$channel = current($channel->getChildren());
			} else {
				$channel = Channel::get($value);
			}
			if (!$channel) {
				throw new CHttpException(404, '栏目没有找到');
			}
		}

		$this->_channel = $channel;
	}

	/**
	 * 首页
	 * @param string $path
	 */
	public function actionIndex($path)
	{
		$channel = $this->getChannel();
		if ($channel->type == Channel::TYPE_LIST) {
			$model = $channel->getObjectModel(false);
			$model->cache(60);

			if ($model instanceof Node) {
				$model->recently();
			}

			$method = $channel->model . 'List';

			if (method_exists($this, $method)) {
				$data = call_user_func(array($this, $method), $model);
			} else {
				$data['dataProvider'] = new CActiveDataProvider($model, array(
					'pagination'=> array(
						'pageSize'=>20,
						'pageVar'=>'page',
					)
				));
			}

			$this->render($this->findView(), $data);
		} else {
			$this->render($this->findView());
		}
	}

	/**
	 * 查看
	 * @param string $path
	 * @param integer $id
	 * @throws CException
	 * @throws CHttpException
	 */
	public function actionView($path, $id)
	{
		$channel = $this->getChannel();
		if (!$staticModel = $channel->getObjectModel()) {
			throw new CException('内容模型丢失');
		}

		if (!$model = $staticModel->published()->findByPk($id)) {
			throw new CHttpException(404, '页面没有找到');
		}

		$this->render($this->findView(), array('model'=>$model));
	}

	/**
	 * 获取栏目
	 * @return Channel
	 */
	public function getChannel()
	{
		return $this->_channel;
	}

	/**
	 * @see CController::beforeRender()
	 */
	public function beforeRender($view)
	{
		if (parent::beforeRender($view)) {
			$channel = $this->getChannel();
			if($channel) {
				if ($this->action->id !== 'view')
					$this->pageTitle = $channel->title;

				foreach ($channel->getParentsAll() as $index => $parent)
					$this->breadcrumbs[$parent->title] = array('/content/index', 'path'=>$parent->getPath());

				if ($this->action->id !== 'view') {
					$this->breadcrumbs[] = $channel->title;
				} else {
					$this->breadcrumbs[$channel->title] = array('/content/index', 'path'=>$channel->getPath());
				}
			}
			return true;
		} else
			return false;
	}

	/**
	 * 查找视图
	 * @return string
	 */
	protected function findView()
	{
		$channel = $this->getChannel();

		$channelName = $channel->name;

		$type = $channel->type;

		if ($this->action->id == 'view')
			$view = 'view';
		elseif ($type == Channel::TYPE_PAGE)
			$view = 'page';
		elseif ($type == Channel::TYPE_LIST)
			$view = 'list';
		else
			$view = 'index';

		$parts = explode('/', $this->_path);

		if (isset($this->viewMap[$channelName][$view]))
			return $parts[0] . '/' . $this->viewMap[$channelName][$view];
		else
			return $parts[0] . '/' . $view;
	}
}