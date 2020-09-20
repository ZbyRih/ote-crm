import _ from 'lodash';
import change from './change.js';

"use strict";

class List {
	constructor(items, parent) {
		this.opened = true;
		this.position = null;
		this.itemsOrg = Object.assign({}, items);
		this.itemsVis = _(items);
		this.wrap = this.list = $('<ul class="select2-results__options combobox"></ul>');
		this.onClick = null;

		this._createList();

		this.close();

		$('body').append(this.wrap);

		let off = parent.offset();
		this.wrap.offset({
			left: off.left,
			top: off.top + parent.outerHeight()
		});
		this.wrap.width(parent.outerWidth());
	};

	open() {
		if (this.opened) {
			return;
		}

		this.opened = true;
		this.wrap.show();
	}

	close() {
		if (!this.opened) {
			return;
		}

		this.opened = false
		this.wrap.hide();
	}

	moveUp() {
		if (this.position === null) {
			this.position = 0;
		} else if (this.position < 1) {
			return;
		} else {
			this.position--;
		}
		this._updateSelected();
	}

	moveDown() {
		if (this.position === null) {
			this.position = 0;
		} else if (this.position >= (this._getSize() - 1)) {
			return;
		} else {
			this.position++;
		}
		this._updateSelected();
	}

	filter(text) {
		this.position = null;
		this.itemsVis = _(this.itemsOrg).filter((e) => {
			return e.text.toLowerCase().indexOf(text.toLowerCase()) >= 0;
		});
		this._createList();
	}

	getOption() {
		if (this.position === null) {
			return null;
		}
		let item = null;
		this.list.find('li').each((i, e) => {
			if (i == this.position) {
				item = {
					value: $(e).attr('value'),
					text: $(e).text()
				};
			}
		});
		return item;
	}

	isOpen() {
		return this.opened;
	}

	remove() {
		this.list.remove();
	}

	_updateSelected() {
		this.list.find('li').each((i, e) => {
			if (this.position === null) {
				$(e).removeClass('select2-results__option--highlighted');
			} else if (this.position == i) {
				$(e).addClass('select2-results__option--highlighted');
			} else {
				$(e).removeClass('select2-results__option--highlighted');
			}
		});
	}

	_createList() {
		this.list.find('li').remove();
		_(this.itemsVis).each((e) => {
			let li = $('<li value="' + e.id + '" class="select2-results__option pointer-hover">' + e.text + '</li>');

			li.on('click', this._onClick.bind(this));

			this.list.append(li);
		});
	}

	_onClick(e) {

		let pos = false;
		let val = $(e.currentTarget).val();

		this.list.find('li').each((i, _e) => {
			if ($(_e).val() == val) {
				pos = i;
			}
		});

		if (pos !== false && this.onClick !== null) {
			this.position = pos;
			this.onClick();
		}
	}

	_getSize() {
		return this.itemsVis.size();
	}
};

export default ($e) => {

	const opts = $e.data('opts');
	const list = new List(opts, $e);
	let onChange = null;

	if ($e.data('onchange')) {
		onChange = change($e, false);
	}

	$e.on('keydown keypress', (ev) => {
		if (ev.which == 13 && list.isOpen()) { // enter
			ev.preventDefault();
			ev.stopPropagation();
			return false;
		}
	});

	list.onClick = () => {
		let item = list.getOption();
		$e.val(item.value);
		list.remove();
		onChange.call(() => { });
	};

	$e.on('keyup', (ev) => {
		if (ev.which == 13 && list.isOpen()) { // enter
			let item = list.getOption();
			$e.val(item.value);
			list.remove();
			onChange.call(() => { });
		} else if (ev.which == 38) { // up
			list.open();
			list.moveUp();
		} else if (ev.which == 40) { // down
			list.open();
			list.moveDown();
		} else if (ev.which == 27) { // esc
			list.close();
		} else {
			list.filter($e.val());
			list.open();
		}
	});

	$e.append();
}