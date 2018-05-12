<? amp_header($domain);
admin_bar($login,"home");

echo "<h1>".$publisher."</h1>";

if (!(empty($description))):
	echo body_process($description);
	endif;

$count_temp = 0;
$sql_temp = "SELECT page_id, created_time, header FROM $database.pages ORDER BY created_time DESC LIMIT 3";
foreach($connection_pdo->query($sql_temp) as $row):
	if ($count_temp == 0): echo "<h2>Recent posts</h2>"; $count_temp++; endif;
	echo body_process("{{{".$row['page_id']."}{".$row['header']."}{tile}}}");
	endforeach;

$count_temp = 0;
$sql_temp = "SELECT media_id FROM $database.media ORDER BY datetime_original DESC LIMIT 3"; // datetime_process is alternative method of sorting
foreach($connection_pdo->query($sql_temp) as $row):
	if ($count_temp == 0): echo "<h2>Latest images</h2>"; $count_temp++; endif;
	echo body_process("[[[".$row['media_id']."][large]]]");
	endforeach;

echo "<a href='/schedule/'>view more</a>";

footer(); ?>
