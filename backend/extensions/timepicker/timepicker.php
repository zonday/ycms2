<?php
class timepicker extends CWidget {

	public $assets = '';
	public $options = array();
	public $skin = 'default';

	public $form;
	public $model;
	public $name;
	public $language;
	public $select = 'datetime'; # also avail 'time' and 'date'

	public function init() {
		$this->assets = Yii::app()->assetManager->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');

		Yii::app()->clientScript
		->registerCoreScript( 'jquery' )
		->registerCoreScript( 'jquery.ui' )

		->registerScriptFile( $this->assets.'/js/jquery.ui.timepicker.js' )
		->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css')
		//->registerCssFile( $this->assets.'/css/ui.theme.smoothness/jquery-ui-1.7.3.css' )
		->registerCssFile( $this->assets.'/css/timepicker.css' );

		//language support
		if (empty($this->language))
			$this->language = Yii::app()->language;

		if(!empty($this->language)){
			$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';
			$langFile = '/js/jquery.ui.timepicker.'.$this->language.'.js';

			if (is_file($path.DIRECTORY_SEPARATOR.$langFile))
				Yii::app()->clientScript->registerScriptFile($this->assets.$langFile);
		}

		$default = array(
			'dateFormat'=>'yy-mm-dd',
			'timeFormat'=>'hh:mm:ss',
			//'showOn'=>'button',
			'showSecond'=>false,
			'changeMonth'=>false,
			'changeYear'=>false,
			'value'=>'',
			'tabularLevel'=>null,
		);

		$this->options = array_merge($default, $this->options);

		$options=empty($this->options) ? '' : CJavaScript::encode($this->options);

		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id,"
			jQuery('#{$this->id}').".$this->select."picker($options);
		");

		parent::init();
	}

	public function run(){
		if ($this->form && $this->form->type='horizontal'):
		?>
		<div class="control-group">
			<?php echo $this->form->labelEx($this->model,$this->name, array('class'=>'control-label')); ?>
			<div class="controls">
			<input type="text" class="timepicker" id="<?php echo $this->id; ?>" value="<?php echo $this->model->{$this->name}?$this->model->{$this->name}:$this->options['value']; ?>" name="<?php echo get_class($this->model).(!empty($this->options['tabularLevel'])?$this->options['tabularLevel']:'').'['.$this->name.']'; ?>" />
			<?php echo $this->form->error($this->model,'create_time', array('class'=>'help-block')); ?>
			</div>
		</div>
		<?php
		else:
		?>
			<?php echo $this->form->labelEx($this->model,$this->name); ?>
			<input type="text" class="timepicker" id="<?php echo $this->id; ?>" value="<?php echo $this->model->{$this->name}?$this->model->{$this->name}:$this->options['value']; ?>" name="<?php echo get_class($this->model).(!empty($this->options['tabularLevel'])?$this->options['tabularLevel']:'').'['.$this->name.']'; ?>" />
			<?php echo $this->form->error($this->model,'create_time', array('class'=>'help-block')); ?>
		<?php
		endif;
	}
}
?>