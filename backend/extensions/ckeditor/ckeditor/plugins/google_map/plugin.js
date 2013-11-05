var YH = window.YH || {};
(function() {
	var path;

	CKEDITOR.plugins.add( 'google_map', {
		requires: 'iframedialog',
		init: function( editor ) {
			var pluginName = 'google_map';
			path = this.path;
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand( pluginName ));

			editor.ui.add( 'google_map', CKEDITOR.UI_BUTTON, {
				label: 'Google Map',
				command: pluginName,
				icon: this.path + 'images/gg.png',
				toobar: 'insert,11'
			});
		}
	});

	var googleMapDialog = function( editor ) {
		return {
			title: 'Google Map',
			minWidth: '600',
			minHeight: '400',
			contents: [
			{
				id: 'map',
				label: '',
				title: '',
				elements: [
				{
					id: 'iframe',
					type: 'iframe',
					src: path + '/google_map.html',
					width: '100%',
					height: '400px',
					onLoad: function() {
						YH.dialog = this.getDialog();
						//console.log(dialog);
					}
				},
				{
					type: 'text',
					id: 'lat',
					label: '经度',
					validate: CKEDITOR.dialog.validate.notEmpty('经度不能为空'),
					required: true,
					commit: function( data ) {
						data.lat = this.getValue();
					}
				},
				{
					type: 'text',
					id: 'lng',
					label: '维度',
					validate: CKEDITOR.dialog.validate.notEmpty('维度不能为空'),
					required: true,
					commit: function( data ) {
						data.lng = this.getValue();
					}
				},

				{
					type : 'select',
					id : 'zoom',
					label : '放大比例 (默认=10)',
					// Items that will appear inside the selection field, in pairs of displayed text and value.
					// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.select.html#constructor
					items :
					[
						[ '默认', '10' ],[ '0', '0' ],[ '1', '1' ], ['2', '2' ], [ '3', '3' ], [ '4', '4' ], [ '5', '5' ], [ '6', '6' ],
						[ '7', '7' ], [ '8', '8' ], [ '9', '9' ], [ '10', '10' ], [ '11', '11' ], [ '12', '12' ], [ '13', '13' ], [ '14', '14' ],
						[ '15', '15' ], [ '16', '16' ], [ '17', '17' ], [ '18', '18' ], [ '19', '19' ], [ '20', '20' ], [ '21', '21' ]
					],
					commit: function( data )
					{
						data.zoom = this.getValue();
					}
				},
				]
			}],
			onOk: function() {
				var dialog = this,
					data = {};
				this.commitContent(data);
				var  iframe= dialog.getContentElement('map', 'iframe').getElement();
				var width = iframe.getSize('width');
				var height = iframe.getSize('height');
				editor.insertHtml('test');
				editor.insertHtml('<img src="http://maps.googleapis.com/maps/api/staticmap?zoom='+data.zoom+'&size='+width+'x'+height+'&markers=color:red%7Ccolor:red%7Clabel:C%7C'+data.lat+','+data.lng+'&sensor=false"/>');
			}
		};
	};

	CKEDITOR.dialog.add( 'google_map', function( editor) {
		return googleMapDialog( editor );
	});
})();
