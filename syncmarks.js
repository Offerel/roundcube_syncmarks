/**
 * Roundcube Bookmarks Plugin
 *
 * @version 2.2.5
 * @author Offerel
 * @copyright Copyright (c) 2021, Offerel
 * @license GNU General Public License, version 3
 */
function h_del(t, o, format) {
    t.preventDefault();
	var e = rcmail.gettext("bookmarks_del", "syncmarks").replace("%b%", o.innerHTML);
    if (1 == confirm(e)) {
		let r = encodeURIComponent(o.href);
		let i = o.attributes['bid']['value'];
        rcmail.http_post("syncmarks/del_url", "_url=" + r + "&_format=" + format + "&_bid=" + i)
    }
}

function bookmarks_cmd() {
	if(document.getElementById("bookmarkpane").clientWidth != "300") {
		let dv = document.createElement("div");
		dv.classList.add("db-spinner");
		dv.id = "db-spinner";
		document.getElementById("layout").parentNode.appendChild(dv);
		rcmail.http_post("syncmarks/get_bookmarks", "_url=2")
	}
	else {
		document.getElementById("bookmarkpane").style.width = "0";
	}
}

function add_url(format) {
	var t = encodeURIComponent(prompt(rcmail.gettext("bookmarks_url", "syncmarks")));
	console.log(format);
    0 < t.length && (t.startsWith("http") || t.startsWith("ftp")) && rcmail.http_post("syncmarks/add_url", "_url=" + t + "&_format=" + format)
}

function url_removed(response) {
	console.log(response.message);
	document.getElementById("bookmarkpane").style.width = "0";
}

function urladded(t) {
    console.log(t.message), 0 < t.data.length && $("#bookmarkpane").html(t.data)
}

function get_bookmarks(response) {
	bookmarks = JSON.parse(response.data);
	$('#bookmarkpane').html(bookmarks);
	document.getElementById("bookmarkpane").style.width = "300px";
	let node = document.getElementById("db-spinner");
	setTimeout(function() {
		if (node.parentNode) {
			node.parentNode.removeChild(node);
		}
	}, 400);
}

function en_noti(elem) {
	if(elem.checked) {
		if (!("Notification" in window)) {
			alert("This browser does not support desktop notification");
		}
		else if (Notification.permission === "granted") {
			var notification = new Notification("Syncmarks", {
				body: "Notifications will be enabled for Syncmarks.",
				icon: './plugins/syncmarks/bookmarks.png'
			});
		}
		else if (Notification.permission !== "denied") {
			Notification.requestPermission().then(function (permission) {
				if (permission === "granted") {
					var notification = new Notification("Syncmarks", {
						body: "Notifications will be enabled for Syncmarks.",
						icon: './plugins/syncmarks/bookmarks.png'
					});
				}
			});
		}
	}
}

function get_notifications(response) {
	let notifications = JSON.parse(response);
	notifications.forEach(function(notification){
		show_noti(notification);
	});
}

function show_noti(noti) {
	if (Notification.permission !== 'granted')
		Notification.requestPermission();
	else {
		let notification = new Notification(noti.title, {
			body: noti.url,
			icon: './plugins/syncmarks/bookmarks.png',
			requireInteraction: true
		});
		
		notification.onclick = function() {
			window.open(noti.url);
			rcmail.http_post("syncmarks/del_not", '&_nkey=' + noti.nkey);
		};
	}
}

window.rcmail && rcmail.addEventListener("init", function(t) {}), $(document).ready(function() {
    $("#7f3f3c06-5b85-4e7f-b527-d061478e9446").on("click", bookmarks_cmd), document.getElementById("bookmarkpane").addEventListener("click", function(t) {
        "A" == t.target.tagName && bookmarks_cmd()
	})
	
})

rcmail.addEventListener('plugin.sendNotifications', get_notifications);