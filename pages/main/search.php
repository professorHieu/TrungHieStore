<?php
if (isset($_POST['timkiem'])) {
    $tukhoa = $_POST['tukhoa'];
}
$sql_pro = "SELECT * FROM tbl_sanpham,tbl_danhmuc WHERE tbl_sanpham.id_danhmuc = tbl_danhmuc.id_danhmuc AND  tbl_sanpham.tensp LIKE '%" . $tukhoa . "%'";
$query_pro = mysqli_query($mysqli, $sql_pro);
?>
<div class="page-shop">
    <div class="page-shop-heading">
        <img src="././assets/img/new2.jpg" alt="" class="page-shop-heading-img">
        <div class="page-shop-heading-text">
            <h2>Tìm Kiếm</h2>
            <p>Trang chủ > Tìm kiếm</p>
        </div>
    </div>
    <h2 style="text-align:center; margin-bottom: 50px;" class="text-kq">Bạn đang tìm kiếm theo từ khóa:
        "<?php echo $tukhoa ?>"</h2>
    <div class="page-shop-body row">
        <?php
        while ($row_pro = mysqli_fetch_array($query_pro)) {
        ?>
            <form action="pages/main/addtocart.php?id=<?php echo $row_pro['id_sp'] ?>" method="POST">
                <div class="col-xl-3 col-md-4 col-sm6">
                    <div class="page-shop-body-products">
                        <div class="container-product-sales-img">
                            <img src="admin/modules/quanlysp/uploads/<?php echo $row_pro['hinhanh'] ?>" alt="">
                        </div>
                        <div class="product-icon-cart">
                            <ul class="icon-cart-list">
                                <li class="icon-cart-item">
                                    <i class="fa-solid fa-heart"></i>
                                </li>
                                <li class="icon-cart-item">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </li>
                                <li class="icon-cart-item">
                                    <i class="fa-solid fa-right-left"></i>
                                </li>
                            </ul>
                        </div>
                        <p class="product-cate">
                            <?php
                            echo $row_pro['tendanhmuc']
                            ?>
                        </p>
                        <a href="index.php?quanly=sanpham&id=<?php echo $row_pro['id_sp'] ?>&iddm=<?php echo $row_pro['id_danhmuc'] ?>" class="product-title"><?php echo $row_pro['tensp'] ?>
                        </a>
                        <?php
                        if ($row_pro['gia_sale'] > 0) {
                        ?>
                            <p style="text-decoration:line-through;" class="product-price">
                                <?php echo number_format($row_pro['giasp'], 0, '.', '.') . " vnđ" ?></p>
                            <p class="product-price-sales"><?php echo number_format($row_pro['gia_sale'], 0, '.', '.') . " vnđ" ?>
                            </p>
                        <?php
                        } else {
                        ?>
                            <p class="product-price">
                                <?php echo number_format($row_pro['giasp'], 0, '.', '.') . " vnđ" ?>
                            </p>
                        <?php
                        }
                        ?>
                        <div class="add-to-cart add-cart-action" data-product-id="<?php echo $row_pro["id_sp"] ?>">Thêm giỏ hàng</div>
                    </div>
                </div>
            </form>
        <?php
        }
        ?>
    </div>
</div>
<div class="go-to-top js-top">
    <a href="#top">
        <i class="fa-solid fa-up-long"></i>
    </a>
</div>
<script>
    window.addEventListener("scroll", function() {
        var goTop = document.querySelector(".js-top");
        goTop.classList.toggle("open", window.scrollY > 0);
    });
</script>