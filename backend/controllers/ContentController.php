<?php
/**
 * ContentController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * ContentController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class ContentController extends Controller
{
	/**
	 * 栏目
	 * @var Channel
	 */
	private $_channel;

	/**
	 * 获取栏目
	 * @return Channel
	 */
	public function getChannel()
	{
		if (!isset($this->_channel)) {
			if (!isset($_GET['channel']) || !$channel = Channel::get($_GET['channel'])) {
				throw new CHttpException(404, '栏目没有找到');
			}
			$this->_channel = $channel;
		}
		return $this->_channel;
	}

	/**
	 * 获取栏目模型类名
	 * @return string
	 */
	public function getModelClass()
	{
		$channel = $this->getChannel();
		$modelClass = $channel->model;

		if (!$modelClass || !class_exists($modelClass, true))
			throw new CHttpException(404, '栏目内容模型没有找到');

		return $modelClass;
	}

	/**
	 * 处理栏目内容单页面
	 * @param mixed $channel
	 */
	public function actionChannel($channel)
	{
		$model = $this->getChannel();

		if (isset($_POST['Channel']))
		{
			$model->attributes = $_POST['Channel'];
			if ($model->save(true,array('title', 'content', 'keywords', 'description')))
			{
				Yii::app()->getUser()->setFlash('success', '更新页面内容成功。');
				$this->redirect(array('channel', 'channel'=>$model->id));
			}
		}

		$this->render('channel', array('model'=>$model));
	}

	/**
	 * 管理内容
	 * @param mixed $channel
	 */
	public function actionIndex($channel)
	{
		$channelModel = $this->getChannel();
		$modelClass = $this->getModelClass();

		if (isset($_POST['doaction'])) {
			$action = !empty($_POST['action']) ? $_POST['action'] : null;
			$ids = !empty($_POST['ids']) ? $_POST['ids'] : null;
			if ($action && $ids)
				$this->processBulkAction($action, $ids);
			$this->redirect(array('index', 'channel'=>$channel));
			return;
		}

		$model = new $modelClass('search');

		$model->unsetAttributes();

		if (isset($_GET[$modelClass])) {
			$model->attributes = $_GET[$modelClass];
		}

		if ($model->hasAttribute('channel_id')) {
			$model->channel_id = $channelModel->id;
		}

		$this->render('index', array('model'=>$model));
	}

	/**
	 * 创建内容
	 * @param mixed $channel
	 */
	public function actionCreate($channel)
	{
		$channelModel = $this->getChannel();
		$modelClass = $this->getModelClass();
		$model = new $modelClass;

		if (isset($_POST[$modelClass]))
		{
			$model->attributes = $_POST[$modelClass];

			$model->user_id = Yii::app()->getUser()->id;
			$model->channel_id = $channelModel->id;

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', $channelModel->modelName . '创建成功');
				if (isset($_POST['_addanother'])) {
					$url = array('create', 'channel'=>$channel);
				} elseif (isset($_POST['_continue'])) {
					$url = array('update', 'channel'=>$channel, 'id'=>$model->id);
				} else {
					$url = array('view', 'channel'=>$channel, 'id'=>$model->id);
				}
				$this->redirect($url);
			}
		}

		$this->render('create', array('model'=>$model));
	}

	/**
	 * 更新内容
	 * @param mixed $channel
	 * @param integer $id
	 */
	public function actionUpdate($channel, $id)
	{
		$channelModel = $this->getChannel();
		$model = $this->loadModel($id);
		$modelClass = get_class($model);

		if (isset($_POST[$modelClass]))
		{
			$model->attributes = $_POST[$modelClass];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', $channelModel->modelName . '更新成功');
				if (isset($_POST['_addanother'])) {
					$url = array('create', 'channel'=>$channel);
				} elseif (isset($_POST['_continue'])) {
					$url = array('update', 'channel'=>$channel, 'id'=>$model->id);
				} else {
					$url = array('view', 'channel'=>$channel, 'id'=>$model->id);
				}
				$this->redirect($url);
			}
		}

		$this->render('update', array('model'=>$model));
	}

	/**
	 * 查看内容
	 * @param mixed $channel
	 * @param integer $id
	 */
	public function actionView($channel, $id)
	{
		$model = $this->loadModel($id);
		$this->render('view', array('model'=>$model));
	}

	/**
	 * 删除内容
	 * @param mixed $channel
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionDelete($channel, $id)
	{
		$channelModel = $this->getChannel();
		if(Yii::app()->request->isPostRequest) {
			$result = $this->loadModel($id)->delete();

			if(!isset($_GET['ajax'])){
				$result && Yii::app()->getUser()->setFlash('success', $channelModel->modelName .'删除成功');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index', 'channel'=>$channel));
			}
		} else {
			throw new CHttpException(400,'无效的请求，请重试');
		}
	}

	/**
	 * 读取模型
	 * @param Channel $channel
	 * @param integer $id
	 * @throws CHttpException
	 * @return Node
	 */
	public function loadModel($id)
	{
		$modelClass = $this->getModelClass();

		if (!$model = CActiveRecord::model($modelClass)->findByPk($id))
			throw new CHttpException(404, '页面没有找到。');

		return $model;
	}

	/**
	 * 批量处理
	 * @param string $action
	 * @param mxied $ids
	 */
	protected function processBulkAction($action, $ids)
	{
		$ids = (array) $ids;
		$count = 0;

		$staticModel = CActiveRecord::model($this->getModelClass());
		switch ($action) {
			case 'draft':
				foreach ($staticModel->findAllByPk($ids) as $model) {
					$model->changeStatus(Node::STATUS_DRAFT);
				}
				break;
			case 'public':
				foreach ($staticModel->findAllByPk($ids) as $model) {
					$model->changeStatus(Node::STATUS_PUBLIC);
				}
				break;
			case 'delete':
				foreach ($staticModel->findAllByPk($ids) as $model) {
					if ($model->delete()) {
						$count++;
					}
				}
				$message = sprintf('已删除%d个%s', $count, $this->getChannel()->getModelName());
				break;
			default:
				$method = 'bulk' . $action;
				if (method_exists($staticModel, $method)) {
					$staticModel->$method($ids);
				}
				break;
		}

		if (!isset($message)) {
			$message = '应用已执行';
		}

		Yii::app()->getUser()->setFlash('success', $message);
	}

	/**
	 * 获取视图目录
	 * @param Node $model
	 * @return string
	 */
	protected function getViewDirectory($model)
	{
		$className = get_class($model);
		$viewDirectory  = $this->getViewPath() . DIRECTORY_SEPARATOR . strtolower($className);

		if (($model instanceof Article) && !file_exists($viewDirectory))
			return 'article';
		else
			return strtolower($className);
	}

	/**
	 * 生成面包屑
	 * @param Channel $channel
	 * @return array
	 */
	protected function generateBreadcrumb(Channel $channel=null)
	{
		if ($channel === null)
			$channel = $this->getChannel();
		$breadcrumb = array('栏目' => array('channel/index'));
		$parents = $channel->getParentsAll();
		foreach ($parents as $parent)
		{
			if ($parent->model) {
				$staticModel = CActiveRecord::model($parent->model);
				if (!$staticModel instanceof Node) {
					$url = array('/other/' . strtolower($parent->model));
				} else {
					$url = array('/content/index', 'channel'=>$parent->id);
				}
			} else {
				$url = array('/content/channel', 'channel'=>$parent->id);
			}
			$breadcrumb[$parent->title] = $url;
		}

		return $breadcrumb;
	}
}