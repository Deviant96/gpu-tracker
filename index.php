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
        <link rel="icon" type="image/x-icon" href="favicon.png">
        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
        <style>
            #gpu_log_history {
                background-color: #EEE;
                height: 500px;
                width: 50vw;
                margin: 0 auto;
                margin-bottom: 50px;
            }
            @media(max-width:767px){
                #gpu_log_history {
                    width: 100vw;
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
                font-size: 12px;
                letter-spacing: -.3px;
                word-spacing: 3px;
                margin-top: 5px;
            }
            #gpu_log_history button.gpu-log-view-more {
                background: none;
                border: none;
                text-decoration: none;
                font-size: 16px;
                color: #fff;
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
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
            Lihat Daftar
        </button>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasExampleLabel">Daftar Item</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <nav class="nav flex-column navbar-light bg-light">
                    <div class="navbar-nav mr-auto">
                        <?php
                            $stmt = $conn->prepare("SELECT DISTINCT gpu_model FROM url_list ORDER BY gpu_model DESC");
                            $stmt->execute();
                            while($row = $stmt->fetch()){
                        ?>
                                <button class="nav-link btn navbar-btn js-select-gpu" value="<?php echo $row['gpu_model'];?>"><?php echo $row['gpu_model'];?></button>
                            
                        <?php
                            }
                        ?>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <section class="container mt-3">
        <h4>Tambah data baru</h4>
        <form action="new_url.php" method="POST">
            <div class="input-group mb-3">
              <input type="text" id="add_url" name="add_url" class="form-control" placeholder="Masukkan URL" aria-label="Masukkan URL" aria-describedby="basic-addon2" data-toggle="popover" data-placement="bottom" data-trigger="focus" title="Masukkan URL" data-content="Dari Tokped, URLnya harus produk varian yang spesifik (udah dipilih variannya jika ada pilihannya di sebelah kanan) Contoh : https://www.tokopedia.com/enterkomputer/galax-geforce-gtx-1050-ti-4gb-ddr5-1-click-oc-dual-fan-garansi" required>
            <input type="text" id="gpu_model" name="gpu_model" class="form-control" placeholder="Masukkan model GPU" aria-label="Model GPU" aria-describedby="basic-addon2" data-toggle="popover" data-placement="bottom" data-trigger="focus" title="Masukkan model GPU" data-content="Contoh : RTX3060TI" maxlength="20" size="20">
              <div class="input-group-append" required>
                <button class="btn btn-outline-primary" type="submit">Tambahkan</button>
              </div>
            </div>
        </form>
        <hr>
    </section>

    <section class="container mt-5">
        <h4>Data GPU</h4>
        <small class="text-muted">Data diupdate setiap jam</small>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th rowspan=2 class="align-middle">Toko</th>
                    <th rowspan=2 class="align-middle">Nama</th>
                    <th rowspan=2 class="align-middle">Harga Lama</th>
                    <th rowspan=2 class="align-middle">Stok Lama</th>
                    <th rowspan=2 class="align-middle">Harga Terbaru</th>
                    <th rowspan=2 class="align-middle">Stok</th>
                    <th colspan=2 class="align-middle">Update Terakhir</th>
                </tr>
                <tr>
                    <th>Waktu</th>
                    <th>Tanggal (tgl/bln/thn)</th>
                </tr>
            </thead>
            <tbody class="js_gpu_result">
                <tr><td colspan=8 class="text-center">Kosong. Silahkan pilih item.</td></tr>
            </tbody>
        </table>
        
    </section>
    
    <section id="gpu_log_history">
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

    <footer>
        Using <a href="https://systemuicons.com" target="_blank">System UIcons</a>
    </footer>
    
        <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

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
                            $(".js_gpu_result").html('<tr><td colspan=8 class="text-center"><img class="loading-image" src="./img/loader-dark.svg" alt=""></td></tr>');

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

</body>
</html>

<?php $conn = null;?>