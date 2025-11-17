// Payment method handling for all forms
window.selectedPaymentSaved = null;
window.totalPriceSaved = 0;
window.downpaymentAmountSaved = 0;
window.remainingBalanceSaved = 0;

// Main payment selection function
window.selectPayment = function(method, button) {
    console.log('Selecting payment:', method);
    
    // Reset all buttons to gray
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb'; // gray-200
        btn.style.color = '#374151'; // gray-700
        btn.style.border = '2px solid transparent';
    });
    
    // Style clicked button as active
    button.style.backgroundColor = '#fbbf24'; // yellow-400
    button.style.color = '#1f2937'; // gray-900
    button.style.border = '2px solid #f59e0b'; // yellow-500 border
    
    // Set hidden input
    const paymentInput = document.getElementById('payment_method');
    if (paymentInput) {
        paymentInput.value = method;
        console.log('Payment method set to:', paymentInput.value);
    }
}

// Payment selection for saved builds
window.selectPaymentSavedBuild = function(method, btn) {
    console.log('Selecting payment for saved build:', method);
    
    window.selectedPaymentSaved = method;
    const paymentInput = document.getElementById('payment_method');
    if (paymentInput) {
        paymentInput.value = method;
    }

    // Reset all payment button styles using inline styles
    document.querySelectorAll('.payment-btn-saved').forEach(b => {
        b.style.backgroundColor = '#e5e7eb'; // bg-gray-200
        b.style.color = '#374151'; // text-gray-800
        b.style.border = '2px solid transparent';
    });

    // Apply active styles based on payment method using inline styles
    if (method === 'PayPal') {
        btn.style.backgroundColor = '#fbbf24'; // bg-yellow-400
        btn.style.color = '#1f2937'; // text-gray-900
        btn.style.border = '2px solid #f59e0b'; // border-yellow-500
    } else if (method === 'PayPal_Downpayment') {
        btn.style.backgroundColor = '#c084fc'; // bg-purple-400
        btn.style.color = '#1f2937'; // text-gray-900
        btn.style.border = '2px solid #a855f7'; // border-purple-500
    }

    // Handle downpayment display
    const downpaymentSection = document.getElementById('downpayment-section-saved');
    const paymentSummary = document.getElementById('payment-summary-saved');
    const paymentAmount = document.getElementById('payment-amount-saved');
    const submitButton = document.getElementById('submit-button-saved');

    // Get the total price from the modal display (more reliable method)
    const totalPriceElement = document.querySelector('[x-text*="total_price"]');
    let totalPrice = 0;
    
    if (totalPriceElement) {
        // Extract the price from the displayed text
        const priceText = totalPriceElement.textContent;
        const priceMatch = priceText.match(/₱([\d,]+\.\d{2})/);
        if (priceMatch) {
            totalPrice = parseFloat(priceMatch[1].replace(/,/g, ''));
        }
    }
    
    // Fallback: Try to get from hidden input
    if (!totalPrice) {
        const totalPriceInput = document.querySelector('input[name="total_price"]');
        if (totalPriceInput) {
            totalPrice = parseFloat(totalPriceInput.value) || 0;
        }
    }
    
    window.totalPriceSaved = totalPrice;
    window.downpaymentAmountSaved = window.totalPriceSaved * 0.5;
    window.remainingBalanceSaved = window.totalPriceSaved * 0.5;

    console.log('Total price found:', window.totalPriceSaved);

    if (method === 'PayPal_Downpayment') {
        if (downpaymentSection) downpaymentSection.classList.remove('hidden');
        if (paymentSummary) paymentSummary.classList.remove('hidden');
        
        // Update amounts
        const downpaymentAmountEl = document.getElementById('downpayment-amount-saved');
        const remainingBalanceEl = document.getElementById('remaining-balance-saved');
        
        if (downpaymentAmountEl) downpaymentAmountEl.textContent = '₱' + window.downpaymentAmountSaved.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (remainingBalanceEl) remainingBalanceEl.textContent = '₱' + window.remainingBalanceSaved.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (paymentAmount) paymentAmount.textContent = '₱' + window.downpaymentAmountSaved.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        // Update submit button text
        if (submitButton) submitButton.textContent = 'Pay 50% Downpayment';
    } else if (method === 'PayPal') {
        if (downpaymentSection) downpaymentSection.classList.add('hidden');
        if (paymentSummary) paymentSummary.classList.remove('hidden');
        if (paymentAmount) paymentAmount.textContent = '₱' + window.totalPriceSaved.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (submitButton) submitButton.textContent = 'Pay Full Amount';
    }

    console.log('Payment method set to:', paymentInput ? paymentInput.value : 'Not found');
}

