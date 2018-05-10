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



if (!(empty($citations))):
	$page_confirmed[$page_temp]['body'] .= "\n\n<hr>\n\n";
	foreach($citations as $entry_id):
		$page_confirmed[$page_temp]['body'] .= "\n\n(((".$entry_id.")))";
		endforeach;
	endif;


echo "<article><div vocab='http://schema.org/' typeof='Article'>";

echo "<header>";
echo "<h1 property='name' amp-fx='parallax' data-parallax-factor='1.3'>";
echo $page_confirmed[$page_temp]['header']."</h1></header>";

if (!(empty($page_confirmed[$page_temp]['body'])) || !(empty($gallery))):
	echo "<p amp-fx='parallax' data-parallax-factor='1.3' class='nesting-or-popover'>";
	echo "By <span property='author'>Levi Clancy</span> for <span property='publisher'>$publisher</span>";
	echo " on <time datetime='".$page_confirmed[$page_temp]['created_time']."' property='datePublished'>".date("l jS F, o", strtotime($page_confirmed[$page_temp]['created_time']))."</time>";
	if ($page_confirmed[$page_temp]['created_time'] !== $page_confirmed[$page_temp]['updated_time']):
		echo "<br><i>updated <time datetime='".$page_confirmed[$page_temp]['updated_time']."' property='dateModified'>".date("jS F, o", strtotime($page_confirmed[$page_temp]['updated_time']))."</time></i>";
		endif;
	echo "</p>";
	endif;

if (!(empty($children)) || !(empty($parents))):

	if (!(empty($page_confirmed[$page_temp]['body'])) || !(empty($gallery))):
		echo "<details amp-fx='parallax' data-parallax-factor='1.3' class='nesting-or-popover'>";
		echo "<summary class='summary-outline'>View nesting</summary>";
		endif;

	$parents = array_intersect(array_keys($pages_array), $parents);
	$children = array_intersect(array_keys($pages_array), $children);

	echo "<ul>";

	if (!(empty($parents))):
		$plural_temp = null; if (count($parents) > 1): $plural_temp = "s"; endif;
		echo "<li>Parent".$plural_temp."<ul>";
		foreach ($parents as $parent_id):
			if ($parent_id == $page_temp): continue; endif;
			echo "<li><a href='/$parent_id/'>".$pages_array[$parent_id]['header']."</a></li>";
			if (!(empty($siblings_temp[$parent_id]))): $siblings = array_merge($siblings, $siblings_temp[$parent_id]); endif;
			endforeach;
		echo "</ul></li>";
		$genealogy_map = array_merge($genealogy_map, $parents);
		endif;

	$siblings = array_intersect(array_keys($pages_array), $siblings);
	$siblings = array_diff($siblings, [$page_temp]);

	if (!(empty($siblings))):
		$plural_temp = null; if (count($siblings) > 1): $plural_temp = "s"; endif;
		echo "<li>Sibling".$plural_temp."<ul>";
		foreach ($siblings as $sibling_id):
			if ($sibling_id == $page_temp): continue; endif;
			echo "<li><a href='/$sibling_id/'>".$pages_array[$sibling_id]['header']."</a></li>";
			endforeach;
		echo "</ul></li>";
		$genealogy_map = array_merge($genealogy_map, $siblings);
		endif;

	if (!(empty($children))):
		$plural_temp = null; if (count($children) > 1): $plural_temp = "s"; endif;
		echo "<li>Subpage".$plural_temp."<ul>";
		foreach ($children as $child_id):
			if ($child_id == $page_temp): continue; endif;
			echo "<li><a href='/$child_id/'>".$pages_array[$child_id]['header']."</a></li>";
			endforeach;
		echo "</ul></li>";
		$genealogy_map = array_merge($genealogy_map, $children);
		endif;

	echo "</ul>";

	if (!(empty($page_confirmed[$page_temp]['body'])) || !(empty($gallery))):
		echo "</details>";
		endif;

	endif;


if (!(empty($page_confirmed[$page_temp]['body'])) || !(empty($gallery))):

	if (!(empty($page_confirmed['popover']))):
		echo "<details class='nesting-or-popover'>";
		echo "<summary>Show table of contents</summary>";
		echo $page_confirmed['popover'];
		echo "</details>";
		endif;

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

	endif;

echo "</div></article>";

if (!(empty($genealogy_map))):
	shuffle($genealogy_map);
	echo "<div class='genealogy_map' amp-fx='parallax' data-parallax-factor='1.5'>";
	echo "<i>Related Pages</i>";
	$count_temp = 0;
	foreach ($genealogy_map as $entry_id):
		if ($entry_id == $page_temp): continue; endif;
		echo "<span><a href='/$entry_id/'>".$pages_array[$entry_id]['header']."</a></span>";
		$count_temp++; if ($count_temp >= 4): break; endif;
		endforeach;
	echo "</div>";
	endif;

footer(); ?>
