<?php
include ("db.php");

$data = json_decode($_POST['matrix']);

//if the user did not insert a valid ID, it generates a random ID
if (empty($idPerson)) {
	$idPerson = rand(100000,999999);
	$result = $mysqli->query($sql);
	while ($mysqli->query("SELECT idsurvey FROM survey WHERE idperson = " . $idPerson)->num_rows > 0) {
	   $idPerson = rand(100000,999999);
	}
}

//insert the data of the survey into the database
$insert_row = $mysqli->query("INSERT INTO survey (idperson) VALUES(".$idPerson.")");

if($insert_row){
    $idSurvey = $mysqli->insert_id ;
}else{
    echo "Error to save the survey. Please, try again.";
    exit;
}

//prepare the data of the iat before save it
$values_mysql = "";
$array_csv = array();
$array_csv[] = array("Survey ID", "Seq Number", "Response Time", "Item", "Category", "Error","Block");
$seq_number = 1;
foreach ($data as $key => $block) {
	for ($i=0; $i < count($block[0]); $i++) {
		$item = $block[0][$i];
		$responseTime = $block[1][$i];
		$error = $block[2][$i];
		$category = $block[3][$i];

		//create the array to save the data into the csv file
		$array_csv[] = array(1, $seq_number, $responseTime, $item, $category, $error, ($key+1));

		//create the string to do the insert into the database
		if ($seq_number == 1) {
			$values_mysql .= "(" . $idSurvey . "," . $seq_number . "," . $responseTime . ",'" . $item . "','"  . $category . "'," . $error . "," . ($key + 1) . ")" ;
		} else {
			$values_mysql .= ",(" . $idSurvey . "," . $seq_number . "," . $responseTime . ",'" . $item . "','"  . $category . "'," . $error . "," . ($key + 1) . ")" ;
		}
		
		$seq_number++;
	}
}

//insert the data of the iat into the database
if (!$mysqli->query("INSERT INTO trials (idsurvey, trial_seq, response_time, item, category, error, block) VALUES ".$values_mysql."")) {
    echo "Multi-INSERT failed: (" . $mysqli->errno . ") " . $mysqli->error;
} else {
	echo "Success! The data was saved in the database and in the csv file with the name ".$idSurvey.".";
}
$mysqli->close();

//insert the data of the iat into the csv file
$fp = fopen('report_csv/'.$idSurvey.'.csv', 'w');
foreach ($array_csv as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);
exit;
?>