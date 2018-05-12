<? amp_header($domain);
admin_bar($login,"home");

$content_array = [];

$sql_temp = "SELECT page_id, created_time, header FROM $database.pages ORDER BY created_time DESC LIMIT 2";
foreach($connection_pdo->query($sql_temp) as $row):
	echo body_process("{{{".$row['page_id']."}{".$row['header']."}{tile}}}");
	endforeach;

$sql_temp = "SELECT media_id FROM $database.media ORDER BY datetime_original DESC LIMIT 5"; // datetime_process is alternative method of sorting
foreach($connection_pdo->query($sql_temp) as $row):
	echo body_process("[[[".$row['media_id']."][large]]]");
	endforeach;

echo "<a href='/schedule/'>view more</a>";

footer(); ?>
