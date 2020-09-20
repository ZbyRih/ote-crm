export default ($e, len) => {
	"use strict";

	var $sp = $('<span class="strength"></span>');

	$e.parent().append($sp);

	$e.keyup(function (e) {

		var val = $(this).val();
		var strongRegex = new RegExp("^(?=.{" + len + ",})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
		var mediumRegex = new RegExp("^(?=.{" + len + ",})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
		var enoughRegex = new RegExp("(?=.{" + len + ",}).*", "g");

		if (false == enoughRegex.test(val)) {
			$sp.html('Málo znaků');
		} else if (strongRegex.test(val)) {
			$sp.addClass('ok');
			$sp.removeClass('alert');
			$sp.removeClass('error');
			$sp.html('Silné!');
		} else if (mediumRegex.test(val)) {
			$sp.addClass('alert');
			$sp.removeClass('ok');
			$sp.removeClass('error');
			$sp.html('Střední!');
		} else {
			$sp.addClass('error');
			$sp.removeClass('alert');
			$sp.removeClass('ok');
			$sp.html('Slabé!');
		}

		return true;
	});
}