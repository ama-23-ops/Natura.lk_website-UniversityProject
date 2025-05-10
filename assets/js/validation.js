function showNotification(message) {
    // Existing showNotification function
    console.log(message); // For debugging purposes
}

function validateProfileForm() {
    let isValid = true;

    isValid = validateName() && isValid;
    isValid = validateEmailField() && isValid;
    isValid = validateContactNumber() && isValid;
    isValid = validateAddress() && isValid; // Add this line

    return isValid;
}

function validatePasswordForm() {
    let isValid = true;

    isValid = validateCurrentPassword() && isValid;
    isValid = validateNewPassword() && isValid;
    isValid = validateConfirmPassword() && isValid;

    return isValid;
}

function validateProductForm() {
    let isValid = true;

    isValid = validateProductTitle() && isValid;
    isValid = validatePurchaseCost() && isValid;
    isValid = validateSalePrice() && isValid;
    isValid = validateStockQuantity() && isValid;
    isValid = validateDiscount() && isValid;
    isValid = validateProductDetails() && isValid;

    return isValid;
}

function validateProductTitle() {
    const title = document.getElementById('title').value.trim();
    const titleError = document.getElementById('titleError');
    if (title === '') {
        titleError.textContent = 'Product title is required.';
        return false;
    } else if (title.length < 3) {
        titleError.textContent = 'Product title must be at least 3 characters long.';
        return false;
    } else {
        titleError.textContent = '';
        return true;
    }
}

function validatePurchaseCost() {
    const cost = document.getElementById('purchase_cost').value.trim();
    const costError = document.getElementById('purchaseCostError');
    if (cost === '') {
        costError.textContent = 'Purchase cost is required.';
        return false;
    } else if (isNaN(cost) || parseFloat(cost) < 0) {
        costError.textContent = 'Please enter a valid purchase cost.';
        return false;
    } else {
        costError.textContent = '';
        return true;
    }
}

function validateSalePrice() {
    const price = document.getElementById('sale_price').value.trim();
    const priceError = document.getElementById('salePriceError');
    if (price === '') {
        priceError.textContent = 'Sale price is required.';
        return false;
    } else if (isNaN(price) || parseFloat(price) < 0) {
        priceError.textContent = 'Please enter a valid sale price.';
        return false;
    } else {
        priceError.textContent = '';
        return true;
    }
}

function validateStockQuantity() {
    const stock = document.getElementById('stock_quantity').value.trim();
    const stockError = document.getElementById('stockQuantityError');
    if (stock === '') {
        stockError.textContent = 'Stock quantity is required.';
        return false;
    } else if (isNaN(stock) || parseInt(stock) < 0 || !Number.isInteger(parseFloat(stock))) {
        stockError.textContent = 'Please enter a valid stock quantity (whole number).';
        return false;
    } else {
        stockError.textContent = '';
        return true;
    }
}

function validateDiscount() {
    const discount = document.getElementById('discount').value.trim();
    const discountError = document.getElementById('discountError');
    if (discount === '') {
        // Discount can be empty (default 0)
        discountError.textContent = '';
        return true;
    } else if (isNaN(discount) || parseFloat(discount) < 0 || parseFloat(discount) > 100) {
        discountError.textContent = 'Please enter a valid discount between 0 and 100.';
        return false;
    } else {
        discountError.textContent = '';
        return true;
    }
}

function validateProductDetails() {
    const details = document.getElementById('details').value.trim();
    const detailsError = document.getElementById('detailsError');
    if (details.length > 1000) {
        detailsError.textContent = 'Product details must be less than 1000 characters.';
        return false;
    } else {
        detailsError.textContent = '';
        return true;
    }
}

function validateName() {
    const name = document.getElementById('name').value.trim();
    const nameError = document.getElementById('nameError');
    if (name === '') {
        nameError.textContent = 'Name is required.';
        return false;
    } else {
        nameError.textContent = '';
        return true;
    }
}

function validateEmailField() {
    const email = document.getElementById('email').value.trim();
    const emailError = document.getElementById('emailError');
    if (email === '') {
        emailError.textContent = 'Email is required.';
        return false;
    } else if (!validateEmail(email)) {
        emailError.textContent = 'Please enter a valid email address.';
        return false;
    } else {
        emailError.textContent = '';
        return true;
    }
}

function validateContactNumber() {
    const contactNumber = document.getElementById('contact_number').value.trim();
    const contactNumberError = document.getElementById('contactNumberError');
    const phoneRegex = /^[0-9]+$/; // Regex to allow only numbers

    if (contactNumber === '') {
        contactNumberError.textContent = 'Contact number is required.';
        return false;
    } else if (!phoneRegex.test(contactNumber)) {
        contactNumberError.textContent = 'Contact number must contain only numbers.';
        return false;
    } else if (contactNumber.length < 10 || contactNumber.length > 12) {
        contactNumberError.textContent = 'Contact number must be between 10 and 12 digits.';
        return false;
    } else {
        contactNumberError.textContent = '';
        return true;
    }
}

function validateCurrentPassword() {
    const currentPassword = document.getElementById('current_password').value.trim();
    const currentPasswordError = document.getElementById('currentPasswordError');
    if (currentPassword === '') {
        currentPasswordError.textContent = 'Current password is required.';
        return false;
    } else {
        currentPasswordError.textContent = '';
        return true;
    }
}

function validateNewPassword() {
    const newPassword = document.getElementById('new_password').value.trim();
    const newPasswordError = document.getElementById('newPasswordError');
    if (newPassword === '') {
        newPasswordError.textContent = 'New password is required.';
        return false;
    } else {
        newPasswordError.textContent = '';
        return true;
    }
}

function validateConfirmPassword() {
    const newPassword = document.getElementById('new_password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    if (confirmPassword === '') {
        confirmPasswordError.textContent = 'Confirm password is required.';
        return false;
    } else if (newPassword !== confirmPassword) {
        confirmPasswordError.textContent = 'Passwords do not match.';
        return false;
    } else {
        confirmPasswordError.textContent = '';
        return true;
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateAddress() {
    const address = document.getElementById('address');
    const addressError = document.getElementById('addressError');
    
    if (address.value.trim().length < 10) {
        addressError.textContent = 'Address must be at least 10 characters long';
        return false;
    }
    addressError.textContent = '';
    return true;
}
