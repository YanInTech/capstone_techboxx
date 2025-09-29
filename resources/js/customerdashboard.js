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

// Initialize payment buttons on load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all payment buttons have initial gray style
    document.querySelectorAll('.payment-btn').forEach(btn => {
        if (!btn.style.backgroundColor) {
            btn.style.backgroundColor = '#e5e7eb';
            btn.style.color = '#374151';
        }
    });
    
    console.log('Payment module loaded');
});