/*
window.rcmail && rcmail.addEventListener('init', function(evt) {
	rcmail.register_command('plugin.bookmarks_cmd', bookmarks_cmd, true);
});
*/
$(document).ready(function() {
	document.getElementById('7f3f3c06-5b85-4e7f-b527-d061478e9446').addEventListener("click", bookmarks_cmd);
	
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