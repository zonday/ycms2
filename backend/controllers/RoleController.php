<?php
/**
 * RoleController class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * RoleController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class RoleController extends Controller
{
	/**
	 * 首页
	 */
	public function actionIndex()
	{
		$model = new Role('search');
		$model->unsetAttributes();

		if(isset($_GET['Role']))
			$model->attributes = $_GET['Role'];

		$this->render('index', array('model' => $model));
	}

	/**
	 * 创建角色
	 */
	public function actionCreate()
	{
		$model = new Role();

		if (isset($_POST['Role'])) {
			$model->attributes = $_POST['Role'];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '角色创建成功');
				$this->redirect(array('index'));
			}
		}

		$this->render('create', array('model' => $model));
	}

	/**
	 * 更新角色
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if (isset($_POST['Role'])) {
			$model->attributes = $_POST['Role'];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '角色更新成功');
				$this->redirect(array('index'));
			}
		}

		$this->render('update', array('model' => $model));
	}

	/**
	 * 删除角色
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->getRequest()->getIsPostRequest()) {
			$result = $this->loadModel($id)->delete();

			if ($result) {
				if (!isset($_GET['ajax'])) {
					Yii::app()->getUser()->setFlash('success', '角色删除成功');
				}
			}

			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		} else
			throw new CHttpException(403, '无效的请求，请重试');
	}

	/**
	 * 保存权重
	 */
	public function actionSaveWeight()
	{
		$weights = isset($_POST['weight']) ? $_POST['weight'] : null;

		foreach ((array) $weights as $id => $weight) {
			Role::updateWeightByPk($id, $weight);
		}

		Role::model()->deleteCache();
		Yii::app()->getUser()->setFlash('success', '保存权重成功');
		$this->redirect(array('index'));
	}

	/**
	 * 获取模型
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		if (!$model = Role::model()->findByPk($id))
			throw new CHttpException(404, '页面没有找到');
		return $model;
	}
}