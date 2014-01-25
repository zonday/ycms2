<?php
/**
 * YUploadWidget Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YUploadWidget
 *
 * @see TbBulkActions
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YUploadWidget extends CInputWidget
{
	/**
	 * @var string 上传标签
	 */
	public $uploadLabel;

	/**
	 * @var CActiveForm  表单
	 */
	public $form;

	/**
	 * @var boolean 提示
	 */
	public $hint = true;

	/**
	 * 展示的图片尺寸名
	 * @var string
	 */
	public $showImageSizeName = File::IMAGE_POST_THUMBNAIL;

	/**
	 * 是否可以预览
	 * @var boolean
	 */
	public $preview = false;

	/**
	 * @var mixed 文件参数
	 */
	private $_fileParams;

	/**
	 * @var integer 文件上传数目
	 */
	private $_fileCount = 0;

	/**
	 * @var string 脚本资源路径
	 */
	private static $_assets;

	/**
	 * @var integer wiget 计数
	 */
	private static $_count = 0;

	/**
	 * 初始化
	 * @see CWidget::init()
	 */
	public function init()
	{
		$fileFields = $this->model->getUploadFields();

		if (!isset($fileFields[$this->attribute])) {
			throw new CException(sprintf('%s 属性没有在模型上传字段中找到', $this->attribute));
		}

		self::$_count++;

		$cs = Yii::app()->getClientScript();
		$baseDir = dirname(__FILE__);

		$this->_fileParams = $fileFields[$this->attribute];
		$cs->registerCoreScript('jquery.ui');

		if (!isset($this->uploadLabel)) {
			$this->uploadLabel = '选择' . ($this->_fileParams['type'] === 'image' ? '图片' : '文件');
		}

		if (!empty($this->_fileParams['preview']) || $this->preview) {
			$popupAssets = Yii::app()->getAssetManager()->publish($baseDir . '/../assets/magnific_popup');
			$cs->registerScriptFile($popupAssets . '/jquery.magnific-popup.js');
			$cs->registerCssFile($popupAssets . '/magnific-popup.css');
		}

		if (!isset(self::$_assets))
			self::$_assets = Yii::app()->getAssetManager()->publish($baseDir . '/../assets/plupload');
	}

	/**
	 * 运行
	 * @see CWidget::run()
	 */
	public function run()
	{
		$model = $this->model;
		$form = $this->form;
		$attribute = $this->attribute;
		$hiddenField = $form->hiddenField($model, $attribute, array('value'=>implode(',', $model->getFileIdsByField($attribute))));

		if (isset($form->type) && $form->type == 'horizontal') {
			echo '<div class="control-group' . ($model->hasErrors($attribute) ? ' error' : '') . '">';
			echo $form->labelEx($model, $attribute, array('class'=>'control-label'));
			echo '<div class="controls">';
			$this->displayFiles();
			$this->displayPlupload();
			echo $hiddenField;
			echo $form->error($model, $attribute);
			echo '</div></div>';
		} else {
			echo $form->labelEx($model, $attribute);
			$this->displayFiles();
			$this->displayPlupload();
			echo $hiddenField;
			echo $form->error($model, $attribute);
		}
	}

	/**
	 * 显示提示
	 */
	protected function displayHint()
	{
		$many = $this->_fileParams['many'];
		$type = $this->_fileParams['type'];
		$typeName = $type === 'image' ? '图片' : '文件';

		if ($this->hint === true) {
			$hint = '';
			if ($many !== true && $many !== 1) {
				$hint .= "最多可以上传{$many}个{$typeName}.";
			} elseif ($many === true) {
				$hint .= "可以上传多个{$typeName}.";
			}
			$hint .= " 允许上传的{$typeName}类型为：" . $this->model->getFileTypesByField($this->attribute) . '.';
			$hint .= " {$typeName}的最大大小为：" . $this->model->getFileMaxSizeByField($this->attribute). '.';
		}

		if ($this->hint !== false) {
			echo '<div class="help-block">' . $hint . '</div>';
		}
	}

	/**
	 * 显示文件
	 */
	protected function displayFiles()
	{
		$model = $this->model;
		$attribute = $this->attribute;
		$type = $this->_fileParams['type'];
		$many = $this->_fileParams['many'];
		$total = $many !== 1 ? 'multi' : 'single';
		$class = "media-container media-{$type}-container media-{$type}-{$total} clearfix" ;
		$id = "media-container-" . self::$_count;
		echo CHtml::openTag('div', array('class' => $class, 'id' => $id));

		if ($files = $model->getFilesByField($attribute)) {
			$this->_fileCount = count($files);
			foreach ($files as $file) {
				echo CHtml::openTag('div', array(
					'class' => "media-item",
					'id' => "media-item-{$file->id}",
					'data-id' => $file->id,
				));
				if ($type === 'image') {
					echo $file->getImage($many === 1 ? $this->showImageSizeName : File::IMAGE_THUMBNAIL, array(
						'class' => "thumbnail",
						'width' => false,
						'height' => false,
					));
				} else {
					echo '<span class="media-icon">' . $file->getIcon(array('width'=>60, 'height'=>60)) . '</span>';
					echo '<span class="media-name">' . $file->getName() . '</span>';
					echo '<span class="media-ext">' . strtoupper($file->getExt()) . '</span>';
					echo '<span class="media-datetime">' . Yii::app()->format->datetime($file->create_time) . '</span>';
					echo '<span class="media-size">' . $file->getSize() . '</span>';
					if ($this->_fileParams['isAttachment'] === true) {
						echo '<span class="badge media-download-count" title="下载次数" data-toggle="tooltip">'
								. $file->getDownloadCount() . '</span>';
					}
				}
				echo '<div class="media-item-actions">'
					.'<a href="#" title="编辑" data-id="' . $file->id . '" ref="tooltip" class="media-item-edit"><i class="icon-pencil"></i></a>'
					.'<a href="#" title="删除" data-id="' . $file->id . '" ref="tooltip" class="media-item-delete"><i class="icon-trash"></i></a>'
					. (($this->_fileParams['preview'] || $this->preview) ? '<a href="#" data-image-title="' . $file->getName() . '" data-mfp-src="' . $file->getUrl() . '" title="预览" ref="tooltip" class="media-item-preview"><i class="icon-zoom-in"></i></a>' : '')
					.'</div>';
				echo CHtml::closeTag('div');
			}
		}

		echo CHtml::closeTag('div');
	}

	/**
	 * 显示plupload
	 */
	protected function displayPlupload()
	{
		$type = $this->_fileParams['type'];
		$many = $this->_fileParams['many'];

		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile(self::$_assets . '/js/plupload.js');
		$options = $this->options();
		$pluploadOptions = &$options['plupload'];
		foreach ($pluploadOptions['runtimes'] as $runtime) {
			switch ($runtime) {
				case 'browserplus':
					$cs->registerScriptFile('http://bp.yahooapis.com/2.4.21/browserplus-min.js');
					break;
				case 'flash':
					$pluploadOptions['flash_swf_url'] = self::$_assets . '/js/plupload.flash.swf';
					break;
				case 'silverlight':
					$pluploadOptions['silverlight_xap_url'] = self::$_assets .'/js/plupload.silverlight.xap';
					break;
			}
			$cs->registerScriptFile(self::$_assets . "/js/plupload.{$runtime}.js");
		}
		$pluploadOptions['runtimes'] = implode(',', $pluploadOptions['runtimes']);

		$pluploadHtmlOptions = array('class'=>'plupload-container', 'id'=>'plupload-container-' . self::$_count);
		if ($many !== true && $this->_fileCount >= $many) {
			$pluploadHtmlOptions['style'] = 'display: none';
		}

		echo CHtml::openTag('div', $pluploadHtmlOptions);
		echo CHtml::button($this->uploadLabel, array('id'=>'plupload-button-' . self::$_count, 'class'=>'btn'));
		$this->displayHint();
		echo CHtml::closeTag('div');

		$csrfParams = array();
		$request = Yii::app()->getRequest();
		if ($request->enableCsrfValidation) {
			$csrfParams[$request->csrfTokenName] = $request->getCsrfToken();
		}

		if (self::$_count === 1) {
			echo '<script type="text/javascript">'
				.'window.YH = window.YH || {};'
				.'YH.csrfParams = ' . CJavaScript::encode($csrfParams) . ';'
				.'YH.uploaderInit = {};'
				.'YH.mediaItemEditUrl = "' . Yii::app()->createAbsoluteUrl('file/update', array('id'=>':id')) . '";'
				.'YH.mediaItemDeleteUrl = "' . Yii::app()->createAbsoluteUrl('file/delete', array('id'=>':id')) . '";'
				.'</script>';
		}
		echo "<script type='text/javascript'>YH.uploaderInit[" . self::$_count . "] = " . CJavaScript::encode($options) . "</script>";
		$cs->registerScriptFile(self::$_assets . '/handlers.js', CClientScript::POS_END);
		$cs->registerCssFile(self::$_assets . '/plupload.css');
	}

	/**
	 * 获取脚本选项
	 * @return array
	 */
	protected function options()
	{
		$type = $this->_fileParams['type'];
		$many = $this->_fileParams['many'];

		$fileTypes = $this->model->getFileTypesByField($this->attribute);
		$maxFileSize = strtolower($this->model->getFileMaxSizeByField($this->attribute));

		list($name, $id) = $this->resolveNameID();

		$options = array(
			'sort' => $this->_fileParams['sort'],
			'input_id' => $id,
			'object' => get_class($this->model),
			'object_id' => $this->model->getPrimaryKey(),
			'file_count' => $this->_fileCount,
			'many' => $many,
			'type' => $type,
			'preview' => $this->_fileParams['preview'],
			'plupload' => array(
				'url' => Yii::app()->createAbsoluteUrl('file/upload', array('model'=>get_class($this->model), 'field'=>$this->attribute)),
				'container' => 'plupload-container-' . self::$_count,
				'browse_button' => 'plupload-button-' . self::$_count,
				'drop_element' => 'drag-drop-area-' . self::$_count,
				'runtimes' => array('html5', 'flash', 'silverlight', 'html4'),
				'multipart' => true,
				'urlstream_upload' => true,
				'multi_selection' => $many !== 1 ? true : false,
				'max_file_size' => $maxFileSize,
				'file_data_name' => $name,
				'multiple_queues' => true,
				'multipart_params' => array(
					$name => true,
					'ajax' => 'file-upload',
					'plupload' => true,
				),
				'filters'=>array(
					array('title'=>$type === 'image' ? '图片': '文件', 'extensions'=>$fileTypes),
				),
				'headers'=>array(
					'X_REQUESTED_WITH' => 'XMLHttpRequest',
				)
			),
		);

		$request = Yii::app()->getRequest();
		if ($request->enableCsrfValidation) {
			$options['plupload']['multipart_params'][$request->csrfTokenName] = $request->getCsrfToken();
		}

		return $options;
	}
}
