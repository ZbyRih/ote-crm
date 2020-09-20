import overlay from '../../../common/overlay';
import idle from '../../../common/idle';
import ScopeItem from './scope.item';

"use strict";

class Scope {
	constructor() {
		this.data = {};
		this.ajax = false;
		this.timer = null;
	}

	get(id, src, full, prop, callback) {
		if (!this.data.hasOwnProperty(id)) {
			this.data[id] = new ScopeItem(src, full, prop, this, callback);
		}

		if (this.data[id].src != src) {
			this.data[id].src = src;
		}

		if (this.data[id].callback != callback) {
			this.data[id].callback = callback;
		}

		return this.data[id];
	}

	setTimer() {
		if (this.timer) {
			return;
		}

		if (this.isAjax()) {
			return;
		}

		this.timer = setTimeout(() => { this.checkIdle(); }, 1000);
	}

	checkIdle() {
		this.clearTimer();
		clearTimeout(this.timer);
		this.timer = null;

		if (idle.isIdle(3)) {
			this.doCalls();
		} else {
			this.timer = setTimeout(() => { this.checkIdle(); }, 1000);
		}
	}

	clearTimer() {
		if (this.timer) {
			clearTimeout(this.timer);
		}
		this.timer = null;
	}

	update() {
		for (let a in this.data) {
			this.data[a].clear();
			this.data[a].update();
		}
	}

	doCalls() {
		scope.setAjax();
		overlay.show();

		let focused = document.activeElement;

		let calls = [];

		for (let a in this.data) {
			let si = this.data[a];
			if (si.isTriggered()) {
				console.log(si.prop);
				calls.push(si);
			}
		}

		if (calls.length < 1) {
			return;
		}

		let fin = function () {
			overlay.hide();

			for (let a in this.data) {
				this.data[a].clear();
				this.data[a].update();
			}

			this.timer = null;
			this.clearAjax();

			if (focused.id) {
				const ae = document.getElementById(focused.id);
				if (focused.nodeName == 'INPUT') {
					ae.select();
				} else {
					ae.focus();
				}
			}
		}.bind(this);

		let index = 0;

		let call = function (si) {
			console.log(si.prop);
			index++;
			si.call(calls.length > index ? function () {
				call(calls[index]);
			} : function () {
				fin.call();
			});
		}.bind(this);

		call(calls[index]);
	}

	setAjax() {
		this.ajax = true;
	}

	clearAjax() {
		this.ajax = false;
	}

	isAjax() {
		return this.ajax;
	}
}

const scope = new Scope();

export default scope;