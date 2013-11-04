<?php
/**
 * TermController class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * TermController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class TermController extends Controller
{
	/**
	 * 查看术语
	 * @param integer $id
	 */
	public function actionView($id)
	{
		$this->render('view', array('model' => $this->loadModel($id)));
	}

	/**
	 * 创建术语
	 * @param integer $taxonomy_id
	 * @throws CHttpException
	 */
	public function actionCreate($taxonomy_id)
	{
		if (!$taxonomy = Taxonomy::findFromCache($taxonomy_id))
			throw new CHttpException(404, '页面没有找到');

		$model = new Term();
		$model->taxonomy = $taxonomy;
		$model->taxonomy_id = $taxonomy_id;

		if (isset($_POST['Term'])) {
			$model->attributes = $_POST['Term'];
			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '术语创建成功');
				if (isset($_POST['_addanother'])) {
					$url = array('create', 'taxonomy_id'=>$taxonomy_id);
				} elseif (isset($_POST['_continue'])) {
					$url = array('update', $model->id);
				} else {
					$url = array('view', 'id'=>$model->id);
				}
				$this->redirect($url);
			}
		}

		$this->render('create', array('model' => $model));
	}

	/**
	 * 更新术语
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);
		$model->taxonomy = Taxonomy::findFromCache($model->taxonomy_id);

		if (isset($_POST['Term'])) {
			$model->attributes = $_POST['Term'];
			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '术语更新成功');
				if (isset($_POST['_addanother'])) {
					$url = array('create', 'taxonomy_id'=>$taxonomy_id);
				} elseif (isset($_POST['_continue'])) {
					$url = array('update', $model->id);
				} else {
					$url = array('view', 'id'=>$model->id);
				}
				$this->redirect($url);
			}
		}

		$this->render('update', array('model' => $model));
	}

	/**
	 * 删除术语
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModel($id);
			$result = $model->delete();

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', '术语删除成功');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('taxonomy/view', 'id' => $model->taxoonomy_id));
			}
		} else {
			throw new CHttpException(403, '无效的请求，请重试');
		}
	}

	/**
	 * 保存权重
	 * @param integer $taxonomy_id
	 * @throws CHttpException
	 */
	public function actionSaveWeight($taxonomy_id)
	{
		if (!$taxonomy = Taxonomy::findFromCache($taxonomy_id))
			throw new CHttpException(404, '页面没有找到');

		$weights = isset($_POST['weight']) ? $_POST['weight'] : null;
		foreach ((array) $weights as $id => $weight) {
			Term::updateWeightByPk($id, $weight);
		}
		Yii::app()->getCache()->delete("term_tree_{$taxonomy_id}");
		Yii::app()->getUser()->setFlash('success', '保存权重成功');
		$this->redirect(array('taxonomy/view', 'id'=>$taxonomy_id));
	}

	/**
	 * 读取一个模型
	 * @param integer $id
	 * @throws CHttpException
	 * @return Term
	 */
	public function loadModel($id)
	{
		if (!$model = Term::model()->findByPk($id))
			throw new CHttpException(404, '页面没有找到');
		return $model;
	}
}
