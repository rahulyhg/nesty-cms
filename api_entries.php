<? $sql_temp = "SELECT * FROM $database.pages";
$retrieve_page = $connection_pdo->prepare($sql_temp);
$retrieve_page->execute();
$result = $retrieve_page->fetchAll();
foreach ($result as $row):

print_r($row); exit;
	$information_array[$row['page_id']] = [
	"entry_id" => $row['entry_id'],
	"page_id" => $row['page_id'],
	"link" => "https://".$domain."/".$row['entry_id']."/",
	"name" => json_decode($row['name'], true),
	"summary" => null ];
	endforeach;
    
 print_r(json_encode($information_array));

?>
