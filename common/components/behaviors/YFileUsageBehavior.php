<?php
/**
 * YFileUsageBehavior class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YFileUsageBehavior 文件使用上传行为
 *
 * @author Yang <css3@qq.com>
 * @package common.components.behaviors
 */
class YFileUsageBehavior extends CActiveRecordBehavior
{
	/**
	 * 对象文件字段
	 * @var array
	 */
	public $fields = array();

	/**
	 * 字段对应的文件模型列表 一个字段对应多个文件
	 * @var array
	 */
	private $_files = array();

	private $_oldFileIds = array();

	/**
	 * 获取图片文件 根据模型中的image属性
	 * @return File|null
	 */
	public function getImageFile()
	{
		if ($fileId = current($this->getFileIdsByField('image')))
			return File::findFromCache($fileId);
	}

	/**
	 * 获取图片文件列表 根据模型中的image属性
	 * @return array
	 */
	public function getImageFiles()
	{
		return $this->getFilesByField('image');
	}

	/**
	 * 获取附件列表 根据模型中的attachment属性
	 * @return array
	 */
	public function getAttachmentFiles()
	{
		return $this->getFilesByField('attachment');
	}

	/**
	 * 获取附件文件 根据模型中的attachment属性
	 * @return File|null
	 */
	public function getAttachmentFile()
	{
		if ($fileId = current($this->getFileIdsByField('attachment')))
			return File::findFromCache($fileId);
	}

	/**
	 * 获取上传文件字段列表（已预处理过）
	 * @param array $fields
	 */
	public function getUploadFields()
	{
		static $cache = array();
		$owner = $this->getOwner();

		$className = get_class($owner);
		if (!isset($cache[$className])) {
			$maxSize = File::fileMaxSize();
			$rule = array(
				'types' => File::fileTypes(),
				'maxSize' => $maxSize,
				'minSize' => 1024,
				'tooLarge' => sprintf('{file}太大了，必须小于%s', Yii::app()->format->size($maxSize)),
				'tooSmall' => sprintf('{file}太小了，必须大于%s', Yii::app()->format->size(1024)),
			);

			$fields = array();

			foreach ((array) $this->fields as $field => $params) {
				$params = (array) $params;

				if (empty($params['many'])) {
					$params['many'] = 1;
				} elseif (is_numeric($params['many'])) {
					$params['many'] = abs(intval($params['many']));
				} else {
					$params['many'] = true;
				}

				if (empty($params['type']) || !in_array($params['type'], array('file', 'image'))) {
					$params['type'] = 'file';
				}

				if ($params['many'] === 1) {
					$params['sort'] = false;
				} else {
					$params['sort'] = !empty($params['sort']);
				}

				if (!isset($params['preview'])) {
					if ($params['many'] !== 1 && $params['type'] === 'image') {
						$params['preview'] = true;
					} else {
						$params['preview'] = false;
					}
				}

				$params['isAttachment'] = !empty($params['isAttachment']);

				if (!isset($params['rule'])) {
					$params['rule'] = $rule;
				} else {
					if (!isset($params['rule']['types']))
						$params['rule']['types'] = File::fileTypes();

					if (!isset($params['rule']['maxSize']))
						$params['rule']['maxSize'] = File::fileMaxSize();

					if (!isset($params['rule']['minSize']))
						$params['rule']['minSize'] = 1024;

					$params['rule']['tooLarge'] = sprintf('{file}太大了，必须小于%s', Yii::app()->format->size($params['rule']['maxSize']));
					$params['rule']['tooSmall'] = sprintf('{file}太小了，必须大于%s', Yii::app()->format->size($params['rule']['minSize']));
				}

				if ($params['type'] === 'image') {
					$params['rule']['types'] = Setting::get('system', '_file_image_types', 'jpg,jpeg,gif,png');
				}

				$params['isSelf'] = $owner->hasAttribute($field); //是否是模型自带字段（表中有这个字段）
				$fields[$field] = $params;
			}
			$cache[$className] = $fields;
		}
		return $cache[$className];
	}

