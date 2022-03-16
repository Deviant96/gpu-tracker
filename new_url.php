<?php
$time_start = microtime(true); 

require_once 'simple_html_dom.php';

include "db.php";
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// Dari Tokped, URLnya harus produk varian yang spesifik (udah dipilih variannya jika ada pilihannya di sebelah kanan)
$listurl = $_POST['add_url'];
$gpu_model = $_POST['gpu_model'];

//----------------Execution Timer END-------------------
$time_first = microtime(true);
$first_execution_time = ($time_first - $time_start);
print_r('Connection and variables execution time: '.$first_execution_time.' seconds');
//------------------------------------------------------

//$gpu_data = array_map('get_gpu_info', array_values($result), array_keys($result));

//var_dump($gpu_data);

    function string_between_two_string($str, $starting_word, $ending_word)
{
    $subtring_start = strpos($str, $starting_word);
    //Adding the starting index of the starting word to
    //its length would give its ending index
    $subtring_start += strlen($starting_word); 
    //Length of our required sub string
    $size = strpos($str, $ending_word, $subtring_start) - $subtring_start; 
    // Return the substring from the index substring_start of length size
    return substr($str, $subtring_start, $size); 
}

function get_gpu_info(string $targeturl)
{
    $results = array();
    $html = new simple_html_dom();
    $html->load_file($targeturl);
    
    if (!empty($html)) {
    $div_class = $title = "";
    
    $div_class = $html->find("#main-pdp-container", 0);
        $get_shop_name = $html->find("title", 0)->innertext;
        $shop_name = string_between_two_string($get_shop_name, ' -  - ', ' | Tokopedia');
        $title = $div_class->find("h1.css-t9du53", 0)->innertext;

        // Clear $html variable to avoid memory leak
        $html->clear();
        unset($html);
    
            if (!empty($title)) {
                $results = array(
                    'SHOPNAME' => $shop_name,
                    'TITLE' => $title,
                    );
            } else {echo "Title not found";}
} else {echo "URL Not Found";}

    return $results;
}

//----------------Execution Timer END-------------------
$time_second = microtime(true);
$second_execution_time = ($time_second - $time_start);
print_r('Functions execution time: '.$second_execution_time.' seconds');
//------------------------------------------------------

//$gpu_data = array_map('get_gpu_info', $listurl);

$db_time_start = microtime(true); 

    try {
        $gpu_info = get_gpu_info($listurl);
        
        $stmt = $conn->prepare("INSERT INTO url_list (gpu_model, the_url) VALUES (:gpu_model, :the_url)");
        $stmt->bindParam(':gpu_model', $gpu_model);
        $stmt->bindParam(':the_url', $listurl);
        $stmt->execute();
        $last_id = $conn->lastInsertId();
        
        $stmt = $conn->prepare("INSERT INTO gpu_data (gpu_id, 
            shopname, 
            title, 
            old_price, 
            old_price_int, 
            old_stock, 
            old_datetime, 
            latest_price, 
            latest_price_int, 
            stock, 
            latest_update_time, 
            latest_update_date) 
            VALUES (:insert_gpu_id, 
                :insert_shopname, 
                :insert_title,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL)");
        
        $stmt->bindParam(':insert_gpu_id', $last_id);
        $stmt->bindParam(':insert_shopname', $insert_shopname);
        $stmt->bindParam(':insert_title', $insert_title);
        
        $insert_shopname = $gpu_info['SHOPNAME'];
        $insert_title = $gpu_info['TITLE'];
        $stmt->execute();
        
        
        //Untuk code submit multivalue (textarea)
        /*foreach ($gpu_data as $data => $val) {
            $insert_shopname = $val['SHOPNAME'];
            $insert_title = $val['TITLE'];
            $stmt->execute();
        }*/
        
        echo "New URL has been added";
    
    } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage(); }

    //----------------Execution Timer END-------------------
$db_time_end = microtime(true);
$db_time_execution_time = ($db_time_end - $db_time_start);
print_r('Inserting to database execution time: '.$db_time_execution_time.' seconds');
//------------------------------------------------------

$conn = null;

//----------------Execution Timer END-------------------
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
print_r('Total execution time: '.$execution_time.' seconds');
//------------------------------------------------------
?>