!function($) {
	$(function(){
		var $window = $(window);
		var $formActions = $('.form-actions');
		var formActionsHeight = $formActions.outerHeight();

		setTimeout(function() {
			if ($formActions.length) {
				var height;
				var onChange = function() {
					if (height < $(window).scrollTop() + $(window).height()) {
						$formActions.removeClass('fixed');
					} else {
						$formActions.css('width', $formActions.width());
						$formActions.addClass('fixed');
					}
				};

				var bindScroll = function () {
					height = $formActions.offset()['top'];
					$window.scroll(onChange);
				};

				if (window.CKEDITOR) {
					var timeInterval;
					var timeout = function() {
						var editor = window.CKEDITOR.instances[$formActions.data('editor')];
						if (editor && editor.status == 'ready') {
							bindScroll();
							clearTimeout(timeInterval);
						} else {
							timeInterval = setTimeout(timeout, 10);
						}
					};
					timeInterval = setTimeout(timeout, 10);
				} else {
					bindScroll();
					onChange();
				}
			}
		}, 100);


		setTimeout(function() {
			var $formSide = $('#form-side');
			if ($formSide.length == 0) {
				return;
			} else {
				$formSide.css('width', $formSide.width());
			}
			var offsetTop = $formSide.offset()['top'];
			$formSide.affix({
				offset: {
					top: function() {return offsetTop-60;}
				}
			});
		}, 60);

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