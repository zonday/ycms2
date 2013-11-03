<?php
/**
 * 后台基础Controller
 * @author Yang <css3@qq.com>
 */
class Controller extends CController
{
	/**
	 * 餐单
	 * @var array
	 */
	public $menus = array();

	/**
	 * 面包屑导航
	 * @var array
	 */
	public $breadcrumbs = array();

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