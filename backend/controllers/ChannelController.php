<?php
/**
 * ChannelController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * ChannelController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class ChannelController extends Controller
{
	/**
	 * 查看栏目
	 * @param integer $id
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * 创建栏目
	 */
	public function actionCreate()
	{
		$model=new Channel;

		if(isset($_POST['Channel'])) {
			$model->attributes=$_POST['Channel'];

			if($model->save()) {
				Yii::app()->getUser()->setFlash('success', '栏目已创建');
				if (isset($_POST['_addanother'])) {
					$url = array('create');
				} elseif (isset($_POST['_continue'])) {
					$url = array('update', 'id'=>$model->id);
				} else {
					$url = array('view', 'id'=>$model->id);
				}
				$this->redirect($url);
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * 更新栏目
	 * @param integer $id
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);

		if(isset($_POST['Channel']))
		{
			$model->attributes=$_POST['Channel'];
			if($model->save()) {
				Yii::app()->getUser()->setFlash('success', '栏目已更新');
				if (isset($_POST['_addanother'])) {
					$url = array('create');
				} elseif (isset($_POST['_continue'])) {
					$url = array('update', 'id'=>$model->id);
				} else {
					$url = array('view', 'id'=>$model->id);
				}
				$this->redirect($url);
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * 删除栏目
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest) {
			$result = $this->loadModel($id)->delete();

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', '栏目已删除');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(400,'无效的请求，请重试');
		}
	}

	/**
	 * 还原栏目
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionReset($id)
	{
		if(Yii::app()->request->isPostRequest) {
			$result = $this->loadModel($id)->changeStatus(Channel::STATUS_DEFAULT);

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', '栏目已还原');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(400,'无效的请求，请重试');
		}
	}

	/**
	 * 回收栏目
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionTrash($id)
	{
		if(Yii::app()->request->isPostRequest) {
			$result = $this->loadModel($id)->changeStatus(Channel::STATUS_TRASH);

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', '栏目已移动至回收站');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(400,'无效的请求，请重试');
		}
	}

	/**
	 * 首页
	 */
	public function actionIndex($view=null)
	{
		if (isset($_POST['saveWeight'])) {
			$weights = isset($_POST['weight']) ? $_POST['weight'] : null;
			foreach ((array) $weights as $id => $weight) {
				Channel::updateWeightByPk($id, $weight);
			}
			Yii::app()->getUser()->setFlash('success', '保存权重成功');
			$this->redirect(array('index'));
			return;
		}

		if (isset($_POST['doaction'])) {
			$action = !empty($_POST['action']) ? $_POST['action'] : null;
			$ids = !empty($_POST['ids']) ? $_POST['ids'] : null;
			if ($action && $ids)
				$this->processBulkAction($action, $ids);
			$this->redirect(array('index'));
			return;
		}

		$model=new Channel('search');
		$model->unsetAttributes();
		if(isset($_GET['Channel']))
			$model->attributes=$_GET['Channel'];

		if ($view == 'trash') {
			$model->status = Channel::STATUS_TRASH;
		} else {
			$model->status = Channel::STATUS_DEFAULT;
		}

		$this->render('index',array(
			'model' => $model,
			'view' => $view
		));
	}

	/**
	 * 读取模型
	 * @param integer $id
	 * @throws CHttpException
	 * @return Channel
	 */
	public function loadModel($id)
	{
		$model = Channel::model()->findByPk($id);
		if($model === null)
			throw new CHttpException(404,'页面没有找到');
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
		switch ($action) {
			case 'reset':
				foreach (Channel::model()->findAllByPk($ids) as $model) {
					$model->changeStatus(Channel::STATUS_DEFAULT);
				}
				break;
			case 'trash':
				foreach (Channel::model()->findAllByPk($ids) as $model) {
					$model->changeStatus(Channel::STATUS_TRASH);
				}
				break;
			case 'delete':
				foreach (Channel::model()->findAllByPk($ids) as $model) {
					if ($model->delete()) {
						$count++;
					}
				}
				$message = sprintf('已删除%d个栏目', $count);
				break;
		}

		if (!isset($message)) {
			$message = '应用已执行';
		}

		Yii::app()->getUser()->setFlash('success', $message);
	}
}
