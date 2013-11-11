<?php
/**
 * YBackendComponent class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * YBackendComponent
 * @author Yang <css3@qq.com>
 * @package backend.components
 */
class YBackendComponent extends CApplicationComponent
{
	protected $assets;

	public function init()
	{
		$this->assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__).'/../assets/backend');
		$this->register();
	}

	public function register()
	{
		$this->registerCss();
		$this->registerCommonJs();
		$this->registerScrollTop();
	}

	public function registerCss()
	{
		Yii::app()->getClientScript()->registerCssFile($this->assets . '/css/style.css');
	}

	public function registerCommonJs()
	{
		Yii::app()->getClientScript()->registerScriptFile($this->assets . '/js/common.js');
	}

	public function registerScrollTop()
	{
		$clientScript = Yii::app()->getClientScript();
		$clientScript->registerCoreScript('jquery');
		$clientScript->registerScriptFile($this->assets . '/js/jquery/jquery.scrollUp.min.js');
		$clientScript->registerScript(__CLASS__ . '#scollTop', '$.scrollUp({scrollText:"Top"})');
	}

	public function getName()
	{
		return 'YCMS';
	}

	public function getVersion()
	{
		return '0.0.3';
	}
}