(function(namespace, $){
	"use strict";
	var Forms = function(){
		var o = this;
		$(document).ready(function(){
			o.initialize();
		});
	};
	var p = Forms.prototype;

	p.initialize = function(){
		admin.App.datepicker($('.form-group .datepicker'));

		var bind_cke = function(i, e){
			CKEDITOR.replace(e, {
				  baseHref: admin.App.url
				, toolbar: $(e).hasClass('tiny')? 'Basic' : 'Full'
				, height: $(e).hasClass('tiny')? '100px' : '200px'
			});
		};

		var ckes = $('.form-group .fck-editor .fck');
		if(ckes.size() > 0){
			window.CKEDITOR_BASEPATH = admin.App.url + 'libs/ckeditor/';
			admin.Libs.load('cke', function(){
				ckes.each(bind_cke);
			});
		}

		$('.form-group .select-content .vybrat').bind('click', function(ev){
			ev.preventDefault();

			var a = $(ev.target);
			var src = new admin.Link(a.attr('data'));
			var pf = a.closest('form');
			var f2u = a.attr('fields2url');

			if(f2u){
				var params = JSON.parse(f2u.replace(/\|/g, '"'));
				var f = undefined;
				for (var p in params) {
				    if (params.hasOwnProperty(p)) {
				        src.drop(p);
				        f = pf.find('#' + params[p].replace(/\./g, '_'));
				        src.add(p, f.val());
				    }
				}
			}

			var bind = function(){
				admin.Popup.create();
				admin.Popup.load(src.get(), function(oLink, jqe){

					var modul = oLink.getParam('module');
					var str = a.attr('id');
					var id = str.substring(0, str.length - 5);
					var name = '';
					var sep = '';

					jqe.parentsUntil('tbody').find('td a').each(function(){
						name += sep + $(this).html();
						sep = ', ';
					});
					if(name.length == 0){
						jqe.parentsUntil('li').find('a').each(function(){
							name += sep + $(this).html();
							sep = ', ';
						});
					}

					$('#'+id).val(oLink.getParam(modul + 'r'));
					$('#'+id+'_link').html(name);
					$('#'+id+'_il').remove();
					$('#'+id+'_sp').remove();

					var l2f = a.attr('list2fields');
					if(l2f){
						var params = JSON.parse(l2f.replace(/\|/g, '"'));
						var f = undefined;
						var t = jqe.closest('table');
						var r = jqe.closest('tr');

						for (var p in params) {
						    if (params.hasOwnProperty(p)) {
						    	var vals = [];
						    	t.find('th').each(function(it, e){
						    		if($(e).attr('si') == p){
						    			vals.push(p);
						    		}else{
						    			vals.push('');
						    		}
						    	});

						    	r.find('td').each(function(it, e){
						    		if(vals[it].length > 0){
								    	f = pf.find('#' + params[p].replace(/\./g, '_'));

								    	if($(e).has('span').length){
											f.val($('span', e).html());
								    	}else if($(e).has('a').length){
								    		f.val($('a', e).html());
										}else{
											$(e).html();
										}
						    		}
						    	});
						    }
						}
					}

					admin.Popup.close();
				});
			}

			admin.Libs.load(['popover', 'spin'], bind);

			return false;
		});

		$('.form-group .select-content .odpojit').bind('click', function(ev){
			ev.preventDefault();

			var a = $(ev.target);

			var id_str = a.attr('id');
			var id = id_str.substring(0, id_str.length - 4);

			$('#' + id).val('');
			if($('#' + id + '_il').length > 0){
				$('#' + id + '_il').remove();
			}else{
				var a_sel = $('#' + id + '_link');
				var tit = a_sel.attr('data-title');
				a_sel.html(tit);
			}
		});

		$('.form-group select.self-send').bind('change', function(ev){
			var select = $(ev.target);
			var link = new admin.Link(document.location);
			link.add(select.attr('name'),  select.val()).goTo();
		});

		$('.form-group select.form-send').bind('change', function(ev){
			var form = $(ev.target).closest('form');
			var submit = $('button[type="submit"]');
			if(submit.length == 1){
				submit.trigger('click');
			}else{
				form.submit();
			}
		});

		$('.form-group select[fields]').each(function(i, item){
			var select = $(item);
			var form = select.closest('form');
			var fields = select.attr('fields').split(',');

			var setFields = function(){
				for(var i=0; i < fields.length; i++){
					var val = $('option:selected', select).attr(fields[i]);
					var field = $('[name=' + fields[i] + ']', form);
					if(field.attr('an') == 'bind'){
						field.autoNumeric('set', val);
					}else{
						field.attr('value', val);
					}
				}
			};

			select.on('change', function(){
				setFields();
			});

			setFields();
		});

		$('.form-group input[mask]').each(function(i, item){
			admin.Libs.load('mask', function(){
				$(item).mask($(item).attr('mask'));
			});
		});

		$('.form-group [dt="currency"]').each(function(i, item){
			admin.an.currency(item);
		});

		$('.form-group [dt="float"]').each(function(i, item){
			var p = $(item).data('precision');
			if(p){
				admin.an.float(item, p);
			}else{
				admin.an.float(item);
			}
		});

		$('.form-group [dt="numeric"]').each(function(i, item){
			admin.an.num(item);
		});

		$('.form-group div[hide]').each(function(i, e){
			var select = $('select', e)
				, json = $(e).attr('hide')
				, pars = JSON && JSON.parse(json) || $.parseJSON(json)
				, hidden = [];

			select.on('change', function(){
				var val = select.val(), i = 0;

				for(;i < hidden.length; i++){
					hidden[i].show();
				}

				hidden.length = 0;

				if(pars.hasOwnProperty(val)){
					var fields = pars[val];
					var input, label;
					for (var field in fields){
						if(fields.hasOwnProperty(field)){
							input = $('#' + fields[field]).parentsUntil('.form-group');
							label = $('[for="' + fields[field] + '"]');
							input.hide();
							label.hide();
							hidden.push(label);
							hidden.push(input);
						}
					}
				}
			});
			select.trigger('change');
		});

		$('.form-group .btn-file :file').on('change', function(){
		    var   input = $(this)
		        , numFiles = input.get(0).files ? input.get(0).files.length : 1
		        , label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

		    $('.form-group input[name="' + input.attr('name') + '_name"]').val(label);
		});

		p.handleTags(document);

		var dropzones = $('.form-group.dropzone,.input-group.dropzone');
		var bind_dropzone = function(i, e){
			var uf = $(e);

			var url = uf.closest('form').attr('action');
			var elems = uf.closest('form').find('input,select,textarea');
			var parName = uf.find('input[type=file]').attr('name');

			uf.children().remove();

			uf.dropzone({
				  url: url
				, metod: 'post'
				, paramName: parName
				, parallelUploads: 1
				, uploadMultiple: false
				, maxFilesize: (uf.attr('max-size')/1024.0)/1024.0
				, dictDefaultMessage: "Sem přetahnout soubory pro nahrání"
				, success: function (file, response) {
					file.previewElement.classList.add('dz-success');
				}
				, error: function (file, response) {
					file.previewElement.classList.add('dz-error');
				}
				, queuecomplete: function(file){
//					location.reload();
				}
				, sending: function(data, xhr, formData){
					var $e = null, tag = null, name = undefined, but = null;
					elems.each(function(i, e){
						$e = $(e);
						tag = $e.prop('tagName');
						name = $e.attr('name');
						if(name !== undefined){
							if(tag == 'TEXTAREA'){
								formData.append(name, $e.content());
							}else if(tag == 'SELECT' && $e.attr('data-role') == '_tagsinput'){
								var opt = $e.find('option'), j = 0, opts = [];
								for(;j < opt.length; j++){
									opts.push($(opt[j]).val());
								}
								formData.append(name, opts.join(','));
							}else{
								formData.append(name, $e.val());
							}
						}
					});
					but = uf.closest('form').find('button[type=submit]');
					if(but.length > 0){
						formData.append(but.attr('name'), but.val());
					}
//
//					var els = uf.cloasest('form').find('input,select,textarea'),
//					   i = 0,
//					   e = null;
//					for(;i < els.length; i++){
//						e = $(els.get(i));
//						if(e[0].nodeName == 'TEXTAREA'){
//							formData.append(e.attr('name'), e.content());
//						}else if(e[0].nodeName == 'SELECT' && e.attr('data-role') != undefined && e.attr('data-role') == '_tagsinput'){
//							var opt = e.find('option'), j = 0, opts = [];
//							for(;j < opt.length; j++){
//								opts.push($(opt[j]).val());
//							}
//							formData.append(e.attr('name'), opts.join(','));
//						}else if(e.attr('name') !== undefined){
//							formData.append(e.attr('name'), e.val());
//						}
//					}
//
//					var but = uf.parents('form').find('button[type=submit]');
//					if(but.length > 0){
//						formData.append(but.attr('name'), but.val());
//					}
				}
			});
		}

		if(dropzones.size() > 0){
			admin.Libs.load('dz', function(){
				Dropzone.autoDiscover = false;
				dropzones.each(bind_dropzone);
			});
		}

		$('.form-group .ajax-multi-select').each(function (i, e){
			var link = new admin.Link($(e).attr('url'));
			link.add('field', $(e).attr('field'));

			$('.remove-all', e).on('click', function(ev){
				ev.preventDefault();

				var url = new admin.Link(link.get());
				url.add('ajax', 'remove');
				url.add('data', 'all');

				admin.App.ajax(url.get(), 'Úspěšně odstraněno', 'Odstranění se nezdařilo', function(){
					$('.ms_list', e).empty();
				});
				return false;
			});

			var removeAction = function(ev){
				ev.preventDefault();

				var url = new admin.Link(link.get());
				url.add('ajax', 'remove');
				url.add('data', $(ev.delegateTarget).attr('data-id'));

				admin.App.ajax(url.get(), 'Úspěšně odstraněno', 'Odstranění se nezdařilo', function(){
					$(ev.target).closest('span.item').remove();
				});
				return false;
			};

			$('.ms_list .remove', e).on('click', removeAction);

			$('.vybrat', e).on('click', function(ev){
				ev.preventDefault();
				var src = $(ev.delegateTarget).attr('url');

				var bind = function(){
					admin.Popup.create();
					admin.Popup.load(src, function(oLink, jqe){

						var modul = oLink.getParam('module');
						var name = '';
						var sep = '';

						jqe.closest('tbody').find('td a').each(function(){
							name += sep + $(this).html();
							sep = ', ';
						});
						if(name.length == 0){
							jqe.closest('li').find('a').each(function(){
								name += sep + $(this).html();
								sep = ', ';
							});
						}

						var id = oLink.getParam(modul + 'r');

						var url = new admin.Link(link.get());
						url.add('ajax', 'add');
						url.add('data', id);

						admin.App.ajax(url.get(), 'Úspěšně přidáno', 'Přidání se nezdařilo', function(){
							$('.ms_list', e).append(
								'<span class="item">' + name + '<a href="#" data-id="' + id + '" class="remove"><i class="md md-delete"></i></a></span><br />'
							).on('click', removeAction);
						});

						admin.Popup.close();
					});
				};

				admin.Libs.load(['popover', 'spin'], bind);
				return false;
			});
		});
	};

	p.handleTags = function(context, altLink){
		if(context){
			var tags = $('[data-role="_tagsinput"]', context);
			if(tags.size() > 0){
				admin.Libs.load(['tags', 'tah'], function(){
					tags.each(function(it, e){
						var tgCtrl = new admin.Tags(e, altLink);
					});
				});
			}
		}
	}

	window.admin.Forms = new Forms;
}(this.admin, jQuery));