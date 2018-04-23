<?php
/**
 * Roundcube Bookmarks Plugin
 *
 * @version 1.0.2
 * @author Offerel
 * @copyright Copyright (c) 2018, Offerel
 * @license GNU General Public License, version 3
 */
class ffbookmarks extends rcube_plugin
{
	public $task = '?(?!login|logout).*';
	
	public function init() {
		$rcmail = rcmail::get_instance();
		
		$this->add_texts('localization/', true);
		$this->register_task('bookmarks');
		$this->include_stylesheet($this->local_skin_path() . '/plugin.css');
		$this->include_script('plugin.js');
		
		$this->add_button(array(
			'label'	=> 'ffbookmarks.bookmarks',
			'command'	=> 'bookmarks_cmd',
			'id'		=> '7f3f3c06-5b85-4e7f-b527-d061478e9446',
			'class'		=> 'button-bookmarks',
			'classsel'	=> 'button-bookmarks button-bookmarks',
			'innerclass'=> 'button-inner',
			'type'		=> 'link'
		), 'taskbar');
		
		$this->add_hook('render_page', array($this, 'add_bookmarks'));
	}

	function add_bookmarks($args) {
		$rcmail = rcmail::get_instance();
		$this->load_config();		
		$bmfile = str_replace("%u", $rcmail->user->get_username(), $rcmail->config->get('bookmarks_path', false).$rcmail->config->get('bookmarks_filename', false));
		if(file_exists($bmfile)) {
			$bookmarks = file_get_contents($bmfile);
			$bookmarks = preg_replace("/<DD>[^>]*?</i", "<", $bookmarks);
			$bookmarks = preg_replace("/<DT><H3 [^>]*? PERSONAL_TOOLBAR_FOLDER=\"true\">(.+?)<\/H3>/is", "</ol><H1>$1</H1>", $bookmarks);
			$bookmarks = preg_replace("/<DT><H3 [^>]*? UNFILED_BOOKMARKS_FOLDER=\"true\">(.+?)<\/H3>/is", "<H1>$1</H1>", $bookmarks);
			$bookmarks = preg_replace("/<H1>(.+?)<\/H1>/is", "<li>\n<label for=\"$1\">$1</label><input type=\"checkbox\" id=\"$1\">", $bookmarks);
			$bookmarks = preg_replace("/<DT><H3\s(.+?)>(.+?)<\/H3>/is", "<li><label for=\"$2\">$2</label><input type=\"checkbox\" id=\"$2\">", $bookmarks);
			$bookmarks = str_replace("<DT><A HREF=","<li class=\"file\"><A target='_blank' HREF=",$bookmarks);
			$bookmarks = str_replace("</A>","</A></li>",$bookmarks);
			$bookmarks = preg_replace("/<A (.+?)>(.+?)<\/A>/is", "<a title=\"$2\" $1>$2</a>", $bookmarks);
			$bookmarks = str_replace("<DL><p>","<ol>",$bookmarks);
			$bookmarks = str_replace("</DL><p>","</ol>",$bookmarks);
			$bookmarks = str_replace("</DL>","</ol>",$bookmarks);		
			$bookmarks.= "<span style=\"margin-left: 10px; font-size: 0.8em; color: rgba(190, 190, 190, 1.0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; \">Date: " . date ("d.m.Y H:i:s.", filemtime($bmfile)) . "</span>";
			$rcmail->output->add_footer("<div id=\"bookmarks\">".$bookmarks."</div>");
			return $args;
		}
		else {
			return false;
		}
	}
	
	function bookmarks_cmd() {
		$this->include_script('plugin.js');
	}
}
?>