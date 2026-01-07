// Initialize global selectedComponents object
window.selectedComponents = window.selectedComponents || {};
// Make payment variables globally accessible
window.selectedPayment = null;
window.totalPrice = 0;
window.downpaymentAmount = 0;
window.remainingBalance = 0;

// Add this function to check if all required components are selected
function areAllComponentsSelected() {
    // Define all required component types
    const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case'];
    
    // Check if all required components are selected
    const allRequiredSelected = requiredComponents.every(type => {
        return window.selectedComponents[type] && window.selectedComponents[type].componentId;
    });
    
    // Check if at least one storage component is selected
    const storageSelected = window.selectedComponents.ssd?.componentId || 
                           window.selectedComponents.hdd?.componentId;
    
    return allRequiredSelected && storageSelected;
}

// Function to update the validate button state
function updateValidateButton() {
    const validateButton = document.getElementById('validateBuild');
    if (!validateButton) return;
    
    const allComponentsSelected = areAllComponentsSelected();
    
    // Enable/disable button based on component selection
    validateButton.disabled = !allComponentsSelected;
    
    // Update button appearance
    if (allComponentsSelected) {
        validateButton.classList.remove('bg-gray-400');
        validateButton.classList.add('bg-green-600', 'hover:bg-green-700');
    } else {
        validateButton.classList.remove('bg-green-600', 'hover:bg-green-700');
        validateButton.classList.add('bg-gray-400');
    }
}

// Function to show which components are missing (for debugging or user info)
function getMissingComponents() {
    const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case'];
    const missingComponents = [];
    
    requiredComponents.forEach(type => {
        if (!window.selectedComponents[type] || !window.selectedComponents[type].componentId) {
            const componentNames = {
                'gpu': 'GPU',
                'motherboard': 'Motherboard',
                'cpu': 'CPU',
                'psu': 'Power Supply',
                'ram': 'RAM',
                'cooler': 'Cooler',
                'case': 'Case'
            };
            missingComponents.push(componentNames[type]);
        }
    });
    
    // Check storage
    if (!window.selectedComponents.ssd?.componentId && !window.selectedComponents.hdd?.componentId) {
        missingComponents.push('Storage (HDD or SSD)');
    }
    
    return missingComponents;
}

function updateSelectedComponentsDisplay() {
    const tbody = document.getElementById('components-body');
    const emptyState = document.getElementById('empty-state');
    const totalRow = document.getElementById('total-row');
    const totalPriceEl = document.getElementById('total-price');
    
    if (!tbody || !emptyState || !totalRow) return;
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    if (Object.keys(window.selectedComponents).length === 0) {
        emptyState.classList.remove('hidden');
        totalRow.classList.add('hidden');
        return;
    }
    
    // Hide empty state, show total row
    emptyState.classList.add('hidden');
    totalRow.classList.remove('hidden');
    
    let total = 0;
    
    // Add compact component rows
    for (const [type, component] of Object.entries(window.selectedComponents)) {
        const row = document.createElement('tr');
        row.className = 'border-t border-gray-100';
        
        // Format type display - shorter
        const typeDisplay = {
            'cpu': 'CPU',
            'gpu': 'GPU',
            'ram': 'RAM',
            'psu': 'PSU',
            'ssd': 'SSD',
            'hdd': 'HDD',
            'motherboard': 'MBD',
            'cooler': 'CLR',
            'case': 'CASE'
        }[type] || type.substring(0, 3).toUpperCase();
        
        // Truncate component name if too long
        let componentName = component.name;
        if (componentName.length > 10) {
            componentName = componentName.substring(0, 12) + '...';
        }
        
        row.innerHTML = `
            <td class="py-1 px-2">
                <p class="text-[10px] font-semibold text-gray-700">${typeDisplay}</p>
            </td>
            <td class="py-1 px-2">
                <p class="text-gray-800" title="${component.name}">${componentName}</p>
            </td>
            <td class="py-1 px-2 text-right whitespace-nowrap">
                <p class="font-medium text-green-600 text-sm">
                    ₱${component.price.toFixed(2)}
                </p>
            </td>
        `;
        tbody.appendChild(row);
        
        total += component.price || 0;
    }
    
    // Update total price
    if (totalPriceEl) {
        totalPriceEl.textContent = `₱${total.toFixed(2)}`;
    }
}

