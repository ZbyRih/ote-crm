/**
 * toasternotifikace
 */



(function(modules, $, undefined) {
	"use strict";

	var enabled = false;

	modules.initNotif = function(){
		if ("Notification" in window) {
			var permission = Notification.permission;

			if (permission === "denied") {
				return;
			} else if (permission === "granted") {
				enabled = true;
				return;
			}

    		Notification.requestPermission().then(function() {
    			enabled = true;
    		});
  		}
	}

	modules.notif = function(body, icon, title, link, duration){
		if(!enabled){
			return;
		}

		link = link || 0;
		duration = duration || 5000;

		var options = {
			body: body,
			icon: icon
		};

		var n = new Notification(title, options);

		if (link) {
			n.onclick = function () {
				window.open(link);
			};
		}

		setTimeout(n.close.bind(n), duration);
	}
})(estetica = window.estetica || {}, window.jQuery);