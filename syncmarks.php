<?php
/**
 * Roundcube Bookmarks Plugin
 *
 * @version 2.0.3
 * @author Offerel
 * @copyright Copyright (c) 2018, Offerel
 * @license GNU General Public License, version 3
 */
class syncmarks extends rcube_plugin
{
	public $task = '?(?!login|logout).*';
	
	public function init() {
		$rcmail = rcmail::get_instance();
		
		$this->add_texts('localization/', true);
		$this->register_task('syncmarks');
		$this->include_stylesheet($this->local_skin_path().'/plugin.min.css');
		$this->include_script('plugin.min.js');
		
		$this->add_button(array(
			'label'	=> 'syncmarks.bookmarks',
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
		$bmfile = str_replace("%u", $rcmail->user->get_username(), $rcmail->config->get('bookmarks_path', false).$rcmail->config->get('bookmarks_filename', false));
		$del_url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST);
		$format = rcube_utils::get_input_value('_format', rcube_utils::INPUT_POST);
		
		if($format == "html") {
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
				$bookmarks = parse_bookmarks($bookmarks, time(), $this->gettext('bookmarks_new'));

				$rcmail->output->command('syncmarks/urladded', array('message' => "Bookmark deleted", 'data' => $bookmarks));
			}
		}
		elseif($format == "json") {
			$path = $rcmail->config->get('bookmarks_path', false);
			$filename = $rcmail->config->get('bookmarks_filename', false);
			$url = $path."/".$filename;
			$context = stream_context_create(array ('http' => array ('header' => 'Authorization: Basic '.base64_encode($rcmail->user->get_username().":".$rcmail->get_user_password()))));
			$bookmarks = file_get_contents($url, false, $context);
			
			$offset = mb_strlen($bookmarks)-mb_strrpos($bookmarks, $del_url);
			$startPos = mb_strrpos($bookmarks, ",{", -$offset);
			$endPos = mb_strpos($bookmarks, "}", $startPos + 1)+1;
			
			$nBookmarks = mb_substr($bookmarks,0,$startPos).mb_substr($bookmarks,$endPos);
			
			$tempfile = tmpfile();
			fwrite($tempfile,$nBookmarks);
			$fstat = fstat($tempfile);
			$url = $path."/".$filename;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_PUT, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$fields = array("id" => 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
			fseek($tempfile,0);
			curl_setopt($ch, CURLOPT_INFILE, $tempfile);
			curl_setopt($ch, CURLOPT_INFILESIZE, $fstat['size']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: '.$fstat['size']));
			curl_setopt($ch, CURLOPT_USERPWD, $rcmail->user->get_username().":".$rcmail->get_user_password());  
			$response = curl_exec($ch);
			fclose($tempfile);
			
			$cmarks = parseJSONMarks($nBookmarks, time(), $this->gettext('bookmarks_new'));
			$rcmail->output->command('syncmarks/urladded', array('message' => "Bookmark deleted", 'data' => $cmarks));
		}
	}
	
	function add_url() {
		$rcmail = rcmail::get_instance();
		$this->load_config();
		$new_url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST);
		$format = rcube_utils::get_input_value('_format', rcube_utils::INPUT_POST);
		$path = $rcmail->config->get('bookmarks_path', false);
		$filename = $rcmail->config->get('bookmarks_filename', false);
		
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
		
		if($format == 'html') {
			$bmfile = str_replace("%u", $rcmail->user->get_username(), $rcmail->config->get('bookmarks_path', false).$rcmail->config->get('bookmarks_filename', false));

			if(file_exists($bmfile)) {
				$bookmarks = file_get_contents($bmfile);
				$end = strrpos($bookmarks, "</DL><p>");

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
					$bookmarks = parse_bookmarks($bookmarks, time(), $this->gettext('bookmarks_new'));
					
					$rcmail->output->command('syncmarks/urladded', array('message' => 'URL is added.','data' => $bookmarks));
				}
				else {
					$rcmail->output->command('syncmarks/urladded', array('message' => "Error. Cant add URL"));
				}
			}
		}
		elseif($format == 'json') {
			$url = $path."/".$filename;
			$context = stream_context_create(array ('http' => array ('header' => 'Authorization: Basic '.base64_encode($rcmail->user->get_username().":".$rcmail->get_user_password()))));
			$bookmarks = file_get_contents($url, false, $context);
			$offset = strlen($bookmarks)-mb_strrpos($bookmarks, 'unfiled_____');
			$indexStart = mb_strrpos($bookmarks, "index", -$offset)+7;
			$index = mb_substr($bookmarks,$indexStart,mb_strpos($bookmarks, ",", $indexStart)-$indexStart);

			$bookmark_str = ",{\"id\":\"".substr(str_shuffle(md5(time())),0,12)."\",\"title\":\"$title\",\"index\":".++$index.",\"dateAdded\":".round(microtime(true) * 1000).",\"type\":\"bookmark\",\"url\":\"$new_url\",\"parentId\":\"unfiled_____\"}";
			
			$unfiled_last = '"parentId":"unfiled_____"}';
			$marker = mb_strrpos($bookmarks, $unfiled_last) + mb_strlen($unfiled_last);
			$bookmarks = mb_substr($bookmarks,0,$marker).$bookmark_str.mb_substr($bookmarks,$marker);
			
			$tempfile = tmpfile();
			fwrite($tempfile,$bookmarks);
			$fstat = fstat($tempfile);
			$url = $path."/".$filename;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_PUT, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$fields = array("id" => 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
			fseek($tempfile,0);
			curl_setopt($ch, CURLOPT_INFILE, $tempfile);
			curl_setopt($ch, CURLOPT_INFILESIZE, $fstat['size']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: '.$fstat['size']));
			curl_setopt($ch, CURLOPT_USERPWD, $rcmail->user->get_username().":".$rcmail->get_user_password());  
			$response = curl_exec($ch);
			fclose($tempfile);
			
			$cmarks = parseJSONMarks($bookmarks, time(), $this->gettext('bookmarks_new'));
			$rcmail->output->command('syncmarks/urladded', array('message' => 'URL is added.','data' => $cmarks));
		}
	}

	function add_bookmarks($args) {
		$rcmail = rcmail::get_instance();
		$this->load_config();
		$path = $rcmail->config->get('bookmarks_path', false);
		$filename = $rcmail->config->get('bookmarks_filename', false);
		$username = $rcmail->user->get_username();
		$password = $rcmail->get_user_password();
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		if($ext === "json") {
			$url = $path."/".$filename;
			$context = stream_context_create(array ('http' => array ('header' => 'Authorization: Basic ' . base64_encode("$username:$password"))));
			$bookmarks = file_get_contents($url, false, $context);
			foreach ($http_response_header as &$value) {
				if (strpos($value, 'ast-Modified') != 0) {
					$modified = strtotime(substr($value, 15));
					break;
				}
			}
			$bookmarks = parseJSONMarks($bookmarks,$modified, $this->gettext('bookmarks_new'));
			$rcmail->output->add_footer("<div id=\"bookmarkpane\">".$bookmarks."</div>");
			return $args;
		}
		elseif($ext === "html") {
			$bmfile = str_replace("%u", $username, $path.$filename);
			if(file_exists($bmfile)) {
				$bookmarks = file_get_contents($bmfile);
				$bookmarks = parseHTMLMarks($bookmarks, filemtime($bmfile), $this->gettext('bookmarks_new'));
				$rcmail->output->add_footer("<div id=\"bookmarkpane\">".$bookmarks."</div>");
				return $args;
			}
			else {
				return false;
			}
		}
		elseif($ext === "php") {
			$remote_url = $path.$filename;
			$opts = array('http'=>array(
				'method'=>"GET",
				'header' => "Authorization: Basic ".base64_encode("$username:$password")                 
				)
			);
			$context = stream_context_create($opts);
			$bms = htmlspecialchars(file_get_contents($remote_url, false, $context));
			$rcmail->output->add_footer("<div id=\"bookmarkpane\"><iframe id='bmframe' srcdoc=\"$bms\"></iframe>");
			return $args;
		}
	}
	
	function bookmarks_cmd() {
		$this->include_script('plugin.min.js');
	}
}

function parseJSONMarks($bookmarks, $bdate, $button) {
	$jsonMarks = json_decode($bookmarks, true);
	$bookmarks = "";
	$bookmarks = makeHTMLTree($jsonMarks);
	
	do {
		$start = strpos($bookmarks,"%ID");
		$end = strpos($bookmarks,"\n",$start);
		$len = $end - $start;
		$bookmarks = substr_replace($bookmarks, "", $start, $len);
	} while (strpos($bookmarks,"%ID") > 0);
	
	$bookmarks = str_replace("<li><label for=\"\"></label><input id=\"\" type=\"checkbox\"><ol>","",$bookmarks);
	$bookmarks = substr($bookmarks,2,strlen($bookmarks)-12);
	$bookmarks = "<div id=\"bheader\" >Date: ".date("d.m.Y H:i:s", $bdate)."</div><div id=\"bookmarks\">".$bookmarks."\n</div><div id=\"add\" onclick=\"jadd_url();\">".$button."</div>";
	return $bookmarks;
}

function makeHTMLTree($arr) {
	static $bookmarks = "";
	
	if(is_array($arr) && array_key_exists("url", $arr)) {
		$bookmark = "\t<li class=\"file\"><a title=\"".$arr['title']."\" oncontextmenu=\"j_del(event, this);\" target=\"_blank\" href=\"".$arr['url']."\">".$arr['title']."</a></li>\n%ID".$arr['parentId'];
		$bookmarks = str_replace("%ID".$arr['parentId'], $bookmark, $bookmarks);
	}
	elseif(is_array($arr) && !array_key_exists("url", $arr) && $arr['id'] != "") {
		$nFolder = "<li><label for=\"".$arr['title']."\">".$arr['title']."</label><input id=\"".$arr['title']."\" type=\"checkbox\"><ol>\n%ID".$arr['id']."\n</ol></li>";
		$start = strpos($bookmarks, "%ID".$arr['parentId']);
		if($start > 0) {
			$nFolder = "\t".$nFolder."\n%ID".$arr['parentId'];
			$bookmarks = str_replace("%ID".$arr['parentId'], $nFolder, $bookmarks);
		}
		else {
			$bookmarks.= $nFolder;
		}
	}
	
	if(is_array($arr)) {
    foreach($arr as $k => $v) {
        makeHTMLTree($v);
    }
}
	return $bookmarks;
}

function parseHTMLMarks($bookmarks, $bdate, $button) {
	$rcmail = rcmail::get_instance();
	$bookmarks = preg_replace("/<DD>[^>]*?</i", "<", $bookmarks);
	$bookmarks = preg_replace("/<DT><H3 [^>]*? PERSONAL_TOOLBAR_FOLDER=\"true\">(.+?)<\/H3>/is", "</ol><H1>$1</H1>", $bookmarks);
	$bookmarks = preg_replace("/<DT><H3 [^>]*? UNFILED_BOOKMARKS_FOLDER=\"true\">(.+?)<\/H3>/is", "<H1>$1</H1>", $bookmarks);
	$bookmarks = preg_replace("/<H1>(.+?)<\/H1>/is", "<li>\n<label for=\"$1\">$1</label><input type=\"checkbox\" id=\"$1\">", $bookmarks);
	$bookmarks = preg_replace("/<DT><H3\s(.+?)>(.+?)<\/H3>/is", "<li><label for=\"$2\">$2</label><input type=\"checkbox\" id=\"$2\">", $bookmarks);
	$bookmarks = str_replace("<DT><A HREF=","<li class=\"file\"><A onContextMenu=\"h_del(event, this);\" target='_blank' HREF=",$bookmarks);
	$bookmarks = str_replace("</A>","</A></li>",$bookmarks);
	$bookmarks = preg_replace("/<A (.+?)>(.+?)<\/A>/is", "<a title=\"$2\" $1>$2</a>", $bookmarks);
	$bookmarks = str_replace("<DL><p>","<ol>",$bookmarks);
	$bookmarks = str_replace("</DL><p>","</ol>",$bookmarks);
	$bookmarks = str_replace("</DL>","</ol>",$bookmarks);
	$bookmarks = "<div id=\"bheader\" >Date: ".date("d.m.Y H:i:s", $bdate)."</div><div id=\"bookmarks\">".$bookmarks."</div><div id=\"add\" onclick=\"add_url();\">".$button."</div>";
	
	return $bookmarks;
}
?>