/**
 * Roundcube Bookmarks Plugin
 *
 * @version 2.1.0
 * @author Offerel
 * @copyright Copyright (c) 2019, Offerel
 * @license GNU General Public License, version 3
 */
function h_del(t, o) {
    t.preventDefault();
    var e = rcmail.gettext("bookmarks_del", "syncmarks").replace("%b%", o.innerHTML);
    if (1 == confirm(e)) {
        var r = encodeURIComponent(o.href);
        rcmail.http_post("syncmarks/del_url", "_url=" + r + "&_format=html")
    }
}

function j_del(t, o) {
    t.preventDefault();
    var e = rcmail.gettext("bookmarks_del", "syncmarks").replace("%b%", o.innerHTML);
    if (1 == confirm(e)) {
        var r = encodeURIComponent(o.href);
        rcmail.http_post("syncmarks/del_url", "_url=" + r + "&_format=json")
    }
}

function bookmarks_cmd() {
	if(document.getElementById("bookmarkpane").clientWidth != "300") {
		document.getElementById("bookmarkpane").style.width = "300px";
	}
	else {
		document.getElementById("bookmarkpane").style.width = "0";
	}
}

function add_url() {
    var t = encodeURIComponent(prompt(rcmail.gettext("bookmarks_url", "syncmarks")));
    0 < t.length && (t.startsWith("http") || t.startsWith("ftp")) && rcmail.http_post("syncmarks/add_url", "_url=" + t + "&_format=html")
}

function jadd_url() {
    var t = encodeURIComponent(prompt(rcmail.gettext("bookmarks_url", "syncmarks")));
    0 < t.length && (t.startsWith("http") || t.startsWith("ftp")) && rcmail.http_post("syncmarks/add_url", "_url=" + t + "&_format=json")
}

function urladded(t) {
    console.log(t.message), 0 < t.data.length && $("#bookmarkpane").html(t.data)
}
window.rcmail && rcmail.addEventListener("init", function(t) {}), $(document).ready(function() {
	/*
	if(document.getElementById("phppane")) {
		alert(rcmail.get_user_email());
	}
	*/
    $("#7f3f3c06-5b85-4e7f-b527-d061478e9446").on("click", bookmarks_cmd), document.getElementById("bookmarkpane").addEventListener("click", function(t) {
        "A" == t.target.tagName && bookmarks_cmd()
    })
})