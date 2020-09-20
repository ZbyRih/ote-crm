admin.Tags = (function(){
	"use strict";
	function constructor(bstagsinput, altLink){
		var org = $(bstagsinput);
		var oUrl = null;
		
		if(org.parents('form').attr('action')){
			oUrl = new admin.Link(org.parents('form').attr('action'));
		}else if(altLink != undefined){
			oUrl = new admin.Link(altLink);
		}else{
			oUrl = new admin.Link(document.location.href);
		}
		
		var cfg = {};
		
		var tii = org.tagsinput(cfg);
		var ajax = null;
		
		var last_query = {
			delta: 0,
			query: '',
			list: [],
			min_time: 300,
			min_len: 3,
			timer: null,
			check: function(query, callback){
				if(this.timer){
					clearTimeout(this.timer)
				}
				
				if(this.query != null && query != null){
					var qt = query.trim(), tqt = this.query.trim();
					
					if(qt == tqt && qt != '' && tqt != ''){
						return false;
					}
					
					if(qt.length < this.min_len){
						return false;
					}
				}

				if((new Date()).getTime() - this.delta < this.min_time){
					this.timer = setTimeout(callback, this.min_time + 100)
					return false;
				}
				
				this.delta = (new Date()).getTime();
				
				return true;
			}
		};
		
		var createActionLink = function(link, action){
			var new_link = new admin.Link(link.get());
			if(new_link.has('ajax')){
				new_link.add('ajax_ex', action);
			}else{
				new_link.add('ajax', action);
			}
			return new_link;
		}

		var fillTagList = function(query, render){
			if(ajax !== null){
				ajax.abort();
			}
			
			last_query.query = query;

			var url = createActionLink(oUrl, 'ajaxFindTag');
			url.add('ffield', org.attr('id'));
			url.add('tag', encodeURIComponent(query));

			ajax = $.ajax({
				  url: url.get()
				, complete: function(jhx, status){
					
					var items = $('item', jhx.responseXML);

					if (items.length > 0) {

						var list = [];
						
						items.each(function(index, element){
							list.push($(this).text());
						});
						
						render(list);
						
						last_query.list = list;
					}
				}
			});
		}
		
		var tah = $(tii[0].$input).typeahead(null, {
			  async: true
			, minLength: 3
			, source: function(query, sync, async) {
				if(last_query.check(query, function (){fillTagList(query, async);})){
					fillTagList(query, async);
				}else{ return false;
//					sync(last_query.list);
				}
		    }
		});
		
		if(!$('.twitter-typeahead').hasClass('form-control')){
			$('.twitter-typeahead').addClass('form-control');
			$('.twitter-typeahead').after('<div class="form-control-line"></div>');
			$(tah).on('focus', function(){
				$('.twitter-typeahead').addClass('focus');
			})
			$(tah).on('blur', function(){
				$('.twitter-typeahead').removeClass('focus');
			})
		}

		var urlField = '&ffield=' + org.attr('id');
		
		org.on('beforeItemAdd', function(event) {
			var tag = event.item;
			if ((!event.options || !event.options.preventPost) && org.attr('ajax') != undefined) {
				
				var url = createActionLink(oUrl, 'ajaxAddTag');
				url.add('ffield', org.attr('id'));
				url.add('tag', encodeURIComponent(tag));
				
				$.ajax(url.get(), function(response) {
					if (response.failure) {
						org.tagsinput('remove', tag, {preventPost: true}); // "preventPost" here will stop this ajax call from running when the tag is removed
					}
				});
			}
			$(tii[0].$input).typeahead('val', '');
			$(tii[0].$input).typeahead('close');
			return true;
		});
		
		org.on('beforeItemRemove', function(event) {
			var tag = event.item;
			if ((!event.options || !event.options.preventPost) && org.attr('ajax') != undefined) {
				
				var url = createActionLink(oUrl, 'ajaxDelTag');
				url.add('ffield', org.attr('id'));
				url.add('tag', encodeURIComponent(tag));
				
				$.ajax(url.get(), function(response) {
					if (response.failure) {
						org.tagsinput('add', tag, {preventPost: true}); // "preventPost" here will stop this ajax call from running when the tag is added
					}
				});
			}
			$(tii[0].$input).val('');
			$(tii[0].$input).typeahead('val', '');
			return true;
		});
	};
	return constructor;
})();