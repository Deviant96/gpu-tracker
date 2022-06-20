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
        
        $html .= "<td data-label='Harga'><strong>";
        $html .= $row['latest_price'] . "</strong> <sup>(" . $row['stock'] . ")</sup>  <span style='font-size:10px;'>" . $row['latest_update_time'] . " " . date('d-m-Y', strtotime($row['latest_update_date'] ?? '1970-01-01')) . "</span></td>";
        
        if($row['old_price_int']==0) {
            $html .= "<td data-label='Harga Lama'>N/A</td>";
        } else {
            $html .= "<td data-label='Harga Lama'><strong>" . $row['old_price'] . "</strong> <sup>(" . $row['old_stock'] . ")</sup> <span style='font-size:10px;'> " . $row['old_datetime'] . "</span></td>";
        }
        
        $html .= "</tr>";
    
    }
    
    echo $html;

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>