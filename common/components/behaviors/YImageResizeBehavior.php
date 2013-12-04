<?php
/**
 * YImageResizeBehavior class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YImageResizeBehavior 图片缩放行为
 *
 * @author Yang <css3@qq.com>
 * @package common.components.behaviors
 */
class YImageResizeBehavior extends CActiveRecordBehavior
{
	/**
	 * @var array 图片尺寸
	 */
	private $_sizes = array();

	/**
	 * 设置图片尺寸
	 * @param array $sizes
	 */
	public function setSizes($sizes)
	{
		foreach ($sizes as $name => $size) {
			if (empty($size)) {
				$this->deleteSize($name);
				continue;
			}
			$crop = isset($size[2]) ? $size[2] : false;
			$this->setSize($name, $size[0], $size[1], $crop);
		}
	}

	/**
	 * 获取图片尺寸
	 * @return array
	 */
	public function getSizes()
	{
		return $this->_sizes;
	}

	/**
	 * 设置一个尺寸
	 * @param string $name 尺寸名
	 * @param integer $weight 宽
	 * @param integer $height 高
	 * @param boolean $crop 是否裁剪
	 */
	public function setSize($name, $weight, $height, $crop=false)
	{
		$this->_sizes[$name] = array($weight, $height, $crop);
	}

	/**
	 * 删除尺寸
	 * @param array $names
	 */
	public function deleteSize($names)
	{
		foreach((array) $names as $name)
			unset($this->_sizes[$name]);
	}

	/**
	 * 获取一个尺寸
	 * @param string $name
	 * @return array|null
	 */
	public function getSize($name)
	{
		if (isset($this->_sizes[$name]))
			return $this->_sizes[$name];
	}

	/**
	 * 模型保存之前
	 * @see CActiveRecordBehavior::beforeSave()
	 */
	public function beforeSave($event)
	{
		if (!$event->isValid)
			return;

		$owner = $this->getOwner();
		if (!$owner->isNewRecord)
			return;

		if ($owner->isImage()) {
			$sizes = $this->resizeImage($owner->getPath(), $this->_sizes);

			if (is_array($sizes)) {
				$owner->meta['sizes'] = $sizes;
			}
		}
	}

	/**
	 * 缩放图片
	 * @param string $filePath 文件路径
	 * @param array $sizes 图片尺寸
	 * @return boolean|array
	 */
	public function resizeImage($filePath, $sizes)
	{
		if (!Yii::app()->hasComponent('image'))
			return false;

		$owner = $this->getOwner();

		$metaSizes = array();

		$parts = pathinfo($filePath);
		$dirname = $parts['dirname'];
		$filename = $parts['filename'];
		$ext = $parts['extension'];
		$image = Yii::app()->image->load($filePath);

		$scheme = $owner->uriScheme($owner->uri);
		$baseUri = str_replace(basename($owner->uri), '', $owner->uri);
		if (substr($baseUri, -1) !== '/')
			$baseUri .= '/';

		foreach ($sizes as $name => $size) {
			if (!isset($size[2]))
				$size[2] = false;

			list($width, $height, $crop) = $size;

			if (empty($width) && $height) {
				$master = Image::HEIGHT;
				$width = ceil($image->width * $height / $image->height);
			} elseif (empty($height) && $width) {
				$master = Image::WIDTH;
				$height = ceil($image->height * $width / $image->width);
			} else {
				$master = Image::AUTO;
			}

			if ($image->width < $width && $image->height < $height && $name !== File::IMAGE_THUMBNAIL)
				continue;

			if (!$crop) {
				$image->resize($width, $height, $master);
			} else {
				list($cropW, $cropH, $top, $left, $newW, $newH) = $this->resizeImageCrop($image->width, $image->height, $width, $height);
				$image->crop($cropW, $cropH, $top, $left)->resize($newW, $newH);
			}

			$directory = $baseUri . "{$name}";

			if (!$owner->prepareDirectory($owner->localPath($directory)))
				continue;

			$uri = $directory . "/{$filename}.{$ext}";
			$saveFilePath = $owner->localPath($uri);

			if ($image->save($saveFilePath)) {
				$imageSize = getimagesize($saveFilePath);
				$metaSizes[$name] = array(
					'uri'=>$uri,
					'width'=>$imageSize[0],
					'height'=>$imageSize[1],
					//'mime'=>$imageSize['mime'],
				);
			}
		}

		$metaSizes['origin'] = array(
			'width' => $image->width,
			'height'=> $image->height,
			'uri' => $owner->uri,
		);

		return $metaSizes;
	}

	/**
	 * 获取裁剪图片的相关参数 裁剪后的定位
	 * @param integer $origW
	 * @param integer $origH
	 * @param integer $destW
	 * @param integer $destH
	 * @return array
	 */
	public function resizeImageCrop($origW, $origH, $destW, $destH)
	{
		$aspectRatio = $origW / $origH;
		$newW = min($destW, $origW);
		$newH = min($destH, $origH);

		if (!$newW) {
			$newW = intval($newH * $aspectRatio);
		}

		if (!$newH) {
			$newH = intval($newW / $aspectRatio);
		}

		$sizeRatio = max($newW / $origW, $newH / $origH);
		$cropW = round($newW / $sizeRatio);
		$cropH = round($newH / $sizeRatio);

		$sX = floor(($origW - $cropW) / 2);
		$sY = floor(($origH - $cropH) / 2);

		return array($cropW, $cropH, $sY, $sX, $newW, $newH);
	}
}