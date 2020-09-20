"use strict";

export default ($context) => {
	let view = $context.find('#preformat-view');
	let src = $context.find('#preformat-src');

	if (!view.length || !src.length) {
		return;
	}

	let ifrm = $('<iframe frameborder="0" tabindex="0" allowtransparency="true" style="width: 100%; height: 100%;" scrolling="no">')
		.attr('src', 'about:blank')
		.appendTo(view);

	let iDoc = ifrm[0].contentWindow ? ifrm[0].contentWindow.document : ifrm[0].contentDocument;
	iDoc.open('text/html', 'replace');
	iDoc.write(src[0].textContent);
	iDoc.close();

	ifrm.load(() => ifrm.height(iDoc.body.scrollHeight + 15));
};