// Function to handle component selection from catalog
function selectComponent(componentData) {
    const componentType = componentData.type.toLowerCase();
    
    // CALL TOGGLE STORAGE IF SSD OR HDD IS SELECTED - DO THIS FIRST
    if (componentType === 'ssd' || componentType === 'hdd') {
        toggleStorage(componentType);
    }
    
    window.selectedComponents[componentType] = {
        componentId: componentData.id,
        name: componentData.name,
        price: componentData.price,
        image: componentData.image,
        modelUrl: componentData.modelUrl || null,
        type: componentType
    };
    
    console.log('Selected component:', componentType, window.selectedComponents[componentType]);

    // update the sidebar display
    updateSelectedComponentsDisplay();

    // Update session in backend
    updateSession(window.selectedComponents);
    
    // Update total price for payment calculations
    updateTotalPrice();

    updateValidateButton();
}

// Update total price calculation
function updateTotalPrice() {
    window.totalPrice = 0;
    for (const [type, component] of Object.entries(window.selectedComponents)) {
        if (component && component.price) {
            window.totalPrice += component.price;
        }
    }
    window.downpaymentAmount = window.totalPrice * 0.5;
    window.remainingBalance = window.totalPrice * 0.5;
    
    console.log('Updated total price:', window.totalPrice);
}

// BUILD CART FORM SUBMISSION - UPDATED VERSION
function handleFormSubmit(e) {
    console.log('=== FORM SUBMISSION INTERCEPTED ===');
    e.preventDefault(); // PREVENT DEFAULT IMMEDIATELY
    
    // Get the form to check its action (order vs save)
    const form = e.target;
    const isOrder = form.action.includes('order');
    console.log('Form type:', isOrder ? 'ORDER' : 'SAVE');

    
    // Payment method validation - ONLY FOR ORDERS
    if (isOrder) {
        const paymentMethod = document.getElementById('payment_method').value;
        console.log('Payment method:', paymentMethod);
        if (!paymentMethod) {
            alert('Please select a payment method.');
            return false;
        }
    }

    // Build name validation
    const buildNameInput = document.querySelector('input[name="build_name"]');
    const buildName = buildNameInput ? buildNameInput.value : '';
    console.log('Build name:', buildName);
    if (!buildName.trim()) {
        alert('Please enter a build name.');
        return false;
    }

    // Check if any components are selected
    console.log('Selected components:', window.selectedComponents);
    if (Object.keys(window.selectedComponents).length === 0) {
        alert('Please select at least one component.');
        return false;
    }

    // Update all hidden inputs before submission
    console.log('=== UPDATING HIDDEN INPUTS ===');
    for (const [type, component] of Object.entries(window.selectedComponents)) {
        const hiddenInput = document.getElementById(`hidden_${type}`);
        if (hiddenInput && component && component.componentId) {
            hiddenInput.value = component.componentId;
            console.log(`Set ${type} to:`, component.componentId);
        } else {
            console.log(`Missing hidden input or component for: ${type}`);
        }
    }

    // Update storage components specifically
    const storageInput = document.getElementById('hidden_storage');
    if (storageInput) {
        if (window.selectedComponents.hdd && window.selectedComponents.hdd.componentId) {
            storageInput.value = window.selectedComponents.hdd.componentId;
        } else if (window.selectedComponents.ssd && window.selectedComponents.ssd.componentId) {
            storageInput.value = window.selectedComponents.ssd.componentId;
        }
        console.log('Storage set to:', storageInput.value);
    }

    // Update total price hidden input
    const totalPriceInput = document.getElementById('hidden_total_price');
    if (totalPriceInput) {
        let totalPrice = 0;
        for (const [type, component] of Object.entries(window.selectedComponents)) {
            if (component && component.price) {
                totalPrice += component.price;
            }
        }
        totalPriceInput.value = totalPrice.toFixed(2);
        console.log('Total price set to:', totalPriceInput.value);
    }

    // VALIDATE IF ALL COMPONENTS ARE SELECTED
    console.log('=== VALIDATING COMPONENTS ===');
    const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case', 'storage'];
    const allComponentsSelected = requiredComponents.every(type => {
        if (type === 'storage') {
            return (window.selectedComponents.hdd && window.selectedComponents.hdd.componentId) || 
                   (window.selectedComponents.ssd && window.selectedComponents.ssd.componentId);
        }
        return window.selectedComponents[type] && window.selectedComponents[type].componentId;
    });

    console.log('All components selected:', allComponentsSelected);

    if (!allComponentsSelected) {
        const missingComponents = [];
        requiredComponents.forEach(type => {
            if (type === 'storage') {
                if (!window.selectedComponents.hdd?.componentId && !window.selectedComponents.ssd?.componentId) {
                    missingComponents.push('Storage (HDD or SSD)');
                }
            } else if (!window.selectedComponents[type]?.componentId) {
                const componentNames = {
                    'gpu': 'GPU',
                    'motherboard': 'Motherboard',
                    'cpu': 'CPU',
                    'psu': 'Power Supply',
                    'ram': 'RAM',
                    'cooler': 'Cooler',
                    'case': 'Case'
                };
                missingComponents.push(componentNames[type]);
            }
        });

        alert(`Please select the following components:\n\n${missingComponents.join('\n')}`);
        return false;
    }

    console.log('=== FORM VALIDATION PASSED - SUBMITTING ===');
    
    // If all validations pass, submit the form programmatically
    console.log('Submitting form...');
    form.submit(); // Use form.submit() instead of this.submit()
}

