<?php
require_once __DIR__ . '/../../vendor/autoload.php';
include(__DIR__ . '/../../admin/config/config.php');
if (isset($_GET['orderCode']) && isset($_GET['mode'])) {
    $query_pay_os_order = 'SELECT * FROM tbl_pay_os_order WHERE payos_order_code=' . $_GET["orderCode"];
    $pay_os_order = mysqli_query($mysqli, $query_pay_os_order);
    while ($row = mysqli_fetch_assoc($pay_os_order)) {
        $order_id = $row['order_id'];
        $update_table_name = $_GET['mode'] == 'register' ? 'tbl_cart_registered' : 'tbl_cart_unregistered';
        $update_col_name = $_GET['mode'] == 'register' ? 'id_cart_registered' : 'id_cart_unregistered';
        $update_status_query = 'UPDATE ' . $update_table_name . ' SET cart_status=4 WHERE ' . $update_col_name . '=' . $order_id;
        mysqli_query($mysqli, $update_status_query);
    }
}
?>

<div class="page-thanks">
    <div class="page-thanks-heading">
        <img src="././assets/img/new1.jpg" alt="" class="page-thanks-heading-img">
        <div class="page-thanks-heading-text">
            <h2>Đặt hàng không thành công</h2>
            <p>Trang chủ > Hủy đơn hàng</p>
        </div>
    </div>
    <div class="page-thanks-body">
        <div class="camon-form">
        <i class="fas fa-times-circle" style="color: #d31212;"></i>
            <p>Đặt hàng không thành công</p>
            <p>Vui lòng thanh toán online để hoàn tất đơn hàng của bạn</p>
            <a href="/TrungHieStore">
                <i class="fas fa-long-arrow-alt-left"></i>
                Trang chủ
            </a>
        </div>
    </div>
</div>