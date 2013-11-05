<?php
class CkeditorWidget extends CInputWidget
{
	public $form;
	protected $assets;
	protected $options=array(
		'allowedContent'=>false,
		'height'=>500,
	);

	public function init() {
		$baseDir = dirname(__FILE__);
		$this->assets = Yii::app()->getAssetManager()->publish($baseDir.DIRECTORY_SEPARATOR.'ckeditor');
	}

	public function setOptions($options)
	{
		$this->options=CMap::mergeArray($this->options, $options);
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function run() {
		$this->registerClientScript();
		$this->htmlOptions=array_merge(array('cols'=>50, 'rows'=>10), $this->htmlOptions);
		if ($this->hasModel()) {
			if (!isset($this->htmlOptions['id']))
				$this->htmlOptions['id'] = CHtml::activeId($this->model, $this->attribute);

			$textarea = $this->form->textAreaRow($this->model, $this->attribute, $this->htmlOptions);
		} else {
			$textarea = CHtml::textArea($this->name, $this->value, $this->htmlOptions);
		}

		echo $textarea;
	}

	public function registerClientScript()
	{
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile($this->assets.'/ckeditor.js');
		$id = $this->getId();
		if (isset($this->htmlOptions['id'])) {
			$textareaId = $this->htmlOptions['id'];
		} elseif ($this->hasModel()) {
			$textareaId = CHtml::activeId($this->model, $this->attribute);
		} else {
			$textareaId = $this->name;
		}

		if (!isset($this->options['toolbarGroups']))
			$this->options['toolbarGroups'] = $this->getStandardToobar();

		if (!isset($this->options['removButtons']))
			$this->options['removeButtons'] = $this->getRemoveButtons();

		if (!isset($this->options['format_tags']))
			$this->options['format_tags'] = $this->getformat_tags();

		if (!isset($this->options['removeDialogTabs']))
			$this->options['removeDialogTabs'] = $this->getRemoveDialogTabs();

		if (!isset($this->options['font_names']))
			$this->options['font_names'] = $this->getfont_names();

		if (!isset($this->options['extraPlugins']))
			$this->options['extraPlugins'] = $this->extraPlugins();

		if (!isset($this->options['image_previewText']))
			$this->options['image_previewText'] = $this->getimage_previewText();

		$this->options['filebrowserBrowseUrl'] = Yii::app()->createAbsoluteUrl('/file/browse');
		$params=array();
		if (Yii::app()->getRequest()->enableCsrfValidation) {
			$params[Yii::app()->getRequest()->csrfTokenName] = Yii::app()->getRequest()->getCsrfToken();
		}

		$this->options['filebrowserUploadUrl'] = Yii::app()->createAbsoluteUrl('/file/upload', $params);
		$js = "CKEDITOR.replace('$textareaId', " . CJavaScript::encode($this->options) . "); ";
		/*@todo ckf upload csrfValidation */
		/*
		$js .= <<<EOT
CKEDITOR.on('dialogDefinition', function(ev) {
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	if (dialogName == 'image') {
		console.log(dialogDefinition.dialog);
		var uploadTab = dialogDefinition.getContents('Upload');
		var file = uploadTab.get('uploadButton');
	}
});
var editor = CKEDITOR.instances['{$textareaId}'];
editor.on('dialogShow', function(evt) {
	var dialog = evt.data;
	if (dialog.getName() == 'image') {
		var file = dialog.getContentElement('Upload', 'upload').getInputElement();
		//alert('test');
		//file.getParent().appendHtml('<input type="test" value="test" />');
	}
});
EOT;
*/
		$cs->registerScript(__CLASS__ .'_#_'.$id, $js);
	}

	public function getStandardToobar()
	{
		return array(
			array('name'=>'clipboard', 'groups'=>array('clipboard', 'undo')),
			array('name'=>'editing', 'groups'=>array('find', 'selection', 'spellchecker')),
			array('name'=>'links'),
			array('name'=>'insert'),
			//array('name'=>'forms'),

			array('name'=>'document', 'groups'=>array('mode', 'document', 'doctools')),
			array('name'=>'others'),
			'/',
			array('name'=>'basicstyles', 'groups'=>array('basicstyles', 'cleanup')),
			array('name'=>'paragraph', 'groups'=>array('list', 'indent', 'blocks', 'align', 'bidi')),
			array('name'=>'styles'),
			array('name'=>'colors'),
			array('name'=>'tools'),
			array('name'=>'about'),
		);
	}

	public function extraPlugins()
	{
		return 'google_map';
	}

	public function getRemoveButtons()
	{
		return 'Underline,Subscript,Superscript,CreateDiv,Styles';
	}

	public function getformat_tags()
	{
		return 'p;h1;h2;h3;pre';
	}

	public function getRemoveDialogTabs()
	{
		return 'image:advanced;link:advanced';
	}

	public function getFont_names()
	{
		return
		'宋体/宋体;黑体/黑体;仿宋/仿宋_GB2312;楷体/楷体_GB2312;隶书/隶书;幼圆/幼圆;微软雅黑/微软雅黑;'.
				'Arial/Arial, Helvetica, sans-serif;' .
	'Comic Sans MS/Comic Sans MS, cursive;' .
	'Courier New/Courier New, Courier, monospace;' .
	'Georgia/Georgia, serif;' .
	'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' .
	'Tahoma/Tahoma, Geneva, sans-serif;' .
	'Times New Roman/Times New Roman, Times, serif;' .
	'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' .
	'Verdana/Verdana, Geneva, sans-serif';
	}

	public function getimage_previewText()
	{
		return '曾经有一份真挚的爱情摆在我的面前</br>我没有好好珍惜</br> 等到失去时才感到后悔</br>如果老天能够再给我一次机会</br>我回对那个女孩说 </br>我爱你</br>如果非要在这个爱上加个期限的话</br>我希望是 一万年...</br>——————————</br>这只是一段预览文本';
	}
}