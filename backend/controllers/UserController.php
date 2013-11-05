<?php
/**
 * UserController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * UserController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class UserController extends Controller
{
	/**
	 * 首页
	 */
	public function actionIndex()
	{
		if (isset($_POST['doaction'])) {
			$action = !empty($_POST['action']) ? $_POST['action'] : null;
			$ids = !empty($_POST['ids']) ? $_POST['ids'] : null;
			if ($action && $ids)
				$this->processBulkAction($action, $ids);
			$this->redirect(array('index'));
			return;
		}

		$model = new User('search');
		$model->unsetAttributes();

		if (isset($_GET['User']))
			$model->attributes = $_GET['User'];

		$this->render('index', array('model' => $model));
	}

	/**
	 * 创建用户
	 */
	public function actionCreate()
	{
		$model = new User();

		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '用户创建成功');
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

	/**
	 * 更新用户
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);
		unset($model->password); //不显示密码

		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];

			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '用户更新成功');
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

	/**
	 * 查看用户
	 * @param integer $id
	 */
	public function actionView($id)
	{
		$this->render('view', array('model' => $this->loadModel($id)));
	}

	/**
	 * 删除用户
	 * @param integer $id
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = $this->loadModel($id)->delete();

			if ($result) {
				if (!isset($_GET['ajax'])) {
					Yii::app()->getUser()->setFlash('success', '用户删除成功');
				}
			}

			if (!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			throw new CHttpException(403, '无效的请求，请重试');
		}
	}

	/**
	 * 个人资料
	 */
	public function actionProfile()
	{
		$model = $this->loadModel(Yii::app()->getUser()->id);
		unset($model->password);

		if(isset($_POST['User']))
		{
			$model->attributes=$_POST['User'];
			if($model->save(array('username', 'nickname', 'password', 'password_repeat', 'email')))
			{
				Yii::app()->getUser()->nickname= $model->nickname;
				Yii::app()->getUser()->setFlash('success', '个人资料已更新');
				$this->redirect(array('profile'));
			}
		}

		$this->render('profile', array('model'=>$model));
	}

	/**
	 * 获取模型
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		if (!$model = User::model()->findByPk($id)) {
			throw new CHttpException(404, '页面没有找到');
		}
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

		switch ($action) {
			case 'active':
				User::model()->updateByPk($ids, array('status' => User::STATUS_DEFAULT));
				break;
			case 'notactive':
				User::model()->updateByPk($ids, array('status' => User::STATUS_NOT_ACTIVATED));
				break;
			case 'block':
				User::model()->updateByPk($ids, array('status' => User::STATUS_BLOCK));
				break;
			 case 'delete':
				foreach (User::model()->findAllByPk($ids) as $model)
					$model->delete();
				break;
		}

		if (($pos = strpos($action, 'add_role-')) !== false) {
			$roleName = substr($action, 9);
			if ($roleName) {
				foreach (User::model()->findAllByPk($ids) as $model)
					$model->addRole($roleName);
			}
		} elseif (($pos = strpos($action, 'remove_role-')) !== false) {
			$roleName = substr($action, 12);
			if ($roleName) {
				foreach (User::model()->findAllByPk($ids) as $model)
					$model->removeRole($roleName);
			}
		}

		Yii::app()->getUser()->setFlash('success', '应用已执行');
	}
}