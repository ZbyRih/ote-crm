admin.Modal = (function(){
	"use strict";
	
	function modal(ID, title, type){
		var _this = this;
		
		type = (type === undefined)? 'full': type;
		
		this._content = null;
		this._buttons = null;
		this._loader = false;
		this.result = null; // callback
		
		this.jqForm = null;
		
		if(type == 'full'){
			this.jqForm = full(ID, title, this);
		}else if(type == 'simple'){
			this.jqForm = simple(ID, title, this);
		}
		
		this.jqForm.find('.close').bind('click', function(ev){
			ev.preventDefault();
			_this.close();
			_this.triggerResult(false);
		});
	}
	
	function simple(ID, title, parent){
		var form = $('<div id="modal" class="well"><div class="content"></div><button class="btn btn-flat btn-primary ink-reaction modal_close">Zavřít</button></div>');
		parent._content = form.find('.content');
		return form;
	}
	
	function full(ID, title, parent){
		var form = $('<div id="' + ID +'" class="modal fade" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">'
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
			+ '</div>');
		parent._content = form.find('.modal-body');
		parent._buttons = form.find('.modal-footer');
		return form;
	}
	
	modal.prototype = {
		  show: function(result, loader, afterloader){
			 
			this.result = result;
			
			$('body', document).append(this.jqForm);
			this.jqForm.addClass('in').css({display:'block'});

			var _this = this;
			var _show = function(){
				if(loader){
					_this.addCardLoader();
					if(afterloader !== undefined){
						afterloader();
					}
				}
			}  
			  
			if(loader !== undefined && loader){
				admin.Libs.load('spin', _show);
			}else{
				_show();
			}
		}
		, close: function(){
			this.removeCardLoader();
			this.jqForm.removeClass('in').remove();
		}
		, content: function(html){
			if(html === undefined){
				return this._content;
			}
			
			this.removeCardLoader();
			this._content.html(html);
		}
		, message: function(text){
			this.content('<p>' + text + '</p>');
		}
		, buttons: function(ok, cancel){
			var _this = this;
			if(cancel !== undefined){
				this._buttons
					.append($('<button type="button" class="btn btn-default" data-dismiss="modal">' + cancel + '</button>')
						.bind('click', function(){
							_this.close();
							_this.triggerResult(false);
						}));				
			}
			
			if(ok !== undefined){
				this._buttons
					.append($('<button type="button" class="btn btn-primary">' + ok + '</button>')
						.bind('click', function(){
							_this.close();
							_this.triggerResult(true);
						}));				
			}
		}
		, triggerResult: function(result){
			if(this.result !== undefined){
				this.result(result);
			}
		}
		, addCardLoader: function(){
			materialadmin.AppCard.addCardLoader(this._content);
			this._loader = true;
		}
		, removeCardLoader: function(){
			if(this._loader){
				materialadmin.AppCard.removeCardLoader(this._content);
				this._loader = false;
			}
		}
	};
	
	return modal;
})();