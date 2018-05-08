<? amp_header($domain);
admin_bar($login,"home");

$content_array = [];

$sql_temp = "SELECT page_id, created_time, header FROM $database.pages ORDER BY created_time DESC LIMIT 10";
foreach($connection_pdo->query($sql_temp) as $row):
	$content_array[] = "{{{".$row['page_id']."}{".$row['header']."}{tile}}}";
	endforeach;

$sql_temp = "SELECT media_id FROM $database.media ORDER BY datetime_original DESC LIMIT 10"; // datetime_process is alternative method of sorting
foreach($connection_pdo->query($sql_temp) as $row):
	$content_array[] = "[[[".$row['media_id']."]]]";
	endforeach;

if (!(empty($content_array))):
	shuffle($content_array);
	$content_array = implode("\n\n", $content_array);
	echo body_process($content_array);
	endif;

footer(); ?>
