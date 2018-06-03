<?php
/**
 * Roundcube Bookmarks Plugin
 *
 * @version 1.1.0
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
		$this->register_task('ffbookmarks');
		$this->include_stylesheet($this->local_skin_path().'/plugin.css');
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
		
		$this->register_action('add_url', array($this, 'add_url'));
		$this->register_action('del_url', array($this, 'del_url'));
	}
	
	function del_url() {
		$rcmail = rcmail::get_instance();
		$this->load_config();
		$new_url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST);
		$bmfile = str_replace("%u", $rcmail->user->get_username(), $rcmail->config->get('bookmarks_path', false).$rcmail->config->get('bookmarks_filename', false));
		$del_url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST);
		
		if(file_exists($bmfile)) {
			$bookmarks = file_get_contents($bmfile);
			$start = strrpos($bookmarks, $del_url) - 13;
			$line_end = strpos($bookmarks,"\n",$start);
			$new_line = strpos($bookmarks,"<",$line_end);
			
			switch(substr($bookmarks,$new_line,3)) {
				case "<DD": $end = strpos($bookmarks,"\n",$new_line); break;
				case "<DT": $end = strpos($bookmarks,"\n",$start); break;
				default: $end = strpos($bookmarks,"\n",$start); break;
			}
			
			$length = $end - $start;
			$entry = substr($bookmarks,$start,$length);
			$bookmarks = str_replace($entry,"",$bookmarks);
			
			file_put_contents($bmfile, $bookmarks);
			
			$rcmail->output->command('ffbookmarks/urladded', array('message' => "Bookmark deleted"));
		}
	}
	
	function add_url() {
		$rcmail = rcmail::get_instance();
		$this->load_config();
		$new_url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST);
		$bmfile = str_replace("%u", $rcmail->user->get_username(), $rcmail->config->get('bookmarks_path', false).$rcmail->config->get('bookmarks_filename', false));

		if(file_exists($bmfile)) {
			$bookmarks = file_get_contents($bmfile);
			$end = strrpos($bookmarks, "</DL><p>");
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $new_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
			curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);

			$data = curl_exec($ch);		
			curl_close($ch);

			$doc = new DOMDocument();
			@$doc->loadHTML($data);
			$nodes = $doc->getElementsByTagName('title');
			$title = $nodes->item(0)->nodeValue;
			
			if(strlen($title) > 0) {
				$date = time();
				$meta_arr = get_meta_tags($new_url);
				
				if(strlen($meta_arr['keywords']) > 0){
					$new_tags = "TAGS=\"".str_replace(" ", ",", $meta_arr['keywords'])."\"";
				}
				else
					$new_tags = "";
				
				$new_bookmark = "\t<DT><A HREF=\"".$new_url."\" ADD_DATE=\"$date\" LAST_MODIFIED=\"$date\" LAST_CHARSET=\"UTF-8\" $new_tags>".trim($title)."</A>";
				
				if(strlen($meta_arr['description']) > 0){
					$new_bookmark = utf8_encode($new_bookmark."\n\t\t<DD>".$meta_arr['description']."\n\t");
				}
				
				$bookmarks = substr_replace($bookmarks, $new_bookmark, $end, 0);
				file_put_contents($bmfile, $bookmarks);
				$rcmail->output->command('ffbookmarks/urladded', array('message' => 'URL is added.'));
			}
			else {
				$rcmail->output->command('ffbookmarks/urladded', array('message' => "Error. Cant add URL"));
			}
		}
		
		
	}
	
	public function get_content($URL){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $URL);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
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
			$bookmarks = str_replace("<DT><A HREF=","<li class=\"file\"><A onContextMenu=\"b_menu(event, this);\" target='_blank' HREF=",$bookmarks);
			$bookmarks = str_replace("</A>","</A></li>",$bookmarks);
			$bookmarks = preg_replace("/<A (.+?)>(.+?)<\/A>/is", "<a title=\"$2\" $1>$2</a>", $bookmarks);
			$bookmarks = str_replace("<DL><p>","<ol>",$bookmarks);
			$bookmarks = str_replace("</DL><p>","</ol>",$bookmarks);
			$bookmarks = str_replace("</DL>","</ol>",$bookmarks);		
			$bookmarks.= "<span style=\"margin-left: 10px; font-size: 0.8em; color: rgba(190, 190, 190, 1.0); font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; \">Date: " . date ("d.m.Y H:i:s.", filemtime($bmfile)) . "</span>";
			$rcmail->output->add_footer("<div id=\"bookmarkpane\"><div id=\"bookmarks\">".$bookmarks."</div><div id=\"add\" onclick=\"add_url();\">".$this->gettext('bookmarks_new').$name."</div></div>");
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