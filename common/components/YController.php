<?php
/**
 * YController class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YController
 *
 * @author Yang <css3@qq.com>
 * @package common.components
 */
class YController extends CController
{
	/**
	 * @var string 布局
	 */
	public $layout='//layouts/main';

	/**
	 * @var array 餐单
	 */
	public $menu=array();

	/**
	 * @var array 面包屑导航
	 */
	public $breadcrumbs=array();

	/**
	 * @var string
	 */
	public $baseUrl;

	/**
	 * 获取设置
	 * @param string $key
	 * @param mixed $default
	 * @param string $category
	 * @return mixed
	 */
	public function setting($key, $default=null, $category='general')
	{
		return Setting::get($category, $key, $default);
	}

	/**
	 * @see CController::beforeRender()
	 */
	protected function beforeRender($view)
	{
		if (parent::beforeRender($view)) {
			$theme = Yii::app()->getTheme();
			if ($theme) {
				$this->baseUrl = $theme->getBaseUrl();
			} else {
				$this->baseUrl = Yii::app()->getBaseUrl();
			}
			return true;
		} else {
			return false;
		}
	}
}