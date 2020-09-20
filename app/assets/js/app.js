"use strict"

require('jquery');

global.Nette = {
	noInit: true
};
global.Nette = require('nette-forms');

require('nette.ajax.js');

import appCommon from './common/common';
import appForms from './forms/forms';
import appFlashes from './common/flashes';

$(document).ready(() => {

	global.Nette.initOnLoad();
	$.nette.init();

	initApp($(document));

	$.nette.ext('snippets').after(function ($els) {
		$els.each(function () {
			initApp($(this));
		});
	});
});


function initApp($c) {
	appForms($c);
	appFlashes($c);
	appCommon($c);
}