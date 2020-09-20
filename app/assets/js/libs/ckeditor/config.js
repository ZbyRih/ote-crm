/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
	// var roxyFileman = document.location.origin + '/dist/ckeditor/fileman/index.html';
	// config.filebrowserBrowseUrl = roxyFileman;
	config.removeDialogTabs = 'link:upload;image:upload';
	config.language = 'cs';
	config.height = '200px';
	config.resize_dir = 'vertical';
	config.skin = 'bootstrapck';

	config.toolbar_Full = [{
			name: 'document',
			items: ['Source', '-', 'Templates']
		},
		{
			name: 'clipboard',
			items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
		},
		{
			name: 'editing',
			items: ['Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt']
		},
		{
			name: 'forms',
			items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']
		},
		'/',
		{
			name: 'links',
			items: ['Link', 'Unlink', 'Anchor']
		},
		{
			name: 'insert',
			items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe']
		},
		{
			name: 'paragraph',
			items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
		},
		'/',
		{
			name: 'basicstyles',
			items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
		},
		{
			name: 'styles',
			items: ['Styles', 'Format', 'Font', 'FontSize']
		},
		{
			name: 'colors',
			items: ['TextColor', 'BGColor']
		},
		{
			name: 'tools',
			items: ['Maximize', 'ShowBlocks', '-', 'About']
		}
	];

	config.toolbar_Basic = [
		['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'About']
	];
};