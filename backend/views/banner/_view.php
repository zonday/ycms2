<li>
	<div class="thumbnail">
		<?php echo $data->imageFile->getImage(File::IMAGE_ORIGIN, array('style'=>'max-width: 800px')); ?>
		<div class="caption">
			<h3><?php echo CHtml::encode($data->name); ?></h3>
			<?php echo CHtml::link('查看', array('view', 'id'=>$data->id), array('class'=>'btn')); ?>
			<?php echo CHtml::link('更新', array('update', 'id'=>$data->id), array('class'=>'btn')); ?>
		</div>
	</div>
</li>