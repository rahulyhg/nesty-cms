<? html_header("Create");

$sql_temp = "SELECT COUNT(page_id) AS count FROM $database.pages";
$count_pages = $connection_pdo->prepare($sql_temp);
$count_pages->execute();
$result = $count_pages->fetchAll();
foreach ($result as $row):
	print_r($row);
	endforeach;

$sql_temp = "SELECT COUNT(entry_id) AS count FROM $database.entries";
$count_entries = $connection_pdo->prepare($sql_temp);
$count_entries->execute();
$result = $count_entries->fetchAll();
foreach ($result as $row):
	print_r($row);
	endforeach;

echo "<div id='create-window'>";
echo "<a href='/new/'><div id='create-window-new-page-button' class='background_1'>new page</div></a>";
echo "<a href='/create/'><div id='create-window-add-entry-button' class='background_2'>add entry</div></a>";
echo "<a href='/'><div id='create-window-home-button'>Home</div></a>";
echo "</div>";
footer(); ?>