function setComponentImage(componentData) {
    const targetButton = document.querySelector(`.component-button[data-type="${componentData.type}"]`);
    if (targetButton) {
        const imgTag = targetButton.querySelector('img');
        if (imgTag && componentData.image) {
            imgTag.src = componentData.image;
            imgTag.style.display = 'block';
        }
        // Set the selected ID on the button to indicate the item has been selected
        targetButton.setAttribute('data-selected-id', componentData.id);
    }
}

// Storage toggle function to clear the other storage type
function toggleStorage(selectedType) {
    const otherType = selectedType === 'ssd' ? 'hdd' : 'ssd';
    
    console.log(`Toggle storage: ${selectedType} selected, checking ${otherType}`);
    
    // If the other type was previously selected, clear it
    if (window.selectedComponents[otherType]) {
        // Clear from selectedComponents
        delete window.selectedComponents[otherType];

        // update sidebar display
        updateSelectedComponentsDisplay();

        updateValidateButton();
        
        // Clear the UI for the other storage type
        const otherComponent = document.querySelector(`.component-button[data-type="${otherType}"]`);
        if (otherComponent) {
            // Reset the component button
            otherComponent.innerHTML = `
                <img src="" alt="" style="display: none;">
                <p>${otherType.toUpperCase()}</p>
            `;
            otherComponent.removeAttribute('data-selected-id');
        }
        
        // Clear hidden input
        const otherHiddenInput = document.getElementById(`hidden_${otherType}`);
        if (otherHiddenInput) otherHiddenInput.value = '';
        
        console.log(`Cleared ${otherType} from selection`);
        
        // Update total price after clearing
        updateTotalPrice();
    } else {
        console.log(`No ${otherType} to clear`);
    }
}

