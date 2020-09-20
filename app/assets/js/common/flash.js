import toastr from '../libs/toastr';

toastr.options = {
	closeButton: true,
	debug: false,
	positionClass: 'toast-top-right',
	onclick: null,
	showDuration: 1000,
	hideDuration: 1000,
	timeOut: 5000,
	extendedTimeOut: 1000,
	showEasing: 'swing',
	hideEasing: 'linear',
	showMethod: 'fadeIn',
	hideMethod: 'fadeOut'
};

const _reset = () => {
	toastr.extendedTimeOut = 1000;
}

const _stay = () => {
	toastr.options.timeOut = 0;
	toastr.options.extendedTimeOut = 0;
};

export default {
	info(msg) {
		toastr['info'](msg);
		return this;
	},
	success(msg) {
		toastr['success'](msg);
		return this;
	},
	warning(msg) {
		toastr['warning'](msg);
		return this;
	},
	danger(msg) {
		toastr['error'](msg);
		return this;
	},
	msg(msg, type) {
		toastr[type](msg);
		return this;
	},
	duration(length) {
		toastr.options.timeOut = 5000 * length;
		return this;
	},
	stay() {
		_stay();
		return this;
	},
	reset() {
		_reset();
		return this;
	}
}