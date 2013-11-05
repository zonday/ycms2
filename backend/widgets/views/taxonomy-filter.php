<?php foreach ($data as $row):
$taxonomy = $row['taxonomy']->slug;
?>
<div class="control-group ">
<?php echo CHtml::activeLabel($model, "taxonomy[{$taxonomy}]", array('label'=>$row['params']['label'], 'class'=>'control-label')); ?>
<div class="controls">
<?php
$htmlOptions['class'] = 'span2';
if ($row['params']['multiple'])
	$htmlOptions['multiple']=true;

if ($row['params']['multiple']) {
	$htmlOptions = array('separator'=>'');
	$htmlOptions['checkAll']= '全部';
	$htmlOptions['template']='<label class="checkbox inline">{input} {label}</label>';
	echo CHtml::activeCheckBoxList($model, "taxonomy[{$taxonomy}]", $row['list'], $htmlOptions);
} else {
	$htmlOptions['empty']='';
	echo CHtml::activeDropDownList($model, "taxonomy[{$taxonomy}]", $row['list'], $htmlOptions);
}
?>
</div>
</div>

<?php endforeach; ?>