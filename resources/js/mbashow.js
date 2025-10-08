// Function to calculate total based on checked items
            function updateTotal() {
                const checkboxes = document.querySelectorAll('.item-checkbox');
                let total = 0;
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        total += parseFloat(cb.dataset.price);
                    }
                });
                document.getElementById('totalPrice').textContent = total.toLocaleString('en-PH', {minimumFractionDigits: 2});
            }

            // Initial calculation
            updateTotal();

            // Update total whenever a checkbox changes
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateTotal);
            });