<div class="media-container media-file-container clearfix">
	<?php foreach ($models as $model):?>
	<div class="media-item">
		<span class="media-icon"><?php echo $model->getIcon(); ?></span>
		<span class="media-name"><?php echo CHtml::link(CHtml::encode($model->getName()), $model->getUrl()); ?></span>
		<span class="media-ext"><strong><?php echo strtoupper($model->getExt()); ?></strong></span>
		<span class="media-datetime label"><?php echo Yii::app()->format->datetime($model->create_time); ?></span>
		<span class="media-size label"><?php echo Yii::app()->format->size($model->filesize); ?></span>
		<!-- <span class="badge media-download-count" title="下载次数" data-toggle="tooltip">
								<?php echo $model->getDownloadCount() ?>  </span> -->
	</div>
	<?php endforeach; ?>
</div>