	/**
	 * 获取对象字段上传允许的文件类型
	 * @param string $field
	 */
	public function getFileTypesByField($field)
	{
		$fields = $this->getUploadFields();
		if (isset($fields[$field]))
			return $fields[$field]['rule']['types'];
	}

	/**
	 * 获取对象字段上传允许的最大文件大小
	 * @param string $field
	 */
	public function getFileMaxSizeByField($field)
	{
		$fields = $this->getUploadFields();
		if (isset($fields[$field]))
			return Yii::app()->format->size($fields[$field]['rule']['maxSize']);
	}

	/**
	 * 上传文件到对象字段中去
	 * @param string $field 字段名
	 * @throws CException
	 * @return boolean|File
	 */
	public function upload($field)
	{
		$fields = $this->getUploadFields();
		if (!isset($fields[$field]))
			throw new CException($field . '字段不是文件字段');

		$owner = $this->getOwner();

		$validator = CValidator::createValidator('file', $owner, $field, $fields[$field]['rule'] );
		$validator->validate($owner, $field);

		if ($owner->hasErrors($field))
			return false;

		if (!$owner->$field instanceof CUploadedFile) {
			$uploadedFile = CUploadedFile::getInstance($owner, $field);
		} else {
			$uploadedFile = $owner->$field;
		}

		if (isset($fields[$field]['location']))
			$location = $fields[$field]['location'];
		else
			$location = Setting::get('system', 'file_upload_location', 'public://');

		if (is_callable($location))
			$location = call_user_func($location);

		$directory = File::localPath($location);
		if (!$directory || !File::prepareDirectory($directory))
			throw new CException('上传目录不能被创建');

		$filename = uniqid() . '.' . strtolower($uploadedFile->getExtensionName());
		if (substr($location, -1) == '/')
			$uri = $location . $filename;
		else
			$uri = $location . '/' . $filename;

		$filepath = File::localPath($uri);
		if ($uploadedFile->saveAs($filepath)) {
			$data = array(
				'user_id' => Yii::app()->getUser()->getId(),
				'bundle' => get_class($owner),
				'filename' => $uploadedFile->getName(),
				'uri' => $uri,
				'filemime' => $uploadedFile->getType(),
				'filesize' => $uploadedFile->getSize(),
				'status' => $owner instanceof File ? 1 : 0,
			);

			$file = new File();
			$file->setAttributes($data, false);
			$resize = isset($fields[$field]['resize']) ? $fields[$field]['resize'] : true;

			if ($fields[$field]['isAttachment']) {
				$resize = false;
			}

			if ($resize !== false) {
				$defaultSizes = File::imageSizes();
				if (!is_array($resize))
					$resize = array(File::IMAGE_THUMBNAIL => $defaultSizes[File::IMAGE_THUMBNAIL]);
				else
					$resize = array(File::IMAGE_THUMBNAIL => $defaultSizes[File::IMAGE_THUMBNAIL]) + $resize;

				$file->attachBehavior('imageResize', array(
					'class'=>'YImageResizeBehavior',
					'sizes'=>$resize,
				));
			} elseif ($file->isImage()) {
				$imageSize = getimagesize($filepath);
				$file->meta['sizes'][File::IMAGE_ORIGIN] = array(
					'width' => $imageSize[0],
					'height' => $imageSize[1],
					'uri' => $uri,
				);
			}

			if ($file->save(false)) {
				return $file;
			} else {
				unlink($filepath);
				return false;
			}
		}
		return false;
	}

	/**
	 * 根据对象字段获取该字段下的文件列表
	 * @param string $field
	 * @throws CException
	 * @return array
	 */
	public function getFilesByField($field)
	{
		$fields = $this->getUploadFields();
		if (!isset($fields[$field]))
			throw new CException($field . '字段不是文件字段');

		if (!isset($this->_files[$field])) {
			$fileIds = $this->getFileIdsByField($field);
			$this->_files[$field] = File::findFromCache($fileIds);
		}

		return $this->_files[$field];
	}

