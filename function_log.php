<?php
//Log pesan tertentu pada suatu bagian kode dengan cara menyimpan di database
function log_this(string $message, string $category) {
    date_default_timezone_set('Asia/Jakarta');
    $datetime = date('Y-m-d H:i:s');
    global $conn;
    if($conn) {
        try {
            $stmt = $conn->prepare("INSERT INTO logging (date_time, mssg, category) VALUES (:date_time, :mssg, :category)");
            $stmt->bindParam(':date_time', $datetime);
            $stmt->bindParam(':mssg', $message);
            $stmt->bindParam(':category', $category);
            
            $stmt->execute();
          
        } catch(PDOException $e) {
            echo "Logging failed: " . $e->getMessage();
        }
    } else {
        //Jika gagal koneksi ke database, ubah menjadi log ke file
        $log  = "[".$datetime."][".$category."] Message : ".$message;
        //Save string to log, use FILE_APPEND to append.
        file_put_contents('./log/message_log.log', $log, FILE_APPEND);
    }
}


function insertNewProductFromUrl($product_id, $product_url) {
    $sql = "INSERT INTO urls (product_id, product_url, created_at) 
        VALUES (:insert_product_id, :insert_product_url, :insert_created_at)";
    $datetime = date('Y-m-d H:i:s');

    $params = array(
        'insert_product_id' => $product_id,
        'insert_product_url' => $product_url,
        'insert_created_at' => $datetime
    );

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $id = $conn->lastInsertId();
    
}








?>