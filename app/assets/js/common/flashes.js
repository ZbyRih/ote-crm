import flash from './flash';

const subl = ('alert alert-').length;

export default ($c) => {
	"use strict";

	$c.find('.flashes').each(function (i, e) {

		var $al = $(e).find('.alert');

		if ($al.length > 0) {

			flash.duration($al.length);

			$($al.get().reverse()).each((i, e) => {
				var $m = $(e);
				var _class = $m.attr('class');

				var t = _class.substring(subl, _class.length);
				if (t == 'danger') {
					t = 'error';
				}

				flash.msg($m.html(), t);
				$m.remove();
			});
		}
	});

	$.nette.ext({
		error: (jqXHR, status, error, settings) => {
			if (status == 'abort') {
				return;
			}

			flash.stay().danger('Došlo k chybě při ajax volání `' + error + '`');
		}
	});
};