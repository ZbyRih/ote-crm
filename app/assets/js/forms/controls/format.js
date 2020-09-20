/**
 * formularove elementy
 * format
 */

import CKEditor from '../../common/ckeditor';
import AutoNumeric from 'autonumeric';

"use strict";

const numericCommon = {
	digitGroupSeparator: ' ',
	decimalCharacter: ',',
	modifyValueOnWheel: false
};

const numericSpec = {
	currency: {
		currencySymbol: ' Kč',
		currencySymbolPlacement: 's'
	},
	float: {
		decimalPlaces: 2
	},
	num: {
		allowDecimalPadding: true,
		decimalPlaces: 2
	}
};

const formats = {
	'date': ($e) => {
		$e.datepicker({
			format: 'd.m. yyyy',
			language: 'cs',
			startDate: $e.data('start-date'),
			autoclose: true,
			orientation: 'top',
			// toolbarplacement: 'bottom',
			todayHighlight: true
		});
	},
	'color': ($e) => {
		$e.minicolors({
			theme: 'bootstrap'
		});
	},
	'time': ($e) => {
		if ($e.data('type') == 'select') {
			$e.data('format', 'HH:mm');
			$e.clockface();
			return;
		}

		$e.inputmask({
			mask: $e.data('mask')
		});
	},
	'time-mask': ($e) => {
		$e.inputmask({
			mask: $e.data('mask')
		});
	},
	'wswg': ($e) => {
		CKEditor($e[0]);
	},
	'numeric': ($e) => {
		new AutoNumeric($e[0], $.extend({}, numericCommon, numericSpec[$e.data('numeric')]));
	}
};

export default ($e) => {

	const format = $e.data('format')

	if (!format) {
		return null;
	}

	if (formats.hasOwnProperty(format)) {
		formats[format]($e);
	}
}