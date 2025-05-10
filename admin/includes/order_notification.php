<?php
function sendOrderStatusNotification($orderId, $newStatus) {
    global $conn;
    
    // Get order and customer details
    $stmt = $conn->prepare("
        SELECT o.*, u.email, u.name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false;
    }
    
    // Prepare email content based on status
    $subject = "Order #$orderId Status Update";
    $message = "Dear " . htmlspecialchars($order['name']) . ",\n\n";
    
    switch ($newStatus) {
        case 'Processing':
            $message .= "Your order #{$orderId} is now being processed. We'll notify you once it's shipped.";
            break;
        case 'Shipped':
            $message .= "Great news! Your order #{$orderId} has been shipped and is on its way to you.";
            break;
        case 'Delivered':
            $message .= "Your order #{$orderId} has been delivered. We hope you enjoy your purchase!";
            break;
        case 'Cancelled':
            $message .= "Your order #{$orderId} has been cancelled. If you have any questions, please contact our support team.";
            break;
        default:
            $message .= "The status of your order #{$orderId} has been updated to {$newStatus}.";
    }
    
    $message .= "\n\nOrder Details:";
    $message .= "\nOrder Total: $" . number_format($order['total_amount'], 2);
    $message .= "\nStatus: " . $newStatus;
    $message .= "\n\nYou can view your order details at: http://yourdomain.com/customer/order_details.php?id=" . $orderId;
    
    // Email headers
    $headers = "From: Your Store <noreply@yourdomain.com>\r\n";
    $headers .= "Reply-To: support@yourdomain.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    return mail($order['email'], $subject, $message, $headers);
}
?>