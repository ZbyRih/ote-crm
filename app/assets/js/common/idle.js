"use strict";

let instance;

class Idle {
	constructor() {
		if (instance) {
			return instance;
		}

		instance = this;

		this.handlers = [];
		this.idleTime = 0;
		this.timer = null;

		$(document).ready(() => {
			this.init();
		});
	}

	init() {
		this.timer = setInterval(() => {
			this.idleTime++;
		}, 1000); // 1 s

		$(document).mousemove(() => {
			if (this.idleTime < 1) {
				return;
			}
			this.idleTime = 0;
			this.handlers.forEach(element => {
				element();
			});
		});

		$(document).keydown(() => {
			if (this.idleTime < 1) {
				return;
			}
			this.idleTime = 0;
			this.handlers.forEach(element => {
				element();
			});
		});
	}

	isIdle(threshold) {
		return this.idleTime > threshold;
	}

	getIdle() {
		return this.idleTime;
	}

	setHandler(calback) {
		this.handlers.push(calback);
	}
}

instance = new Idle();

export default instance;