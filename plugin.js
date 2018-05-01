/**
 * Roundcube Firefox Bookmarks Plugin
 *
 * @version 1.0.3
 * @author Offerel
 * @copyright Copyright (c) 2018, Offerel
 * @license GNU General Public License, version 3
 */
$(document).ready(function() {
	$('#7f3f3c06-5b85-4e7f-b527-d061478e9446').on("click", bookmarks_cmd);
	
	document.getElementById('bookmarks').addEventListener('click', function(e){
		if(e.target.tagName=="A"){
			bookmarks_cmd();
		}
	})
});

function bookmarks_cmd() {
	if(document.getElementById('bookmarks').clientWidth != "300") {
		document.getElementById('bookmarks').style.width = "300px";
		document.getElementById('bookmarks').style.border = "thin solid #b2b8bf";
	} else {
		document.getElementById('bookmarks').style.width = "0";
		document.getElementById('bookmarks').style.border = "0";
	}
}