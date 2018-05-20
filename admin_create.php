<? html_header("Create");

$sql_temp = "SELECT COUNT(page_id) AS count FROM $database.pages";
$count_pages = $connection_pdo->prepare($sql_temp);
$count_pages->execute();
$result = $count_pages->fetchAll();
foreach ($result as $row):
	$count_pages = $row['count'];
	endforeach;

$sql_temp = "SELECT COUNT(entry_id) AS count FROM $database.entries";
$count_entries = $connection_pdo->prepare($sql_temp);
$count_entries->execute();
$result = $count_entries->fetchAll();
foreach ($result as $row):
	$count_entries = $row['count'];
	endforeach;

echo "<div id='create-window'>";

echo "<a href='/'><div id='create-window-home-button'>Home</div></a>";

echo "<a href='/new/'><div id='create-window-new-page-button' class='background_1'>new page</div></a>";
if (empty($count_pages)): echo "<span>There are no pages.</span>";
elseif ($count_pages == 1): echo "<span>There is one page.</span>";
else: echo "<span>There are ".number_format($count_pages)." pages.<span>"; endif;

echo "<a href='/add/'><div id='create-window-add-entry-button' class='background_2'>add entry</div></a>";
if (empty($count_entries)): echo "<span>There are no entries.</span>";
elseif ($count_entries == 1): echo "<span>There is one entry.</span>";
else: echo "<span>There are ".number_format($count_entries)." entries.<span>"; endif;

echo "</div>";

footer(); ?>
