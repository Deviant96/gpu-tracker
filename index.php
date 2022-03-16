<?php
include "db.php";

try {
    $stmt = $conn->prepare("SELECT * FROM gpu_data");
    $stmt->execute();
    $result = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

function grab_specific_gpu(string $gpu_name)
{
    global $conn;
    $results = array();
    try {
        $stmt = $conn->prepare("SELECT * FROM url_list LEFT JOIN gpu_data ON gpu_data.gpu_id = url_list.id WHERE url_list.gpu_model = :gpu_name");
        $stmt->bindParam(':gpu_name', $gpu_name);
        $stmt->execute();
        $results = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    return $results;
}

try {
    $stmt2 = $conn->prepare("SELECT date_time, mssg FROM logging WHERE category = 'gpu-progress'");
    //$stmt2->bindParam(':category', "gpu-progress");
    $stmt2->execute();
    $gpuprogress = $stmt2->fetchAll();

} catch (PDOException $e) {
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
            .content {
                display: none;
            }
            .show_hide > button.toggle {
                float: right;
                font-weight: normal;
                text-align: right;
                padding-right: 0.2em;
                padding-left: 0.2em;
                cursor: pointer;
                line-height: 1.5em;
                border-spacing: 0;
            }
        </style>
    </head>
    <body>
    <header class="container">
        <h1>Dashboard Data GPU</h1>
        <small class="text-muted">Data diupdate setiap jam</small>
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
        <button class="btn">Show All</button>
    </section>

    <section class="container-fluid mt-5" style="font-size:70%;">
        <div class="row">
            <div class="col">
            <h5>RX6900XT</h5>
            <button class="toggle">Show</button>
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
            <tbody>
            <?php
            foreach (grab_specific_gpu("RX6900XT") as $data => $val) {
                if ($val['stock'] > 0) {
                    echo "<tr>";} else {
                    echo "<tr class=table-warning>";}
                echo "<td>" . $val['shopname'] . "</td>";
                echo "<td>" . $val['title'] . "</td>";
                if($val['old_price_int']==0) {
                    echo "<td>N/A</td>";
                    echo "<td>N/A</td>";
                } else {
                    echo "<td>" . $val['old_price'] . " <small class=text-muted>pada " . $val['old_datetime'] . "</small></td>";
                    echo "<td>" . $val['old_stock'] . "</td>";
                }
                echo "<td>";
                echo $val['latest_price'] . "</td>";
                echo "<td>" . $val['stock'] . "</td>";
                echo "<td>" . $val['latest_update_time'] . "</td>";
                echo "<td>" . date('d-m-Y', strtotime($val['latest_update_date'])) . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
            </div>
        <div class="col">
            <h5>RX6800XT</h5>
            <button class="toggle">Show</button>
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
                <tbody">
                <?php
                foreach (grab_specific_gpu("RX6800XT") as $data => $val) {
                    if ($val['stock'] > 0) {
                        echo "<tr>";} else {
                        echo "<tr class=table-warning>";}
                    echo "<td>" . $val['shopname'] . "</td>";
                    echo "<td>" . $val['title'] . "</td>";
                    if($val['old_price_int']==0) {
                        echo "<td>N/A</td>";
                        echo "<td>N/A</td>";
                    } else {
                        echo "<td>" . $val['old_price'] . " <small class=text-muted>pada " . $val['old_datetime'] . "</small></td>";
                        echo "<td>" . $val['old_stock'] . "</td>";
                    }
                    echo "<td>";
                    echo $val['latest_price'] . "</td>";
                    echo "<td>" . $val['stock'] . "</td>";
                    echo "<td>" . $val['latest_update_time'] . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($val['latest_update_date'])) . "</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="col">
            <h5>RX6700XT</h5>
            <button class="toggle">Show</button>
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
                <tbody>
                <?php
                foreach (grab_specific_gpu("RX6700XT") as $data => $val) {
                    if ($val['stock'] > 0) {
                        echo "<tr>";} else {
                        echo "<tr class=table-warning>";}
                    echo "<td>" . $val['shopname'] . "</td>";
                    echo "<td>" . $val['title'] . "</td>";
                    if($val['old_price_int']==0) {
                        echo "<td>N/A</td>";
                        echo "<td>N/A</td>";
                    } else {
                        echo "<td>" . $val['old_price'] . " <small class=text-muted>pada " . $val['old_datetime'] . "</small></td>";
                        echo "<td>" . $val['old_stock'] . "</td>";
                    }
                    echo "<td>";
                    echo $val['latest_price'] . "</td>";
                    echo "<td>" . $val['stock'] . "</td>";
                    echo "<td>" . $val['latest_update_time'] . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($val['latest_update_date'])) . "</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        </div>

        <table class="table-striped">
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
            <tbody>
            <?php
            foreach ($result as $data => $val) {
                if ($val['stock'] > 0) {
                    echo "<tr>";} else {
                    echo "<tr class=table-warning>";}
                echo "<td>" . $val['shopname'] . "</td>";
                echo "<td>" . $val['title'] . "</td>";
                if($val['old_price_int']==0) {
                    echo "<td>N/A</td>";
                    echo "<td>N/A</td>";
                } else {
                    echo "<td>" . $val['old_price'] . " <small class=text-muted>pada " . $val['old_datetime'] . "</small></td>";
                    echo "<td>" . $val['old_stock'] . "</td>";
                }
                echo "<td>";
                echo $val['latest_price'] . "</td>";
                echo "<td>" . $val['stock'] . "</td>";
                echo "<td>" . $val['latest_update_time'] . "</td>";
                echo "<td>" . date('d-m-Y', strtotime($val['latest_update_date'])) . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>

    </section>

    <footer>
        <?php
        foreach ($gpuprogress as $data) {
            echo ":" . $data['date_time'] . " : " . $data['mssg'] . "<br>";
        }
        ?>
    </footer>

    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
    $(function () {
        $('[data-toggle="popover"]').popover()


    })

    $(document).ready(function(){
        $("table.table").addClass("content");
    });

    $(".toggle").click (function(){
        var target = $(this).closest('.col').find('.content');

        if($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).text("Show");
            target.stop().slideToggle(0);
        } else {
            $(this).addClass('active');
            $(this).text("Hide");
            target.stop().slideToggle(0);
        }

    });
</script>
</body>
</html>

<?php $conn = null;?>