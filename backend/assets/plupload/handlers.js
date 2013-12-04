jQuery(function($) {
	if (!window.YH || !window.YH.uploaderInit)
		return;

	function fileError(file, message, up) {
		var $item = $('#media-item-' + file.id);

		if ($item.length === 0) {
			$item = $('<div id="media-item-' + file.id + '" class="media-item media-item-error alert alert-error"><button data-dismiss="alert" class="close" type="button">×</button>' + file.name + '  ' +  message + '</div>');
			$('#media-container-' + up._id).append($item);
		} else {
			$item.attr('class', 'media-item media-item-error alert alert-error')
				.html('<button data-dismiss="alert" class="close" type="button">×</button>' + message);
		}

		$item.data('last-err', file.id);
	}

	function uploadError(file, code, message, up) {
		var hundredmb = 100 * 1024 * 1024, max;

		switch (code) {
			case plupload.FAILED:
				fileError(file, '上传失败。', up);
				break;
			case plupload.FILE_EXTENSION_ERROR:
				fileError(file, '不允许上传该类型的文件，请选择其它文件。', up);
				break;
			case plupload.FILE_SIZE_ERROR:
				fileError(file, '超过了站点的最大上传限制', up);
				break;
			case plupload.IMAGE_FORMAT_ERROR:
				fileError(file, '该文件不是图像，请使用其它文件。', up);
				break;
			case plupload.IMAGE_MEMORY_ERROR:
				fileError(file, '达到内存限制，请使用小一些的文件。', up);
				break;
			case plupload.IMAGE_DIMENSIONS_ERROR:
				fileError(file, '该文件超过了最大大小，请使用其它文件。', up);
				break;
			case plupload.GENERIC_ERROR:
				fileError(file, '上传失败。', up);
				break;
			case plupload.IO_ERROR:
				max = parseInt(up.settings.max_file_size, 10);
				if ( max > hundredmb && file.size > hundredmb )
					fileError(file, '请尝试使用标准的浏览器上传工具来上传这个文件。', up);
				else
					fileError(file, 'IO 错误。', up);
				break;
			case plupload.HTTP_ERROR:
				fileError(file, 'HTTP 错误。', up);
				break;
			case plupload.INIT_ERROR:
				break;
			case plupload.SECURITY_ERROR:
				fileError(file, '安全错误', up);
				break;
			default:
				fileError(file, '上传时发生了错误。请稍后再试。', up);
		}

		up.removeFile(file);
	};

	function fileQueued(file, up) {
		var $item = $('#media-item-' + file.id);
		if ($item.data('last-err') == file.id)
			return;
		$('#media-container-' + up._id).append('<div id="media-item-' + file.id + '" class="media-item media-item-uploading"><div class="progress"><div class="bar"></div><div class="percent">0%</div></div><div class="filename original"> ' + file.name + ' </div></div>');
	}

	function fileUplading(up, file) {
		var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
		if (max > hundredmb && file.size > hundredmb) {
			setTimeout(function(){
				var done;

				if (file.status < 3 && file.loaded == 0) { // not uploading
					fileError(file, '请尝试使用标准的浏览器上传工具来上传这个文件。', up);
					up.stop(); //停止上传
					up.removeFile(file); //移除文件
					up.start(); //开始上传
				}
			}, 10000); //等待10s
		}
	}

	function uploadProgress(up, file) {
		var html;
		var $item = $('#media-item-' + file.id);

		$('.bar', $item).width(($item.width() * file.loaded) / file.size );

		if (file.status == plupload.DONE) {
			$item.removeClass('media-item-uploading');
			html = '处理中...';
		} else {
			html = file.percent + '%';
		}
		$('.percent', $item).html(html);
	}

	function uploadSuccess(up, file, response) {
		try {
			response = $.parseJSON(response);
		} catch (e) {
			fileError(file, '服务器发生错误', up);
			return;
		}

		if (response.error !== 0) {
			fileError(file, response.message, up);
			return;
		} else {
			var $mediaContainer = $('#media-container-' + up._id),
				$item = $('#media-item-' + file.id),
				file = response.file;

			up._fileCount++;

			if (up._fileType === 'image') {
				var imageUrl,
					sizes = file.sizes,
					$postThumbnail = $('<div class="thumbnail"></div>');

				if (up._many === 1) {
					if (sizes['post-thumbnail']) {
						imageUrl = sizes['post-thumbnail']['url'];
					} else {
						imageUrl = sizes['origin']['url'];
					}
					$postThumbnail.addClass('post-thumbnail');
				} else {
					imageUrl = sizes['thumbnail']['url'];
				}

				$postThumbnail.append('<img src="' + imageUrl + '" alt="' + file.filename + '" data-mfp-src="' + file.url + '" />');
				$item.html($postThumbnail);
			} else {
				var html;
				html = '<span class="media-icon"><img src="' + file.icon + '" alt="' + file.filename + '" width="46" height="60" /></span>';
				html += '<span class="media-name">' + file.name + '</span>';
				html += '<span class="media-ext">' + file.ext.toUpperCase() + '</span>';
				html += '<span class="media-datetime">' + file.datetime + '</span>';
				html += '<span class="media-size">' + file.size + '</span>';
				$item.html(html);
			}

			var actions = '<div class="media-item-actions">'
				+ '<a href="#" title="编辑" data-id="' + file.id + '" ref="tooltip" class="media-item-edit"><i class="icon-pencil"></i></a>'
				+ '<a href="#" title="删除" data-id="' + file.id + '" ref="tooltip" class="media-item-delete"><i class="icon-trash"></i></a>'
				+ (up._preview ? '<a href="#" data-image-title="' + file.name + '" data-mfp-src="' + file.url + '" title="预览" ref="tooltip" class="media-item-preview"><i class="icon-zoom-in"></i></a>' : '')
				+ '</div>';
			$item.append(actions);
			$item.attr({
				id: 'media-item-' + file.id,
				'data-id': file.id
			});

			//加入拖拽删除
			if (up._many !== 1 && up.features.dragdrop && !up._sort) {
				$item.draggable({
					revert: 'invalid',
					cursor: 'move',
					helper: 'clone'
				});
			}

			if (up._many === 1) {
				$mediaContainer.html($item);
				up._mediaIds = [parseInt(file.id)];
			} else {
				up._mediaIds.push(parseInt(file.id));
			}
			up._$input.trigger('update', [up._mediaIds]);
		}
	}

	function initMediaIds(up) {
		up._mediaIds = up._mediaIds || [];
		$.each(up._$input.val().split(','), function(i, id) {
			if ($.isNumeric(id)) {
				up._mediaIds.push(parseInt(id));
			}
		});
		up._$input.trigger('update', [up._mediaIds]);
	}

	function deleteMediaItem(up, id) {
		var $item = $('#media-item-' + id);
		var mediaName = $('.media-name', $item).html();

		if (up._object && up._object == 'File') {
			$item.hide();
			var url = YH.mediaItemDeleteUrl.replace('%3Aid', id);
			$.ajax({
				type: 'post',
				url: url,
				data: YH.csrfParams,
				success: function(response) {
					var $alert = $('<div class="alert"></div>');
					var message;
					$item.after($alert).remove();

					if (response.error == 0) {
						$alert.addClass('alert-success').html(mediaName + '删除成功');
					} else {
						$alert.addClass('alert-error').html(mediaName + '删除失败');
					}

					setTimeout(function() {
						$alert.fadeOut().remove();
					}, 3000);
				}
			});
		} else {
			$item.remove();
			_upadateInputVal(up, id);
		}
	}

	function _upadateInputVal(up, id) {
		var index;
		if ((index = $.inArray(id, up._mediaIds)) !== -1) {
			up._mediaIds.splice(index, 1);
			up._$input.trigger('update', [up._mediaIds]);
		}

		up._$input.val(up._mediaIds.join(','));

		up._fileCount--;

		if (up._many !== true && up._fileCount < up._many) {
			up._$pluploadContainer.show();
		}
	}

	function processModal($modal, response) {
		$('.modal-body', $modal).html(response);
		$('form', $modal).one('submit.YH-uploader', function() {
			$.ajax({
				type: 'post',
				url: this.action,
				data: $(this).serialize(),
				success: function(response) {
					var $mediaItem, newFileName;
					if (response) {
						processModal($modal, response);
					} else {
						newFileName = $('#File_name').val();
						$mediaItem = $('#media-item-' + $modal.data('id'));
						$mediaItem.find('.media-name').html(newFileName);
						$mediaItem.find('.media-item-preview').attr('data-image-title', newFileName);
						$modal.modal('hide');
					}
				}
			});
			return false;
		});
	}

	$.each(window.YH.uploaderInit, function(i, config){
		var uploader = new plupload.Uploader(config['plupload']),
			$pluploadContainer = $('#' + config['plupload']['container']),
			$mediaContainer = $('#media-container-' + i),
			$uploadButton = $('#' + config['plupload']['browse_button']),
			type = config['type'],
			typeName = type === 'image' ? '图片' : '文件';

		uploader._$mediaContainer = $mediaContainer;
		uploader._$pluploadContainer = $pluploadContainer;
		uploader._$input = $('#' + config['input_id']);
		uploader._id = i;
		uploader._fileType = type;
		uploader._many = config['many'];
		uploader._fileCount = $.isNumeric(config['file_count']) ? parseInt(config['file_count']) : 0;
		uploader._object = config['object'];
		uploader._preview = config['preview'];
		uploader._mediaIds = [];
		uploader._sort = config['sort'];

		if (config['sort']) {
			$mediaContainer.sortable({
				items: '> .media-item',
				cursor: "move",
				update: function(event, ui) {
					var fileIds = [];
					$mediaContainer.find('.media-item').each(function(index, item){
						var id = $(item).data('id');
						if (id) {
							fileIds.push(parseInt(id));
						}
					});
					uploader._mediaIds = fileIds;
					uploader._$input.val(fileIds.join(','));
				}
			});
		}

		if (config['preview'] && type === 'image') {
			$mediaContainer.magnificPopup({
				delegate: '.media-item-preview',
				type: 'image',
				gallery: {
					enabled: true
				},
				image: {
					titleSrc: 'data-image-title'
				}
			});
		}

		$mediaContainer.on('click.YH-uploader', '.media-item-delete', function(e) {
			var id = $(this).data('id');
			deleteMediaItem(uploader, id);
			return false;
		});

		$mediaContainer.on('click.YH-uploader', '.media-item-edit', function(e) {
			var id = $(this).data('id'),
				$modal = $('#modal'),
				html;

			if ($modal.length === 0) {
				html = '<div class="modal hide fade" id="modal">'
					+ '<div class="modal-header">'
					+ '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'
					+ '<h3></h3></div>'
					+ '<div class="modal-body"></div></div>';
				$modal = $(html);
				$('body').append($modal);
			}
			$('h3', $modal).html('编辑'+ typeName);
			var url = window.YH.mediaItemEditUrl.replace('%3Aid', id);
			$.ajax({
				type: 'get',
				url: url,
				success: function(response) {
					$modal.data('id', id);
					processModal($modal, response);
					$modal.modal('show');
				}
			});
			return false;
		});

		initMediaIds(uploader); //初始化文件ids

		uploader.bind('Init', function(up, params) {
			if (up.features.dragdrop) {
				$pluploadContainer.addClass('drag-drop');
				$pluploadContainer.prepend('<div class="drag-drop-area" id="drag-drop-area-' + i +'">'
					+ '<div class="drag-drop-inside">'
					+ '<div class="info-upload"><p class="drag-drop-info">将' + typeName + '拖到这里</p><p>或</p><p class="drag-drop-buttons"></p></div>'
					+ '<div class="info-trash" style="display:none"><p class="drag-drop-info">将文件拖到这里将会删除它</p></div>'
					+ '</div></div>');
				$('.drag-drop-buttons', $pluploadContainer).append($uploadButton);
				$('.drag-drop-area', $pluploadContainer).bind('dragover.YH-uploader', function() {
					$pluploadContainer.addClass('drag-over');
				}).bind('dragleave.YH-uploader, drop.YH-uploader', function(){
					$pluploadContainer.removeClass('drag-over');
				});
				if (up._many !== 1) {
					if (!up._sort) {
						$(".media-item", $mediaContainer).draggable({
							revert: 'invalid',
							cursor: 'move',
							helper: 'clone'
						});
					}
					$('.drag-drop-area', $pluploadContainer).droppable({
						accept: function(el) {
							var $el = $(el);
							if ($el.hasClass('media-item') && $el.data('id'))
								return true;
						},
						activeClass: "media-trash-highlight",
						activate: function(event, ui) {
							$('.info-upload', this).hide();
							$('.info-trash', this).show();
						},
						deactivate: function(event, ui) {
							$('.info-upload', this).show();
							$('.info-trash', this).hide();
						},
						drop: function(event, ui ) {
							deleteMediaItem(up, ui.helper.data('id'));
						}
					});
				}
			} else {
				$pluploadContainer.removeClass('drag-drop');
				$('.drag-drop-area', $pluploadContainer).unbind('.YH-uploder');
			}
		});

		uploader.init();

		uploader.bind('Error', function(up, error){
			uploadError(error.file, error.code, error.message, up);
			up.refresh();
		});

		uploader.bind('FilesAdded', function(up, files) {
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
			var i;
			if (up._many !== true) {
				i = up._many - up._fileCount;
			}
			plupload.each(files, function(file){
				if (i === 0) {
					up.removeFile(file);
					return;
				}
				i && i--;
				if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5') {
					fileError(file, '文件过大超过了100M', up);
				} else {
					fileQueued(file, up);
				}
			});
			up.refresh();
			up.start();
		});

		uploader.bind('FilesRemoved', function(up, files) {
			plupload.each(files, function(file) {
				if (file.status == plupload.DONE) {
					up._fileCount--;
				}
			});

			if (up._many !== true && up._fileCount  < up._many) {
				$pluploadContainer.show();
			}
		});

		uploader.bind('UploadFile', function(up, file) {
			fileUplading(up, file);
		});

		uploader.bind('UploadProgress', function(up, file) {
			uploadProgress(up, file);
		});

		uploader.bind('FileUploaded', function(up, file, response) {
			uploadSuccess(up, file, response.response);
		});

		uploader.bind('UploadComplete', function(up, files) {
			if (up._fileCount === up._many) {
				$pluploadContainer.hide();
			}
			up._$input.val(up._mediaIds.join(','));
		});
	});
});
