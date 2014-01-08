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

		$allow = true;
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
	 * 获取内容模型导航
	 * @param integer $parent 栏目id
	 * @return array
	 */
	public function getContentItems($parent=0)
	{
		$items = array();
		$children = Channel::model()->getChildren($parent);
		foreach ($children as $parent => $channel) {
			if ($channel->status == Channel::STATUS_TRASH) {
				continue;
			}

			if ($channel->model) {
				if (is_subclass_of($channel->model, 'Node')) {
					$url = array('/content/index', 'channel'=>$channel->id);
				} else {
					$url = array('/other/' . strtolower($channel->model));
				}
			} else {
				$url = array('/content/channel', 'channel'=>$channel->id);
			}

			$item = array('label'=>$channel->title, 'url'=>$url);
			$childrenItems = $this->getContentItems($channel->id);

			if ($childrenItems !== array())
				$item['items'] = $childrenItems;
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * 获取其他模型导航
	 * @return array
	 */
	public function getOtherNavItems()
	{
		$items = array();
		if (isset(Yii::app()->params['otherModels'])) {
			foreach (Yii::app()->params['otherModels'] as $model => $name) {
				$items[] = array(
					'label'=>$name,
					'url'=>array('/other/' . strtolower($model) . '/index'),
				);
			}
		}
		if ($items) {
			array_unshift($items, array('label' => '其他', 'itemOptions' => array('class' => 'nav-header')));
		}
		return $items;
	}

	/**
	 * 获取扩展导航
	 */
	public function getExtendNavTtems()
	{
		$items = array();
		if (isset(Yii::app()->params['extendNav'])) {
			$items = Yii::app()->params['extendNav'];
		}
		if ($items) {
			array_unshift($items, array('label' => '扩展', 'itemOptions' => array('class' => 'nav-header')));
		}
		return $items;
	}

	/**
	 * 生成侧边栏导航
	 * @return array
	 */
	protected function generateNavItems()
	{
		$cacheKey = 'backend_nav_items_' . Yii::app()->getSession()->getSessionID();
		if (($items = Yii::app()->getCache()->get($cacheKey)) === false) {
			$items = array_merge(array(
					array('label' => '首页', 'url' => array('/site/index'), 'icon' => 'fixed-width home')
				)
				,
				$this->getContentItems(),
				$this->getOtherNavItems(),
				$this->getExtendNavTtems(),
				array(
				array('label' => '系统', 'itemOptions' => array('class' => 'nav-header')),
				array('label' => '栏目', 'url' => array('/channel/index'), 'icon' => 'fixed-width list'),
				array('label' => '分类', 'url' => array('/taxonomy/index'), 'icon' => 'fixed-width tags'),
				array('label' => '链接', 'url' => array('/link/index'), 'icon' => 'fixed-width link'),
				array('label' => 'Banner', 'url' => array('/banner/index'), 'icon' => 'fixed-width picture'),
				array('label' => '文件', 'url' => array('/file/index'), 'icon' => 'fixed-width file'),
				array('label' => '用户', 'url' => array('/user/index'), 'icon' => 'fixed-width user'),
				array('label' => '角色', 'url' => array('/role/index'), 'icon' => 'fixed-width group'),
				array('label' => '权限', 'url' => array('/permission/index'), 'icon' => 'fixed-width lock'),
				array('label' => '设置', 'url' => array('/site/setting'), 'icon' => 'fixed-width cog'),
			));

			$this->filterNavItems($items);
			Yii::app()->getCache()->set($cacheKey, $items, 3600);
		}

		return $items;
	}

	protected function filterNavItems(&$items)
	{
		if (Yii::app()->getUser()->getId() == User::SUPERADMIN_ID)
			return;

		$user = Yii::app()->getUser();
		foreach ($items as $index => &$item)
		{
			if (isset($item['items']) && is_array($item['items']))
				$this->filterNavItems($item['items']);

			if (!isset($item['url']) || $item['url'] === '#' || !is_array($item['url']) || !isset($item['url'][0]))
				continue;

			$parts = explode('/', trim($item['url'][0], '/'));

			if (empty($parts))
				continue;

			if ($user->checkAccess(ucfirst($parts[0]) . '.*') === false)
			{
				if (isset($parts[1]) && ($user->checkAccess(ucfirst($parts[0]) . '.' . ucfirst($parts[1])) === true))
					continue;
				unset($items[$index]);
			}
		}
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