<?php
/**
 * File class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * File Model
 *
 * @property integer $id
 * @property string $name
 * @property string $alt
 * @property string $description
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class File extends CActiveRecord
{
	const IMAGE_THUMBNAIL = 'thumbnail';
	const IMAGE_MEDIUM = 'medium';
	const IMAGE_LARGE = 'large';
	const IMAGE_ORIGIN = 'origin';

	/**
	 * 状态临时的 可以被清理的 文件没有被使用
	 * @var integer
	 */
	const STATUS_TEMPORARY = 0;

	/**
	 * 状态永久的
	 * @var integer
	 */
	const STATUS_PERMANENT = 1;

	/**
	 * 上传虚拟字段
	 * @var CUploadedFile
	 */
	public $file;

	public $files;

	/**
	 *
	 * @var array
	 */
	public $meta = array();

	/**
	 * 获取模型
	 * @param string $className
	 * @return File
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 获取表名
	 * @see CActiveRecord::tableName()
	 */
	public function tableName()
	{
		return '{{file}}';
	}

	public function rules() {
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>64),
			array('alt', 'length', 'max'=>128),
			array('description, caption', 'safe'),
			array('create_time, update_time, user_id, id', 'safe', 'on'=>'search'),
		);
	}

	public function behaviors()
	{
		return array(
			'CTimestampBehavior' => array(
				'class' => 'zii.behaviors.CTimestampBehavior',
				'createAttribute' => 'create_time',
				'updateAttribute' => 'update_time',
				'setUpdateOnCreate' => true,
			),
			'YFileUsageBehavior' => array(
				'class' => 'YFileUsageBehavior',
				'fields' => array(
					'file' => array(
						'location' => 'public://files/' . date('Y/m'),
						'resize' => self::imageSizes(),
						'many' => 1,
					),
					'files' => array(
						'location' => 'public://files/' . date('Y/m'),
						'resize' => self::imageSizes(),
						'many' => true,
					),
				),
			),
		);
	}

	/**
	 * 字段标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => '上传者',
			'create_time' => '上传时间',
			'update_time' => '更新时间',
			'file' => '文件',
			'files' => '文件',
			'name' => '名称',
			'caption' => '说明',
			'description' => '描述',
			'alt' => '替代文本',
			'size' => '大小',
			'path' => '路径',
		);
	}

	/**
	 * 关系列表
	 * @see CActiveRecord::relations()
	 */
	public function relations()
	{
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * 搜索
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria=new CDbCriteria(array(
			'condition' => 'bundle=:bundle',
			'params' => array(
				':bundle' => 'File',
			),
		));

		$criteria->compare('t.id',$this->id);
		$criteria->compare('filename',$this->name,true);

		if (is_numeric($this->user_id)) {
			$criteria->compare('user_id',$this->user_id);
		} else {
			$this->with('user');
			$criteria->compare('user.username', $this->user_id, true);
		}

		if ($this->create_time)
		{
			$condition = YUtil::generateTimeCondition($this->create_time, 't.create_time');
			if ($condition)
				$criteria->addCondition($condition);
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=> 't.create_time DESC'
			)
		));
	}

	/**
	 * 图片尺寸列表
	 * @return array
	 */
	public static function imageSizes()
	{
		return array(
			self::IMAGE_THUMBNAIL => array(150, 150, true),
			self::IMAGE_MEDIUM => array(800, 600),
			self::IMAGE_LARGE => array(1024, 1024),
		);
	}

	/**
	 * 获取文件名称
	 * @return string
	 */
	public function getName()
	{
		$ext = $this->getExt();
		if ( ($pos = strrpos($this->filename, $ext)) !== false) {
			return substr($this->filename, 0, $pos - 1);
		}
	}

	/**
	 * 设置文件名称
	 * @param string $value
	 */
	public function setName($value)
	{
		if (trim($value) == '') {
			$this->filename = '';
		} else {
			$this->filename = $value . '.' . $this->getExt();
		}
	}

	/**
	 * 获取文件说明
	 * @return string
	 */
	public function getCaption()
	{
		return isset($this->meta['caption']) ? $this->meta['caption'] : null;
	}

	/**
	 * 设置文件说明
	 * @param string $value
	 */
	public function setCaption($value)
	{
		$this->meta['caption'] = $value;
	}

	/**
	 * 获取文件名称
	 * @return string
	 */
	public function getDescription()
	{
		return isset($this->meta['description']) ? $this->meta['description'] : null;
	}

	/**
	 * 设置文件描述
	 * @param string $value
	 */
	public function setDescription($value)
	{
		$this->meta['description'] = $value;
	}

	/**
	 * 获取图片替代文本
	 * @return string
	 */
	public function getAlt()
	{
		return isset($this->meta['alt']) ? $this->meta['alt'] : null;
	}

	/**
	 * 设置图片替代文本
	 * @param unknown_type $value
	 */
	public function setAlt($value)
	{
		$this->meta['alt'] = $value;
	}

	/**
	 * 获取文件扩展名
	 * @return string
	 */
	public function getExt()
	{
		return pathinfo($this->filename, PATHINFO_EXTENSION);
	}

	/**
	 * 获取文件尺寸大小
	 */
	public function getSize()
	{
		return Yii::app()->format->size($this->filesize);
	}

	/**
	 * 获取下载数目
	 * @return number|0
	 */
	public function getDownloadCount()
	{
		if ($this->isNewRecord)
			return 0;

		$cacheKey = "file_{$this->id}_download_count";
		if (($count = Yii::app()->getCache()->get($cacheKey)) === false) {
			$count = $this->getDbConnection()->createCommand('SELECT download_count FROM {{file_usage}} WHERE file_id=:file_id LIMIT 1')
			->bindValue(':file_id', $this->id, PDO::PARAM_INT)
			->queryScalar();
			Yii::app()->getCache()->add($cacheKey, $count ? $count : 0, Setting::get('system', '_file_cache_expire', 3600));
		}
		return $count;
	}

	/**
	 * 更新下载数目缓存
	 */
	public function updateDownloadCountCache()
	{
		$cacheKey = "file_{$this->id}_download_count";
		$count = $this->getDownloadCount();
		$count +=1;
		Yii::app()->getCache()->set($cacheKey, $count, Setting::get('system', '_file_cache_expire', 3600));
	}

	/**
	 * 删除下载数目缓存
	 */
	public function deleteDownlodCountCache()
	{
		$cacheKey = "file_{$this->id}_download_count";
		Yii::app()->getCache()->delete($cacheKey);
	}

	/**
	 * 下载文件
	 */
	public function download()
	{
		$this->getDbConnection()->createCommand('UPDATE {{file_usage}} SET download_count = download_count+1 WHERE file_id=:file_id')
		->bindValue(':file_id', $this->id, PDO::PARAM_INT)
		->execute();
		$this->updateDownloadCountCache();
		Yii::app()->getRequest()->xSendFile($this->getPath(), array('mimeType'=>$this->filemime, 'saveName'=>$this->filename));
	}

	/**
	 * 获取图片 根据生成的图片尺寸名
	 * @param string $name 图片尺寸名
	 * @param array $htmlOptions
	 * @return string
	 */
	public function getImage($name, $htmlOptions=array())
	{
		if (!$this->hasImage($name)) {
			$name = self::IMAGE_ORIGIN;
		}

		if ($name == self::IMAGE_ORIGIN) {
			$meta = isset($this->meta['sizes'][self::IMAGE_ORIGIN]) ? $this->meta['sizes'][self::IMAGE_ORIGIN] : array();
			$src = $this->createUrl($this->uri);
		} else {
			$meta = isset($this->meta['sizes'][$name]) ? $this->meta['sizes'][$name] : array();;
			$src = $this->createUrl($meta['uri']);
		}

		if ($meta)
			$htmlOptions = array_merge( array(
				'width'=>$meta['width'],
				'height'=>$meta['height'],
			), $htmlOptions);

		if (empty($htmlOptions['width']))
			unset($htmlOptions['width']);

		if (empty($htmlOptions['height']))
			unset($htmlOptions['height']);

		if (isset($htmlOptions['alt'])) {
			$alt = $htmlOptions['alt'];
			unset($htmlOptions['alt']);
		} else {
			$alt = $this->getAlt() ? $this->getAlt() : $this->name;
		}

		return CHtml::image($src, $alt, $htmlOptions);
	}

	/**
	 * 获取图片URL 根据图片尺寸名
	 * @param string $name
	 * @param boolean $force 如果图片尺寸不存在 是否强制返回图片
	 * @return NULL|string
	 */
	public function getImageUrl($name, $force=false)
	{
		if (!$this->hasImage($name)) {
			if ($name == self::IMAGE_THUMBNAIL || $force) {
				$name = self::IMAGE_ORIGIN;
			} else {
				return null;
			}
		}

		if ($name == self::IMAGE_ORIGIN) {
			return $this->createUrl($this->uri);
		} else {
			return $this->createUrl($this->meta['sizes'][$name]['uri']);
		}
	}

	/**
	 * 获取文件的缩略图
	 * @param array $htmlOptions
	 * @return string
	 */
	public function getImageThumbnail($htmlOptions=array())
	{
		return $this->getImage(self::IMAGE_THUMBNAIL, $htmlOptions);
	}

	/**
	 * 获取文件的图标地址
	 * @return string|NULL
	 */
	public function getIconUrl()
	{
		if ($this->isImage() && $this->uriScheme($this->uri) === 'public')
			$iconUrl =  $this->getImageUrl('thumbnail');

		if (!isset($iconUrl) && Yii::app()->hasComponent('crystal'))
			$iconUrl = Yii::app()->crystal->getIconUrl($this->getExt());

		return isset($iconUrl) ? $iconUrl : null;
	}

	/**
	 * 获取文件的图标
	 * @return string
	 */
	public function getIcon($htmlOptions=array())
	{
		if ($this->isImage() && $this->uriScheme($this->uri) === 'public')
		{
			if (!$this->hasImage('thumbnail'))
				$htmlOptions['style']='max-width: 100%;';
			return $this->getImageThumbnail(array_merge(array('width'=>60, 'height'=>60), $htmlOptions));
		} elseif (Yii::app()->hasComponent('crystal'))
			return Yii::app()->crystal->getIcon($this->getExt(), CHtml::$this->caption, $htmlOptions);
		else
			return '';
	}

	/**
	 * 获取图片尺寸列表
	 * @return string
	 */
	public function getImageSizeList()
	{
		$sizeMap = array(
			self::IMAGE_ORIGIN=>'原图',
			self::IMAGE_LARGE=>'大图',
			self::IMAGE_MEDIUM=>'中图',
			self::IMAGE_THUMBNAIL=>'缩略图',
		);

		$file = $this->toArray();

		$data = array();
		if (isset($file['sizes'])) {
			foreach ($file['sizes'] as $name => $meta) {
				if (isset($sizeMap[$name])) {
					if ($name == self::IMAGE_ORIGIN)
						$key = $file['url'];
					else
						$key = $meta['url'];

					$data[$key] = $sizeMap[$name] . '[' . $meta['width'] . 'x' . $meta['height'] . ']';
				}
			}
		}
		return $data;
	}

	/**
	 * 根据文件后缀 判断文件是否是图片
	 * @return boolean
	 */
	public function isImage()
	{
		return !!preg_match('/jpe?g|gif|png$/i', $this->uri);
	}

	/**
	 * 检测文件是否有相关的图片尺寸
	 * @param string $name
	 */
	public function hasImage($name)
	{
		return isset($this->meta['sizes'][$name]);
	}

	/**
	 * 获取$uri中的协议
	 * @param string $uri
	 * @return boolean|string
	 */
	public static function uriScheme($uri)
	{
		$pos = strpos($uri, '://');
		return $pos ? substr($uri, 0, $pos) : false;
	}

	/**
	 * 根据$uri获取其目标目录
	 * @param string $uri
	 * @return boolean|string
	 */
	public static function uriTarget($uri)
	{
		$data = explode('://', $uri, 2);
		return count($data) == 2 ? trim($data[1], '\/') : false;
	}

	/**
	 * 根据$uri获取url
	 * @param string $uri
	 * @throws CException
	 * @return string
	 */
	public function createUrl($uri)
	{
		$scheme = self::uriScheme($uri);
		if (!$scheme) {
			if (substr($uri, 0, 1) == '/')
				return $uri;
			else
				return Yii::app()->getBaseUrl(true) . '/' . str_replace('%2F', '/', rawurlencode($path));;
		} elseif ($scheme == 'http' || $scheme == 'https') {
			return $uri;
		} else {
			$schemeDirectoryMap = $this->schemeDirectoryMap();
			if (!isset($schemeDirectoryMap[$scheme]))
				throw new CException(sprintf('%s 协议没有映射路径', $scheme));
			$directoryPath = $schemeDirectoryMap[$scheme];
			return Yii::app()->getBaseUrl(true) . '/' . $directoryPath . '/' . self::uriTarget($uri);
		}
	}

	/**
	 * 获取文件url
	 * @return string
	 */
	public function getUrl()
	{
		return $this->createUrl($this->uri);
	}

	/**
	 * 获取文件物理路径
	 * @return string
	 */
	public function getPath()
	{
		return realpath(self::localPath($this->uri));
	}

	/**
	 * 转换成数组
	 * @return array
	 */
	public function toArray()
	{
		$data = array(
			'id' => $this->id,
			'name' => $this->getName(),
			'ext' => $this->getExt(),
			'filename' => $this->filename,
			'url' => $this->getUrl(),
			'icon' => $this->getIconUrl(),
			'size' => $this->getSize(),
			'datetime' => Yii::app()->format->datetime($this->create_time),
		);

		foreach ($this->meta as $key => $value) {
			if ($key == 'sizes') {
				foreach ($value as $name => $size) {
					$data['sizes'][$name]['width'] = $size['width'];
					$data['sizes'][$name]['height'] = $size['height'];
					$data['sizes'][$name]['url'] = $name === File::IMAGE_ORIGIN ? $data['url'] : $this->createUrl($size['uri']);
				}
			}
		}

		return $data;
	}

	/**
	 * 协议目录映射
	 * @return array
	 */
	public static function schemeDirectoryMap()
	{
		return array(
			'public' => Setting::get('system', 'file_public_path', 'uploads'),
		);
	}

	/**
	 * 根据$uri获取协议下的目录路径
	 * @param string $uri
	 * @return boolean|string
	 */
	public static function directoryPath($uri)
	{
		$scheme = self::uriScheme($uri);
		$schemeDirectoryMap = self::schemeDirectoryMap();
		return isset($schemeDirectoryMap[$scheme]) ? $schemeDirectoryMap[$scheme] : false;
	}

	/**
	 * 根据$uri转换本地路径
	 * @param string $uri
	 * @return boolean|string
	 */
	public static function localPath($uri)
	{
		static $cache = array();

		$directoryPath = self::directoryPath($uri);
		if ($directoryPath === false)
			return false;

		if (!isset($cache[$directoryPath])) {
			$directory = realpath($directoryPath);
			$cache[$directoryPath] = $directory;
		}

		if ($cache[$directoryPath] === false)
			return false;

		$path = $cache[$directoryPath] . '/' . self::uriTarget($uri);
		return $path;
	}

	/**
	 * 解析目录
	 * @param string $directory
	 * @return boolean
	 */
	public static function prepareDirectory($directory)
	{
		$directory = rtrim($directory, '/\\');
		if (!is_dir($directory))
		{
			if (self::mkdir($directory, array(), true))
				return true;
			else
				return false;
		}
		return true;
	}

	/**
	 * 生成目标文件夹
	 * @param string $dst
	 * @param array $options
	 * @param boolean $recursive 是否递归生成文件夹
	 * @return boolean
	 */
	public static function mkdir($dst,array $options,$recursive)
	{
		$prevDir=dirname($dst);
		if($recursive && !is_dir($dst) && !is_dir($prevDir))
			self::mkdir(dirname($dst),$options,true);

		$mode=isset($options['newDirMode']) ? $options['newDirMode'] : 0777;
		$res=mkdir($dst, $mode);
		@chmod($dst,$mode);
		return $res;
	}

	/**
	 * 获取默认的允许上传的文件后缀
	 * @return string
	 */
	public static function fileTypes()
	{
		return Setting::get('system', 'file_types', 'gif,jpg,jpeg,doc,pdf,zip,rar,txt,chm,flv');
	}

	/**
	 * 获取默认的上传文件的最大大小
	 * @return integer
	 */
	public static function fileMaxSize()
	{
		$maxSize = ((int) ($maxUp = @ini_get('upload_max_filesize') ) < (int) ( $maxPost = @ini_get('post_max_size') ) ) ? $maxUp : $maxPost;
		return min(self::sizeToBytes($maxSize), self::sizeToBytes(Setting::get('system', 'file_max_size', '2m')));
	}

	/**
	 * 把大小转换成字节
	 * @param string $sizeStr
	 * @return integer
	 */
	public static function sizeToBytes($sizeStr)
	{
		// get the latest character
		switch (strtolower(substr($sizeStr, -1))) {
			case 'm': return (int)$sizeStr * 1048576; // 1024 * 1024
			case 'k': return (int)$sizeStr * 1024; // 1024
			case 'g': return (int)$sizeStr * 1073741824; // 1024 * 1024 * 1024
			default: return (int)$sizeStr; // do nothing
		}
	}

	/**
	 * 从缓存中获取文件
	 * @param array $ids
	 * @return File|array|NULL  如果$ids为数组则返回数组，不是则返回File对象（如果存在)，否则NULL
	 */
	public static function findFromCache($ids)
	{
		$models = array();
		$uncached = array();

		foreach ((array) $ids as $id) {
			$cacheKey = self::getCacheKey($id);
			if (($model = Yii::app()->getCache()->get($cacheKey)) !== false) {
				$models[] = $model;
			} else {
				$uncached[] = $id;
			}
		}
		if ($uncached) {
			foreach (self::model()->findAllByPk($uncached) as $model) {
				$cacheKey = self::getCacheKey($model->id);
				Yii::app()->getCache()->add($cacheKey, $model, Setting::get('system', '_file_cache_expire', 3600));
				$models[] = $model;
			}
		}

		return is_array($ids) ? $models : (isset($models[0]) ? $models[0] : null);
	}

	/**
	 * 批量更新
	 * @param array $columns
	 * @param mixed $condition
	 * @param array $params
	 */
	public function bulkUpdate($columns, $condition='', $params=array())
	{
		$connection = $this->getDbConnection();
		$offset = 0;
		$limit = 500;
		while (
			$ids = $connection->createCommand()
				->select('id')
				->from($this->tableName())
				->where($condition, $params)
				->limit($limit, $offset)
				->queryColumn()
		) {
			$offset += $limit;
			foreach ($ids as $id) {
				$this->id = $id;
				$this->deleteCache();
			}
		}
		$connection->createCommand()->update($this->tableName(), $columns, $condition, $params);
	}

	/**
	 * 批量删除
	 * @param mixed $condition
	 * @param array $params
	 */
	public function bulkDelete($condition='', $params=array())
	{
		$connection = $this->getDbConnection();
		$offset = 0;
		$limit = 50;
		while (
			$rows = $connection->createCommand()
				->select('id, uri, meta')
				->from($this->tableName())
				->where($condition, $params)
				->limit($limit, $offset)
				->queryAll()
		) {
			$offset += $limit;
			$ids = array();
			foreach ($rows as $row) {
				$ids[] = $row['id'];
				$this->id = $row['id'];
				$this->uri = $row['uri'];
				$this->meta = unserialize($row['meta']);
				$this->deleteCache();
				$this->deleteFile();
			}
			$connection->createCommand()->delete('{{file_usage}}', array('in', 'file_id', $ids));
		}
		$this->unsetAttributes(); //释放属性
		$connection->createCommand()->delete($this->tableName(), $condition, $params);
	}

	/**
	 * 删除所有状态为 self::STATUS_TEMPORARY 的文件
	 * @return integer 已删除的文件数目
	 */
	public static function clean()
	{
		$count = 0;
		foreach (self::model()->findAll('status=:status', array(':status'=>self::STATUS_TEMPORARY)) as $model) {
			if ($model->delete())
				$count++;
		}
		return $count;
	}

	/**
	 * 获取文件缓存key
	 * @param integer $id 文件ID
	 * @return string
	 */
	protected static function getCacheKey($id)
	{
		return "file_{$id}";
	}

	/**
	* 删除缓存
	*/
	protected function deleteCache()
	{
		Yii::app()->getCache()->delete(self::getCacheKey($this->id));
		Yii::app()->getCache()->delete("file_{$this->id}_download_count");
	}

	/**
	 * 删除文件
	 */
	protected function deleteFile()
	{
		$sizes = isset($this->meta['sizes']) ? $this->meta['sizes'] : array();
		foreach ($sizes as $name => $meta) {
			if ($name == File::IMAGE_ORIGIN) {
				continue;
			}
			if (isset($meta['uri']) && ($filePath = realpath(self::localPath($meta['uri'])))) {
				if (!@unlink($filePath))
					Yii::log(sprintf('删除文件  %s 失败', $filePath));
			}
		}

		$filePath = $this->getPath();
		if ($filePath) {
			if (!@unlink($filePath))
				Yii::log(sprintf('删除文件  %s 失败', $filePath));
		}
	}

	/**
	 * 查找之后
	 * @see CActiveRecord::afterFind()
	 */
	protected function afterFind()
	{
		parent::afterFind();
		$this->meta = unserialize($this->meta);
	}

	/**
	 * 保存之前
	 * @see CActiveRecord::beforeSave()
	 */
	protected function beforeSave()
	{
		if (parent::beforeSave()) {
			$this->meta = serialize($this->meta);
			return true;
		} else
			return false;
	}

	/**
	 * 保存之后
	 * @see CActiveRecord::afterSave()
	 */
	protected function afterSave()
	{
		parent::afterSave();
		if (!is_array($this->meta))
			$this->meta = unserialize($this->meta);
		Yii::app()->getCache()->set(self::getCacheKey($this->id), $this);
	}

	/**
	 * 删除之后
	 * @see CActiveRecord::afterDelete()
	 */
	protected function afterDelete()
	{
		parent::afterDelete();
		$this->deleteFile();
		$this->deleteCache();
	}
}