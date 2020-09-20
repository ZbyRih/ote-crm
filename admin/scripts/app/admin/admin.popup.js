(function(namespace, $){
	"use strict";
	var Popup = function(){
		var o = this;
		$(document).ready(function(){
			o.initialize();
		});
	};

	var p = Popup.prototype;

	p.initialize = function (){};

	p.popup = null;
	p.content = null;
	p.onclose = null;
	p.onhit = null;

	p.create = function(){
		var o = this;

		this.popup = $('<div id="modal" class="well"><div class="content"></div><button class="btn btn-flat btn-primary ink-reaction modal_close">Zavřít</button></div>')
			.appendTo('body');

		this.popup.popup({
			  autoopen: true
			, detach: true
//			, onclose: function(){o.close();}
		});

		this.content = this.popup.find('.content');
	};

	p.load = function(src_link, hit, close){
		this.onclose = close;
		this.onhit = hit;
		this.innerLoad(src_link);
	};

	p.innerLoad = function(src_link){
		var o = this;
		o.content.html('');
		materialadmin.AppCard.addCardLoader(o.content);
		$.get(src_link, function(content, status){
			materialadmin.AppCard.removeCardLoader(o.content);
			o.content.html(content);
			o.bind(src_link);
		});
	};

	p.close = function(){
		if(this.onclose){
			this.onclose();
		}

		this.popup.popup('hide');
	};

	p.bind = function(src_link){
		var o = this;
		var sel = o.content.find('>select');

		admin.Forms.handleTags(o.content, src_link);

		admin.Lists.handleFilter(o.content.find('.dataTables_filter'), src_link, function(oLink){
			o.innerLoad(oLink.get());
		});
		admin.Lists.handleLinks(o.content.find('.dataTable tbody,.tree ul'), function(oLink, jelement){
			if(o.onhit){
				if(sel.length > 0){
					oLink.add(sel.attr('name'), sel.val());
				}
				o.onhit(oLink, jelement, sel.attr('name'));
			}
		});
		admin.Lists.handlePages(o.content.find('.dataTables_paginate'), src_link, function(oLink, jelement){
			o.innerLoad(oLink.get());
		});
		admin.Lists.handleStatic(o.content.find('.dataTables_wrapper'));

		if(sel.length > 0){
			o.content.find('>select').on('change', function(){
				var link = new admin.Link(src_link);
				link.add(sel.attr('name'), sel.val());
				o.innerLoad(link.get());
			});
		}

		$('[data-toggle="tooltip"]').tooltip({container: o.content});
	};

	p.modalText = function(title, contentUrl){
		var form = new admin.Modal('modal-text', title, 'full');

		form.show(undefined, true);

		$.get(contentUrl, function(content, status){
			form.removeCardLoader();
			form.content(content);
		});
	};

	p.modalAlert = function(title, message, action){
		var form = new admin.Modal('alert', title, 'simple');
		form.message(message);
		form.show(action, false);
	};

	p.modalConfirm = function(title, message, action){
		var form = new admin.Modal('confirm', title, 'full');
		form.message(message);
		form.buttons('Ano', 'Ne');
		form.show(action, false);
	};

	p.modalFormCreate = function(modalID, title){
		var form = '<div id="' + modalID +'" class="modal fade" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">'
			+ '<div class="modal-dialog">'
				+ '<div class="modal-content">'
					+ '<div class="modal-header">'
						+ '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'
						+ '<h4 class="modal-title" id="formModalLabel">' + title + '</h4>'
					+ '</div>'
					+ '<form class="form-horizontal" role="form">'
						+ '<div class="modal-body">' // <!-- formulář -->
						+ '</div>'
						+ '<div class="modal-footer">' // <!-- buttony -->
						+ '</div>'
					+ '</form>'
				+ '</div>'
			+ '</div>'
		+ '</div>';
		return $(form);
	};

	window.admin.Popup = new Popup;
}(this.admin, jQuery));