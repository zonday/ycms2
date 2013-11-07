<?php foreach ($data as $row):
$taxonomy = $row['taxonomy']->slug;
?>
<div class="control-group ">
<?php echo CHtml::activeLabel($model, "termIds[{$taxonomy}]", array('label'=>$row['params']['label'], 'class'=>'control-label')); ?>
<div class="controls">
<?php
$htmlOptions['class'] = 'span2';
if ($row['params']['many'])
	$htmlOptions['multiple']=true;

if (!empty($htmlOptions['multiple'])) {
	$htmlOptions = array('separator'=>'');
	$htmlOptions['checkAll']= '全部';
	$htmlOptions['template']='<label class="checkbox inline">{input} {label}</label>';
	$htmlOptions['uncheckValue']= null;
	echo CHtml::activeCheckBoxList($model, "termIds[{$taxonomy}]", $row['list'], $htmlOptions);
} else {
	$htmlOptions['empty']='';
	echo CHtml::activeDropDownList($model, "termIds[{$taxonomy}]", $row['list'], $htmlOptions);
}
?>
</div>
</div>

<?php endforeach; ?>