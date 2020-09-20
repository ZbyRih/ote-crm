import dropzone from './controls/dropzone.js';
import format from './controls/format.js';
import password from './controls/password.js';
import select from './controls/select.js';
// import change from './controls/change.js';
import masks from './controls/masks.js';
import combo from './controls/combobox.js';

var autosize = require('autosize');

"use strict";

const nodes = {
	'TEXTAREA': ($e) => {
		if ($e.data('format') == 'wswg') {
			return;
		}
		autosize($e);
		$e.css({
			resize: 'vertical'
		});
	},
	'SELECT': ($e) => {
		select($e);
	},
	'INPUT': ($e) => {
		if ($e.attr('type') == 'password' && $e.data('pasword-strength')) {
			password($e, $e.data('pasword-strength'));
			return;
		}

		if ($e.data('user-input')) {
			combo($e);
		}
	}
};

export default ($context) => {

	$context.find('.select2-container--bootstrap').css({
		width: ''
	});

	$context.find('label.mt-checkbox').append('<span></span>');
	$context.find('label.mt-radio').append('<span></span>');

	$context.find('form[data-dropzone]').each(function (i, e) {
		dropzone(e);
	});

	$context.find('input,select,button,textarea').each(function (i, e) {
		const $e = $(e);
		const name = e.nodeName;

		format($e);

		const mask = $e.data('input-mask');
		if (mask) {
			const opts = $e.data('input-mask-opts');
			masks(e, mask, opts ? opts : {});
		}

		if ($e.data('disabled')) {
			$e.attr('disabled', 'true');
		}

		if (nodes.hasOwnProperty(name)) {
			nodes[name]($e);
		}
	});
};