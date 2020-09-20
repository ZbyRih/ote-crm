/**
 * formularove elementy
 * dropzone
 */

export default ($e) => {
	if ($e[0].dropzone != undefined) {
		return;
	}

	$e.dropzone({
		dictDefaultMessage: "Tady dropněte soubory nebo klikněte pro výběr",
		dictFallbackMessage: "Tento prohlížeč není podporován",
		paramName: 'soubory',
		uploadMultiple: true,
		init: function () {
			this.on('queuecomplete', function () {
				$.nette.ajax({
					url: $e.data('refresh')
				});
			});
		}
	});
}