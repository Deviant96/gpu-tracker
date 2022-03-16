<?php
include "db.php";
include "function_log.php";
error_reporting(E_ALL ^ E_WARNING);
date_default_timezone_set('Asia/Jakarta');

require_once 'simple_html_dom.php';

//place this before any script you want to calculate time
$time_start = microtime(true); 

//Ambil daftar URL dari database
try {
  $stmt = $conn->prepare("SELECT id, the_url FROM url_list");
  $stmt->execute();
  $url_list = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE);
  
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

function get_gpu_info(string $targeturl, int $gpu_id)
{
    

    $results = array();
    $html = new simple_html_dom();
    $html->load_file($targeturl);
    
    if (!empty($html)) {
    $div_class = $price = $stock = "";
    
    $div_class = $html->find("#main-pdp-container", 0);
        $out_of_stock = $html->find(".css-1igct5v-unf-quantity-editor__input[disabled]", 0); 
        $price = $div_class->find(".price", 0)->innertext;
        $price_int = intval(preg_replace('/[^\d\,]+/', '', $price));
        if($out_of_stock) {
            $stock = 0;
        } else {
            $stock = $div_class->find(".css-1a29oke p b", 0)->innertext;
        }

        // Clear $html variable to avoid memory leak
        $html->clear();
        unset($html);
    
        $results = array(
            'GPUID' => $gpu_id,
            'PRICE' => $price,
            'PRICEINT' => $price_int,
            'STOCK' => $stock
        );
    } else {echo "URL Not Found";}
    return $results;
}

$gpu_data = array_map('get_gpu_info', array_values($url_list), array_keys($url_list));

try {
    $time = date("H:i:s");
    $date = date("Y-m-d");

    // Tambahkan record ke table price_history 
    $stmt = $conn->prepare("INSERT INTO price_history (gpu_id, price, price_int, stock, update_time, update_date) 
        VALUES (:insert_gpu_id, :insert_price, :insert_price_int, :insert_stock, :insert_update_time, :insert_update_date)");
    
    $stmt->bindParam(':insert_gpu_id', $new_gpu_id);
    $stmt->bindParam(':insert_price', $new_price);
    $stmt->bindParam(':insert_price_int', $new_price_int);
    $stmt->bindParam(':insert_stock', $new_stock);
    $stmt->bindParam(':insert_update_time', $time);
    $stmt->bindParam(':insert_update_date', $date);
    
    foreach ($gpu_data as $data => $val) {
        $new_gpu_id = $val['GPUID'];
        $new_price = $val['PRICE'];
        $new_price_int = $val['PRICEINT'];
        $new_stock = $val['STOCK'];
        $stmt->execute();
        
        // Cek apakah sudah ada data GPU di table gpu_data dari record yang sedang dimasukkan
        $stmt2 = $conn->prepare("SELECT COUNT(gpu_id) FROM gpu_data WHERE gpu_id = :gpu_id");
        $stmt2->bindValue(':gpu_id', $new_gpu_id, PDO::PARAM_INT);
        $stmt2->execute();
        $count = (int)$stmt2->fetchColumn();
        
        if($count) { 
            // Cek data GPU di table gpu_data
            $stmt4 = $conn->prepare("SELECT title, old_price, old_price_int, old_stock, old_datetime, latest_price, latest_price_int, stock, latest_update_time, latest_update_date FROM gpu_data WHERE gpu_id = :gpu_id");
            $stmt4->bindParam(':gpu_id', $new_gpu_id);
            $stmt4->execute();
            $old_data = $stmt4->fetch(PDO::FETCH_ASSOC);
            
            $gpu_title = $old_data['title'];
            $old_price = $old_data['old_price'];
            $old_price_int = $old_data['old_price_int'];
            $old_stock = $old_data['old_stock'];
            $old_datetime = $old_data['old_datetime'];
            $latest_price = $old_data['latest_price'];
            $latest_price_int = $old_data['latest_price_int'];
            $stock = $old_data['stock'];
            $latest_date = $old_data['latest_update_date'];
            $latest_time = $old_data['latest_update_time'];
            $combined_latest_date_time = date('Y-m-d H:i:s', strtotime("$latest_date $latest_time"));
            
            // Jika harga dari record yang sedang dimasukkan sama dengan harga data GPU terbaru..
            if($new_price_int == $latest_price_int) { 
                echo "Harga sama";
                
                $stmt3 = $conn->prepare("UPDATE gpu_data SET 
                latest_update_time = :latest_update_time, 
                latest_update_date = :latest_update_date 
                WHERE gpu_id = :gpu_id");
                
            } else {
                echo "Harga beda";
                $stmt3 = $conn->prepare("UPDATE gpu_data SET 
                old_price = :old_price,
                old_price_int = :old_price_int, 
                old_stock = :old_stock, 
                old_datetime = :old_datetime, 
                latest_price = :latest_price, 
                latest_price_int = :latest_price_int, 
                stock = :stock, 
                latest_update_time = :latest_update_time, 
                latest_update_date = :latest_update_date 
                WHERE gpu_id = :gpu_id");
                
                // Taruh data terbaru ke bagian data lama
                $stmt3->bindParam(':old_price', $latest_price);
                $stmt3->bindParam(':old_price_int', $latest_price_int);
                $stmt3->bindParam(':old_stock', $stock);
                $stmt3->bindParam(':old_datetime', $combined_latest_date_time);

                // Update data terbaru dengan record yang sedang dimasukkan (new)
                $stmt3->bindParam(':latest_price', $new_price);
                $stmt3->bindParam(':latest_price_int', $new_price_int);
                $stmt3->bindParam(':stock', $new_stock);
                
                print_r("Old price updated");

                if(!empty($latest_price_int) || !empty($old_price_int)) {
                    $diff = round(abs((($new_price_int - $latest_price_int) / ($new_price_int)) * 100), 2);
                    if($new_price_int > $latest_price_int) { 
                        $progress_harga = "Harga naik (".$diff."%) : ".$gpu_title;
                        log_this($progress_harga, "gpu-progress");
                    } else if($new_price_int < $latest_price_int) {
                        $progress_harga = "Harga turun (".$diff."%) : ".$gpu_title;
                        log_this($progress_harga, "gpu-progress");
                    }
                }
                
            }
            
            $stmt3->bindParam(':latest_update_time', $time);
            $stmt3->bindParam(':latest_update_date', $date);
            $stmt3->bindParam(':gpu_id', $new_gpu_id);
            
            $stmt3->execute();
            
            //print_r("GPU Data with the same record found and has been updated");
        } else {//print_r("ERROR: No GPU Data with that GPU ID has been found");
        }
    }
    //print_r("Price record/s updated successfully");

} catch(PDOException $e) {
echo $e->getLine() . $e->getTrace() . $e->getMessage();}

$conn = null;

//----------------Execution Timer END-------------------
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
print_r('Total execution time: '.$execution_time.' seconds');
//------------------------------------------------------
?>