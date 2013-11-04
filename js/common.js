!function($) {
	$(function(){
		var $window = $(window);

		var $formActions = $('.form-actions');
		var formActionsHeight = $formActions.outerHeight();
		setTimeout(function() {
			if ($formActions.length) {
				var height = $formActions.offset()['top'];
				var onChange = function() {
					if (height < $(window).scrollTop() + $(window).height()) {
						$formActions.removeClass('fixed');
					} else {
						$formActions.css('width', $formActions.width());
						$formActions.addClass('fixed');
					}
				};
				$window.scroll(onChange);
				onChange();
			}
		}, 100);

		$('#btn-layout-full').toggle(function(){
			$('body').addClass('layout-full');
			$(this).addClass('active');
			$(this).attr('title', '还原');
		}, function() {
			$('body').removeClass('layout-full');
			$(this).removeClass('active');
			$(this).attr('title', '全屏');
		});

		$('.panel-heading .chevron').toggle(function() {
			$(this).addClass('icon-chevron-down').removeClass('icon-chevron-up').parent().next().slideUp();
		}, function() {
			$(this).addClass('icon-chevron-up').removeClass('icon-chevron-down').parent().next().slideDown();
		});
	});
}(jQuery);