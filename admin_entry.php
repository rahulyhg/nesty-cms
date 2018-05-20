<? if ($page_temp == "add"): html_header("new", "+ entry");
else: html_header($entry_confirmed['name'], $domain."/edit/"); endif;

$sql_temp = "SELECT page_id, header, slug FROM $database.pages ORDER BY header ASC, slug ASC";
$retrieve_pages = $connection_pdo->prepare($sql_temp);
$retrieve_pages->execute();
$result = $retrieve_pages->fetchAll();
foreach ($result as $row):
	$pages_array[$row['page_id']] = $row; endforeach;

if (empty($pages_array)):
	echo "<span class='warning'>must have at least one page for parentage</span>";
	footer(); endif;

$entry_temp = $slug_temp;

if (isset($_POST['entry_edit'])):

	$entry_temp = $_POST['entry_id'];

	$body_text = preg_replace("/[\r]/", "\n", $_POST['body']);
	$body_text = str_replace("[[[", "\n\n[[[", $body_text);
	$body_text = str_replace("]]]", "]]]\n\n", $body_text);
	$body_text = preg_replace("/[\s][\s][\s]+/", "\n\n", $body_text);
	$array_temp[$key_temp] = htmlspecialchars(trim($body_text));
	if (ctype_space($body_text)): $body_text = null; endif;

	$values_temp = [
		"entry_id"=>$_POST['entry_id'],
		"updated_time"=>date("Y-m-d"), 
		"name"=>$_POST['name'], 
		"year"=>$_POST['year'], 
		"month"=>$_POST['month'], 
		"day"=>$_POST['day'], 
		"created_time"=>$_POST['created_time'], 
		"body"=>$body_text ];
	$sql_temp = sql_setup($values_temp, "$database.entries");
	$update_entry = $connection_pdo->prepare($sql_temp);
	$update_entry->execute($values_temp);
	execute_checkup($update_entry->errorInfo(), "updating entry content okay");
	if (empty($_POST['parents'])): $_POST['parents'] = []; endif;

	// delete from where child_id = entry_id
	$sql_temp = "DELETE FROM $database.paths WHERE child_id=:entry_id";
	$paths_delete_statement = $connection_pdo->prepare($sql_temp);
	$paths_delete_statement->execute(["entry_id"=>$_POST['entry_id']]);
	execute_checkup($paths_delete_statement->errorInfo(), "clearing old paths");
	$values_temp = [
		"path_id"=>null,
		"parent_id"=>null,
		"child_id"=>null ];
	$sql_temp = sql_setup($values_temp, "$database.paths");
	$paths_insert_statement = $connection_pdo->prepare($sql_temp);
	foreach($_POST['parents'] as $page_id_temp):
		$values_temp = [
			"path_id"=>random_code(7),
			"parent_id"=>$page_id_temp,
			"child_id"=>$_POST['entry_id'] ];
		$paths_insert_statement->execute($values_temp);
		execute_checkup($paths_insert_statement->errorInfo(), "inserting parent paths");
		endforeach;
	endif;

$retrieve_entry->execute(["entry_id"=>$entry_id_temp]);
$result = $retrieve_entry->fetchAll();
foreach ($result as $row): $entry_confirmed = $row; endforeach;

if (empty($entry_confirmed)):
	$entry_confirmed = ["entry_id"=>random_code(10), "name"=>null, "created_time"=>date("Y-m-d"), "body"=>null];
else:
	$entry_confirmed = $entry_confirmed[$slug_temp]; endif;


$entry_confirmed['parents'] = [];
$sql_temp = "SELECT * FROM $database.paths WHERE child_id=:entry_id";
$retrieve_paths = $connection_pdo->prepare($sql_temp);
$retrieve_paths->execute(["entry_id"=>$entry_confirmed['entry_id']]);
$result = $retrieve_paths->fetchAll();
foreach ($result as $row):
	$entry_confirmed['parents'][]= $row['parent_id']; endforeach;

$entry_confirmed['citations'] = [];
$sql_temp = "SELECT page_id FROM $database.pages WHERE body LIKE :entry_id";
$retrieve_page_citations = $connection_pdo->prepare($sql_temp);
$retrieve_page_citations->execute(["entry_id"=>"$".$entry_confirmed['entry_id']."%"]);
$result = $retrieve_page_citations->fetchAll();
foreach ($result as $row):
	$entry_confirmed['citations'][] = $row['page_id']; endforeach;

echo "<style> td { padding: 0 10px; } .header { position: absolute !important; } </style>";
echo "<style> optgroup { font-weight: 400; padding: 10px 10px 0 10px; font-style: italic !important; color: #bbb !important; } </style>";
echo "<style> optgroup option { font-style: normal; color: #333; } optgroup option:first-child { margin-top: 5px; } </style>";

