<?php
/**
 * SiteController class File
 *
 * @author Yang <css3@qq.com>
 */

/**
 * SiteController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class SiteController extends Controller
{
	/**
	 * actions
	 * @see CController::actions()
	 * @return array
	 */
	public function actions()
	{
		return array(
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
		);
	}

	/**
	 * 首页
	 */
	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * 登录
	 */
	public function actionLogin()
	{
		$this->layout = 'none';
		$model = new LoginForm;

		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			if ($model->validate() && $model->login()) {
				User::model()->updateByPk(Yii::app()->getUser()->getId(), array('login_time'=>time()));
				$this->redirect(Yii::app()->getUser()->returnUrl);
			}
		}

		$this->render('login', array('model' => $model));
	}

	/**
	 * 忘记密码
	 */
	public function actionLostpassword()
	{
		if (!Yii::app()->hasComponent('mailer')) {
			throw new CException('应用程序没有配置邮件服务，不能找回密码。');
		}

		$this->layout = 'none';

		$model = new LostPasswordForm;

		if(isset($_POST['LostPasswordForm'])) {
			$model->attributes = $_POST['LostPasswordForm'];
			if($model->validate() && $model->lostPassword()) {
				$user = $model->getUser();
				$subject = sprintf('%s重置密码', Yii::app()->name);
				$message = sprintf('请复制下面地址进行重置密码%s', Yii::app()->createAbsoluteUrl('site/resetpassword', array('login'=>$user->username, 'key'=>$user->activation_key)));
				Yii::app()->mailer->sendmail($user->email, $subject, $message);
				Yii::app()->getUser()->setFlash('success', '重置密码邮件已发送您的电子邮箱。');
				$this->redirect(array('login'));
			}
		}

		$this->render('lost-password',array('model'=>$model));
	}

	/**
	 * 重置密码
	 */
	public function actionResetpassword()
	{
		$this->layout = 'none';

		$model = new ResetPasswordForm;

		if(isset($_POST['ResetPasswordForm'])) {
			$model->attributes = $_POST['ResetPasswordForm'];
			if($model->validate()) {
				if (!$model->validateKey()) {
					Yii::app()->getUser()->setFlash('error', '无效的KEY，请重新获取重置密码邮件');
					$this->redirect(array('lostpassword'));
				} elseif ($model->resetPassword()) {
					Yii::app()->getUser()->setFlash('success', '重置密码成功');
					$this->redirect(array('login'));
				} else {
					Yii::app()->getUser()->setFlash('error', '重置密码失败');
				}
			}
		} else {
			if (isset($_GET['login']))
				$model->login = $_GET['login'];

			if (isset($_GET['key']))
				$model->key = $_GET['key'];

			if (!$model->validateKey()) {
				Yii::app()->getUser()->setFlash('error', '无效的KEY，请重新获取重置密码邮件');
				$this->redirect(array('lostpassword'));
			}
		}

		$this->render('reset-password',array('model' => $model));
	}

	/**
	 * 激活认证电子邮件
	 */
	public function actionActivation()
	{
		$this->layout = 'none';
		$key = $login = '';

		if (isset($_GET['key']))
			$key = $_GET['key'];

		if (isset($_GET['login']))
			$login = $_GET['login'];

		$user = User::validateKey($key, $login);
		if ($user) {
			$result = $user->saveAttributes(array(
				'status' => User::STATUS_NOMAL,
				'activation_key' => '',
			));
		} else {
			$resutl = false;
		}

		if ($result)
			Yii::app()->getUser()->setFlash('success', '验证电子邮箱成功');
		else
			Yii::app()->getUser()->setFlash('error', '验证电子邮箱失败');

		$this->redirect(array('login'));
	}

	/**
	 * 退出
	 */
	public function actionLogout()
	{
		$cacheKey = 'backend_nav_items_' . Yii::app()->getSession()->getSessionID();
		Yii::app()->getCache()->delete($cacheKey);
		Yii::app()->getUser()->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * 错误
	 */
	public function actionError()
	{
		$this->layout = 'none';

		if($error = Yii::app()->errorHandler->error) {
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * 设置
	 * @param string $category
	 * @throws CHttpException
	 */
	public function actionSetting($category='general')
	{
		$categories = Setting::getCategories();
		if (!isset($categories[$category]))
			throw new CHttpException(404, '页面没有找到');

		$model = new Setting();
		$model->bindCategory($category);
		if (isset($_POST['Setting'])) {
			$model->attributes = $_POST['Setting'];
			if ($model->save()) {
				Yii::app()->getUser()->setFlash('success', '设置已更新');
				$this->redirect(array('setting', 'category'=>$category));
			}
		}
		$this->render('setting', array('model'=>$model, 'category'=>$category, 'categories'=>$categories));
	}

	/**
	 * 清空缓存
	 */
	public function actionFlushCache()
	{
		Yii::app()->getCache()->flush();
		Yii::app()->getUser()->setFlash('success', '站点缓存已清空');
		$this->redirect(array('setting'));
	}
}