// Payment method handling for buildext
window.selectPayment = function(method, button) {
    console.log('Selecting payment:', method);
    
    window.selectedPayment = method;
    const paymentInput = document.getElementById('payment_method');
    if (paymentInput) {
        paymentInput.value = method;
    }

    // Reset all payment button styles using inline styles
    document.querySelectorAll('.payment-btn').forEach(b => {
        b.style.backgroundColor = '#e5e7eb'; // bg-gray-200
        b.style.color = '#374151'; // text-gray-800
        b.style.border = '2px solid transparent';
    });

    // Apply active styles based on payment method using inline styles
    if (method === 'PayPal') {
        button.style.backgroundColor = '#fbbf24'; // bg-yellow-400
        button.style.color = '#1f2937'; // text-gray-900
        button.style.border = '2px solid #f59e0b'; // border-yellow-500
    } else if (method === 'PayPal_Downpayment') {
        button.style.backgroundColor = '#c084fc'; // bg-purple-400
        button.style.color = '#1f2937'; // text-gray-900
        button.style.border = '2px solid #a855f7'; // border-purple-500
    }

    // Handle downpayment display
    const downpaymentSection = document.getElementById('downpayment-section');
    const paymentSummary = document.getElementById('payment-summary');
    const paymentAmount = document.getElementById('payment-amount');
    const submitButton = document.getElementById('submit-button');

    // Use the global total price that we update when components change
    console.log('Current total price for payment:', window.totalPrice);
    
    window.downpaymentAmount = window.totalPrice * 0.5;
    window.remainingBalance = window.totalPrice * 0.5;

    if (method === 'PayPal_Downpayment') {
        if (downpaymentSection) downpaymentSection.classList.remove('hidden');
        if (paymentSummary) paymentSummary.classList.remove('hidden');
        
        // Update amounts
        const downpaymentAmountEl = document.getElementById('downpayment-amount');
        const remainingBalanceEl = document.getElementById('remaining-balance');
        
        if (downpaymentAmountEl) downpaymentAmountEl.textContent = '₱' + window.downpaymentAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (remainingBalanceEl) remainingBalanceEl.textContent = '₱' + window.remainingBalance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (paymentAmount) paymentAmount.textContent = '₱' + window.downpaymentAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        // Update submit button text
        if (submitButton) submitButton.textContent = 'Pay 50% Downpayment';
    } else if (method === 'PayPal') {
        if (downpaymentSection) downpaymentSection.classList.add('hidden');
        if (paymentSummary) paymentSummary.classList.remove('hidden');
        if (paymentAmount) paymentAmount.textContent = '₱' + window.totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (submitButton) submitButton.textContent = 'Pay Full Amount';
    }

    console.log('Payment method set to:', paymentInput ? paymentInput.value : 'Not found');
}

// Function to reset payment selection when modal closes
function resetPaymentSelection() {
    window.selectedPayment = null;
    
    // Reset UI elements
    const downpaymentSection = document.getElementById('downpayment-section');
    const paymentSummary = document.getElementById('payment-summary');
    const submitButton = document.getElementById('submit-button');
    
    if (downpaymentSection) downpaymentSection.classList.add('hidden');
    if (paymentSummary) paymentSummary.classList.add('hidden');
    if (submitButton) submitButton.textContent = 'Order';
    
    // Reset payment buttons
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb';
        btn.style.color = '#374151';
        btn.style.border = '2px solid transparent';
    });
    
    // Clear payment method input
    const paymentInput = document.getElementById('payment_method');
    if (paymentInput) paymentInput.value = '';
    
    console.log('Payment selection reset');
}

