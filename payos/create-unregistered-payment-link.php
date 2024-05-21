<?php

require_once __DIR__ . '/../vendor/autoload.php';
include(__DIR__ . '/../admin/config/config.php');

use PayOS\PayOS;
use Carbon\Carbon;

session_start();
$payOsClientId = "2b704ee2-ede2-41fd-9d3a-cfcf51ac8bd4";
$payOsApiKey = "5b8f11fa-8581-45ca-a6e2-a762229ed12e";
$payOsChecksumKey = "47042f2624ae6d846d0de73572cadcbec87812d833de8ebf57733e0c5baca522";

// Initialize PayOS
$payOS = new PayOS($payOsClientId, $payOsApiKey, $payOsChecksumKey);

$DOMAIN = "http://localhost:8081/TrungHieStore";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $amount = intval(number_format($amount, 0, '.', ''));
    $orderCode = intval(substr(strval(microtime(true) * 10000), -6));
    $data = [
        "orderCode" => $orderCode,
        "amount" => $amount,
        "description" => "Test",
        "returnUrl" => $DOMAIN . "/index.php?quanly=camon",
        "cancelUrl" => $DOMAIN . "/index.php?quanly=cancel&mode=unregister"
    ];

    try {
        $hoten = $_POST['ten'];
        $diachi = $_POST['diachi'];
        $email = $_POST['email'];
        $sdt = $_POST['sdt'];
        $noidung = $_POST['noidung'];
        $code_order = rand(0, 9999);
        $now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();

        // Kiểm tra số lượng tồn kho trước khi thực hiện bất kỳ thao tác nào khác
        foreach ($_SESSION['cart'] as $key => $value) {
            $id_sanpham = $value['id'];
            $soluong = $value['soluong'];

            $sql_desc = "SELECT soluong FROM tbl_sanpham WHERE id_sp = '$id_sanpham' LIMIT 1";
            $query_desc = mysqli_query($mysqli, $sql_desc);
            $row_desc = mysqli_fetch_array($query_desc);

            if ($row_desc) {
                $soluongton = $row_desc['soluong'];

                if ($soluong > $soluongton) {
                    // Hiển thị thông báo lỗi nếu số lượng đặt hàng vượt quá số lượng tồn kho
                    echo "<script>alert('Số lượng đặt hàng vượt quá số lượng tồn kho. Vui lòng điều chỉnh số lượng.'); window.history.back();</script>";
                    exit;
                }
            } else {
                // Xử lý lỗi nếu sản phẩm không tìm thấy
                echo "<script>alert('Không tìm thấy sản phẩm với ID: " . $id_sanpham . "'); window.history.back();</script>";
                exit;
            }
        }

        // Nếu qua được kiểm tra số lượng tồn kho, thực hiện lưu thông tin vào cơ sở dữ liệu
        $insert_cart_unregistered = "INSERT INTO tbl_cart_unregistered(tenkh, diachi, sdt, email, noidung, code_cart, cart_status, cart_date) 
                                     VALUES ('$hoten', '$diachi', '$sdt', '$email', '$noidung', '$code_order', 1, '$now')";
        $cart_query_unregistered = mysqli_query($mysqli, $insert_cart_unregistered);
        if ($cart_query_unregistered) {
            $response = $payOS->createPaymentLink($data);
            $insertedID = mysqli_insert_id($mysqli);
            $insert_pay_os = "INSERT INTO tbl_pay_os_order (payos_order_code, cart_type, order_id) 
                             VALUES ('$orderCode', 2, '$insertedID')";
            mysqli_query($mysqli, $insert_pay_os);

            foreach ($_SESSION['cart'] as $key => $value) {
                $id_sanpham = $value['id'];
                $soluong = $value['soluong'];
                $insert_order_details = "INSERT INTO tbl_cart_details(id_sp, code_cart, soluongmua) 
                                         VALUES ('$id_sanpham', '$code_order', '$soluong')";
                mysqli_query($mysqli, $insert_order_details);

                // Cập nhật lại số lượng sản phẩm trong kho
                $sql_update_sl = "UPDATE tbl_sanpham SET soluong = soluong - $soluong WHERE id_sp = '$id_sanpham'";
                mysqli_query($mysqli, $sql_update_sl);
            }
            unset($_SESSION['cart']);
            header("Location: " . $response['checkoutUrl']);
            exit;
        }
    } catch (\Throwable $th) {
        echo $th->getMessage();
        return $th->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
