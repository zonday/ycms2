<?php
/**
 * YContentController class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YContentController
 *
 * @author Yang <css3@qq.com>
 * @package common.components
 */
class YContentController extends YController
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
	 * @var Channel 栏目
	 */
	private $_channel;

	/**
	 * @var string 栏目路径
	 */
	private $_path;

	/**
	 * 首页
	 * @param string $path
	 */
	public function actionIndex()
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

			$this->render($this->findView(), isset($data) ? $data : array());
		} elseif ($channel->type == Channel::TYPE_PAGE) {
			$method = str_replace('-', '', $channel->name) . 'Page';
			if (method_exists($this, $method)) {
				$data = call_user_func(array($this, $method));
			} else {
				$data = array('model'=>$channel);
			}
			$this->render($this->findView(), isset($data) ? $data : array());
		} else {
			$method = str_replace('-', '', $channel->name) . 'Index';
			if (method_exists($this, $method)) {
				$data = call_user_func(array($this, $method));
			}
			$this->render($this->findView(), isset($data) ? $data : array());
		}
	}

	/**
	 * 查看
	 * @param string $path
	 * @param integer $id
	 * @throws CException
	 * @throws CHttpException
	 */
	public function actionView($id)
	{
		$channel = $this->getChannel();

		if (!$staticModel = $channel->getObjectModel(true, false)) {
			throw new CException('内容模型丢失');
		}

		if (!$model = $staticModel->cache(60)->published()->findByPk($id)) {
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
		if (!isset($this->_channel)) {
			$path = $this->getPath();
			$parts = explode('/', $path);
			$this->setChannel($parts[count($parts) - 1]);
		}
		return $this->_channel;
	}

	/**
	 * 设置栏目
	 * @param mixed $idName
	 * @throws CHttpException
	 */
	public function setChannel($idName)
	{
		if (!$channel = Channel::get($idName)) {
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
	 * 获取栏目路径
	 */
	public function getPath()
	{
		if (!isset($this->_path)) {
			if (empty($_GET['path'])) {
				throw new CHttpException(404, '栏目路径没有找到');
			} else {
				$this->_path =  trim($_GET['path']);
			}
		}

		return $this->_path;
	}

	/**
	 * 设置栏目路径
	 * @param string $path
	 */
	public function setPath($path)
	{
		$this->_path = trim($path);
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
					$this->breadcrumbs[$parent->title] = array('/content/index', 'channel_id'=>$parent->id);

				if ($this->action->id !== 'view') {
					$this->breadcrumbs[] = $channel->title;
				} else {
					$this->breadcrumbs[$channel->title] = array('/content/index', 'channel_id'=>$channel->id);
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

		$parts = explode('/', $channel->getPath());

		if (isset($this->viewMap[$channelName][$view]))
			return $parts[0] . '/' . $this->viewMap[$channelName][$view];
		else
			return $parts[0] . '/' . $view;
	}
}