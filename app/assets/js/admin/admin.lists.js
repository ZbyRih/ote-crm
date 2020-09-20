import toastr from '../libs/toastr';

(function (namespace, $) {
	"use strict";
	var Lists = function () {
		var o = this;
		$(document).ready(function () {
			o.initialize();
		});
	};

	var p = Lists.prototype;

	p.initialize = function () {
		$('.dataTables_filter').each(function (i, context) {
			p.handleFilter(context, document.location.href);
		});

		$('.dataTables_wrapper, .tree-wrapper').each(function (i, context) {
			p.handleDataTypes(context);

			p.handleStatic(context);
			p.handleMassActions(context);
			p.handleAjax(context);
		});
	};

	p.handleStatic = function (context) {
		var $context = $(context);
		if ($context.hasClass('static')) {
			var nosort = $context.hasClass('static-nosort');
			var defss = $context.data('default-sort');

			var opts = {
				dom: 'ft',
				paging: false,
				stateSave: true,
				columnDefs: [{
					targets: 'nosort',
					orderable: false
				}],
				language: {
					search: '<i class="fa fa-search"></i>'
				}
			};
			if (nosort) {
				opts.ordering = false;
			}
			if (defss) {
				var DS = defss.split(':');
				opts.order = [
					[parseInt(DS[0]), DS[1]]
				];
			}
			var table = $('table', context).DataTable(opts);
		} else {
			p.handleSort(context);
		}
	};

	p.handleFilter = function (context, link, callback) {
		var listUID = $(context).attr('uid');
		var target = '#filter_' + listUID;
		var url = link;
		var main = $(target);

		if (main.length < 1) {
			return;
		}

		$(context).on('keydown', function (e) {
			if (e.which == 13) {
				if (!$(e.target).parent().parent().hasClass('bootstrap-tagsinput')) {
					main.find(target + '_submit', context).trigger('click');
				}
			}
			if (e.which == 27) {
				if (!$(e.target).parent().hasClass('bootstrap-tagsinput')) {
					main.find(target + '_reset', context).trigger('click');
				} else {
					// pridat if tagy sou prazdne tak poslat
				}
			}
		});

		main.find(target + '_submit', context).on('click', function () {
			var inputs = $(main).find('input:not(.tt-hint),select').filter(':visible');
			var outarray = new Array();
			var inpt = null;

			for (var i = 0; i < inputs.length; i++) {
				inpt = $(inputs[i]);

				if (inpt.parents('.bootstrap-tagsinput').length > 0) {
					var ip = inpt.parents('.bootstrap-tagsinput').prev('select').val();
					if (ip) {
						var p = ip.join('|');
						outarray.push(p);
					} else {
						outarray.push('');
					}
				} else {
					if (inpt.is(':checkbox')) {
						outarray.push(((inpt.is(':checked')) ? 1 : 0));
					} else {
						outarray.push(inpt.val());
					}
				}
			}

			var oLink = new admin.Link(url);

			oLink.drop('rFilter_' + listUID);
			oLink.add('filter_' + listUID, outarray.join(';'));
			if (callback) {
				callback(oLink);
			} else {
				oLink.goTo();
			}
		});

		main.find(target + '_reset', context).on('click', function () {
			var oLink = new admin.Link(link);

			oLink.drop('filter_' + listUID);
			oLink.add('rFilter_' + listUID, 'reset');

			if (callback) {
				callback(oLink);
			} else {
				oLink.goTo();
			}
		});
	};

	p.handleLinks = function (e, callback) {
		$(e).find('a.sel').bind('click', function (ev) {
			ev.preventDefault();
			var oLink = new admin.Link($(this).attr('href'));
			if (callback) {
				callback(oLink, $(this));
			}
		});
	};

	p.handlePages = function (e, link, callback) {
		var oLink = new admin.Link(link);
		$(e).find('a').bind('click', function (ev) {
			ev.preventDefault();

			var pl = new admin.Link($(this).attr('href'));
			var par = undefined;
			var val = undefined;
			pl.each(function (ipar, ival) {
				if (ipar.indexOf('_page') != -1) {
					par = ipar;
					val = ival;
					return false;
				}
				return true;
			});

			if (par != undefined) {
				oLink.add(par, val);
			}

			if (callback) {
				callback(oLink, $(this));
			}
		});
	}

	p.handleDataTypes = function (context) {
		var items1 = $('td[dt="currency"], li[dt="currency"]', context);
		var items2 = $('td input[dt="currency"], li input[dt="currency"]', context);
		var items3 = $('td[dt="float"], li[dt="float"]', context);
		var items4 = $('td input[dt="float"], li input[dt="float"]', context);
		var items5 = $('td[dt="numeric"], li[dt="numeric"]', context);
		var items6 = $('td input[dt="numeric"], li input[dt="numeric"]', context);

		if (items1.length > 0 || items2.length > 0 || items3.length > 0 || items4.length > 0 || items5.length > 0 || items6.length > 0) {

			var workAround = function (i, item) {
				if ($(item).has('a').length > 0) {
					var value = $('a', item).html();
					var html = '<span>' + value + '</span>';
					$('a', item).html('').append(html);
					if (value == 'Změnit') {
						return null;
					} else {
						return $('span', item).get(0);
					}
				} else {
					return item;
					//					return $(item).html($(item).html().trim()).addClass('text-right').get(0);
				}
			};
			var decs = 2;

			var currency = function (i, item) {
				admin.an.currency(workAround(i, item));
			};

			var _float = function (i, item) {
				admin.an.float(workAround(i, item));
			};

			var numeric = function (i, item) {
				admin.an.num(workAround(i, item), decs);
			};

			items1.each(currency);
			items2.each(currency);

			items3.each(_float);
			items4.each(_float);

			items5.each(function (i, item) {
				var spl = $(item).html().trim().split('.');
				if (spl.length > 1 && spl[1].length > decs) {
					decs = spl[1].length;
				}
			}).each(numeric);
			items6.each(numeric);
		}
	};

	p.handleSort = function (context) {
		var sortUrl = $('tr[sort_url]', context).attr('sort_url');
		$('th[role="sort"]', context).each(function (i, item) {
			$(item).bind('click', function (ev) {
				document.location = sortUrl + $(item).attr('si');
			});
		});
	};

	p.handleMassActions = function (context, link) {

		var ma = $('th input.ma, ul li.head span input.ma', context);
		var all = $('td input.ma, ul li[role="row"] span input.ma', context);

		var getCheckedIds = function () {
			var all = $('td input.ma, ul li[role="row"] span input.ma', context);
			var checked = all.filter(':visible').filter(':checked');
			var ids = [];

			for (var i = 0; i < checked.length; i++) {
				ids.push($(checked[i]).val());
			}

			return ids;
		};

		if (all.length < 1) {
			$('.dropdown-menu', context).parent().remove();
			ma.remove();
		} else {
			$('.dropdown-menu a', context).not('.btn.confirm').on('click', function (ev) {
				ev.preventDefault();

				var ids = getCheckedIds();

				if (ids.length > 0) {
					document.location = this.href + '&ids=' + ids.join(',');
					return true;
				}
				return false;
			});

			var delAction = $('.dropdown-menu a.btn.confirm', context);
			if (delAction.length > 0) {
				var orgClick = $._data(delAction[0]).events.click[0].handler; // o muj boze
				delAction.off('click');
				delAction.on('click', function (ev) {
					ev.preventDefault();

					var ids = getCheckedIds();
					var href = this.href + '&ids=' + ids.join(',');

					$(this).attr('href', href);
					orgClick.call(this, ev); // podstrkavam scope this a volam modal potvrzovani z admin.js
				});
			}

			var ma_checkOut = function () {
				ma.prop({
					checked: false,
					title: 'Vše za-škrtnout'
				});
			};

			var ma_checkIn = function () {
				ma.prop({
					checked: true,
					title: 'Vše od-škrtnout'
				});
			};

			var check_click = function (e) {
				var checked = all.filter(':checked');
				var je = $(e.target);

				if (all.length > checked.length) {
					if (je.get(0) == ma.get(0)) {
						all.prop('checked', true);
						ma_checkIn();
					} else {
						ma_checkOut();
					}
				} else {
					if (je.get(0) == ma.get(0)) {
						all.prop('checked', false);
						ma_checkOut();
					} else {
						ma_checkIn();
					}
				}
			};

			ma.bind('click', check_click);
			all.bind('click', check_click);
		}
	};

	p.handleAjax = function (context) {
		var edits = $('.dataTables_ajax .ajax_edit_item', context);
		var open = null;
		var reset = ($(context).attr('data') == 'ajax-reset') ? true : false;

		$('a.ajax', context).each(function (i, element) {
			var org = $(element);
			var parent = org.parent();

			org.on('click', function (ev) {
				ev.preventDefault();
				ev.stopPropagation();
				ev.stopImmediatePropagation();


				if (open != null) {
					open.find('.btn-cancel').trigger('click');
				};

				open = parent;

				var field = $(this).attr('field');
				var value = $(this).html();
				if ($(this).has('span').length) {
					value = $('span', this).html();
				}

				var width = $(this).parent().innerWidth() + 'px';

				org.detach();

				var edit = edits.filter('[field="' + field + '"]').clone(true);

				$('input[mask]', edit).each(function (i, item) {
					// admin.Libs.load('mask', function () {
					$(item).mask($(item).attr('mask'));
					// });
				});

				var dt = edit.attr('dt');

				edit = edit.children();
				edit.css({
					width: width
				});
				var input = edit;

				if (input.find('input').length) {
					input = input.find('input');
				}

				if (input.prop('tagName') == 'SELECT') {
					$('option', input).filter(function () {
						return $(this).text() == value;
					}).prop('selected', true);
				} else {
					if (value == 'Změnit') {
						input.val('');
					} else {
						input.val(value);
					}
				}

				parent.append(edit);
				parent.append('<a class="btn btn-icon-toggle btn-save" title="uložit"><i class="md md-done"></i></a>' +
					'<a class="btn btn-icon-toggle btn-cancel" title="zrušit"><i class="md md-close"></i></a>');

				if (dt == 'currency') {
					admin.an.currency(input.get(0));
				} else if (dt == 'num') {
					admin.an.num(input.get(0));
				}

				admin.App.datepicker($('.datepicker', parent));

				parent.find('.btn-cancel').bind('click', function (aev) {
					org.html(value);
					parent.empty();
					parent.append(org);

					open = null;

					return false;
				});

				parent.find('.btn-save').bind('click', function (aev) {
					aev.preventDefault();
					aev.stopPropagation();

					input = edit;
					if (input.find('input').length) {
						input = input.find('input');
					}
					var text = null,
						value = null;
					if (input.prop('tagName') == 'SELECT') { // dropdown
						value = input.val();
						text = input.find('option[value="' + value + '"]').html();
					} else {
						text = value = input.val();
					}

					var link = parent.closest('tr, li[role="row"]').attr('reclink');
					var model = parent.closest('table, div.tree').attr('model');

					link = link + '&' + model + 'Ajax=' + value + '&ajax_field=' + field;

					admin.App.runLoader();

					$.ajax({
						url: link,
						headers: {
							'Content-Type': 'text/html; charset=UTF-8'
						},
						error: function (jqXHR, textStatus, errorThrown) {
							admin.App.removeLoader();
							admin.App.jqXHRError(jqXHR.responseText, 'Uložení se nezdařilo');
						},
						success: function (data, textStatus, jqXHR) {
							toastr.success('', 'Úspěšně uloženo');

							org.html(text);
							parent.empty();
							parent.append(org);

							open = null;
						},
						complete: function (jqXHR, textStatus) {
							admin.App.removeLoader();
							if (reset && jqXHR.statusCode() == 200) {
								document.location = document.location;
							}
						}
					});

					return false;
				});

				return false;
			});
		});
	};

	global.admin.Lists = new Lists;
}(global.admin, jQuery));