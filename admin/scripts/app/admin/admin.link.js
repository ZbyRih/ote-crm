admin.Link = (function(){
	"use strict";
	function link(_l){
		this.linkEnd = '';
		this.linkBase = '';
		this.linkPairs = [];

		_l = String(_l);
		
		var linkSp = _l.split('#');
		
		if (linkSp.length > 1) {
			this.linkEnd = '#' + linkSp[1];
			_l = linkSp[0];
		}
		
		linkSp = _l.split('?');
		if(linkSp.length > 1){
			this.linkBase = linkSp[0] + '?';
			_l = linkSp[1];
		}else{
			this.linkBase = _l + '?';
		}
		
		var linkEls = _l.split('&');
		var linkPair = '';
		
		for(var i = 0; i < linkEls.length; i++){
			linkPair = linkEls[i].split('=');
			this.linkPairs.push(linkPair);
		}
		
		this._joinPairs = function(){
			var jLink = [];
			for(var i = 0; i < this.linkPairs.length; i++){
				jLink.push(this.linkPairs[i].join('='));
			}
			return jLink.join('&');
		};
	}
	
	link.prototype = {
		drop: function(paramName){
			for(var i = 0; i < this.linkPairs.length; i++){
				if(this.linkPairs[i][0] == paramName){
					this.linkPairs.splice(i, 1);
				}
			}
		}
		, add: function(paramName, paramValue){
			this.drop(paramName);
			this.linkPairs.push([paramName, paramValue]);
			return this;
		}
		, has: function(name){
			for(var i = 0; i < this.linkPairs.length; i++){
				if(this.linkPairs[i][0] == name){
					return true;
				}
			}
			return false;
		}
		, get: function(){
			return this.linkBase + this._joinPairs() + this.linkEnd;
		}
		, each: function(fce){
			for(var i = 0; i < this.linkPairs.length; i++){
				if(!fce(this.linkPairs[i][0], this.linkPairs[i][1])){
					break;
				}
			}
		}
		, getParam: function(param){
			for(var i = 0; i < this.linkPairs.length; i++){
				if(this.linkPairs[i][0] == param){
					return this.linkPairs[i][1];
				}
			}
			return null;
		}
		, goTo: function(){
			document.location = this.linkBase + this._joinPairs() + this.linkEnd;
		}
	};
	
	return link;
})();