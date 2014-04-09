<?php
/**
 * PermissionController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * PermissionController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class PermissionController extends Controller
{
	/**
	 * 权限首页
	 */
	public function actionIndex($name=null)
	{
		$authManager = Yii::app()->getAuthManager();
		$taskItems = $authManager->getAuthItems(CAuthItem::TYPE_TASK);
		$operationItems = $authManager->getAuthItems(CAuthItem::TYPE_OPERATION);

		$roleAuthItems = array();
		$roleList = Role::allList();
		if ($name !== null && isset($roleList[$name])) {
			$roleList = array($name => $roleList[$name]);
		}
		foreach ($roleList as $name => $description) {
			$roleAuthItems[$name] = $authManager->getItemChildren($name);
		}

		$authItems = array();
		$childNames = array();
		foreach ($taskItems as $authItem) {
			$authItems[$authItem->getName()] = $authItem;
			$children = $authManager->getItemChildren($authItem->getName());
			foreach ($children as $child) {
				$authItems[$child->getName()] = $child;
				$childNames[$child->getName()] = $authItem->getName();
			}
		}

		foreach ($operationItems as $authItem)
			if (!isset($authItems[$authItem->getName()]))
				$authItems[$authItem->getName()] = $authItem;

		$data = array();
		foreach ($authItems as $name => $authItem) {
			$row = array('name'=> $name, 'authItem' => $authItem, 'roles' => array());
			foreach ($roleAuthItems as $role => $children) {
				foreach ($children as $child) {
					if ($child->getName() == $authItem->getName()) {
						$row['roles'][$role] = true;
						break;
					}
				}
			}
			$data[] = $row;
		}
		$this->render('index', array('data' => $data, 'roleList' => $roleList, 'childNames' => $childNames));
	}

	/**
	 * 保存权限
	 */
	public function actionSave()
	{
		if (isset($_POST['auth'])) {
			$auth = $_POST['auth'];
			$authManager = Yii::app()->getAuthManager();

			$oldRoleAuthItems = array();
			foreach ( Role::allList() as $roleName => $description) {
				foreach ($authManager->getItemChildren($roleName) as $authItem) {
					$oldRoleAuthItems[$roleName][] = $authItem->getName();
				}
			}

			$addAuthItems = array();
			$delAuthItems = array();
			foreach ((array) $auth as $roleName => $newAuthItems) {
				$newAuthItems = (array) $newAuthItems;
				foreach ($newAuthItems as $index => $newAuthItem)
					if (empty($newAuthItem))
						unset($newAuthItems[$index]);
				$_oldRoleAuthItems = isset($oldRoleAuthItems[$roleName]) ? $oldRoleAuthItems[$roleName] : array();
				$addAuthItems[$roleName] = array_diff($newAuthItems, $_oldRoleAuthItems);
				$delAuthItems[$roleName] = array_diff($_oldRoleAuthItems, $newAuthItems);
			}

			foreach ($addAuthItems as $roleName => $authItems) {
				foreach ($authItems as $name) {
					$authManager->addItemChild($roleName, $name);
				}
			}

			foreach ($delAuthItems as $roleName => $authItems) {
				foreach ($authItems as $name) {
					$authManager->removeItemChild($roleName, $name);
				}
			}

			Yii::app()->getUser()->setFlash('success', '权限保存成功');
		}
		$this->redirect(array('index'));
	}

	/**
	 * 移除任务下的操作
	 * @param string $name 操作名
	 * @param string $parent 任务名
	 */
	public function actionRemove($name, $parent)
	{
		$parentAuthItem = $this->loadAuthItem($parent);
		if ($parentAuthItem->removeChild($name))
			Yii::app()->getUser()->setFlash('success', '移除成功');
		else
			Yii::app()->getUser()->setFlash('error', '移除失败');
		$this->redirect(array('index'));
	}

	/**
	 * 添加任务或操作
	 * @param integer $type
	 */
	public function actionCreate($type=CAuthItem::TYPE_OPERATION)
	{
		$model = new AuthItemForm();
		$model->setType($type);
		if (isset($_POST['AuthItemForm'])) {
			$authManager = Yii::app()->getAuthManager();
			$model->attributes = $_POST['AuthItemForm'];
			if ($model->validate()) {
				$authItem = $authManager->createAuthItem($model->name, $model->getType(), $model->description, $model->bizRule, $model->data);
				if ($model->getType() == CAuthItem::TYPE_OPERATION && !empty($model->task)) {
					$taskAuthItem = $authManager->getAuthItem($model->task);
					if ($taskAuthItem && $taskAuthItem->getType() == CAuthItem::TYPE_TASK)
						$taskAuthItem->addChild($authItem->getName());
				}
				Yii::app()->getUser()->setFlash('success', $model->typeNames[$model->getType()] . '创建成功');
				$this->redirect(array('index'));
			}
		}

		$this->render('create', array('type' => $model->getType(), 'model' => $model));
	}

	/**
	 * 更新任务或者操作
	 * @param string $name
	 */
	public function actionUpdate($name)
	{
		$authItem = $this->loadAuthItem($name);
		$model = new AuthItemForm();
		$model->isNewRecord = false;
		if (isset($_POST['AuthItemForm'])) {
			$authManager = Yii::app()->getAuthManager();
			$model->attributes = $_POST['AuthItemForm'];
			if ($model->validate()) {
				$newAuthItem = new CAuthItem(
					$model->name,
					$model->name,
					$authItem->getType(),
					$model->description,
					$model->bizRule,
					$model->data);
				$authManager->saveAuthItem($newAuthItem, $authItem->getName());
				if ($model->getType() == CAuthItem::TYPE_OPERATION && !empty($model->task)) {
					$taskAuthItem = $authManager->getAuthItem($model->task);
					if ($taskAuthItem && !$taskAuthItem->hasChild($model->name) && $taskAuthItem->getType() == CAuthItem::TYPE_TASK)
						$taskAuthItem->addChild($newAuthItem->getName());
				}
				Yii::app()->getUser()->setFlash('success', $model->typeNames[$model->getType()] . '更新成功');
				$this->redirect(array('index'));
			}
		} else {
			$model->attributes = array(
				'name' => $authItem->getName(),
				'description' => $authItem->getDescription(),
				'bizRule' => $authItem->getBizRule(),
				'data' => $authItem->getData(),
			);
		}
		$model->setType($authItem->getType());
		$this->render('update', array('type' => $authItem->getType(), 'model' => $model));
	}

	/**
	 * 删除任务或者操作
	 * @param string $name
	 * @throws CHttpException
	 */
	public function actionDelete($name)
	{
		if(Yii::app()->getRequest()->getIsPostRequest()) {
			$authItem = $this->loadAuthItem($name);
			$model = new AuthItemForm();
			if (Yii::app()->getAuthManager()->removeAuthItem($name))
				Yii::app()->getUser()->setFlash('success', $model->typeNames[$authItem->getType()] . '删除成功');

			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		} else
			throw new CHttpException(403, '无效的请求，请重试');
	}

	/**
	 * 读取权限
	 * @param string $name
	 * @throws CHttpException
	 * @throws CException
	 */
	public function loadAuthItem($name)
	{
		$authManager = Yii::app()->getAuthManager();
		if (!$authItem = $authManager->getAuthItem($name))
			throw new CHttpException(404, '页面没有找到');

		if ($authItem->getType() == CAuthItem::TYPE_ROLE)
			throw new CException('角色不能被读取');
		return $authItem;
	}
}