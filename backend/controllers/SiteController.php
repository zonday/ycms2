<?php
/**
 * 后台站点Controller
 * @author Yang <css3@qq.com>
 */
class SiteController extends Controller
{
	/**
	 * 首页action
	 */
	public function actionIndex()
	{
		$this->render('index');
	}
}