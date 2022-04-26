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
        if ($row['stock'] > 0) { $html .= "<tr>"; } else { $html .= "<tr class=table-warning>"; }
                $html .= "<td data-label='Nama Toko'>" . $row['shopname'] . "</td>";
        $html .= "<td data-label='Nama Item'><a href=" . $row['the_url'] . " target='_blank'><strong>" . $row['title'] . "</strong></a></td>";
        
        $html .= "<td data-label='Harga'>";
        $html .= $row['latest_price'] . " (" . $row['stock'] . ")  <small>" . $row['latest_update_time'] . " " . date('d-m-Y', strtotime($row['latest_update_date'])) . "</small></td>";
        
        if($row['old_price_int']==0) {
            $html .= "<td data-label='Harga Lama'>N/A</td>";
        } else {
            $html .= "<td data-label='Harga Lama'>" . $row['old_price'] . " (" . $row['old_stock'] . ") <small> " . $row['old_datetime'] . "</small></td>";
        }
        
        $html .= "</tr>";
    
    }
    
    echo $html;

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>