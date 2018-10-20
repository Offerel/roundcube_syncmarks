/**
 * Roundcube Bookmarks Plugin
 *
 * @version 2.0.3
 * @author Offerel
 * @copyright Copyright (c) 2018, Offerel
 * @license GNU General Public License, version 3
 */
function h_del(t, o) {
    t.preventDefault();
    var e = rcmail.gettext("bookmarks_del", "ffbookmarks").replace("%b%", o.innerHTML);
    if (1 == confirm(e)) {
        var r = encodeURIComponent(o.href);
        rcmail.http_post("ffbookmarks/del_url", "_url=" + r + "&_format=html")
    }
}

function j_del(t, o) {
    t.preventDefault();
    var e = rcmail.gettext("bookmarks_del", "ffbookmarks").replace("%b%", o.innerHTML);
    if (1 == confirm(e)) {
        var r = encodeURIComponent(o.href);
        rcmail.http_post("ffbookmarks/del_url", "_url=" + r + "&_format=json")
    }
}

function bookmarks_cmd() {
    "300" != document.getElementById("bookmarkpane").clientWidth ? document.getElementById("bookmarkpane").style.width = "300px" : document.getElementById("bookmarkpane").style.width = "0"
}

function add_url() {
    var t = encodeURIComponent(prompt(rcmail.gettext("bookmarks_url", "ffbookmarks")));
    0 < t.length && (t.startsWith("http") || t.startsWith("ftp")) && rcmail.http_post("ffbookmarks/add_url", "_url=" + t + "&_format=html")
}

function jadd_url() {
    var t = encodeURIComponent(prompt(rcmail.gettext("bookmarks_url", "ffbookmarks")));
    0 < t.length && (t.startsWith("http") || t.startsWith("ftp")) && rcmail.http_post("ffbookmarks/add_url", "_url=" + t + "&_format=json")
}

function urladded(t) {
    console.log(t.message), 0 < t.data.length && $("#bookmarkpane").html(t.data)
}
window.rcmail && rcmail.addEventListener("init", function(t) {}), $(document).ready(function() {
    $("#7f3f3c06-5b85-4e7f-b527-d061478e9446").on("click", bookmarks_cmd), document.getElementById("bookmarkpane").addEventListener("click", function(t) {
        "A" == t.target.tagName && bookmarks_cmd()
    })
})