<?php
/**
 * Controller class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Base Controller
 * @author Yang <css3@qq.com>
 * @package backend.components
 */
class Controller extends CController
{
	/**
	 * 餐单
	 * @var array
	 */
	public $menu = array();

	/**
	 * 面包屑导航
	 * @var array
	 */
	public $breadcrumbs = array();

	/**
	 * 请求过滤
	 * @see CController::filters()
	 */
	public function filters()
	{
		return array(
			'accessControl',
		);
	}

	/**
	 * 访问规则
	 * @see CController::accessRules()
	 */
	public function accessRules()
	{
		return array(
			array(
				'allow',
				'actions' => array('login', 'logout', 'resetpassword', 'lostpassword', 'captcha'),
				'users' => array('*'),
			),
			array('allow',
				'expression' => array($this, 'authorizer'),
			),
			array(
				'deny',
				'users' => array('*')
			)
		);
	}

	/**
	 * 验证授权
	 * @param User $user
	 * @param CAccessRule $rule
	 * @return boolean
	 */
	public function authorizer($user, $rule)
	{
		if ($user->getId() == User::SUPERADMIN_ID)
			return true;

		$controller = $this;
		$action = $this->action;

		$authItem = '';

		if( ($module = $controller->getModule())!==null )
			$authItem .= ucfirst($module->id).'.';

		$authItem .= ucfirst($controller->id);

		if( $user->checkAccess($authItem.'.*')!==true ) {
			$authItem .= '.'.ucfirst($action->id);

			if( $user->checkAccess($authItem)!==true )
				$allow = false;
		}

		if( $allow === false ) {
			return false;
		} else
			return true;
	}
	/**
	 * ajax验证模型
	 * @param CActiveRecord $model
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax'] === strtolower(__CLASS__ . '-form')) {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}