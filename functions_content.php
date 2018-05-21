<? // prepare for nesty_page
$sql_temp = "SELECT * FROM $database.pages WHERE page_id=:page_id";
$retrieve_page = $connection_pdo->prepare($sql_temp);

// prepare for nesty_media
$sql_temp = "SELECT * FROM $database.media WHERE media_id=:media_id";
$retrieve_media = $connection_pdo->prepare($sql_temp);

// prepare for nesty_entry
$sql_temp = "SELECT * FROM $database.entries WHERE entry_id=:entry_id";
$retrieve_entry = $connection_pdo->prepare($sql_temp);



function nesty_page($page_id_temp) {
	global $domain;
	global $publisher;
	global $login;
	
	global $connection_pdo;
	global $retrieve_page;
	global $retrieve_media;
	global $retrieve_entry;


	if (empty($page_id_temp)): return null; endif;
	$domain_temp = $domain;
	if (strpos($page_id_temp, "|")):
		$domain_page_id_temp = explode("|", $page_id_temp);
		if (strpos($domain_page_id_temp[0], ".")): $domain_temp = $domain_page_id_temp[0]; $page_id_temp = $domain_page_id_temp[1];
		else: $domain_temp = $domain_page_id_temp[1]; $page_id_temp = $domain_page_id_temp[0]; endif; endif;
	$page_info = [];
	if (empty($domain_temp) || ($domain == $domain_temp)):
		$retrieve_page->execute(["page_id"=>(string)$page_id_temp]);
		$result = $retrieve_page->fetchAll();
		foreach ($result as $row):
			$slug_temp = null; if (!(empty($row['slug']))): $slug_temp = $row['slug']."/"; endif;
			$page_info[$row['page_id']] = [
				"page_id" => $row['page_id'],
				"slug" => $row['slug'],
				"created_time" => $row['created_time'],
				"updated_time" => $row['updated_time'],
				"header" => $row['header'],
				"domain" => $domain,
				"publisher" => $publisher,
				"link" => "https://".$domain_temp."/".$row['page_id']."/".$slug_temp ];
			endforeach;
	else:
		$page_info = file_get_contents("https://".$domain_temp."/".(string)$page_id_temp."/ping/"); // check if the page exists
		$page_info = json_decode($page_info, true); // decode the json
		endif;
	if (empty($page_info[$page_id_temp])): return null; endif;
	return $page_info; }



function nesty_media($media_id_temp, $response_temp="full") {
	global $domain;
	global $publisher;
	global $login;
	
	global $connection_pdo;
	global $retrieve_page;
	global $retrieve_media;
	global $retrieve_entry;


	if (empty($media_id_temp)): return null; endif;
	$domain_temp = $domain;
	if (strpos($media_id_temp, "|")):
		$domain_media_id_temp = explode("|", $media_id_temp);
		if (strpos($domain_media_id_temp[0], ".")): $domain_temp = $domain_media_id_temp[0]; $media_id_temp = $domain_media_id_temp[1];
		else: $domain_temp = $domain_media_id_temp[1]; $media_id_temp = $domain_media_id_temp[0]; endif; endif;	
	if (empty($domain_temp) || ($domain == $domain_temp)):

		$retrieve_media->execute(["media_id"=>utf8_encode($media_id_temp)]);
	
		$result = $retrieve_media->fetchAll();
		
		foreach ($result as $row):
		
			$description_temp = $width_temp = $height_temp = $type_temp = $attr_temp = null;

			if ($response_temp == "full"):
				$description_temp = $row['description'];
				// convert all images to links
				preg_match_all("/(?<=\[\[\[)(.*?)(?=\]\]\])/is", $description_temp, $matches_temp);
				if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
				foreach ($matches_temp[0] as $temp): $description_temp = str_replace("[[[".$temp."]]]", "{{{".str_replace("][", "}{", $temp)."}}}", $description_temp); endforeach;
				$description_temp = body_process($description_temp);
				endif;

			// check if file exists and height and width
			$thumb_url = "https://".$domain_temp."/media/".$row['directory']."/".$row['filename_thumb'];
			list($width_temp, $height_temp, $type_temp, $attr_temp) = getimagesize($thumb_url);
			if (empty($width_temp)): continue; endif;
	
			$media_info[$row['media_id']] = [
				"media_id"=>$row['media_id'],
				"domain"=>$domain_temp,
				"publisher"=>$publisher,
				"link"=>"https://$domain/m/".$row['media_id']."/",
				"directory"=>$row['directory'],
				"description"=>$description_temp,
				"height"=>$height_temp, // provided by list function
				"width"=>$width_temp, // provided by list function
 				"type"=>$type_temp, // provided by list function
 				"attr"=>$attr_temp, // provided by list function
				"header"=>$row['datetime_original'],
				"datetime_original"=>$row['datetime_original'] ];
			endforeach;
	else:
		$media_info = file_get_contents("https://".$domain_temp."/m/".(string)$media_id_temp."/ping/"); // check if the media exists
		$media_info = json_decode($media_info, true); // decode the json
		endif;
	if (empty($media_info[$media_id_temp])): return null; endif;
	return $media_info; }



