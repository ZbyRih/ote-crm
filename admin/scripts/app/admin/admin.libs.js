(function(namespace, $){
	"use strict";
	var Libs = function(){
		var o = this;
		$(document).ready(function(){
			o.initialize();
		});
	};
	var p = Libs.prototype;

	p.initialize = function(){};

	Libs.libs = {cke: false, popover: false, spin: false, toastr: false, table: false, dz: false};
	Libs.libs_src = {
		  cke: './../libs/ckeditor/ckeditor.js'
		, popover: './scripts/libs/jquery.popupoverlay.js'
//		, popover: './scripts/libs/jquery.webui-popover.js'
		, spin: './scripts/libs/spin.min.js'
		, toastr: './scripts/libs/toastr.js'
		, mask:  './scripts/libs/jquery.mask.js'
//		, autonum: './scripts/libs/autoNumeric.2.0.js'
		, table: './scripts/libs/jquery.dataTables.min.js'
		, dz: './scripts/libs/dropzone.js'
		, tags: './scripts/libs/bootstrap-tagsinput.min.js'
		, tah: './scripts/libs/typeahead.jquery.min.js'
	};

	p.getMultiLibs = function(arr, path) {
		if(!(arr instanceof Array)){
			arr = [arr];
		}

	    var _arr = $.map(arr, function(scr) {
	        return $.getScript((path||'') + Libs.libs_src[scr]);
	    });

	    _arr.push($.Deferred(function( deferred ){
	        $( deferred.resolve );
	    }));

	    return $.when.apply($, _arr);
	}

	p.load = function(libs, _callback){
		if(!(libs instanceof Array)){
			libs = [libs];
		}

		var toLoad = [];
		var callback = function(){
			if(_callback !== undefined){
				_callback();
			}
		};

		libs.forEach(function(i){
			if(!Libs.libs_src.hasOwnProperty(i)){
				console.error('knihovna neni definovana ' + i);
			}

			if(!Libs.libs[i]){
				toLoad.push(i);
			}
		});

		if(toLoad.length > 0){
			this.getMultiLibs(toLoad).done(function(){
				toLoad.forEach(function (i){
					console.info('loaded ' + i);
					Libs.libs[i] = true;
				})
				callback();
			}).fail(function(jqXHR, status, err){
				if(jqXHR.status == 200 && jqXHR.statusText == "OK" && jqXHR.readyState == 4){
					eval(jqXHR.responseText);
					callback();
				}else{
					toLoad.forEach(function (i){
						console.error('fail to load ' + i);
					});

					console.error(jqXHR);
					console.error(status);
					console.error(err);
				}
			})
		}else{
			callback();
		}
	};

	window.admin.Libs = new Libs;
}(this.admin, jQuery));