/**
 * Roundcube Firefox Bookmarks Plugin
 *
 * @version 1.1.0
 * @author Offerel
 * @copyright Copyright (c) 2018, Offerel
 * @license GNU General Public License, version 3
 */
 window.rcmail && rcmail.addEventListener('init', function(evt) {
	rcmail.register_command('plugin.ffbookmarks.add_url', add_url, true);
});

$(document).ready(function() {
	$('#7f3f3c06-5b85-4e7f-b527-d061478e9446').on("click", bookmarks_cmd);
	
	document.getElementById('bookmarkpane').addEventListener('click', function(e){
		if(e.target.tagName=="A"){
			bookmarks_cmd();
		}
	})
});

function bookmarks_cmd() {
	if(document.getElementById('bookmarkpane').clientWidth != "300") {
		document.getElementById('bookmarkpane').style.width = "300px";
	} else {
		document.getElementById('bookmarkpane').style.width = "0";
	}
}

function add_url() {
	var url = prompt(rcmail.gettext('bookmarks_url', 'ffbookmarks'));
    //if (url.length > 0 && url.startsWith("http")) {
	if (url.length > 0) {
		//rcmail.addEventListener('plugin.somecallback', some_callback_function);
		rcmail.http_post('ffbookmarks/add_url', '_url=' + url);
    }
}

function urladded(response)
{
	location.reload();
	console.log(response.message);
}