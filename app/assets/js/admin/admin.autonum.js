const AutoNumeric = require('autonumeric');

(function (namespace, $) {
	"use strict";

	var common = {
		digitGroupSeparator: ' ',
		decimalCharacter: ',',
		modifyValueOnWheel: false
	};

	namespace.an = {
		currency: function (item) {
			if (item) {
				new AutoNumeric(item, $.extend({}, common, {
					currencySymbol: ' Kč',
					currencySymbolPlacement: 's'
				}));
				return $(item).attr('an', 'bind');
			}
		},
		float: function (item, precision = 2) {
			if (item) {
				new AutoNumeric(item, $.extend({}, common, {
					decimalPlaces: precision
				}));
				return $(item).attr('an', 'bind');
			}
		},
		num: function (item, precision = 2) {
			if (item) {
				new AutoNumeric(item, $.extend({}, common, {
					allowDecimalPadding: true,
					decimalPlaces: precision
				}));
				return $(item).attr('an', 'bind');
			}
		}
	};

}(global.admin, jQuery));