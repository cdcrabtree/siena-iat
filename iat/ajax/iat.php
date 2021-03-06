<?php 
session_start();
include ("../db.php");

$data = json_decode($_POST['matrix']);
/*
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
*/
$idIat = $_SESSION['idIat'];
$idPerson = $_SESSION['idPerson'];

//prepare the data of the iat before save it
$values_mysql = "";
$array_csv = array();
$array_csv[] = array("Seq Number", "Trial Number", "Response Time", "Item", "Category", "Error","Block");
$seq_number = 1;
$trial_number = 1;
foreach ($data as $key => $block) {
	for ($i=0; $i < count($block[0]); $i++) {
		$item = $block[0][$i];
		$responseTime = $block[1][$i];
		$error = $block[2][$i];
		$category = $block[3][$i];

		//create the array to save the data into the csv file
		$array_csv[] = array($seq_number, $trial_number, $responseTime, $item, $category, $error, ($key+1));

		//create the string to do the insert into the database
		if ($seq_number == 1) {
			$values_mysql .= "(" . $idIat  . "," . $seq_number . "," . $trial_number . "," . $responseTime . ",'" . $item . "','"  . $category . "'," . $error . "," . ($key + 1) . ")" ;
		} else {
			$values_mysql .= ",(" . $idIat . "," . $seq_number . "," . $trial_number . "," . $responseTime . ",'" . $item . "','"  . $category . "'," . $error . "," . ($key + 1) . ")" ;
		}
		if ($error == 0) {
			$trial_number++;
		}
		$seq_number++;
	}
}

//insert the data of the iat into the database
if (!$mysqli->query("INSERT INTO trials (idiat, trial_seq, trial_number, response_time, item, category, error, block) VALUES ".$values_mysql."")) {
    echo "Multi-INSERT failed: (" . $mysqli->errno . ") " . $mysqli->error;
} else {
	echo "Success! The data was saved in the database and in the csv file. Do not forget to save your ID ".$idPerson.".";
}

//functions used for scoring algorithm

//finds average of given array
function findAvg($array){
	$size = count($array);

	$sum = 0;
	foreach($array as $val){
		$sum = $sum + $val;
	}

	$avg = $sum / $size;
	
	return $avg;
}

//finds the standard deviation of a given array
function findSD($array, $size){
	$mean = findAvg($array);

	$sum = 0;
	foreach($array as $val){
		$sub = $val - $mean;
		$sq = $sub * $sub;
		$sum = $sum + $sq;
	}

	$div = $sum / $size;

	$sD = sqrt($div);

	return $sD;
}

// find the pooled standard deviation given two SDs and the size of the groups
function findPooledSD($rtsNum1, $rtsNum2, $sD1, $sD2){
	$num1 = ($rtsNum1 - 1) * ($sD1 * $sD1);
	$num2 = ($rtsNum2 - 1) * ($sD2 * $sD2);
	$num3 = ($rtsNum1 + $rtsNum2) - 2;

	$num4 = ($num1 + $num2) / $num3;
	$pooledSD = sqrt($num4);

	return $pooledSD;

}

//throw out iat where 12 or more response times > 10 and where 12 or more response times < 0.3

//calculate number of response times > 10.0
$greaterRT = $mysqli->query("SELECT response_time, sum(response_time) as sum_response_time FROM trials where idiat = " . $idIat . " and (block = 3 or block = 4 or block = 6 or block = 7) group by trial_number having sum_response_time > 10.0");
$tooLong = array();
while($gTimes = mysqli_fetch_array($greaterRT, MYSQLI_ASSOC)){
	$tooLong[] = $gTimes['sum_response_time'];
}
$numGreater = count($tooLong);


//calculate the number of response times < 0.3
$lessRT = $mysqli->query("SELECT response_time, sum(response_time) as sum_response_time FROM trials where idiat = " . $idIat . " and (block = 3 or block = 4 or block = 6 or block = 7) group by trial_number having sum_response_time < 0.3");
$tooShort = array();
while($lTimes = mysqli_fetch_array($lessRT, MYSQLI_ASSOC)){
	$tooShort[] = $lTimes['sum_response_time'];
}
$numLess = count($tooShort);


if ($numGreater >= 12) {
	echo "Your response times were too slow to calculate a score.";  
} elseif ($numLess >= 12) {
	echo "Your response times were too fast to calculate a score.";
} else {
	
//testing arrays for scoring algorithm
/*
$arr3 = array(0.7400000095, 0.8510000706, 0.6970000267);
$arr4 = array(0.728000164, 0.6549999714, 0.6659998894);
$arr6 = array(0.7430000305, 0.8050000668, 0.6959998798);
$arr7 = array(0.6759998798, 0.7999999523, 0.7539999485);

*/
$result = $mysqli->query("SELECT block, sum(response_time) as sum_response_time FROM trials where idiat = " . $idIat . " and (block = 3 or block = 4 or block = 6 or block = 7) group by trial_number having sum_response_time < 10.0 and sum_response_time > 0.3");

$arr3 = array();
$arr4 = array();
$arr6 = array();
$arr7 = array();

while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	switch ($row['block']) {
		case 3: 
			$arr3[] = $row['sum_response_time'];
			break;
		case 4:
			$arr4[] = $row['sum_response_time'];
			break;
		case 6:	
			$arr6[] = $row['sum_response_time'];
			break;	

		case 7:
			$arr7[] = $row['sum_response_time'];
			break;
	}
	
}

//find averages of response times in blocks
$avgB6 = findAvg($arr6);
$avgB3 = findAvg($arr3);
$avgB7 = findAvg($arr7);
$avgB4 = findAvg($arr4);

//find pooled standard deviation for block 3 and block 6
$size3 = count($arr3);
$size6 = count($arr6);
$sD3 = findSD($arr3, $size3);
//echo "sD3". $sD3 ."";
$sD6 = findSD($arr6, $size6);
//echo "sD6". $sD6 ."";
$pooledSDB36 = findPooledSD($size3, $size6, $sD3, $sD6);
//echo "pSB36". $pooledSDB36."";

//find pooled standard deviation for block 7 and 4
$size4 = count($arr4);
$size7 = count($arr7);
$sD4 = findSD($arr4, $size4);
$sD7 = findSD($arr7, $size7);
$pooledSDB47 = findPooledSD($size4, $size7, $sD4, $sD7);

//calculations before finding final score
$q1 = ($avgB6 - $avgB3) / $pooledSDB36;
$q2 = ($avgB7 - $avgB4) / $pooledSDB47;
//echo "q1". $q1 ."";
//echo "q2". $q2 ."";

//final score
$finalScore = ($q1 + $q2) / 2;

echo " Your final score is: ".$finalScore.".";

//update the score in the iat table
if (!$mysqli->query("UPDATE iat SET score = " . $finalScore . "where idiat = " . $idIat . "")) {
    echo "UPDATE failed: (" . $mysqli->errno . ") " . $mysqli->error;
}

}

$mysqli->close();

//insert the data of the iat into the csv file
$fp = fopen('../report_csv/'.$idPerson.'_'.$idIat.'.csv', 'w');
foreach ($array_csv as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

session_destroy();

exit;
?>
