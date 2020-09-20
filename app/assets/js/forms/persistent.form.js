"use strict";

function loadItem(uuid) {
	let data = localStorage.getItem(uuid);
	if (data == null) {
		return {
			init: null,
			saved: null
		};
	}
	return JSON.parse(data);
}

function initItem(uuid, init) {
	let item = {
		init: JSON.stringify(init),
		saved: null
	};
	localStorage.setItem(uuid, JSON.stringify(item));
	return item;
}

function saveData(uuid, item, data) {
	item.saved = data;
	localStorage.setItem(uuid, JSON.stringify(item));
	return item;
}

function dropItem(uuid) {
	localStorage.removeItem(uuid);
}

export default ($context) => {
	"use strict";

	$context.find('form').each((i, e) => {
		const url = document.URL;
		const form = $(e);
		const uuid = 'form-persistance-' + url + '-' + e.getAttribute('id');

		function getData() {
			return form.serializeArray();
		}

		let item = loadItem(uuid);
		if (item.init !== null) {
			let current = JSON.stringify(getData());
			if (item.init == current && item.saved !== null) {
				item.saved.forEach(pair => {
					form.find('[name=' + pair.name + ']').val(pair.value);
				});
			} else {
				item = initItem(uuid, getData());
			}
		} else {
			item = initItem(uuid, getData());
		}

		form.on('change keyup', () => {
			item = saveData(uuid, item, getData());
		});

		form.on('submit', () => {
			dropItem(uuid);
		});
	});
}