	/**
	 * 解析ids
	 * @param mixed $field
	 * @return array
	 */
	public function parseIds($ids)
	{
		if ($ids && !is_array($ids)) {
			$ids = preg_split('/\s*,\s*/', trim($ids), -1, PREG_SPLIT_NO_EMPTY);
		} else {
			$ids = array();
		}

		return array_unique(array_map('intval', $ids));
	}

	/**
	 * 根据对象字段获取该字段下的文件ids
	 * @param string $field 对象字段
	 * @param boolean $real 真实的已保存的
	 * @throws CException
	 * @return array
	 */
	public function getFileIdsByField($field, $real=false)
	{
		$fields = $this->getUploadFields();
		if (!isset($fields[$field]))
			throw new CException($field . '字段不是文件字段');

		$owner = $this->getOwner();

		if (isset($owner->$field) && !$real) {
			return $this->parseIds($owner->$field);
		}

		if ($owner->isNewRecord)
			return array();

		if ($fields[$field]['isSelf'])
			return $this->_oldFileIds[$field];

		$cacheKey = $this->getfileIdsCacheKey($owner->id, $field);
		if (($fieldIds = Yii::app()->getCache()->get($cacheKey)) === false) {
			$connection = $this->getOwner()->getDBConnection();
			$command = $connection->createCommand()
				->select('file_id')
				->from('{{file_usage}}')
				->where('object_id=:object_id AND bundle=:bundle AND field=:field', array(
					':object_id' => $owner->getPrimaryKey(),
					':bundle' => get_class($owner),
					':field' => $field));
			if ($fields[$field]['sort']) {
				$command->order('weight');
			}
			if ($fields[$field]['many'] !== true) {
				$command->limit($fields[$field]['many']);
			}
			$fieldIds = $command->queryColumn();
			Yii::app()->getCache()->add($cacheKey, $fieldIds);
		}

		return $fieldIds;
	}

	/**
	 * 获取对象文件ids缓存Key
	 * @param integer $id 对象id
	 * @param string $field 对象字段名
	 * @return string
	 */
	public function getfileIdsCacheKey($id, $field)
	{
		$owner = $this->getOwner();
		$className = get_class($owner);
		return "{$className}_{$id}_{$field}_filesIds";
	}

	/**
	 * 更新对象文件ids缓存
	 * @param integer $id 对象id
	 * @param string $field 对象字段名
	 * @param array $fileIds 文件ids
	 */
	public function updatefileIdsCache($id, $field, $fileIds)
	{
		$cacheKey = $this->getfileIdsCacheKey($id, $field);
		Yii::app()->getCache()->set($cacheKey, $fileIds);
	}

	/**
	 * 删除对象文件ids缓存
	 * @param integer $id 对象id
	 * @param string $field 对象字段名
	 */
	public function deletefileIdsCache($id, $field)
	{
		$cacheKey = $this->getfileIdsCacheKey($id, $field);
		Yii::app()->getCache()->delete($cacheKey);
	}

	/**
	 * 对象查找之后
	 */
	public function _afterFind($event)
	{
		$fields = $this->getUploadFields();
		$owner = $this->getOwner();
		foreach ($fields as $field => $params) {
			if ($params['isSelf']) {
				$this->_oldFileIds[$field] = $this->parseIds($owner->$field);
			}
		}
	}

