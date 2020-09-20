/**
 * formularovy element select
 */

import combo from './combobox.js';

"use strict";

const leftmatch = {
	matcher: function (params, data) {
		// If there are no search terms, return all of the data
		if ($.trim(params.term) === '') {
			return data;
		}

		// Do not display the item if there is no 'text' property
		if (typeof data.text === 'undefined') {
			return null;
		}

		if (data.text.substr(0, params.term.length).toUpperCase() == params.term.toUpperCase()) {
			return data;
		}

		// Return `null` if the term should not be displayed
		return null;
	}
};

const selectDefault = {
	dropdownAutoWidth: true,
	language: 'cs'
};

const styles = {
	'simple': ($e) => {
		$e.selectpicker({
			style: 'simple', // 'btn-info'
			size: 8
		});
		$e.hide();
	},
	'combo': ($e) => {
		combo($e);
	},
	'select': ($e) => {
		let opts = Object.assign({}, selectDefault);

		if ($e.data('left-match')) {
			opts = $.extend(opts, leftmatch);
		}

		opts.width = $e.parent().width();

		$e.select2(opts);
	}
};

export default ($e) => {

	const style = $e.data('style');

	if (!style) {
		return;
	}

	if (styles.hasOwnProperty(style)) {
		styles[style]($e);
	}

	let onChange = $e.data('on-change');
	let onChangeParam = $e.data('on-change-param');

	if (!onChange || !onChangeParam) {
		return;
	}

	$e.on('change', (el) => {
		let data = {};
		let $el = $(el.target);

		data[onChangeParam] = $el.val();

		$.nette.ajax({
			url: onChange,
			data: data
		});
	});
}