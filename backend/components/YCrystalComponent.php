<?php
/**
 * YCrystalComponent class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * YCrystalComponent
 * @author Yang <css3@qq.com>
 * @package backend.components
 */
class YCrystalComponent extends CComponent
{
	/**
	 * 资源路径
	 * @var string
	 */
	protected $assets;

	/**
	 * 标志映射
	 * @var array
	 */
	protected $iconMap = array(
		'code' => array('html'),
		'archive' => array('tar', 'zip', 'rar'),
		'text' => array('txt'),
		'video'=> array('viv', 'vivo', 'mpeg', 'mpg'),
		'spreadsheet' => array('xls'),
		'audio'=> array('wav', 'mp3'),
		'document'=>array('pdf', 'doc'),
		'interactive'=>array('vcd'),
	);

	/**
	 * 初始化
	 */
	public function init() {
		$this->assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__).'/../assets/crystal');
	}

	/**
	 * 设置标志映射
	 * @param array $iconMap
	 */
	public function setIconMap($iconMap=array())
	{
		$this->iconMap = CArray::merge($this->iconMap, $icoMap);
	}

	/**
	 * 获取标志映射
	 * @return multitype:
	 */
	public function getIconMap()
	{
		return $this->iconMap;
	}

	/**
	 * 获取标志URL
	 * @param unknown_type $ext
	 * @return string
	 */
	public function getIconUrl($ext)
	{
		$crystal = 'default';
		foreach ($this->iconMap as $icon => $types) {
			if (in_array($ext, $types)) {
				$crystal = $icon;
				break;
			}
		}

		return $this->assets . "/$crystal.png";
	}

	/**
	 * 获取标志图片
	 * @param string $ext
	 * @param string $alt
	 * @param array $htmlOptions
	 * @return string
	 */
	public function getIcon($ext, $alt='', $htmlOptions=array()) {
		return CHtml::image($this->getIconUrl($ext), $alt, array_merge($htmlOptions, array('width'=>46, 'height'=>60)));
	}
}