echo "<form action='/e/".$entry_confirmed['entry_id']."/edit/' method='post'>";
echo "<input type='hidden' name='entry_id' value='".$entry_confirmed['entry_id']."'>";

echo "<div id='content_edit' style='width: 1000px; text-align: left; vertical-align: top; padding: 15px; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 0 30px -2px rgba(150,150,150,0.45); background: #fff; display: block; margin: 20px auto 0;'>";

echo "<style> option { width: 360px;  } option:checked {  } </style>";

echo "<select name='parents[]' class='parent_select' style='display: inline-block !important; width: 400px !important; margin: 0 !important; float: right; border-width: 0 0 0 2px; border-style: style; border-color: #eee; vertical-align: top;' multiple required>";
$parents_confirmed = $citations_confirmed = [];
foreach($pages_array as $page_id => $page_info): 
	if (in_array($page_id,$entry_confirmed['parents'])): $parents_confirmed[] = $page_id; endif;
	if (in_array($page_id,$entry_confirmed['citations'])): $citations_confirmed[] = $page_id; endif;
	endforeach;
if (!(empty($citations_confirmed))):
	echo "<optgroup label='citations'>";
	foreach($parents_confirmed as $page_id):
		echo "<option disabled>".$pages_array[$page_id]['header']." (".$pages_array[$page_id]['slug'].")</option>";
		endforeach;
	echo "</optgroup>"; endif;
if (!(empty($parents_confirmed))):
	echo "<optgroup label='selected'>";
	foreach($parents_confirmed as $page_id):
		echo "<option value='$page_id' selected>".$pages_array[$page_id]['header']." (".$pages_array[$page_id]['slug'].")</option>";
		endforeach;
	echo "</optgroup>"; endif;
foreach($pages_array as $page_id => $page_info):
	if (in_array($page_id,$parents_confirmed)): continue; endif;
	echo "<option value='$page_id'>".$page_info['header']." (".$page_info['slug'].")</option>";
	endforeach;
echo "</select>";

echo "<input type='text' name='name' value='".htmlspecialchars($entry_confirmed['name'], ENT_QUOTES)."' placeholder='name' style='margin: 0; padding: 5px 0 15px; border: 0; width: 430px; text-align: left; display: inline-block;'>";
echo "<input type='number' name='year' value='".$entry_confirmed['year']."' placeholder='yyyy' style='margin: 0 0 10px 10px; padding: 0 0 5px; border: 0; border-bottom: 1px solid #bbb; width: 50px; text-align: center; display: inline-block; border-radius: 0;'>";
echo "<input type='number' name='month' value='".$entry_confirmed['month']."' min='1' max='12' placeholder='mm' style='margin: 0 0 10px 10px; padding: 0 0 5px; border: 0; border-bottom: 1px solid #bbb; width: 40px; text-align: center; display: inline-block; border-radius: 0;'>";
echo "<input type='number' name='day' value='".$entry_confirmed['day']."' placeholder='dd' min='1' max='31' style='margin: 0 0 10px 10px; padding: 0 0 5px; border: 0; border-bottom: 1px solid #bbb; width: 35px; text-align: center; display: inline-block; border-radius: 0;'>";
echo "<hr style='height: 2px; background: #ccc; margin: 0 !important; padding: 0; width: 585px; display: block;'>";
echo "<textarea id='textarea_body' name='body' style='width: 570px; margin: 15px 0 0 0; padding: 0 10px 0 0; background: none; display: inline-block; border: 0; border-radius: 0; overflow-y: yes;' required>".$entry_confirmed['body']."</textarea>";

echo "</div>";

if (empty($entry_confirmed['created_time'])): $entry_confirmed['created_time'] = date("Y-m-d"); endif;
echo "<input type='hidden' name='created_time' value='".$entry_confirmed['created_time']."'>";

echo "<div class='bottom_bar'><span class='button float_left'></span><button type='submit' name='entry_edit' value='save' class='material-icons'>save</button>";
echo "<span class='button float_right'></span><a href='/' class='material-icons button float_right'>home</a>";
echo "<a href='/account/' class='material-icons button float_right'>account_circle</a>";
if ($page_temp !== "add"):
	echo "<a href='/new/' class='material-icons button float_right'>note_add</a>";
	echo "<a href='/add/' class='material-icons button float_right'>playlist_add</a>";
	echo "<a href='/".$entry_confirmed['entry_id']."/delete/' class='material-icons button float_right'>delete</a>";
	endif;
echo "</div>";

echo "<script>"; ?>
	$('#content_edit').height($(window).height() - 125);
	$('#textarea_body').height($(window).height() - 185);
	$('.parent_select').height($(window).height() - 122);
	window.onresize = function(event) {
		$('#content_edit').height($(window).height() - 125);
		$('#textarea_body').height($(window).height() - 185);
		$('.parent_select').height($(window).height() - 122) }
<? echo "</script>";

echo "</form>";
footer(); ?>