function nesty_entry($entry_id_temp) {
	global $domain;
	global $publisher;
	global $login;
	
	global $connection_pdo;
	global $retrieve_page;
	global $retrieve_media;
	global $retrieve_entry;


	if (empty($entry_id_temp)): return null; endif;
	if (strpos($entry_id_temp, "|")):
		$domain_entry_id_temp = explode("|", $entry_id_temp);
		if (strpos($domain_entry_id_temp[0], ".")): $domain_temp = $domain_entry_id_temp[0]; $entry_id_temp = $domain_entry_id_temp[1];
		else: $domain_temp = $domain_entry_id_temp[1]; $entry_id_temp = $domain_entry_id_temp[0]; endif; endif;
	if (empty($domain_temp) || ($domain == $domain_temp)):
		$entry_confirmed = [];
		$retrieve_entry->execute(["entry_id"=>(string)$entry_id_temp]);
		$result = $retrieve_entry->fetchAll();
		foreach ($result as $row):

			// convert all images to links
//			preg_match_all("/(?<=\[\[\[)(.*?)(?=\]\]\])/is", $row['body'], $matches_temp);
//			if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
//			foreach ($matches_temp[0] as $temp): $row['body'] = str_replace("[[[".$temp."]]]", "{{{".str_replace("][", "}{", $temp)."}}}", $row['body']); endforeach;

			// convert all citations to links
			preg_match_all("/(?<=\(\(\()(.*?)(?=\)\)\))/is", $row['body'], $matches_temp);
			if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
			foreach ($matches_temp[0] as $temp): $row['body'] = str_replace("(((".$temp.")))", "{{{".str_replace(")(", "}{", $temp)."}}}", $row['body']); endforeach;

			$entry_confirmed[$row['entry_id']] = [
				"entry_id"=>$row['entry_id'],
				"domain"=>$domain,
				"publisher"=>$publisher,
				"name"=>$row['name'],
				"year"=>$row['year'],
				"month"=>$row['month'],
				"day"=>$row['day'],
				"body"=> body_process($row['body']) ];				
			endforeach;
	else:
		$entry_info = file_get_contents("https://$domain/e/".(string)$entry_id_temp."/ping/"); // check if the media exists
		$entry_confirmed = json_decode($entry_info, true); // decode the json
		endif;
	if (empty($entry_confirmed[$entry_id_temp])): return null; endif;
	return $entry_confirmed; }



