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
        "cancelUrl" => $DOMAIN . "/index.php?quanly=cancel&mode=register"
    ];

    try {
        // Kiểm tra số lượng tồn kho trước khi tạo đơn hàng
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
        $response = $payOS->createPaymentLink($data);

        $now = Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString();
        $id_khachhang = $_SESSION['id_khachhang'];
        $code_order = rand(0, 9999);
        $insert_cart_registered = "INSERT INTO tbl_cart_registered (id_khachhang, code_cart, cart_status, cart_date,hinhthuc) VALUES ('$id_khachhang', '$code_order', 1, '$now','PayOS')";
        $cart_query = mysqli_query($mysqli, $insert_cart_registered);

        if ($cart_query) {
            $insertedID = mysqli_insert_id($mysqli);
            $insert_pay_os = "INSERT INTO tbl_pay_os_order (payos_order_code, cart_type, order_id) 
            VALUES ('$orderCode', 1, '$insertedID')";
            mysqli_query($mysqli, $insert_pay_os);

            foreach ($_SESSION['cart'] as $key => $value) {
                $id_sanpham = $value['id'];
                $soluong = $value['soluong'];
                $soluongcon = $row_desc['soluong'] - $soluong;

                // Chèn vào tbl_cart_details
                $insert_order_details = "INSERT INTO tbl_cart_details(id_sp, code_cart, soluongmua) 
                VALUES ('$id_sanpham', '$code_order', '$soluong')";
                mysqli_query($mysqli, $insert_order_details);

                // Cập nhật lại số lượng sản phẩm trong kho
                $sql_update_sl = "UPDATE tbl_sanpham SET soluong = '$soluongcon' WHERE id_sp = '$id_sanpham'";
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
