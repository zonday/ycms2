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
	public function _actionDelete($id)
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
	 * 保存权重
	 */
	public function actionSaveWeight()
	{
		$weights = isset($_POST['weight']) ? $_POST['weight'] : null;
		foreach ((array) $weights as $id => $weight) {
			Channel::updateWeightByPk($id, $weight);
		}
		Channel::model()->deleteCache();
		Yii::app()->getUser()->setFlash('success', '保存权重成功');
		$this->redirect(array('index'));
	}

	/**
	 * 首页
	 */
	public function actionIndex()
	{
		$model=new Channel('search');
		$model->unsetAttributes();
		if(isset($_GET['Channel']))
			$model->attributes=$_GET['Channel'];

		$this->render('index',array(
			'model'=>$model,
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
		$model=Channel::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'页面没有找到');
		return $model;
	}
}