function body_process($body_incoming) {
	global $domain;
	global $publisher;
	global $login;
	
	global $connection_pdo;
	global $retrieve_page;
	global $retrieve_media;
	global $retrieve_entry;
	
	
	$body_incoming = str_replace("\r", "\n", $body_incoming);
	
	$delimiter = "\n\n";

	$body_incoming = $delimiter.$body_incoming.$delimiter;
	
	$body_incoming = str_replace($delimiter."|||***", $delimiter."<table><thead><tr><th>", $body_incoming);
	$body_incoming = str_replace("\n|||***", "</th><th>", $body_incoming);
	$body_incoming = str_replace("|||***", $delimiter."<table><thead><tr><th>", $body_incoming);
	$body_incoming = str_replace($delimiter."---\n---".$delimiter."***", "</th></tr></thead><tbody>\n<tr><td>".$delimiter, $body_incoming);
	$body_incoming = str_replace($delimiter."---\n---", $delimiter."</td></tr></tbody></table>".$delimiter, $body_incoming);
	$body_incoming = str_replace($delimiter."---".$delimiter."***", $delimiter."</td></tr>\n<tr><td>".$delimiter, $body_incoming);
	$body_incoming = str_replace("\n***", $delimiter."</td><td>".$delimiter, $body_incoming);
	$body_incoming = str_replace("<blockquote>", $delimiter."<blockquote>".$delimiter, $body_incoming);
	$body_incoming = str_replace("</blockquote>", $delimiter."</blockquote>".$delimiter, $body_incoming);

	$image_lightbox_array = [];
	
	// process links first
	$matches = [];
	preg_match_all("/(?<=\{\{\{)(.*?)(?=\}\}\})/is", $body_incoming, $matches);
//	preg_match_all("/(?<=\{\{\{)(.+)(?=\}\}\})/is", $body_incoming, $matches); // too greedy
	if (empty($matches)): $matches = [ [], [] ]; endif;
	$matches = array_unique($matches[0]);
	foreach ($matches as $match_temp):

		$link_string = $link_type = null;
	
		$temp_array = explode("}{", $match_temp."}{");

		if (strpos($temp_array[0], "_") !== FALSE): $link_info = nesty_media($temp_array[0], "short");
		else: $link_info = nesty_page($temp_array[0]); endif; // check if the page exists
	
		$link_id_temp = $temp_array[0];
		if (strpos($temp_array[0], "|")):
			$domain_id_temp = explode("|", $temp_array[0]);
			if (strpos($domain_id_temp[0], ".")): $link_id_temp = $domain_id_temp[1];
			else: $link_id_temp = $domain_id_temp[0]; endif;
			endif;

		if (in_array($temp_array[1], ["button", "tile", "link"])): $link_type = $temp_array[1]; unset($temp_array[1]);
		elseif (in_array($temp_array[2], ["button", "tile", "link"])): $link_type = $temp_array[2]; unset($temp_array[2]); endif;

		if (!(empty($temp_array[1]))): $link_string = $temp_array[1];
		elseif (!(empty($temp_array[2]))): $link_string = $temp_array[2];
		elseif (!(empty($link_info[$link_id_temp]['header']))): $link_string = $link_info[$link_id_temp]['header']; endif;

		if (empty($link_info[$link_id_temp])):
			$body_incoming = str_replace("{{{".$match_temp."}}}", $link_string, $body_incoming);
			continue; endif; // page id does not exist so skip it

		if (empty($link_string)): $link_string = "<i class='material-icons'>link</i>"; endif;
	
		if ($link_type == "button"): $link_type = "tile"; endif;
	
		// remove all images inside links
		preg_match_all("/(?<=\[\[\[)(.*?)(?=\]\]\])/is", $link_string, $matches_temp);
		if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
		foreach ($matches_temp[0] as $temp): $link_string = str_replace("[[[".$temp."]]]", null, $link_string); endforeach;

		// remove all citations inside links
		preg_match_all("/(?<=\(\(\()(.*?)(?=\)\)\))/is", $link_string, $matches_temp);
		if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
		foreach ($matches_temp[0] as $temp): $link_string = str_replace("(((".$temp.")))", null, $link_string); endforeach;
	
		if ($link_type == "tile"): $link_string = "<div class='tile'>".$link_string."<div class='background_".rand(1,10)."'>Read more</div></div>"; endif;
		$link_string = "<a href='".$link_info[$link_id_temp]['link']."'>".$link_string."</a>";
	
		$body_incoming = str_replace("{{{".$match_temp."}}}", $link_string, $body_incoming);
	
		endforeach;
	
	// process media next
	$matches = [];
	preg_match_all("/(?<=\[\[\[)(.*?)(?=\]\]\])/is", $body_incoming, $matches);
	if (empty($matches)): $matches = [ [], [] ]; endif;
	$matches = array_unique($matches[0]);	
	foreach ($matches as $match_temp):

		$image_string = $filename_size = $file_description = null;

		$temp_array = explode("][", $match_temp."][");
		if (empty(temp_array[1])): $temp_array[1] = null; endif;
		if (empty(temp_array[2])): $temp_array[2] = null; endif;

		$media_info = nesty_media($temp_array[0]);

		$media_id_temp = $temp_array[0];
		if (strpos($temp_array[0], "|")):
			$domain_id_temp = explode("|", $temp_array[0]);
			if (strpos($domain_id_temp[0], ".")): $media_id_temp = $domain_id_temp[1];
			else: $media_id_temp = $domain_id_temp[0]; endif;
			endif;

		if (empty($media_info[$media_id_temp])):
			$body_incoming = str_replace("[[[".$match_temp."]]]", null, $body_incoming);
			continue; endif; // media id does not exist so skip it
		
		if (in_array($temp_array[1], ["full", "large", "thumb"])): $filename_size = $temp_array[1]; unset($temp_array[1]);
		elseif (in_array($temp_array[2], ["full", "large", "thumb"])): $filename_size = $temp_array[2]; unset($temp_array[2]); endif;

		if (!(empty($temp_array[1]))): $file_description = $temp_array[1];
		elseif (!(empty($temp_array[2]))): $file_description = $temp_array[2];
		elseif (!(empty($media_info[$media_id_temp]['description']))): $file_description = $media_info[$media_id_temp]['description']; endif;
	
		// convert all images to links
		preg_match_all("/(?<=\[\[\[)(.*?)(?=\]\]\])/is", $file_description, $matches_temp);
		if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
		foreach ($matches_temp[0] as $temp): $file_description = str_replace("[[[".$temp."]]]", "{{{".str_replace("][", "}{", $temp)."}}}", $file_description); endforeach;

		// remove all citations inside images
		preg_match_all("/(?<=\(\(\()(.*?)(?=\)\)\))/is", $file_description, $matches_temp);
		if (empty($matches_temp)): $matches_temp = [ [], [] ]; endif;
		foreach ($matches_temp[0] as $temp): $file_description = str_replace("(((".$temp.")))", null, $file_description); endforeach;
	
		$file_description = body_process($file_description);
	
		$img_height = 240;
		$img_width = round(240*$media_info[$media_id_temp]['width']/$media_info[$media_id_temp]['height']);
		$img_height_large = round(2.5*$img_height);
		$img_width_large = round(2.5*$img_width);

		if ($filename_size == "full"):
			$image_string = "<a href='".$media_info[$media_id_temp]['link']."' on='tap:lightbox".$media_id_temp."' role='button' tabindex='1'>view image</a>";
		elseif ($filename_size == "large"):
			$image_string = "<div class='image_large'><figure>";
			$image_string .= "<amp-img on='tap:lightbox".$media_id_temp."' src='".$media_info[$media_id_temp]['link']."large/' width='".$img_width_large."px' height='".$img_height_large."px' role='button' tabindex='1' sizes='(min-width: 1100px) 1000px, (min-width: 500px) 90vw, 90vw'>";	
			$image_string .= "<a href='".$media_info[$media_id_temp]['link']."' target='_blank'><div class='image_open_link material-icons'>link</div></a>";
			$image_stirng .= "</amp-img>";
			if (!(empty($file_description))): $image_string .= "<amp-fit-text width='".($img_width_large)."px' height='30px' min-font-size='14px' max-font-size='14px'>".mb_substr(strip_tags(str_replace(["</th>", "</td>", "</div>", "</p>", "<br>", "<br />"], ' ',$file_description)),0,200)."</amp-fit-text>"; endif;
			$image_string .= "</figure>";
			$image_string .= "</div>";
		else:
			$image_string = "<div class='image_thumbnail'><figure>";
			$image_string .= "<amp-img on='tap:lightbox".$media_id_temp."' src='".$media_info[$media_id_temp]['link']."thumb/' width='".$img_width."px' height='".$img_height."px' role='button' tabindex='1' sizes='(min-width: ".($img_width+100)."px) ".$img_width."px, 70vw'>";
			$image_string .= "<a href='".$media_info[$media_id_temp]['link']."' target='_blank'><div class='image_open_link material-icons'>link</div></a>";
			$image_string .= "</amp-img>";
			$image_string .= "<amp-fit-text width='".($img_width)."px' height='30px' min-font-size='14px' max-font-size='14px' sizes='(min-width: ".($img_width+100)."px) ".($img_width)."px, 70vw'>".mb_substr(strip_tags(str_replace(["</th>", "</td>", "</div>", "</p>", "<br>", "<br />"], ' ', $file_description)),0,200)."</amp-fit-text>";
			$image_string .= "</figure>";
			$image_string .= "</div>"; endif;
	
		$lightbox_temp = "<amp-lightbox id='lightbox".$media_id_temp."' layout='nodisplay'>";
		$lightbox_temp .= "<div class='image_large'><figure><amp-img src='".$media_info[$media_id_temp]['link']."large/' width='".$img_width_large."px' height='".$img_height_large."px' sizes='(min-width: 1000px) 900px, (min-width: 500px) 90vw, 450px'></amp-img>";
		$lightbox_temp .= "<br><figcaption>".$file_description."</figcaption></figure></div>";
		$lightbox_temp .= "<div class='lightbox-link'><a href='".$media_info[$media_id_temp]['link']."' target='_blank'><i class='material-icons'>open_in_new</i></a></div>";
		$lightbox_temp .= "<button class='lightbox-close' on='tap:lightbox".$media_id_temp.".close' tabindex='1' role='button'><i class='material-icons'>cancel</i></button>";
		$lightbox_temp .= "</amp-lightbox>";
		$image_lightbox_array[] = $lightbox_temp;
	
		$body_incoming = str_replace("[[[".$match_temp."]]]", $image_string, $body_incoming);

		endforeach;
	
	// process citations
	$matches = [];
	preg_match_all("/(?<=\(\(\()(.*?)(?=\)\)\))/is", $body_incoming, $matches);	
	if (empty($matches)): $matches = [ [], [] ]; endif;
	$matches = array_unique($matches[0]);
	foreach ($matches as $match_temp):

		$citation_strinng = null;
	
		$temp_array = explode(")(", $match_temp.")(");
	
		$entry_info = nesty_entry($temp_array[0]); // check if the entry exists
	
		$citation_id_temp = $temp_array[0];
		if (strpos($temp_array[0], "|")):
			$domain_id_temp = explode("|", $temp_array[0]);
			if (strpos($domain_id_temp[0], ".")): $citation_id_temp = $domain_id_temp[1];
			else: $citation_id_temp = $domain_id_temp[0]; endif;
			endif;
	
		if (empty($entry_info[$citation_id_temp])):
			$body_incoming = str_replace("(((".$match_temp.")))", null, $body_incoming);
			continue; endif; // entry id does not exist so skip it

		$citation_string = null;
		if (!(empty($entry_info[$citation_id_temp]['name'])) || !(empty($login))):
			if (!(empty($entry_info[$citation_id_temp]['name']))):
				$citation_string[] = $entry_info[$citation_id_temp]['name']; endif;
			if (!(empty(login)) && ($domain == $entry_info[$citation_id_temp]['domain'])):
				$citation_string[] = "via ".$entry_info[$citation_id_temp]['publisher']." @<a href='/e/".$citation_id_temp."/edit/'>".$citation_id_temp."</a>"; endif;
			if ($domain !== $entry_info[$citation_id_temp]['domain']):
				$citation_string[] = "via <a href='https://".$entry_info[$citation_id_temp]['domain']."'>".$entry_info[$citation_id_temp]['publisher']."</a> @".$citation_id_temp; endif;
			$citation_string = "<cite>".implode("<br>", $citation_string)."</cite>";
			endif;
		$entry_string = $delimiter."<aside>".$citation_string."</aside>".$entry_info[$citation_id_temp]['body'].$delimiter;

		$body_incoming = str_replace("(((".$match_temp.")))", $entry_string, $body_incoming);

		endforeach;
		
	$skip_array = [
		"<blockquote", "blockquote>", "<iframe", "iframe>", "<div", "div>", "<hr", "<aside", "aside>", 
		"<table", "table>", "<thead", "thead>", "<tbody", "tbody>", "<tr", "tr>", "<td", "td>", "<th", "th>", 
		"<h1", "h1>", "<h2", "h2>", "<h3", "h3>", "<h4", "h4>", "<h5", "h5>", "<h6", "h6>",
		"<ul", "ul>", "<ol", "ol>", "<li", "li>", "<section", "section>", 
		"<amp-img", "amp-img>",
		"<amp-fit-text", "amp-fit-text>", "<amp-accordion", "amp-accordion>" ];
	
	$body_incoming = preg_replace('/<li(.*?)>/', $delimiter.'<li$1>'.$delimiter, $body_incoming);
	$body_incoming = preg_replace('/<ul(.*?)>/', $delimiter.'<ul$1>'.$delimiter, $body_incoming);
	$body_incoming = preg_replace('/<ol(.*?)>/', $delimiter.'<ol$1>'.$delimiter, $body_incoming);
	$body_incoming = str_replace("</li>", $delimiter."</li>".$delimiter, $body_incoming);
	$body_incoming = str_replace("</ul>", $delimiter."</ul>".$delimiter, $body_incoming);
	$body_incoming = str_replace("</ol>", $delimiter."</ol>".$delimiter, $body_incoming);

	$body_temp = explode($delimiter, $body_incoming);
	$body_incoming = $body_final = null;

	foreach($body_temp as $content_temp):
		$content_temp = trim($content_temp);
		if (ctype_space($content_temp)): continue; endif;
		if (empty($content_temp) && ($content_temp !== "0")): continue; endif;
		if (strpos("*".$content_temp, "///") == 1): continue; endif;
		foreach ($skip_array as $skip_temp):
			if (strpos("*".$content_temp, $skip_temp)):
				$body_final .= $content_temp;
				continue 2; endif;
			endforeach;
		$body_final .= "<p>".$content_temp."</p>";
		endforeach;
	$body_final .= implode(null, $image_lightbox_array);
	$body_final = str_replace("\n", "<br>", $body_final);
	$body_final = str_replace("><br>", ">", $body_final);
	
	return $body_final; }




