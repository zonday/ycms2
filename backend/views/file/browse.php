<?php
/* @var $this FileController */
/* @var $model File */
$this->pageTitle = '文件浏览';
?>

<div class="container file-browse">
<h2>文件浏览</h2>
<?php $this->widget('bootstrap.widgets.TbGridView', array(
	'id'=>'file-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'icon',
			'header'=>'',
			'value'=>'$data->getIcon()',
			'filter'=>false,
			'type'=>'raw',
			'htmlOptions'=>array('class'=>'file-icon-column'),
		),
		array(
			'name'=>'filename',
			'header'=>$model->getAttributeLabel('name'),
			'type'=>'raw',
			'value'=>'"<span>" . CHtml::encode($data->name) . "</span><br /><strong>" . strtoupper($data->getExt()). "</strong>"',
		),
		array(
			'header'=>'尺寸',
			'type'=>'raw',
			'value'=>'CHtml::radioButtonList("size", "", $data->getImageSizeList(), array("baseID"=>"size_" . $data->id))',
			'visible'=>'$data->isImage()',
		),
		array(
			'name'=>'size',
			'type'=>'size',
			'headerHtmlOptions'=>array('class'=>'size-column'),
			'filter'=>false,
		),
		array(
			'name'=>'create_time',
			'class'=>'YDatetimeColumn',
			'type'=>'date',
			'filter'=>YUtil::timeFilterList(),
		),
		array(
			'type'=>'raw',
			'value'=>'CHtml::link("选择", "#", array("class"=>"btn post-select", "data-url"=>$data->getUrl()))',
		),
	),
)); ?>
</div>
<?php
if (isset($_GET['CKEditorFuncNum'])) {
$js = <<<EOT
var funcNum = {$_GET['CKEditorFuncNum']};
$('.post-select').click(function() {
	var url;
	var checked = $('input[name=size]:checked');
	if (checked.length > 0)
		fileUrl = checked.val();
	else
		fileUrl = $(this).data('url');

	if (window.opener.CKEDITOR)
		window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl);

	window.close();
});
EOT;
} elseif (isset($_GET['input'])) {
$input = $_GET['input'];
$js = <<<EOT
$('.post-select').click(function() {
	var url;
	var checked = $('input[name=size]:checked');
	if (checked.length > 0)
		fileUrl = checked.val();
	else
		fileUrl = $(this).data('url');

	if (window.opener)
		window.opener.jQuery("#{$input}").val(fileUrl);

	window.close();
});
EOT;
}
Yii::app()->getClientScript()->registerScript(__CLASS__ .'#'. $this->getId(), $js);
?>
