<? $retrieve_page->execute(["page_id"=>$page_temp]);
$result = $retrieve_page->fetchAll();
foreach ($result as $row):

	// if there is a password, it requires the user form which cannot be an amp page
	if (!(empty($row['password'])) && ( empty($_SESSION[$page_temp]) || ($_SESSION[$page_temp] !== $row['password']) ) ):
		html_header($page_confirmed[$page_temp]['header'], $domain.$proper_uri);
		echo "<div class='login'>";
		echo "<span>".$page_confirmed[$page_temp]['header']."</span>";
		echo "<form method='post' action=''>";
		echo "<input type='password' name='password[".$page_temp."]' placeholder='unlock page' autocomplete='off' required>";
		echo "<button type='submit' name='unlock' value='unlock'>unlock</button>";
		echo "</form></div>";
		if (!(empty($login))): echo "<a href='/".$page_temp."/edit/' class='material-icons button'>edit</a>"; endif;
		echo "<a href='/account/' class='material-icons button'>account_circle</a>";
		echo "<a href='/' class='material-icons button'>home</a>";
		echo "</body></html>";
		footer(); endif;

	$page_confirmed[$page_temp]['body'] = body_process($row['body']);
//	$page_confirmed[$page_temp]['studies'] = body_process($row['studies']);

	endforeach;

amp_header($page_confirmed[$page_temp]['header'], $domain.$proper_uri);
admin_bar($login,$page_confirmed[$page_temp]);


$parents = $siblings_temp = $siblings = $children = $gallery = $citations = [];
$sql_temp = "SELECT * FROM $database.paths";
$result = fetchall($sql_temp);
foreach ($result as $row):
	if ($row['type'] == "page"):
		if ($row['parent_id'] == $page_temp): $children[] = $row['child_id']; endif;
		if ($row['child_id'] == $page_temp): $parents[] = $row['parent_id']; endif;
		if (empty($siblings_temp[$row['parent_id']])): $siblings_temp[$row['parent_id']] = []; endif;
		$siblings_temp[$row['parent_id']][] = $row['child_id'];
		endif;
	if ($row['parent_id'] !== $page_temp): continue; endif;
	if ($row['type'] == "media"): $gallery[] = $row['child_id']; endif;
	if ($row['type'] == "entry"): $citations[] = $row['child_id']; endif; endforeach;
$sql_temp = "SELECT page_id, header FROM $database.pages ORDER BY header ASC";
$result = fetchall($sql_temp);
$pages_array = [];
foreach ($result as $row): $pages_array[$row['page_id']] = $row; endforeach;
if (!(empty($login))): $href_temp = "https://$domain/".$page_temp."/".$page_confirmed[$page_temp]['slug'];
else: $href_temp = "https://$domain/".$page_temp."/".$page_confirmed[$page_temp]['slug']."/edit/"; endif;

$parents = array_intersect(array_keys($pages_array), $parents);
$children = array_intersect(array_keys($pages_array), $children);

if (!(empty($children)) || !(empty($parents))):

	if (!(empty($parents))):
		echo "<div class='genealogy_map'>";
		$plural_temp = null; if (count($parents) > 1): $plural_temp = "s"; endif;
		echo "<i>parent".$plural_temp."</i>";
		foreach ($parents as $parent_id):
			echo "<span><a href='/$parent_id/'>".$pages_array[$parent_id]['header']."</a></span>";
			if (!(empty($siblings_temp[$parent_id]))): $siblings = array_merge($siblings, $siblings_temp[$parent_id]); endif;
			endforeach;
		echo "</div>";
		endif;
	$siblings = array_intersect(array_keys($pages_array), $siblings);
	$siblings = array_diff($siblings, [$page_temp]);

	if (!(empty($siblings))):
		echo "<div class='genealogy_map'>";
		$plural_temp = null; if (count($siblings) > 1): $plural_temp = "s"; endif;
		echo "<i>sibling".$plural_temp."</i>";
		foreach ($siblings as $sibling_id):
			if ($sibling_id == $page_temp): continue; endif;
			echo "<span><a href='/$sibling_id/'>".$pages_array[$sibling_id]['header']."</a></span>";
			endforeach;
		echo "</div>";
		endif;

	if (!(empty($children))):
		echo "<div class='genealogy_map'>";
		$plural_temp = null; if (count($children) > 1): $plural_temp = "s"; endif;
		echo "<i>subpage".$plural_temp."</i>";
		foreach ($children as $child_id):
			echo "<span><a href='/$child_id/'>".$pages_array[$child_id]['header']."</a></span>";
			endforeach;
		echo "</div>";
		endif;
	endif;

if (!(empty($citations))):
	$page_confirmed[$page_temp]['body'] .= "\n\n<hr>\n\n";
	foreach($citations as $entry_id):
		$page_confirmed[$page_temp]['body'] .= "\n\n(((".$entry_id.")))";
		endforeach;
	endif;



if (!(empty($page_confirmed[$page_temp]['body'])) || !(empty($gallery))):

	echo "<article><div vocab='http://schema.org/' typeof='Article'>";

	echo "<header><h1 property='name'>".$page_confirmed[$page_temp]['header']."</h1></header>";

//	echo "<span property='headline'><h6>".$page_confirmed[$page_temp]['headline']."</h6></span>";
	echo "<span property='articleBody'>";
	if (!(empty($page_confirmed[$page_temp]['body']))):
		echo $page_confirmed[$page_temp]['body'];
		endif;
	if (!(empty($gallery))):
		echo "<hr>";
		$gallery_array = [];
		foreach ($gallery as $media_id):
			$media_info = nesty_media($media_id);
			$key_temp = strtotime($media_info[$media_id]['datetime_original'])."_".random_code(5);
			$gallery_array[$key_temp] = $media_id;
			endforeach;
		ksort($gallery_array);
		foreach($gallery_array as $media_id):
			echo body_process("[[[".$media_id."]]]");
			endforeach;
		endif;
	echo "</span>";

	echo "<footer>";
	echo "<div>written by <span property='author'>Levi Clancy</span></div>";
	echo "<div>for <span property='publisher'>$publisher</span></div><br>";
	echo "<div>published <time datetime='".$page_confirmed[$page_temp]['created_time']."' property='datePublished'>".date("l jS F, o", strtotime($page_confirmed[$page_temp]['created_time']))."</time></div>";
	if ($page_confirmed[$page_temp]['created_time'] !== $page_confirmed[$page_temp]['updated_time']):
		echo "<br><div>updated <time datetime='".$page_confirmed[$page_temp]['updated_time']."' property='dateModified'>".date("jS F, o", strtotime($page_confirmed[$page_temp]['updated_time']))."</time></div>";
		endif;
	echo "</footer>";

	echo "</div></article>";

	endif;

footer(); ?>
