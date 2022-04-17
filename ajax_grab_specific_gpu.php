<?php
include "db.php";
error_reporting(E_ALL ^ E_WARNING);

$gpu = $_POST['gpu_name'];

$results = array();
$html = '';

try {
    $stmt = $conn->prepare("SELECT * FROM url_list LEFT JOIN gpu_data ON gpu_data.gpu_id = url_list.id WHERE url_list.gpu_model = :gpu_name");
    $stmt->bindParam(':gpu_name', $gpu);
    $stmt->execute();

    while ($row = $stmt->fetch()) {
        if ($row['stock'] > 0) {
            $html .= "<tr>";} else {
                $html .= "<tr class=table-warning>";}
                $html .= "<td>" . $row['shopname'] . "</td>";
        $html .= "<td>" . $row['title'] . "</td>";
        if($row['old_price_int']==0) {
            $html .= "<td>N/A</td>";
            $html .= "<td>N/A</td>";
        } else {
            $html .= "<td>" . $row['old_price'] . " <small class=text-muted>pada " . $row['old_datetime'] . "</small></td>";
            $html .= "<td>" . $row['old_stock'] . "</td>";
        }
        $html .= "<td>";
        $html .= $row['latest_price'] . "</td>";
        $html .= "<td>" . $row['stock'] . "</td>";
        $html .= "<td>" . $row['latest_update_time'] . "</td>";
        $html .= "<td>" . date('d-m-Y', strtotime($row['latest_update_date'])) . "</td>";
        $html .= "</tr>";
    
    }
    
    echo $html;

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>