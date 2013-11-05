<?php
/**
 * BannerController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * BannerController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class BannerController extends Controller
{
	/**
	 * 查看Banner
	 * @param integer $id
	 */
	public function actionView($id)
	{
		$this->render('view', array('model'=>$this->loadModel($id)));
	}

	/**
	 * 创建Banner
	 */
	public function actionCreate()
	{
		$model=new Banner;

		if(isset($_POST['Banner'])) {
			$model->attributes=$_POST['Banner'];

			if($model->save()) {
				Yii::app()->getUser()->setFlash('success', 'Banner已创建');
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

		$this->render('create', array('model'=>$model,));
	}

	/**
	 * 更新Banner
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['Banner'])) {
			$model->attributes=$_POST['Banner'];
			if($model->save()) {
				Yii::app()->getUser()->setFlash('success', 'Banner已更新');
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
	 * 删除Banner
	 * @param unknown_type $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest) {
			$result = $this->loadModel($id)->delete();

			if(!isset($_GET['ajax'])) {
				$result && Yii::app()->getUser()->setFlash('success', 'Banner已删除');
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(400,'无效的请求，请重试');
		}
	}

	/**
	 * 首页
	 * @param string $view
	 */
	public function actionIndex($view='grid')
	{
		if (isset($_POST['saveWeight'])) {
			$weights = isset($_POST['weight']) ? $_POST['weight'] : null;
			foreach ((array) $weights as $id => $weight) {
				Banner::updateWeightByPk($id, $weight);
			}
			Yii::app()->getUser()->setFlash('success', '保存权重成功');
			$this->redirect(array('index'));
		}

		if (isset($_POST['doaction'])) {
			$action = !empty($_POST['action']) ? $_POST['action'] : null;
			$ids = !empty($_POST['ids']) ? $_POST['ids'] : null;
			if ($action && $ids)
				$this->processBulkAction($action, $ids);
			$this->redirect(array('index'));
			return;
		}

		$model=new Banner('search');
		$model->unsetAttributes();
		if(isset($_GET['Banner']))
			$model->attributes=$_GET['Banner'];

		$this->render('index',array(
			'model'=>$model,
			'view'=>$view,
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
	 * @return Banner
	 */
	public function loadModel($id)
	{
		$model=Banner::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'页面没有找到');
		return $model;
	}

	/**
	 * 批量操作
	 * @param string $action
	 * @param mixed $ids
	 */
	protected function processBulkAction($action, $ids)
	{
		$ids = (array) $ids;
		$count = 0;
		switch ($action) {
			case 'delete':
				foreach (Banner::model()->findAllByPk($ids) as $model) {
					if ($model->delete()) {
						$count++;
					}
				}
				Yii::app()->getUser()->setFlash('success', sprintf('已删除%d个Banner', $count));
				break;
		}
	}
}
