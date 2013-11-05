<?php
/**
 * TaxonomyController class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * TaxonomyController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class TaxonomyController extends Controller
{
	public function actionIndex()
	{
		$model = new Taxonomy('search');
		$model->unsetAttributes();
		if(isset($_GET['Taxonomy']))
			$model->attributes = $_GET['Taxonomy'];
		$this->render('index', array('model' => $model));
	}

	public function actionView($id)
	{
		$this->render('view', array('model' => $this->loadModel($id)));
	}

	public function actionCreate()
	{
		$model = new Taxonomy();

		if (isset($_POST['Taxonomy'])) {
			$model->attributes = $_POST['Taxonomy'];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '分类创建成功');
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

		$this->render('create', array('model' => $model));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if (isset($_POST['Taxonomy'])) {
			$model->attributes = $_POST['Taxonomy'];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '分类更新成功');
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

		$this->render('update', array('model' => $model));
	}

	public function actionDelete($id)
	{
		if(Yii::app()->getRequest()->getIsPostRequest()) {
			$result = $this->loadModel($id)->delete();

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', '分类删除成功');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(403, '无效的请求，请重试');
		}
	}

	public function actionSaveWeight()
	{
		$weights = isset($_POST['weight']) ? $_POST['weight'] : null;
		foreach ((array) $weights as $id => $weight) {
			Taxonomy::updateWeightByPk($id, $weight);
		}
		Yii::app()->getUser()->setFlash('success', '保存权重成功');
		$this->redirect(array('index'));
	}

	/**
	 * 读取一个模型
	 * @param integer $id
	 * @throws CHttpException
	 * @return Taxonomy
	 */
	public function loadModel($id)
	{
		if (!$model = Taxonomy::model()->findByPk($id))
			throw new CHttpException(404, '页面没有找到');
		return $model;
	}
}