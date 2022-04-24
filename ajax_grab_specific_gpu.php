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
                $html .= "<td data-label='Nama Toko'>" . $row['shopname'] . "</td>";
        $html .= "<td data-label='Nama Item'><a href=" . $row['the_url'] . " target='_blank'><strong>" . $row['title'] . "</strong></a></td>";
        if($row['old_price_int']==0) {
            $html .= "<td data-label='Harga Lama'>N/A</td>";
            $html .= "<td data-label='Stok Lama'>N/A</td>";
        } else {
            $html .= "<td data-label='Harga Lama'>" . $row['old_price'] . " <small class=text-muted>pada " . $row['old_datetime'] . "</small></td>";
            $html .= "<td data-label='Stok Lama'>" . $row['old_stock'] . "</td>";
        }
        $html .= "<td data-label='Harga Terbaru'>";
        $html .= $row['latest_price'] . "</td>";
        $html .= "<td data-label='Stok Terbaru'>" . $row['stock'] . "</td>";
        $html .= "<td data-label='Waktu Terbaru'>" . $row['latest_update_time'] . "</td>";
        $html .= "<td data-label='Tanggal Terbaru'>" . date('d-m-Y', strtotime($row['latest_update_date'])) . "</td>";
        $html .= "</tr>";
    
    }
    
    echo $html;

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>