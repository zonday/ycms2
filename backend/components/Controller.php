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
}