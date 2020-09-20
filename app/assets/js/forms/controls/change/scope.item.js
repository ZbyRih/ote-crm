"use strict";

class ScopeItem {
	constructor(src, full, prop, scope, callback) {
		this.src = src;
		this.full = full;
		this.prop = prop;
		this.scope = scope;
		this.callback = callback;
		this.last = this.get();
		this.triggered = false;
	}

	trigger() {
		this.triggered = true;
		this.scope.setTimer();
	}

	isTriggered() {
		return this.triggered;
	}

	clear() {
		this.triggered = false;
	}

	update() {
		this.last = this.get();
	}

	isChanged() {
		const toData = this.get();

		if (!toData) {
			return false;
		}

		for (let a in toData) {
			if (!toData.hasOwnProperty(a) || !this.last.hasOwnProperty(a)) {
				return true;
			}

			if (this.last[a] != toData[a]) {
				return true;
			}
		}

		return false;
	}

	get() {
		let data = {};

		if (this.full) {
			data = this.src.serializeArray().reduce(function (obj, item) {
				if (item.name == '_do') {
					return obj;
				}
				obj[item.name] = item.value;
				return obj;
			}, {});
		} else {
			data[this.prop] = this.src.val();
		}

		return data;
	}

	call(complete) {
		return this.callback(complete);
	}
}

export default ScopeItem;