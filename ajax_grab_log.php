
<?php

// configuration
include "db.php";
error_reporting(E_ALL ^ E_WARNING);

$row_result = $_POST['row'];
$logs_per__page = 10;

// selecting posts


$sql = "SELECT id, date_time, mssg FROM logging WHERE category = 'gpu-progress' ORDER BY date_time DESC LIMIT $row_result, $logs_per__page";
$stmt = $conn->prepare($sql);
// $stmt->bindParam(':logs_per__page', $logs_per__page);
// $stmt->bindParam(':row_result', $row_result);
$rs = $stmt->execute();

$html = '';

while ($row = $stmt->fetch()) {
    $id = $row['id'];
    $date_time = $row['date_time'];
    $mssg = $row['mssg'];
    // Creating HTML structure
    $html .= '<div id="post_'.$id.'" class="post">';
    $html .= '<div class="log_item"> > ';
    $html .= $date_time;
    $html .= ' : ';
    $html .= $mssg;
    $html .= '</div>';
    $html .= '</div>';

}

echo $html;