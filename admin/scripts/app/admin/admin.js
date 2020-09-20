(function($){
	"use strict";
	$.ajaxSetup({async:true});
	var App = function(){
		var o = this;
		$(document).ready(function(){
			window.materialadmin.App.initialize();
			o.initialize();
		});
	};
	var p = App.prototype;

	p.url = null;

	p.initialize = function(){
		var loc = document.location.href;

		p.url = loc.substring(0, loc.lastIndexOf('admin'));

		this._enableLists();
		this._enableForms();
		this._initToolTip();
		this._initToastr();
		this._initHelp();
		this._handleMenu();
		this._handleConfirms();
		this._handleViewAjaxs();
		this._handleAjaxs();
	};

	p._enableForms = function(){
	};

	p._enableLists = function(){
	};

	p._initHelp = function(){
		$('.header-nav .module-help a').bind('click', function(ev){
			ev.preventDefault();
			admin.Popup.modalText($(this).attr('title'), $(this).attr('href'));
			return false;
		});
	};

	p._initToolTip = function(){
		$('[data-toggle="tooltip"]').tooltip({container: 'body'});
	}

	p._initTags = function(){
	}

	p._initToastr = function(){
		admin.Libs.load('toastr', function(){
			toastr.options = {
				  'closeButton': false
				, 'debug': false
				, 'newestOnTop': false
				, 'progressBar': false
				, 'positionClass': 'toast-top-right'
				, 'preventDuplicates': false
				, 'onclick': null
				, 'showDuration': '300'
				, 'hideDuration': '1000'
				, 'timeOut': '5000'
				, 'extendedTimeOut': '1000'
				, 'showEasing': 'swing'
				, 'hideEasing': 'linear'
				, 'showMethod': 'fadeIn'
				, 'hideMethod': 'fadeOut'
            }
		});
	};

	p.ajax = function(url, success_msg, error_msg, success_fce){
		admin.App.runLoader();

		$.ajax({url: url
			, headers : {'Content-Type' : 'text/html; charset=UTF-8'}
			, error: function(jqXHR, textStatus, errorThrown){
				admin.App.removeLoader();
				admin.App.jqXHRError(jqXHR.responseText, error_msg);
			}
			, success: function(data, textStatus, jqXHR){
				toastr.success('', success_msg);
				success_fce();
			}
			, complete: function(jqXHR, textStatus){
				admin.App.removeLoader();
			}
		});
	};

	p.jqXHRError = function(jqXHR, message, title){
		var orgOpts = jQuery.extend({}, toastr.options);

		toastr.options.timeOut = 0;
		toastr.options.closeButton = true;

		if(title == undefined){
			title = message;
			message = '';
		}else{
			mesage += '<br />';
		}

		toastr.error(message + jqXHR.replace('|', '<br />'), title);

		toastr.options = orgOpts;
	};

	p.runLoader = function(){
		admin.Libs.load('spin', function(){
			var container = $('<div class="card-loader" id="modal-loader"></div>').appendTo('body');
			container.hide().fadeIn();
			var opts = {
		        lines: 17 ,
		        length: 0 ,
		        width: 3 ,
		        radius: 6 ,
		        corners: 1 ,
		        rotate: 13 ,
		        direction: 1 ,
		        color: '#000' ,
		        speed: 2 ,
		        trail: 76 ,
		        shadow: false ,
		        hwaccel: false ,
		        className: 'spinner' ,
		        zIndex: 2e9
			};

			var spinner = new Spinner(opts).spin(container.get(0));
			$('body').data('card-spinner', spinner);
		});
	};

	p.removeLoader = function(){
		var spinner = $('body').data('card-spinner');
		var loader = $('#modal-loader');
		loader.fadeOut(function(){
			spinner.stop();
			loader.remove();
		});
	};

	p.datepicker = function(selector){
		var datepickers = selector.datepicker({format:'dd.mm. yyyy', language: 'cs', weekStart: 1});
		datepickers.off('focus');
		datepickers
			.parent()
			.find('.input-group-addon')
			.click(function () {
				$(this).parent().find('.datepicker').datepicker('show');
        	});
	};

	p._handleMenu = function(){
		function setCookie(cname, cvalue, exdays) {
		    var d = new Date();
		    d.setTime(d.getTime() + (exdays*24*60*60*1000));
		    var expires = "expires="+ d.toUTCString();
		    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}

		var toggler = $('[data-toggle="menubar"]');

		var menu_act = materialadmin.AppNavigation.getMenuState()
		var menu_state = localStorage.getItem('menu');

		if(menu_state != undefined){
			if(menu_state != menu_act && menu_act == 2){
				toggler.trigger('click');
			}
		}

		toggler.on('click', function(e){
			localStorage.setItem('menu', (materialadmin.AppNavigation.getMenuState() == 1 ? 2: 1));
			setCookie('ote-admin-menu', materialadmin.AppNavigation.getMenuState(), 30);
		});
	};

	p._handleConfirms = function(){
		$('.btn.confirm').each(function(i, item){
			$(item).on('click', function(ev){
				ev.preventDefault();
				ev.stopPropagation();

				var src = $(this).attr('href');
				var title = $(this).attr('title');

				admin.Popup.modalConfirm(title, 'Opravdu <b>' + title + '</b> ?', function(result){
					if(result){
						document.location = src;
						return true;
					}
				});

				return false;
			});
		});
	};

	p._handleViewAjaxs = function(){
		$('a.ajax-select').each(function(i, item){
			$(item).on('click', function(ev){
				ev.preventDefault();
				ev.stopPropagation();

				var selSrc = new admin.Link($(ev.target).attr('sel'));
				var saveLoc = new admin.Link($(ev.target).attr('sav'));

				var bind = function (){
					admin.Popup.create();
					admin.Popup.load(selSrc.get(), function(oLink, jqe, subName){
						var r = jqe.closest('tr');
						if(r.length < 1){
							r = jqe.closest('li');
						}
						var rec = r.attr('rec');
						if(oLink.getParam(subName)){
							saveLoc.add(subName, oLink.getParam(subName));
						}
						saveLoc.add('selected', rec).goTo();
					});
				}

				admin.Libs.load(['popover', 'spin'], bind);
			});
		});
	};

	p._handleAjaxs = function(){
		$('a.ajax.link').on('click', function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			$.ajax({
				url: $(ev.target).attr('href')
				, complete: function(jqXHR, textStatus){
					location.reload(true);
				}
			});
		});
	};

	window.admin = window.admin || {};
	window.admin.App = new App;
}(jQuery));