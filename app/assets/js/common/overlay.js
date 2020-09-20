"use strict";

let overlay = $('<div class="overlay"><div class="spinner"></div></div>');

overlay.show = () => {
	overlay.css({
		display: 'block'
	});
}

overlay.hide = () => {
	overlay.css({
		display: 'none'
	});
}

$('.page-content').append(overlay);

export default overlay;