/**
 * Roundcube Firefox Bookmarks Plugin
 *
 * @version 1.1.0
 * @author Offerel
 * @copyright Copyright (c) 2018, Offerel
 * @license GNU General Public License, version 3
 */
 window.rcmail && rcmail.addEventListener('init', function(evt) {
	//rcmail.register_command('plugin.ffbookmarks.add_url', add_url, true);
});

$(document).ready(function() {
	$('#7f3f3c06-5b85-4e7f-b527-d061478e9446').on("click", bookmarks_cmd);
	
	document.getElementById('bookmarkpane').addEventListener('click', function(e){
		if(e.target.tagName=="A"){
			bookmarks_cmd();
		}
	})
});

function b_menu(event, bookmark) {
	event.preventDefault();
	var question = rcmail.gettext('bookmarks_del', 'ffbookmarks').replace('%b%', bookmark.innerHTML);
	if(confirm(question) == true) {
		var url = encodeURIComponent(bookmark.href);
		rcmail.http_post('ffbookmarks/del_url', '_url=' + url);
	}
} 

function bookmarks_cmd() {
	if(document.getElementById('bookmarkpane').clientWidth != "300") {
		document.getElementById('bookmarkpane').style.width = "300px";
	} else {
		document.getElementById('bookmarkpane').style.width = "0";
	}
}

function add_url() {
	var url = encodeURIComponent(prompt(rcmail.gettext('bookmarks_url', 'ffbookmarks')));
    if (url.length > 0) {
		if(url.startsWith("http") || url.startsWith("ftp"))
			rcmail.http_post('ffbookmarks/add_url', '_url=' + url);
    }
}

function urladded(response) {
	location.reload();
	console.log(response.message);
}