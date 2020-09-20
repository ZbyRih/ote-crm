import overlay from '../../common/overlay';
import scope from './change/scope';

"use strict";

class OnChange {
	constructor($e, bind) {
		this.$e = $e;
		const form = $e.closest('form');
		this.name = $e.attr('name');
		this.url = this.getUrl($e);

		const f_ocs = form.data('onchange-scope');
		const f_oct = form.data('onchange-type');
		const e_ocs = $e.data('onchange-scope');
		const e_ocp = $e.data('onchange-prop');
		const e_oct = $e.data('onchange-type');
		const e_now = $e.data('onchange-now');

		this.full = f_ocs == 'all' || e_ocs == 'all';
		this.prop = e_ocp ? e_ocp : this.name;
		this.type = e_oct ? e_oct : (f_oct ? f_oct : 'GET');

		const src = this.full ? form : $e;

		this.scope = scope.get($e.attr('id'), src, this.full, this.name, (complete) => {
			return this.call(complete);
		});

		const select = $e.get(0).nodeName == 'SELECT';

		if (bind === false) {
			return;
		}

		if (select || e_now) {

			$e.on($e.data('format') == 'date' ? 'changeDate' : 'change', function (ev) {

				if (!this.scope.isChanged()) {
					return;
				}

				if (scope.isAjax()) {
					return;
				}

				scope.clearTimer();
				scope.setAjax();
				overlay.show();

				this.call(() => {
					scope.clearAjax();
					scope.update();
					overlay.hide();
				});
			}.bind(this));
		} else {
			$e.on('keyup change paste blur', function () {
				if (!this.scope.isChanged()) {
					return;
				}

				this.scope.trigger();
			}.bind(this));
		}
	}

	getUrl($e) {
		const url = $e.data('onchange');
		const field = encodeURI('{field}');
		return (url.indexOf(field) > -1) ? url.replace(field, this.name) : url;
	}

	call(complete) {
		return $.nette.ajax({
			url: this.url,
			type: this.type,
			data: this.scope.get(),
			complete: () => {
				complete();
			}
		});
	}
}

export default ($e, bind) => {
	return new OnChange($e, bind);
}