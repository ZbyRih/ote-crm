"use strict";

import preformat from './preformat';

export default ($context) => {

	$context.find('[data-confirm]:not(.ajax)').on('click', function (e) {
		var $i = $(e.target);

		if (!confirm($i.data('confirm'))) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			return false;
		}
	});

	$context.find('#role-change select').on('change', function () {
		var $s = $(this);
		document.location = $s.attr('url') + '&' + $s.attr('name') + '=' + $s.val();
	});

	preformat($context);
}