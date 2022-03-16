<?php
include "db.php";

try {
        $stmt = $conn->prepare("
        SELECT price_history.gpu_id, price_history.price_int, price_history.stock, price_history.update_time, price_history.update_date, gpu_data.title
        FROM price_history
        JOIN gpu_data 
        ON price_history.gpu_id = gpu_data.gpu_id
        GROUP BY price_int, gpu_id
        ORDER BY title, update_date, update_time DESC");
  $stmt->execute();
  $result = $stmt->fetchAll();
  
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}


$conn = null;
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <title>Data Harga GPU Dari Beberapa Toko di Tokopedia</title>
        <meta charset="utf-8">
        <link rel="icon" type="image/x-icon" href="favicon.png">
        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    </head>
    
    <section class="container">
        <h1>Daftar Tracking Perubahan Harga GPU pada Waktu Tertentu</h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga Terbaru</th>
                    <th>Stok</th>
                    <th>Waktu</th>
                    <th>Tanggal (tgl/bln/thn)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            
            foreach ($result as $data => $val) {
                echo "<tr>";
                //echo "<td>".$val['shopname']."</td>";
                echo "<td>".$val['title']."</td>";
                echo "<td class=table-warning>".$val['price_int']."</td>";
                echo "<td>".$val['stock']."</td>";
                echo "<td>".$val['update_time']."</td>";
                echo "<td>".date('d-m-Y',strtotime($val['update_date']))."</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        
    </section>
</html>