// Function to initialize payment when modal opens (call this from Alpine.js)
window.initializeSavedBuildPayment = function(totalPrice) {
    window.totalPriceSaved = parseFloat(totalPrice) || 0;
    window.downpaymentAmountSaved = window.totalPriceSaved * 0.5;
    window.remainingBalanceSaved = window.totalPriceSaved * 0.5;
    
    console.log('Initialized saved build payment with total:', window.totalPriceSaved);
    
    // Reset any previous payment selection
    window.resetSavedBuildPayment();
}

// Initialize payment buttons on load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all payment buttons have initial gray style
    document.querySelectorAll('.payment-btn').forEach(btn => {
        if (!btn.style.backgroundColor) {
            btn.style.backgroundColor = '#e5e7eb';
            btn.style.color = '#374151';
        }
    });
    
    // Ensure all saved build payment buttons have initial gray style
    document.querySelectorAll('.payment-btn-saved').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb';
        btn.style.color = '#374151';
        btn.style.border = '2px solid transparent';
    });

    // Form submission handling for saved builds
    const savedBuildForm = document.getElementById('saved-build-order-form');
    if (savedBuildForm) {
        savedBuildForm.addEventListener('submit', function(e) {
            if (!window.selectedPaymentSaved) {
                e.preventDefault();
                alert('Please select a payment method before ordering.');
                return false;
            }

            // Add downpayment amount to form if selected
            if (window.selectedPaymentSaved === 'PayPal_Downpayment') {
                const downpaymentInput = document.createElement('input');
                downpaymentInput.type = 'hidden';
                downpaymentInput.name = 'downpayment_amount';
                downpaymentInput.value = window.downpaymentAmountSaved;
                this.appendChild(downpaymentInput);
                console.log('Added downpayment amount:', window.downpaymentAmountSaved);
            }

            // Disable the submit button to prevent multiple submissions
            const submitBtn = document.getElementById('submit-button-saved');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
    }
    
    console.log('Payment module loaded');
});

// Function to update saved build total price (call this when modal opens)
window.updateSavedBuildTotalPrice = function(totalPrice) {
    window.totalPriceSaved = parseFloat(totalPrice) || 0;
    window.downpaymentAmountSaved = window.totalPriceSaved * 0.5;
    window.remainingBalanceSaved = window.totalPriceSaved * 0.5;
    
    console.log('Updated saved build total price:', window.totalPriceSaved);
}

// Function to reset saved build payment selection
window.resetSavedBuildPayment = function() {
    window.selectedPaymentSaved = null;
    window.totalPriceSaved = 0;
    window.downpaymentAmountSaved = 0;
    window.remainingBalanceSaved = 0;
    
    // Reset UI elements
    const downpaymentSection = document.getElementById('downpayment-section-saved');
    const paymentSummary = document.getElementById('payment-summary-saved');
    const submitButton = document.getElementById('submit-button-saved');
    
    if (downpaymentSection) downpaymentSection.classList.add('hidden');
    if (paymentSummary) paymentSummary.classList.add('hidden');
    if (submitButton) submitButton.textContent = 'Order Build';
    
    // Reset payment buttons
    document.querySelectorAll('.payment-btn-saved').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb';
        btn.style.color = '#374151';
        btn.style.border = '2px solid transparent';
    });
    
    // Clear payment method input
    const paymentInput = document.getElementById('payment_method');
    if (paymentInput) paymentInput.value = '';
    
    console.log('Saved build payment selection reset');
}