document.addEventListener('DOMContentLoaded', () => {
    const cartForm = document.getElementById('cartForm');
    const arrow = document.querySelector('.component-arrow');
    const wrapper = document.querySelector('.catalog-wrapper');
    const componentButtons = document.querySelectorAll('.component-section .component-button');
    const catalogItems = document.querySelectorAll('#catalogSection .build-catalog');

    // initialize selected components sidebar
    updateSelectedComponentsDisplay();

    updateValidateButton();

    // CART
    if (cartForm) {
        cartForm.addEventListener('submit', handleFormSubmit);
    }

    arrow.addEventListener('click', () => {
        wrapper.classList.toggle('open');
        arrow.classList.toggle('rotated');
    });

    // FILTER BY TYPE
    componentButtons.forEach(button => {
        button.addEventListener('click', () => {
            const isActive = button.classList.contains('component-active');
            const selectedType = button.getAttribute('data-type');
            
            componentButtons.forEach(c => c.classList.remove('component-active'));
            
            if (isActive) {
                catalogItems.forEach(item => {
                    item.style.display = '';
                });
            } else {
                button.classList.add('component-active');
                catalogItems.forEach(item => {
                    const itemType = item.getAttribute('data-type');
                    item.style.display = (itemType === selectedType) ? '' : 'none';                    
                });    
            }
        });
    });

    // --- PRE-POPULATE SELECTED COMPONENTS FROM SESSION ---
    if (window.selectedComponents && Object.keys(window.selectedComponents).length > 0) {
        // ✅ FIX: Convert 'storage' key into 'ssd' or 'hdd' dynamically
        if (window.selectedComponents.storage) {
            const storage = window.selectedComponents.storage;
            if (storage.name && storage.name.toLowerCase().includes('ssd')) {
                window.selectedComponents.ssd = storage;
            } else if (storage.name && storage.name.toLowerCase().includes('hdd')) {
                window.selectedComponents.hdd = storage;
            }
        }
        
        catalogItems.forEach(item => {
            const type = item.getAttribute('data-type');
            const id = item.getAttribute('data-id');

            // Check if this component is stored in session
            if (window.selectedComponents[type] && window.selectedComponents[type].componentId == id) {
                const componentData = {
                    id: id,
                    type: type,
                    name: item.getAttribute('data-name'),
                    price: parseFloat(item.getAttribute('data-price')) || 0,
                    image: item.getAttribute('data-image'),
                    modelUrl: item.getAttribute('data-model')
                };

                selectComponent(componentData);
                setComponentImage(componentData);

                // Highlight selected
                item.classList.add('selected-component');
            }
        });
        
        // Initialize total price from session components
        updateTotalPrice();
    }

    // Initialize payment buttons on load
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb';
        btn.style.color = '#374151';
        btn.style.border = '2px solid transparent';
    });

    // SINGLE EVENT LISTENER FOR CATALOG ITEMS - COMBINED FUNCTIONALITY
    catalogItems.forEach(item => {
        item.addEventListener('click', function() {
            const componentData = {
                id: this.getAttribute('data-id'),
                type: this.getAttribute('data-type'),
                name: this.getAttribute('data-name'),
                price: parseFloat(this.getAttribute('data-price')) || 0,
                image: this.getAttribute('data-image'),
                modelUrl: this.getAttribute('data-model')
            };
            
            selectComponent(componentData);
            setComponentImage(componentData);
            
            // Highlight selected
            this.classList.add('selected-component');
        });
    });

    // VALIDATION
    document.getElementById('validateBuild').addEventListener('click', function() {
        // If button is disabled (components incomplete), show what's missing
        if (this.disabled) {
            const missing = getMissingComponents();
            if (missing.length > 0) {
                alert(`Please select the following components:\n\n${missing.join('\n')}`);
                return;
            }
        }
        
        // Existing validation logic
        const selections = {};
        document.querySelectorAll('.component-button').forEach(button => {
            const type = button.getAttribute('data-type');
            const selectedId = button.getAttribute('data-selected-id');
            if (selectedId) {
                selections[type + "_id"] = selectedId;
            }
        });

        if (Object.keys(selections).length === 0) {
            alert("⚠️ No components selected.\nPlease choose at least one component before validating.");
            return;
        }

        fetch('/techboxx/build/validate', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(selections)
        })
        .then(res => res.json())
        .then(data => {
            let message = '';

            if (data.errors && data.errors.length > 0) {
                message += '❌ Compatibility Errors:\n' + data.errors.join("\n") + '\n\n';
            }

            if (data.warnings && data.warnings.length > 0) {
                message += '⚠️ Warnings:\n' + data.warnings.join("\n") + '\n\n';
            }

            if ((!data.errors || data.errors.length === 0) && (!data.warnings || data.warnings.length === 0)) {
                message = "✅ No issues found, all components are compatible.";
            }

            alert(message);
        })
        .catch(err => {
            console.error('Validation failed:', err);
            alert('❌ An error occurred while validating the build.');
        });
    });

    document.getElementById('reloadButton').addEventListener('click', function() {
        reloadScene();
    });
    
    console.log('Payment module loaded for buildext');
});

function updateSession(selectedComponents) {
    fetch('/techboxx/build/update-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ selected_components: selectedComponents })
    })
    .then(res => res.json())
    .then(data => {
        console.log('Session updated:', data);
    })
    .catch(err => {
        console.error('Failed to update session:', err);
    });
}