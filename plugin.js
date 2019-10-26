/**
 * Roundcube Bookmarks Plugin
 *
 * @version 2.1.2
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
		rcmail.http_post("syncmarks/get_bookmarks", "_url=2")
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

function get_bookmarks(response) {
	bookmarks = JSON.parse(response.data);

	if(response.message == 'php') {
		$('#bmframe').attr('srcdoc',bookmarks);
		document.getElementById("bookmarkpane").style.width = "300px";
	}
	else {
		$('#bookmarkpane').html(bookmarks);
		document.getElementById("bookmarkpane").style.width = "300px";
	}
}

window.rcmail && rcmail.addEventListener("init", function(t) {}), $(document).ready(function() {
    $("#7f3f3c06-5b85-4e7f-b527-d061478e9446").on("click", bookmarks_cmd), document.getElementById("bookmarkpane").addEventListener("click", function(t) {
        "A" == t.target.tagName && bookmarks_cmd()
    })
})