	/**
	 * 对象保存之后
	 * @param CModelEvent $event
	 */
	public function afterSave($event)
	{
		$owner = $this->getOwner();
		$fields = $this->getUploadFields();

		//File不保存
		if ($owner instanceof File)
			return;

		foreach ($fields as $field => $params) {
			if (!isset($owner->$field))
				continue;

			$newFileIds = $this->parseIds($owner->$field);

			if ($params['many'] !== true)
				$newFileIds =  array_slice($newFileIds, 0, $params['many']);

			$types = explode(',', $params['rule']['types']);
			$validFileIds = array();
			foreach (File::findFromCache($newFileIds) as $model) {
				if (in_array($model->getExt(), $types)) {
					$validFileIds[] = $model->id;
				}
			}

			$oldFileIds = $delFileIds = $addFileIds = array();
			if ($owner->isNewRecord) {
				$addFileIds = $newFileIds;
			} else {
				if ($params['isSelf']) {
					$oldFileIds = $this->_oldFileIds[$field];
				} else {
					$oldFileIds = $this->getFileIdsByField($field, true);
				}
				$delFileIds = array_diff($oldFileIds, $newFileIds);
				$addFileIds = array_diff($newFileIds, $oldFileIds);
			}

			$connection = $owner->getDbConnection();
			if (!$transaction = $connection->getCurrentTransaction())
				$transaction = $connection->beginTransaction();

			$objectId = $owner->getPrimaryKey();
			try {
				if ($delFileIds !== array()) {
					if (!$params['isSelf']) {
						$connection->createCommand()->delete('{{file_usage}}', array('and',
								'object_id=:object_id AND bundle=:bundle AND field=:field',
								array('in', 'file_id', $delFileIds),
						), array(':object_id'=>$owner->id, ':bundle'=>get_class($owner),':field'=>$field));
					}

					$resize = isset($params['resize']) ? $params['resize'] : true;

					if (!empty($params['isAttachment'])) {
						$resize = false;
					}

					foreach (File::model()->findAllByPk($delFileIds) as $file) {
						if ($resize) {
							$file->attachBehavior('imageResize', array(
								'class'=>'YImageResizeBehavior',
							));
						}
						$file->delete();
					}
				}

				if ($addFileIds !== array()) {
					if (!$params['isSelf']) {
						$qField = $connection->quoteValue($field);
						$qBundle = $connection->quoteValue(get_class($owner));

						foreach($addFileIds as $id) {
							$values[] = "($objectId, $id, $qBundle, $qField)";
						}
						$command = $connection->createCommand("INSERT INTO {{file_usage}} (object_id, file_id, bundle, field) VALUES " . implode(',', $values));
						$command->execute();
					}
					File::model()->updateByPk($addFileIds, array('status'=>File::STATUS_PERMANENT));
				}

				if (!$params['isSelf']) {
					if ($params['sort'] && $newFileIds !== $oldFileIds) {
						foreach ($newFileIds as $i => $id) {
							$connection->createCommand()->update('{{file_usage}}', array('weight'=>$i), 'file_id=:file_id', array(
								':file_id' => $id,
							));
						}
					}
				}
				$transaction->commit();
			} catch (CDbException $e) {
				Yii::log('保存对象文件使用信息失败。错误信息：' . $e->getMessage(), CLogger::LEVEL_ERROR);
				$transaction->rollBack();
				return;
			}

			if (!$params['isSelf']) {
				$this->updatefileIdsCache($objectId, $field, $newFileIds);
			}
		}
	}

	/**
	 * 对象删除之后
	 * @param CModelEvent $event
	 */
	public function afterDelete($event)
	{
		$owner = $this->getOwner();
		$fields = $this->getUploadFields();

		if ($owner instanceof File)
			return;

		$objectId = $owner->getPrimaryKey();
		$bundle = get_class($owner);
		$connection = $owner->getDbConnection();

		foreach ($fields as $field => $params) {
			$fileIds = $this->getFileIdsByField($field);
			if (!$params['isSelf']) {
				$connection->createCommand()->delete('{{file_usage}}', array('and',
						'object_id=:object_id AND bundle=:bundle AND field=:field',
						array('in', 'file_id', $fileIds),
				), array(':object_id'=>$objectId, ':bundle'=>$bundle, ':field'=>$field));
			}

			foreach (File::model()->findAllByPk($fileIds) as $file) {
				$file->delete();
			}

			if (!$params['isSelf']) {
				$this->deletefileIdsCache($objectId, $field);
			}
		}
	}
}
