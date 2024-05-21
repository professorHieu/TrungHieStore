<?php
session_start();

use Carbon\Carbon;

include("../../admin/config/config.php");
require('../../vendor/autoload.php');
$now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();


if (isset($_POST['order'])) {
    $hoten = $_POST['ten'];
    $diachi = $_POST['diachi'];
    $email = $_POST['email'];
    $sdt = $_POST['sdt'];
    $noidung = $_POST['noidung'];
    $code_order = rand(0, 9999);
    $insert_cart_unregistered = "INSERT INTO tbl_cart_unregistered(tenkh, diachi,sdt ,email,noidung,code_cart,cart_status,cart_date ) VALUE ('" . $hoten . "','" . $diachi . "','" . $sdt . "','" . $email . "','" . $noidung . "','" . $code_order . "',1,'" . $now . "')";
    $cart_query_unregistered = mysqli_query($mysqli, $insert_cart_unregistered);
    if ($cart_query_unregistered) {
        foreach ($_SESSION['cart'] as $key => $value) {
            $id_sanpham = $value['id'];
            $soluong = $value['soluong'];
            $insert_order_details = "INSERT INTO tbl_cart_details(id_sp,code_cart,soluongmua) VALUE('" . $id_sanpham . "','" . $code_order . "','" . $soluong . "')";
            mysqli_query($mysqli, $insert_order_details);
            //quan ly so luong con cua san pham
            $sql_desc = "SELECT * FROM tbl_sanpham WHERE tbl_sanpham.id_sp = '$id_sanpham' LIMIT 1";
            $query_desc = mysqli_query($mysqli, $sql_desc);
            while ($row_desc = mysqli_fetch_array($query_desc)) {
                $soluongtong = $row_desc['soluong'];
                $soluongcon = $row_desc['soluong'] - $soluong;
            }
            //update lại số lượng
            $sql_update_sl = "UPDATE tbl_sanpham SET soluong = '" . $soluongcon . "' WHERE id_sp = '$id_sanpham'";
            mysqli_query($mysqli, $sql_update_sl);
        }
    }
}
unset($_SESSION['cart']);
header('Location:../../index.php?quanly=camon');
