// import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import CKEditor from './ckeditor.build.js';

import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline';
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import Code from '@ckeditor/ckeditor5-basic-styles/src/code';
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript';
import RemoveFormat from '@ckeditor/ckeditor5-remove-format/src/removeformat';
import Font from '@ckeditor/ckeditor5-font/src/font';

// toolbar: {
// 	items: [
// 		'Source', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'Undo', 'Redo', '|',
// 		'Unlink', 'Anchor', '|',
// 		'Outdent', 'Indent'
// 	]
// },

const config = {
	plugins: CKEditor.builtinPlugins.concat([
		Alignment, Underline, Strikethrough, Code, Subscript, Superscript, RemoveFormat, Font
	]),
	toolbar: {
		items: [
			'heading',
			'|',
			'bold',
			'italic',
			'underline', 'strikethrough', 'code', 'subscript', 'superscript',
			'|',
			'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor',
			'|',
			'removeFormat',
			'|',
			'link',
			'bulletedList',
			'numberedList',
			// 'imageUpload',
			'blockQuote',
			'insertTable',
			'mediaEmbed',
			'|',
			'undo',
			'redo',
			'|',
			'alignment:left', 'alignment:center', 'alignment:right', 'alignment:justify'
		]
	},
	heading: {
		options: [{
				model: 'paragraph',
				title: 'Paragraph',
				class: 'ck-heading_paragraph'
			},
			{
				model: 'heading1',
				view: 'h2',
				title: 'Heading 1',
				class: 'ck-heading_heading1'
			},
			{
				model: 'heading2',
				view: 'h3',
				title: 'Heading 2',
				class: 'ck-heading_heading2'
			},
			{
				model: 'heading3',
				view: 'h4',
				title: 'Heading 3',
				class: 'ck-heading_heading3'
			}
		]
	}
};

export default (e) => {
	CKEditor.create(e, config).then(editor => {
			console.log('Editor was initialized', editor);
		})
		.catch(error => {
			console.error(error.stack);
		});
};