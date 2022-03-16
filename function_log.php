<?php
//Log pesan tertentu pada suatu bagian kode dengan cara menyimpan di database
function log_this(string $message, string $category) {
    date_default_timezone_set('Asia/Jakarta');
    $datetime = date('Y-m-d h:i:s');
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
?>