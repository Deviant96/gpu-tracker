<?php
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/function_log.php';

//place this before any script you want to calculate time
$time_start = microtime(true); 

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

$client = new Client([
    'connect_timeout' => 5,
    'timeout'         => 5.00,
    'headers' => [
        'Host'=> 'tokopedia.com',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
        'Accept'=> '*/*',
        'Accept-Language'=> 'en-US,en;q=0.5',
        'Accept-Encoding'=> 'gzip, deflate, br',
        'Connection'=> 'keep-alive'
    ]
]);

$gpu_data = [];

// Function to scrape product data from a specific page asynchronously with error handling
function scrapeProductDataAsync($url)
{
    global $client;

    return $client->requestAsync('GET', $url)
        ->then(function (ResponseInterface $response) {
            $html = $response->getBody()->getContents();
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $productName = $xpath->query('//div[@id="pdp_comp-product_content"]/div/h1')->item(0)->nodeValue;
            $productPrice = $xpath->query('//div[@class="css-chstwd"]//div[@class="price"]')->item(0)->nodeValue;

            $productData = [
                'name' => $productName,
                'price' => $productPrice,
                // Add more data points to the array
            ];

            return $productData;
        })
        ->otherwise(function (Exception $exception) use ($url) {
            echo 'Error scraping product from URL: ' . $url . PHP_EOL;
            echo 'Error message: ' . $exception->getMessage() . PHP_EOL;
            return null;
        });
}

$url_list = array();

try {
    print_r('Fetching from database using guzzle + xpath');
    $stmt = $conn->prepare("SELECT id, the_url FROM url_list");
    $stmt->execute();
    $url_list = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE);
  
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
}

// Array to store promises
$promises = [];

// Iterate over the product URLs and create promises for each async request
foreach ($url_list as $url) {
    $promises[$url] = scrapeProductDataAsync($url);
}

// Wait for all promises to resolve or fail
$results = Promise\Utils::settle($promises)->wait();

// Process the scraped data
foreach ($results as $url => $result) {
    echo '<hr>';
    if ($result['state'] === Promise\PromiseInterface::FULFILLED) {
        $productData = $result['value'];
        if($productData !== null) {
        echo "<pre>";
        print_r($productData);
        echo "</pre>";
        echo 'Product URL: ' . $url . PHP_EOL . '<br>';
        echo 'Product Name: ' . $productData['name'] . PHP_EOL . '<br>';
        echo 'Product Price: ' . $productData['price'] . PHP_EOL . '<br>';
        // Output or process other scraped data
        } else { 
            echo "Error scraping $url: No result was returned."; 
        }
    } else {
        echo 'Error scraping ' . $url . ': ' . $result['reason']->getMessage() . PHP_EOL;
    }
    
    echo '<hr>';
}

//----------------Execution Timer END-------------------
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
print_r('Total execution time: '.$execution_time.' seconds');
//------------------------------------------------------
?>