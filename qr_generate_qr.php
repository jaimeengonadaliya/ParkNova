<?php
// require_once "phpqrcode/qrlib.php";

$text = $_GET['id'] ?? "BookingID_DEBUG_12345";

// Since phpqrcode is a library that might not be present, 
// we provide a premium placeholder or use a public API for demonstration.

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($text);

// If user really wants to use the local library:
// QRcode::png($text, "ticket.png");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ParkNova Ticket - <?= htmlspecialchars($text) ?></title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f1f5f9; margin: 0; }
        .ticket { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; border-top: 10px solid #2563eb; }
        h2 { color: #0f172a; margin-bottom: 5px; }
        p { color: #64748b; margin-bottom: 30px; font-weight: bold; }
        img { width: 250px; background: #f8fafc; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .footer { margin-top: 30px; font-size: 0.8rem; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="ticket">
        <div style="font-size: 3rem; color: #2563eb; margin-bottom: 10px;">🅿️</div>
        <h2>ParkNova Ticket</h2>
        <p>BOOKING ID: <span style="color: #2563eb;"><?= htmlspecialchars($text) ?></span></p>
        <img src="<?= $qr_url ?>" alt="QR Code">
        <div class="footer">
            Scan this QR at the parking entry/exit.<br>
            ParkNova Smart Systems &copy; <?= date('Y') ?>
        </div>
    </div>
</body>
</html>



