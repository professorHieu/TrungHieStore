<?php
session_start();
use Carbon\Carbon;
include('../../admin/config/config.php');
require('../../vendor/autoload.php');

$now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();
$id_khachhang = $_SESSION['id_khachhang'];
$code_order = rand(0, 9999);
$all_items_available = true; // Biến để kiểm tra tính khả dụng của tất cả sản phẩm

// Kiểm tra số lượng hàng trong kho trước khi thêm vào giỏ hàng chi tiết
foreach ($_SESSION['cart'] as $key => $value) {
    $id_sanpham = $value['id'];
    $soluong = $value['soluong'];
    
    $sql_desc = "SELECT soluong FROM tbl_sanpham WHERE id_sp = '$id_sanpham' LIMIT 1";
    $query_desc = mysqli_query($mysqli, $sql_desc);
    $row_desc = mysqli_fetch_array($query_desc);
    
    if ($row_desc['soluong'] < $soluong) {
        $all_items_available = false;
        break;
    }
}

if ($all_items_available) {
    $insert_cart_registered = "INSERT INTO tbl_cart_registered(id_khachhang, code_cart, cart_status, cart_date, hinhthuc) 
        VALUE ('$id_khachhang', '$code_order', 0, '$now', 'COD')";
    $cart_query = mysqli_query($mysqli, $insert_cart_registered);

    if ($cart_query) {
        foreach ($_SESSION['cart'] as $key => $value) {
            $id_sanpham = $value['id'];
            $soluong = $value['soluong'];
            $insert_order_details = "INSERT INTO tbl_cart_details(id_sp, code_cart, soluongmua) 
                VALUE('$id_sanpham', '$code_order', '$soluong')";
            mysqli_query($mysqli, $insert_order_details);
            
            // Cập nhật số lượng còn lại của sản phẩm
            $sql_desc = "SELECT soluong FROM tbl_sanpham WHERE id_sp = '$id_sanpham' LIMIT 1";
            $query_desc = mysqli_query($mysqli, $sql_desc);
            $row_desc = mysqli_fetch_array($query_desc);
            $soluongcon = $row_desc['soluong'] - $soluong;
            
            $sql_update_sl = "UPDATE tbl_sanpham SET soluong = '$soluongcon' WHERE id_sp = '$id_sanpham'";
            mysqli_query($mysqli, $sql_update_sl);
        }
    }
    unset($_SESSION['cart']);
    header('Location:../../index.php?quanly=camon');
} else {
    // Sử dụng JavaScript để thông báo lỗi và yêu cầu khách hàng đặt lại
    echo "<script>alert('Số lượng sản phẩm bạn đặt vượt quá số lượng còn trong kho. Vui lòng đặt lại.'); window.location.href='../../index.php?quanly=giohang';</script>";
}
?>
