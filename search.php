<?php
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

    if (!isset($conn)) {
        throw new Exception('Database connection not established');
    }

    if (isset($_GET['q'])) {
        header('Content-Type: application/json');
        
        try {
            $query = "%" . $_GET['q'] . "%";
            $stmt = $conn->prepare("SELECT id, title, sale_price as price, image FROM products WHERE title LIKE ? OR details LIKE ? LIMIT 10");
            $stmt->execute([$query, $query]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add absolute URLs to the results based on login status
            session_start();
            $isLoggedIn = isset($_SESSION['user_id']);
            
            foreach ($results as &$product) {
                // Direct to customer area if logged in, otherwise to public product detail
                $product['url'] = $isLoggedIn ? 
                    '/customer/product_details.php?id=' . $product['id'] : 
                    '/product_details.php?id=' . $product['id'];
                    
                $product['image'] = !empty($product['image']) ? '/uploads/' . $product['image'] : '/assets/images/placeholder.jpg';
                $product['price'] = number_format((float)$product['price'], 2, '.', '');
            }

            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
        } catch (PDOException $e) {
            error_log("Search query error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Database error occurred while searching'
            ]);
        }
        exit();
    }

    // If accessed directly without a query, redirect to home
    header('Location: /');
    exit();

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while searching'
    ]);
    exit();
}
