<?php
// Cegah cache - https://stackoverflow.com/questions/49547/how-do-we-control-web-page-caching-across-all-browsers
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

include "db.php";

try {
  $stmt = $conn->prepare("SELECT * FROM gpu_data");
  $stmt->execute();
  $result = $stmt->fetchAll();
  
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

try {
    $stmt2 = $conn->prepare("SELECT date_time, mssg FROM logging WHERE category = 'gpu-progress'");
    //$stmt2->bindParam(':category', "gpu-progress");
    $stmt2->execute();
    $gpuprogress = $stmt2->fetchAll();
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <title>Data Harga GPU Dari Beberapa Toko di Tokopedia</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/x-icon" href="favicon.png">
        
        <link href="css/normalize.min.css" rel="stylesheet">
        <link href="css/style.min.css" rel="stylesheet">
    
        <style>
            #gpu_log_history {
                background-color: #EEE;
                height: 500px;
                width: 100%;
                margin: 0 auto;
                margin-bottom: 50px;
            }
            @media(max-width:1024px){
                #gpu_log_history {
                    width: auto;
                }
            }
            #gpu_log_history .gpu_log_history-header {
                background: linear-gradient(to bottom, #76b900, #5e9400);
                height: 45px;
                font-size: 20px;
                text-align: center;
                line-height: 40px;
                color: white;
                text-shadow: 1px 1px 10px #EEE;
                -webkit-user-select: none;
            }
            #gpu_log_history .gpu_log_history-body {
                background-color: #FFF;
                border: solid 1px #DDD;
                overflow-x: hidden;
                overflow-y: scroll;
                height: calc(100% - 45px);
            }

            #gpu_log_history .gpu_log_history-body .log_item {
                font-family: consolas;
                font-size: 14px;
                letter-spacing: .5px;
                word-spacing: 3px;
                color: rgb(106, 112, 115);
                line-height: 1.2;
                word-wrap: break-word;
                border-bottom: 1px solid rgb(230, 230, 230);

                margin-top: 5px;
            }
            #gpu_log_history button.gpu-log-view-more {
                background: none;
                border: none;
                text-decoration: none;
                font-size: 16px;
                color: #fff;
                cursor: pointer;
            }
            #gpu_log_history button.gpu-log-view-more:hover {
                color: #ccc;
            }
            
            .loading-image {
                margin: auto;
                animation: rotation 1s infinite linear;
                display: none;
            }
            @keyframes rotation {
                100% {transform: rotate(360deg);}
            }
        </style>
    </head>

    <body>
        <header class="container">
            <h1>Dashboard Data GPU</h1>
            <div class="off-section">
                <button class="btn btn-primary js-show-list" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                    Menu
                </button>

                <div class="offcanvas offcanvas-start container" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Daftar Item</h5>
                        <span class="close-button">
                            <svg height="36" viewBox="0 0 21 21" width="36" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" transform="translate(5 5)"><path d="m10.5 10.5-10-10z"/><path d="m10.5.5-10 10"/></g></svg>
                        </span>
                    </div>
                    <div class="offcanvas-body">
                        <nav class="nav flex-column navbar-light bg-light">
                                <?php
                                    $stmt = $conn->prepare("SELECT DISTINCT gpu_model FROM url_list ORDER BY gpu_model DESC");
                                    $stmt->execute();
                                    while($row = $stmt->fetch()){
                                ?>
                                        <button class="nav-link btn navbar-btn js-select-gpu" value="<?php echo $row['gpu_model'];?>"><?php echo $row['gpu_model'];?></button>
                                    
                                <?php
                                    }
                                ?>
                        </nav>
                        <div class="add-new-data">
                            <h4>Tambah data baru</h4>
                            <form action="new_url.php" method="POST">
                                <input type="text" id="add_url" name="add_url" class="form-control" placeholder="Masukkan URL" aria-label="Masukkan URL" aria-describedby="basic-addon2" title="Masukkan URL" required>
                                <input type="text" id="gpu_model" name="gpu_model" class="form-control" placeholder="Masukkan model GPU" aria-label="Model GPU" aria-describedby="basic-addon2" title="Masukkan model GPU" required>
                                <button class="btn btn-outline-primary" type="submit">Tambahkan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="backdrop"></div>
            </div>
        </header>

        <section id="gpu_table" class="container">
            <table class="table">
                <caption>Data GPU <small>(Data diupdate setiap jam)</small></caption>
                <thead>
                    <tr>
                        <th scope="col" rowspan=2>Nama Toko</th>
                        <th scope="col" rowspan=2>Nama Item</th>
                        <th scope="col" rowspan=2>Harga (Stok)</th>
                        <th scope="col" rowspan=2>Harga Lama</th>
                    </tr>
                </thead>
                <tbody class="js_gpu_result">
                    <tr><td colspan=4 style="text-align: center;">Kosong. Silahkan pilih item.</td></tr>
                </tbody>
            </table>
            
        </section>
        
        <section id="gpu_log_history" class="container">
            <div class="gpu_log_history-header">
                <h2>
                    GPU Prices Log 
                    <small>(Top newest)</small>
                    <button type="button" class="gpu-log-view-more js-gpu-log-view-more">
                        <span>View more..</span>
                        <img class="loading-image" src="./img/loader.svg" alt="">
                    </button>
                </h2>
            </div>
            <div class="gpu_log_history-body">
                <?php
                    // ----- Load more logs ----- //
                    $logs_per__page = 10;

                    try {
                        // Hitung jumlah semua log
                        $sql = "SELECT id, date_time, mssg FROM logging WHERE category = 'gpu-progress' ORDER BY date_time DESC";
                        $stmt = $conn->prepare($sql);

                        $rs = $stmt->execute();
                        $logs_total__record = $stmt->rowCount();

                        // Pilih 10 log pertama untuk ditampilkan
                        $sql .= ' LIMIT 0,' .$logs_per__page;
                        $stmt = $conn->prepare($sql);
                        //$stmt->bindParam(':logs_per__page', $logs_per__page);
                        $rs = $stmt->execute();

                        while ($row = $stmt->fetch()) {
                            $id = $row['id'];
                            $date_time = $row['date_time'];
                            $mssg = $row['mssg'];
                ?>
                    <div class="post" id="post_<?php echo $id; ?>">
                        <div class="log_item"> > 
                            <?php echo $date_time; ?> : <?php echo $mssg; ?>
                        </div>
                    </div>

                <?php
                        }
                    } catch (PDOException $e) {
                        echo "Connection failed: " . $e->getMessage();
                    }
                ?>
                <input type="hidden" id="row" value="0" autocomplete=off>
                <input type="hidden" id="all" value="<?php echo $logs_total__record; ?>">

            </div>
        </section>

        <footer class="container">
            <div class="footer-content">
                By <a href="https://miretazam.com">Miretazam</a>
            </div>
            <div class="footer-resources">
                Using <a href="https://systemuicons.com" target="_blank">System UIcons</a>
            </div>
        </footer>
        
        <script src="js/jquery-3.2.1.min.js"></script>
        
        <script>
            $(function () {
            $('[data-toggle="popover"]').popover()
            })
        </script>
        
        <!-- GPU Log History - View more script -->
        <script>
            $(document).ready(function(){

            // Load more data
            $('button.js-gpu-log-view-more').click(function(){
                var row = Number($('#row').val());
                var allcount = Number($('#all').val());
                var rowperpage = 10;
                row = row + rowperpage;

                if(row <= allcount){
                    $("#row").val(row);

                    $.ajax({
                        url: 'ajax_grab_log.php?nocache='+Math.random(), // Prevent cache
                        method: 'post',
                        data: {row:row},
                        cache: 'false',
                        beforeSend:function(){
                            $("button.js-gpu-log-view-more > span").text("Loading..");
                            //$(".loader").show().fadeIn("slow");
                            $("button.js-gpu-log-view-more > img").toggle();
                        },
                        success: function(response){

                            // Setting little delay while displaying new content
                            setTimeout(function() {
                                // appending posts after last post with class="post"
                                $(".post:last").after(response).show().fadeIn("slow");
                                var rowno = row + rowperpage;

                                // checking row value is greater than allcount or not
                                if(rowno > allcount){

                                    // Change the text and background
                                    $("button.js-gpu-log-view-more > span").text("Hide");
                                    $("button.js-gpu-log-view-more > img").toggle();
                                }else{
                                    $("button.js-gpu-log-view-more > span").text("View more..");
                                    $("button.js-gpu-log-view-more > img").toggle();
                                }
                            }, 2000);

                        }
                    });
                }else{
                    $("button.js-gpu-log-view-more > span").text("Loading..");

                    // Setting little delay while removing contents
                    setTimeout(function() {

                        // When row is greater than allcount then remove all class='post' element after 10 element
                        $('.post:nth-child(10)').nextAll('.post').remove();

                        // Reset the value of row
                        $("#row").val(0);

                        // Change the text and background
                        $("button.js-gpu-log-view-more > span").text("View more..");
                        $("button.js-gpu-log-view-more > img").toggle();
                        
                    }, 2000);


                }

            });

            });
        </script>

        <!-- Show Specific GPU -->
        <script>
            $(document).ready(function(){

                // Load more data
                $('.js-select-gpu').click(function(){
                    
                    var gpu_name = $(this).val();
                    // var $gpu_name = $(this).attr("value");

                    $.ajax({
                        url: 'ajax_grab_specific_gpu.php?nocache='+Math.random(), // Prevent cache
                        method: 'post',
                        data: { gpu_name : gpu_name },
                        cache: 'false',
                        // beforeSend:function(){
                        //     $("button.js-gpu-log-view-more > span").text("Loading..");
                        //     //$(".loader").show().fadeIn("slow");
                        //     $("button.js-gpu-log-view-more > img").toggle();
                        // },
                        beforeSend:function(){
                                //alert("Bisa");
                                $(".js_gpu_result").html('<tr><td colspan=4 style="text-align:center"><img class="loading-image" src="./img/Loader-dark.svg" alt=""></td></tr>');

                                $(".js_gpu_result .loading-image").toggle();
                        },
                        success: function(response){

                            // Setting little delay while displaying new content
                            setTimeout(function() {
                                //alert("Bisa");
                                $(".js_gpu_result .loading-image").toggle();
                                $(".js_gpu_result").html(response).show().fadeIn("slow");
                            }, 2000);

                        }
                    });
                });
            });
        </script>

        <script>
            $('button.js-show-list').click(function(){
                $('.offcanvas').toggleClass('active');
                $('.off-section .backdrop').toggleClass('show');
            });
            
            $('.backdrop').click(function(){
                $('.offcanvas').removeClass('active');
                $('.off-section .backdrop').removeClass('show');
            });
            $('.close-button').click(function(){
                $('.offcanvas').removeClass('active');
                $('.off-section .backdrop').removeClass('show');
            });
            $('.js-select-gpu').click(function(){
                $('.offcanvas').removeClass('active');
                $('.off-section .backdrop').removeClass('show');
            });
        </script>
    </body>
</html>

<?php $conn = null;?>