function clip_length($content=null,$length=140,$ellipsis=null,$breaks=null) {
	if ($breaks == null): $content = str_replace(array("\r", "\n", "\r\n", "\v", "\t", "\0","\x"), " ", $content);
	else: $content = str_replace(array("\r\r", "\n\n", "\r\n"), "\r", $content); endif;
	$clip_length = mb_substr($content,0,$length,"utf-8");	
	if (strlen($clip_length) >= ($length-1) && (strrpos($clip_length, ' ') !== FALSE)): $clip_length = mb_substr($clip_length,0,strrpos($clip_length, ' ')); endif;
	if ( ($ellipsis == "ellipsis") && (strlen($content) >= ($length-1)) ): $clip_length .= "…"; endif;
	return $clip_length; }


function number_condense($n, $decimals=1) {
	$negative = null;
	if ($n < 0): $n = abs($n); $negative = "-"; endif;
	if (($n == 0) || ($n == null)): $n_format = "0"; $suffix_temp = null;
	elseif ($n < 1): $n_format = "0"; $suffix_temp = null;
	elseif ($n < 1000): $n_format = number_format($n); $suffix_temp = null;
	elseif ($n < 1000000): $n_format = number_format($n / 1000, $decimals); $suffix_temp = "k";
	elseif ($n < 1000000000): $n_format = number_format($n / 1000000, $decimals); $suffix_temp = "m";
	else: $n_format = number_format($n / 1000000000, $decimals); $suffix_temp = "b"; endif;
	if (strlen($n_format) - strripos($n_format,".0") == 2): $n_format = str_replace(".0", null, $n_format); endif;
	return $negative.$n_format.$suffix_temp; } ?>
