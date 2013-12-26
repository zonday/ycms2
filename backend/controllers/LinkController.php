<?php
/**
 * LinkController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * LinkController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class LinkController extends Controller
{
	/**
	 * 查看链接
	 * @param integer $id
	 */
	public function actionView($id)
	{
		$this->render('view', array('model'=>$this->loadModel($id)));
	}

	/**
	 * 创建链接
	 */
	public function actionCreate()
	{
		$model=new Link;

		if(isset($_POST['Link'])) {
			$model->attributes=$_POST['Link'];
			if($model->save()) {
				Yii::app()->getUser()->setFlash('success', '链接已创建');
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

		$this->render('create', array('model'=>$model));
	}

	/**
	 * 更新链接
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['Link'])) {
			$model->attributes=$_POST['Link'];
			if($model->save()) {
				Yii::app()->getUser()->setFlash('success', '链接已更新');
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

		$this->render('update',array('model'=>$model));
	}

	/**
	 * 删除链接
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			$result = $this->loadModel($id)->delete();

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', '链接已删除');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(405,'无效的请求，请重试');
		}
	}

	/**
	 * 首页
	 */
	public function actionIndex()
	{
		if (isset($_POST['saveWeight'])) {
			$weights = isset($_POST['weight']) ? $_POST['weight'] : null;
			foreach ((array) $weights as $id => $weight) {
				Link::updateWeightByPk($id, $weight);
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

		$model=new Link('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Link']))
			$model->attributes=$_GET['Link'];

		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * 属性toggle
	 * @param integer $id
	 * @param string $attribute
	 */
	public function actionToggle($id, $attribute)
	{
		$model = $this->loadModel($id);
		if ($model->$attribute) {
			$model->$attribute = false;
		} else {
			$model->$attribute = true;
		}
		$model->update($attribute);
	}

	/**
	 * 读取模型
	 * @param integer $id
	 * @throws CHttpException
	 * @return Link
	 */
	public function loadModel($id)
	{
		$model=Link::model()->findByPk($id);
		if($model===null)
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
			case 'delete':
				foreach (Link::model()->findAllByPk($ids) as $model) {
					if ($model->delete()) {
						$count++;
					}
				}
				Yii::app()->getUser()->setFlash('success', sprintf('已删除%d个链接', $count));
				break;
		}
	}
}
