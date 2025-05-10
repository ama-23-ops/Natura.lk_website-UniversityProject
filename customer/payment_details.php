<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Check if shipping address is set in session (user should complete the first step first)
if (!isset($_SESSION['checkout_shipping_address']) || !isset($_SESSION['checkout_final_total'])) {
    header("Location: /customer/checkout.php");
    exit();
}

// Get data from session
$cart_total = $_SESSION['checkout_cart_total'];
$total_discount = $_SESSION['checkout_discount'];
$net_total = $_SESSION['checkout_net_total'];
$tax = $_SESSION['checkout_tax'];
$final_total = $_SESSION['checkout_final_total'];

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['continue_to_confirmation'])) {
    // Store payment method in session
    $_SESSION['payment_method'] = $_POST['payment_method'];
    
    // If credit card is selected, store card details
    if ($_POST['payment_method'] == 'credit') {
        $_SESSION['card_number'] = $_POST['card_number'];
        $_SESSION['card_name'] = $_POST['card_name'];
        $_SESSION['card_expiry'] = $_POST['card_expiry'];
    } else {
        // For PayPal, we don't need card details
        $_SESSION['card_number'] = '';
        $_SESSION['card_name'] = '';
        $_SESSION['card_expiry'] = '';
    }
    
    // Redirect to order summary page
    header("Location: /customer/order_summary.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Payment Details</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        }
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 33.333%;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        .step-icon.active {
            background-color: #0d9488;
            color: white;
        }
        .step-line {
            height: 4px;
            background-color: #e2e8f0;
            width: 100%;
            position: relative;
            top: -22px;
            z-index: 0;
        }
        .step-line.active {
            background-color: #0d9488;
        }
        .step:first-child .step-line-left,
        .step:last-child .step-line-right {
            opacity: 0;
        }
        .step-text {
            font-weight: 500;
            color: #64748b;
        }
        .step-text.active {
            color: #0d9488;
            font-weight: bold;
        }
        .payment-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .payment-card.selected {
            border-color: #0d9488;
            background-color: #f0fdfa;
        }
        .credit-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            max-width: 400px;
            font-family: 'Courier New', monospace;
        }
        .credit-card-chip {
            width: 50px;
            height: 40px;
            background: linear-gradient(135deg, #d4af37 0%, #f9d423 100%);
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .credit-card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6">
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-credit-card text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Payment Details</h1>
                    <p class="text-teal-100">Secure payment information</p>
                </div>
            </div>
        </div>

        <!-- Checkout Steps -->
        <div class="checkout-steps mb-8">
            <div class="step">
                <div class="step-icon active">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="step-line step-line-left"></div>
                <div class="step-line step-line-right active"></div>
                <span class="step-text active">Shipping</span>
            </div>
            <div class="step">
                <div class="step-icon active">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="step-line step-line-left active"></div>
                <div class="step-line step-line-right"></div>
                <span class="step-text active">Payment</span>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-line step-line-left"></div>
                <div class="step-line step-line-right"></div>
                <span class="step-text">Confirmation</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Payment Form -->
            <div class="md:col-span-2">
                <form method="post" id="payment-form" class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-money-check-alt mr-2 text-teal-600"></i>
                            Payment Method
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="payment-card cursor-pointer rounded-lg border p-4 flex items-center selected" onclick="selectPayment('credit')">
                                <input type="radio" name="payment_method" id="credit" value="credit" class="mr-3" checked>
                                <label for="credit" class="cursor-pointer flex items-center w-full">
                                    <i class="fas fa-credit-card text-teal-600 text-xl mr-3"></i>
                                    <span>Credit / Debit Card</span>
                                </label>
                            </div>
                            
                            <div class="payment-card cursor-pointer rounded-lg border p-4 flex items-center" onclick="selectPayment('paypal')">
                                <input type="radio" name="payment_method" id="paypal" value="paypal" class="mr-3">
                                <label for="paypal" class="cursor-pointer flex items-center w-full">
                                    <i class="fab fa-paypal text-blue-600 text-xl mr-3"></i>
                                    <span>PayPal</span>
                                </label>
                            </div>
                        </div>
                        
                        <div id="credit-card-form">
                            <div class="mb-8">
                                <div class="credit-card mx-auto mb-8">
                                    <div class="credit-card-chip"></div>
                                    <div class="credit-card-number mb-4 text-xl tracking-wider">
                                        <span id="card-preview">•••• •••• •••• ••••</span>
                                    </div>
                                    <div class="credit-card-row text-sm">
                                        <div>
                                            <div class="text-gray-300 text-xs mb-1">CARD HOLDER</div>
                                            <div id="name-preview">YOUR NAME</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-300 text-xs mb-1">EXPIRES</div>
                                            <div id="expiry-preview">MM/YY</div>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="space-y-4">
                                    <div>
                                        <label for="card_number" class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                                        <div class="relative">
                                            <input 
                                                type="text" 
                                                id="card_number" 
                                                name="card_number" 
                                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 pr-10" 
                                                placeholder="1234 5678 9012 3456"
                                                maxlength="19"
                                                oninput="formatCardNumber(this); updateCardPreview();"
                                                required
                                            >
                                            <div class="absolute right-3 top-2">
                                                <i class="fab fa-cc-visa text-blue-800 text-2xl"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="card_name" class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                                        <input 
                                            type="text" 
                                            id="card_name" 
                                            name="card_name" 
                                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" 
                                            placeholder="Name as shown on card"
                                            oninput="updateCardPreview();"
                                            required
                                        >
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="card_expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                                            <input 
                                                type="text" 
                                                id="card_expiry" 
                                                name="card_expiry" 
                                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" 
                                                placeholder="MM/YY"
                                                maxlength="5"
                                                oninput="formatExpiry(this); updateCardPreview();"
                                                required
                                            >
                                        </div>
                                        <div>
                                            <label for="card_cvv" class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                            <input 
                                                type="text" 
                                                id="card_cvv" 
                                                name="card_cvv" 
                                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" 
                                                placeholder="123"
                                                maxlength="3"
                                                required
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="paypal-form" class="hidden">
                            <div class="p-6 text-center">
                                <i class="fab fa-paypal text-blue-600 text-5xl mb-4"></i>
                                <p class="mb-4">You will be redirected to PayPal to complete your purchase securely.</p>
                                <p class="text-sm text-gray-500">Note: This is a demo. No actual redirect will happen.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <a href="/customer/checkout.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-full inline-flex items-center gap-2 transition duration-300">
                            <i class="fas fa-arrow-left"></i> Back to Shipping
                        </a>
                        
                        <button 
                            type="submit" 
                            name="continue_to_confirmation" 
                            class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-full inline-flex items-center gap-2 transition duration-300"
                        >
                            Confirm <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                        <i class="fas fa-shopping-bag mr-2 text-teal-600"></i>
                        Order Summary
                    </h2>
                    
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <p class="text-gray-600 mb-2">Subtotal</p>
                        <p class="font-bold text-xl">$<?= number_format($cart_total, 2) ?></p>
                    </div>
                    
                    <?php if ($total_discount > 0) : ?>
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <p class="text-gray-600 mb-2">Discount</p>
                        <p class="text-red-500">-$<?= number_format($total_discount, 2) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <p class="text-gray-600 mb-2">Net Price</p>
                        <p class="font-bold">$<?= number_format($net_total, 2) ?></p>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <p class="text-gray-600 mb-2">Tax (10%)</p>
                        <p>$<?= number_format($tax, 2) ?></p>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <p class="text-gray-600 mb-2">Shipping</p>
                        <p>Free</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-gray-600 mb-2">Total</p>
                        <p class="font-bold text-2xl text-teal-600">$<?= number_format($final_total, 2) ?></p>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-shield-alt text-teal-600 mr-2"></i>
                            <span class="text-sm font-medium">Secure Payment</span>
                        </div>
                        <p class="text-xs text-gray-500">Your payment information is encrypted and secure.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
    
    <script>
        function selectPayment(type) {
            // Reset all cards
            document.querySelectorAll('.payment-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Select current card
            const selectedCard = document.querySelector(`#${type}`).parentElement;
            selectedCard.classList.add('selected');
            
            // Show relevant form
            if (type === 'credit') {
                document.getElementById('credit-card-form').classList.remove('hidden');
                document.getElementById('paypal-form').classList.add('hidden');
                
                // Add required attribute to credit card fields
                document.getElementById('card_number').setAttribute('required', 'required');
                document.getElementById('card_name').setAttribute('required', 'required');
                document.getElementById('card_expiry').setAttribute('required', 'required');
                document.getElementById('card_cvv').setAttribute('required', 'required');
            } else {
                document.getElementById('credit-card-form').classList.add('hidden');
                document.getElementById('paypal-form').classList.remove('hidden');
                
                // Remove required attribute from credit card fields
                document.getElementById('card_number').removeAttribute('required');
                document.getElementById('card_name').removeAttribute('required');
                document.getElementById('card_expiry').removeAttribute('required');
                document.getElementById('card_cvv').removeAttribute('required');
            }
            
            // Check the radio
            document.getElementById(type).checked = true;
        }
        
        function formatCardNumber(input) {
            // Remove all non-numeric characters
            let value = input.value.replace(/\D/g, '');
            // Add a space after every 4 digits
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            // Update the input value
            input.value = value;
        }
        
        function formatExpiry(input) {
            // Remove all non-numeric characters
            let value = input.value.replace(/\D/g, '');
            // Format as MM/YY
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            // Update the input value
            input.value = value;
        }
        
        function updateCardPreview() {
            // Update card number preview
            const cardNumber = document.getElementById('card_number').value;
            if (cardNumber) {
                document.getElementById('card-preview').textContent = cardNumber;
            } else {
                document.getElementById('card-preview').textContent = '•••• •••• •••• ••••';
            }
            
            // Update name preview
            const cardName = document.getElementById('card_name').value;
            if (cardName) {
                document.getElementById('name-preview').textContent = cardName.toUpperCase();
            } else {
                document.getElementById('name-preview').textContent = 'YOUR NAME';
            }
            
            // Update expiry preview
            const cardExpiry = document.getElementById('card_expiry').value;
            if (cardExpiry) {
                document.getElementById('expiry-preview').textContent = cardExpiry;
            } else {
                document.getElementById('expiry-preview').textContent = 'MM/YY';
            }
        }
    </script>
</body>
</html>