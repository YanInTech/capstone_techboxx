<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Techboxx</title>

    @vite([
        'resources\css\app.css',
        'resources\css\build.css',
        'resources\css\buildext.css',
        'resources\js\app.js',
        'resources\js\buildextcomponent-viewer.js',
        'resources\js\buildext.js',
        'resources\css\admin-staff\modal.css',
        ])
    
</head>
<script>
    window.selectedComponents = @json(session('selected_components', []));
    // Make payment variables globally accessible
    window.selectedPayment = null;
    window.totalPrice = 0;
    window.downpaymentAmount = 0;
    window.remainingBalance = 0;
</script>
<body class="flex flex-col"
      x-data="{ 
          showViewModal: false, 
          selectedComponent: {},
          showModal: false,
          modalType: 'order',
          buildName: 'YOUR PC',
          currentUser: {
              first_name: '{{ Auth::user()->first_name ?? '' }}',
              last_name: '{{ Auth::user()->last_name ?? '' }}',
              phone: '{{ Auth::user()->phone_number ?? '' }}'
          },
          // REAL-TIME SYNC: Initialize and watch for changes
          selectedComponents: { ...window.selectedComponents || {} },
          totalPrice: 0,
          compatibilityResults: null,
          
          // Initialize and set up real-time sync
          init() {
              // Initial sync
              this.syncSelectedComponents();
              
              // Watch for changes to window.selectedComponents
              this.$watch('selectedComponents', (value) => {
                  // Update global reference when Alpine state changes
                  window.selectedComponents = { ...value };
              });
          },
          
          // Sync from global to Alpine state
          syncSelectedComponents() {
              this.selectedComponents = { ...window.selectedComponents || {} };
          },
          
          // UPDATED: Open modal with real-time sync
          async openModal(type) {
              this.modalType = type;
              
              // Sync latest components before checking compatibility
              this.syncSelectedComponents();
              
              // Check compatibility before showing modal
              const isCompatible = await this.checkCompatibility();
              if (isCompatible) {
                  this.populateModal();
                  this.showModal = true;
              } else {
                  this.showCompatibilityAlert();
              }
          },
          
          // UPDATED: Check compatibility with real-time data
          async checkCompatibility() {
              // Use the latest Alpine.js state
              const componentSelections = {};
              
              for (const [type, component] of Object.entries(this.selectedComponents)) {
                  if (component && component.componentId) {
                      componentSelections[type + '_id'] = component.componentId;
                  }
              }
              
              // Check if we have enough components to validate
              if (Object.keys(componentSelections).length === 0) {
                  alert('⚠️ No components selected.\nPlease choose at least one component before ordering.');
                  return false;
              }
              
              try {
                  const response = await fetch('/techboxx/build/validate', {
                      method: 'POST',
                      headers: { 
                          'Content-Type': 'application/json', 
                          'X-CSRF-TOKEN': document.querySelector(`meta[name='csrf-token']`).content
                      },
                      body: JSON.stringify(componentSelections)
                  });
                  
                  const data = await response.json();
                  this.compatibilityResults = data;
                  return !data.errors || data.errors.length === 0;
                  
              } catch (error) {
                  console.error('Compatibility check failed:', error);
                  return true;
              }
          },
          
          showCompatibilityAlert() {
              let message = '❌ Compatibility Issues Found:\n\n';
              
              if (this.compatibilityResults.errors && this.compatibilityResults.errors.length > 0) {
                  message += 'Errors:\n' + this.compatibilityResults.errors.join('\n') + '\n\n';
              }
              
              if (this.compatibilityResults.warnings && this.compatibilityResults.warnings.length > 0) {
                  message += 'Warnings:\n' + this.compatibilityResults.warnings.join('\n') + '\n\n';
              }
              
              message += 'Please fix these issues before proceeding with your order.';
              alert(message);
          },
          
          // UPDATED: Populate modal with real-time data
          populateModal() {
              // Use the already synced selectedComponents
              // Calculate and set total price
              let totalPrice = 0;
              for (const [type, component] of Object.entries(this.selectedComponents)) {
                  if (component && component.price) {
                      totalPrice += component.price;
                  }
              }
              this.totalPrice = totalPrice;
              
              // Update global total price for payment calculations
              window.totalPrice = totalPrice;
              window.downpaymentAmount = totalPrice * 0.5;
              window.remainingBalance = totalPrice * 0.5;
              
              this.updateModalHiddenInputs();
              this.showModal = true;
          },
          
          updateModalHiddenInputs() {
              const componentTypes = ['gpu', 'motherboard', 'cpu', 'hdd', 'ssd', 'psu', 'ram', 'cooler', 'case'];
              
              componentTypes.forEach(type => {
                  const component = this.selectedComponents[type];
                  const hiddenInput = document.getElementById(`hidden_${type}`);
                  
                  if (hiddenInput && component && component.componentId) {
                      hiddenInput.value = component.componentId;
                  }
              });

            // Handle storage components
            const storageInput = document.getElementById('hidden_storage');
            if (storageInput) {
                storageInput.value = '';
                
                if (this.selectedComponents.ssd && this.selectedComponents.ssd.componentId) {
                    storageInput.value = this.selectedComponents.ssd.componentId;
                } else if (this.selectedComponents.hdd && this.selectedComponents.hdd.componentId) {
                    storageInput.value = this.selectedComponents.hdd.componentId;
                }
                
                const hddInput = document.getElementById('hidden_hdd');
                const ssdInput = document.getElementById('hidden_ssd');
                if (hddInput) hddInput.value = '';
                if (ssdInput) ssdInput.value = '';
            }

            // Update total price
            const totalPriceInput = document.getElementById('hidden_total_price');
            if (totalPriceInput) {
                let totalPrice = 0;
                for (const [type, component] of Object.entries(this.selectedComponents)) {
                    if (component && component.price) {
                        totalPrice += component.price;
                    }
                }
                totalPriceInput.value = totalPrice.toFixed(2);
            }
        },

        // Form submission handling
        submitForm(event) {
            console.log('=== FORM SUBMISSION STARTED ===');
            
            // Prevent default immediately
            event.preventDefault();
            
            // Validate build name
            const buildNameInput = document.querySelector(`input[name='build_name']`);
            const buildName = buildNameInput ? buildNameInput.value : '';
            if (!buildName.trim()) {
                alert('Please enter a build name.');
                return false;
            }
            
            // Validate components
            if (Object.keys(this.selectedComponents).length === 0) {
                alert('Please select at least one component.');
                return false;
            }
            
            // Validate all required components are selected
            const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case'];
            const missingComponents = [];
            
            requiredComponents.forEach(type => {
                if (!this.selectedComponents[type] || !this.selectedComponents[type].componentId) {
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
            if (!this.selectedComponents.ssd?.componentId && !this.selectedComponents.hdd?.componentId) {
                missingComponents.push('Storage (HDD or SSD)');
            }
            
            if (missingComponents.length > 0) {
                alert(`Please select the following components:\n\n${missingComponents.join('\n')}`);
                return false;
            }
            
            // Check compatibility errors
            if (this.compatibilityResults && this.compatibilityResults.errors && this.compatibilityResults.errors.length > 0) {
                alert('Please fix compatibility issues before proceeding.');
                return false;
            }

            // PAYMENT METHOD VALIDATION - ONLY FOR ORDERS
            if (this.modalType === 'order') {
                const paymentMethod = document.getElementById('payment_method').value;
                console.log('Payment method:', paymentMethod);
                if (!paymentMethod) {
                    alert('Please select a payment method.');
                    return false;
                }

                // Handle PayPal payments - redirect to PayPal instead of submitting form
                if (paymentMethod === 'PayPal' || paymentMethod === 'PayPal_Downpayment') {
                    return this.handlePayPalPayment(event, paymentMethod);
                }
            }
            
            console.log('=== ALL VALIDATIONS PASSED - SUBMITTING FORM ===');
            
            // Update hidden inputs one final time
            this.updateModalHiddenInputs();
            
            // Submit the form programmatically (for non-PayPal payments)
            const form = event.target;
            console.log('Submitting form to:', form.action);
            form.submit();
        },

        async handlePayPalPayment(event, paymentMethod) {
            console.log('=== HANDLING PAYPAL PAYMENT ===');
            
            // Update hidden inputs first
            this.updateModalHiddenInputs();
            
            // Get the total price and calculate amount
            const totalPrice = this.totalPrice;
            const amount = paymentMethod === 'PayPal_Downpayment' ? totalPrice * 0.5 : totalPrice;
            
            console.log('Payment details:', {
                paymentMethod,
                totalPrice,
                amount,
                isDownpayment: paymentMethod === 'PayPal_Downpayment'
            });
            
            // Prepare component IDs for the build
            const componentIds = {};
            for (const [type, component] of Object.entries(this.selectedComponents)) {
                if (component && component.componentId) {
                    // For storage components (SSD/HDD), we need to handle them specially
                    if (type === 'ssd' || type === 'hdd') {
                        // Use the actual storage component that was selected
                        const storageInput = document.getElementById('hidden_storage');
                        if (storageInput && storageInput.value) {
                            componentIds['storage'] = storageInput.value;
                        }
                    } else {
                        // For all other components, use the type directly
                        componentIds[type] = component.componentId;
                    }
                }
            }
            
            // Debug: Log the final component IDs
            console.log('Final component IDs to send:', componentIds);
            
            // Get build name
            const buildNameInput = document.querySelector(`input[name='build_name']`);
            const buildName = buildNameInput ? buildNameInput.value : '';
            
            try {
                // Use FormData instead of JSON for better compatibility
                const formData = new FormData();
                formData.append('build_name', buildName);
                formData.append('total_price', totalPrice);
                formData.append('payment_method', paymentMethod);
                
                // Add component IDs in the correct format
                Object.entries(componentIds).forEach(([type, id]) => {
                    formData.append(`component_ids[${type}]`, id);
                });
                
                // Add downpayment amount if applicable
                if (paymentMethod === 'PayPal_Downpayment') {
                    formData.append('downpayment_amount', amount);
                }
                
                // Add CSRF token
                formData.append('_token', document.querySelector(`meta[name='csrf-token']`).content);
                
                console.log('Sending form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }
                
                // Submit the order via AJAX
                const response = await fetch('{{ route("build.order") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                console.log('Server response:', data);
                
                if (data.success && data.redirect_url) {
                    // Redirect to PayPal
                    console.log('Redirecting to PayPal:', data.redirect_url);
                    window.location.href = data.redirect_url;
                } else {
                    console.error('PayPal redirection failed:', data);
                    alert(data.message || 'Failed to process PayPal payment. Please try again.');
                }
                
            } catch (error) {
                console.error('PayPal payment error:', error);
                alert('An error occurred while processing your payment. Please try again.');
            }
            
            return false;
        },
        
        // Computed properties
        get modalTitle() {
            return this.modalType === 'order' ? 'Order Build' : 'Save Build';
        },
        
        get submitButtonText() {
            return this.modalType === 'order' ? 'Order' : 'Save Build';
        },
        
        get formAction() {
            return this.modalType === 'order' ? '{{ route('build.order') }}' : '{{ route('build.save') }}';
        }
    }"
    >

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    {{-- UPDATED MODAL WITH COMPATIBILITY DISPLAY --}}
    <div x-show="showModal" x-cloak x-transition class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="showModal = false">
            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl z-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800" x-text="modalTitle"></h2>
                    <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200">
                        <x-icons.close class="w-6 h-6 text-gray-500 hover:text-gray-700" />
                    </button>
                </div>
                
                {{-- Compatibility Status Display --}}
                <div x-show="compatibilityResults" class="mt-3">
                    <template x-if="compatibilityResults.errors && compatibilityResults.errors.length > 0">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-red-800 font-semibold">❌ Compatibility Issues Found</span>
                            </div>
                            <ul class="text-red-700 text-sm mt-1 list-disc list-inside">
                                <template x-for="error in compatibilityResults.errors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    
                    <template x-if="(!compatibilityResults.errors || compatibilityResults.errors.length === 0) && compatibilityResults.warnings && compatibilityResults.warnings.length > 0">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-yellow-800 font-semibold">⚠️ Compatibility Warnings</span>
                            </div>
                            <ul class="text-yellow-700 text-sm mt-1 list-disc list-inside">
                                <template x-for="warning in compatibilityResults.warnings" :key="warning">
                                    <li x-text="warning"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    
                    <template x-if="(!compatibilityResults.errors || compatibilityResults.errors.length === 0) && (!compatibilityResults.warnings || compatibilityResults.warnings.length === 0)">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-green-800 font-semibold">✅ All Components Compatible</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- DYNAMIC FORM --}}
            <form :action="formAction" method="POST" id="cartForm" class="p-6 space-y-6" @submit.prevent="submitForm($event)">
                @csrf
                
                {{-- Hidden inputs for component IDs --}}
                @php
                    $componentTypes = ['gpu', 'motherboard', 'cpu', 'storage' , 'psu', 'ram', 'cooler', 'case'];
                @endphp

                @foreach ($componentTypes as $componentType)
                    <input type="hidden" name="component_ids[{{ $componentType }}]" id="hidden_{{ $componentType }}" value="">
                @endforeach

                {{-- Hidden input for total price --}}
                <input type="hidden" name="total_price" id="hidden_total_price" value="">

                {{-- Customer Information --}}
                <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <h4 class="text-lg font-semibold text-gray-800">Customer Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Name</p>
                            <p class="text-gray-800 font-semibold" x-text="currentUser.first_name + ' ' + currentUser.last_name"></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Contact No</p>
                            <p class="text-gray-800 font-semibold" x-text="currentUser.phone"></p>
                        </div>
                    </div>
                    <div>
                        <label for="build_name" class="block text-sm font-medium text-gray-700 mb-2">Build Name</label>
                        <input 
                            required 
                            type="text" 
                            name="build_name" 
                            id="build_name"
                            placeholder="Enter build name" 
                            x-model="buildName"
                            class="text-black w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        >
                    </div>
                </div>

                {{-- Selected Components --}}
                <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <h4 class="text-lg font-semibold text-gray-800">Selected Components</h4>
                    </div>
                    
                    {{-- Dynamic component display --}}
                    <template x-for="(component, type) in selectedComponents" :key="type">
                        <div class="flex items-center justify-between bg-white rounded-lg border border-gray-200 p-4">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide" x-text="type"></p>
                                <p class="text-gray-800 font-semibold" x-text="component.name"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600" x-text="'₱' + component.price.toFixed(2)"></p>
                            </div>
                            <input type="hidden" 
                                :name="'component_ids[' + type + ']'" 
                                :value="component.componentId">
                        </div>
                    </template>

                    {{-- Fallback if no components selected --}}
                    <div x-show="Object.keys(selectedComponents).length === 0" class="text-center py-8">
                        <p class="text-gray-500 text-lg">No components selected yet</p>
                        <p class="text-gray-400 text-sm mt-2">Please select components from the catalog</p>
                    </div>
                </div>

                {{-- Total Price --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xl font-bold text-gray-800">Total Build Price:</h4>
                        <h4 class="text-2xl font-bold text-green-600" x-text="'₱' + totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                    </div>
                </div>

                {{-- PAYMENT METHOD - ONLY FOR ORDER --}}
                <div x-show="modalType === 'order'" class="bg-gray-50 rounded-xl p-6 space-y-4">
                    <div class="border-b border-gray-200 pb-3">
                        <h4 class="text-lg font-semibold text-gray-800">Payment Method</h4>
                    </div>
                    
                    <div class="space-y-4">
                        {{-- Payment Method Buttons --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input type="hidden" name="payment_method" id="payment_method" required>
                            <button
                                type="button"
                                onclick="selectPayment('PayPal', this)"
                                class="payment-btn bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg border-2 border-transparent hover:bg-yellow-400 hover:border-yellow-500 transition-all duration-200 transform hover:scale-105 flex flex-col items-center justify-center">
                                <span class="font-bold">PayPal</span>
                                <span class="text-xs text-gray-600 mt-1">Full Payment</span>
                            </button>
                            <button
                                type="button"
                                onclick="selectPayment('PayPal_Downpayment', this)"
                                class="payment-btn bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg border-2 border-transparent hover:bg-purple-400 hover:border-purple-500 transition-all duration-200 transform hover:scale-105 flex flex-col items-center justify-center">
                                <span class="font-bold">PayPal</span>
                                <span class="text-xs text-gray-600 mt-1">50% Downpayment</span>
                            </button>
                        </div>

                        {{-- Downpayment Information --}}
                        <div id="downpayment-section" class="hidden bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Downpayment (50%):</span>
                                <span id="downpayment-amount" class="text-lg font-bold text-purple-600"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Remaining Balance:</span>
                                <span id="remaining-balance" class="text-lg font-bold text-orange-600"></span>
                            </div>
                            <p class="text-xs text-purple-600 mt-2 text-center">
                                💡 Pay 50% now, settle the remaining 50% upon pickup
                            </p>
                        </div>

                        {{-- Payment Summary --}}
                        <div id="payment-summary" class="bg-white rounded-lg p-4 border border-gray-200 hidden">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-800">Amount to Pay:</span>
                                <span id="payment-amount" class="text-xl font-bold text-green-600"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- UPDATED: Submit Button with Compatibility Check --}}
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div x-show="compatibilityResults && compatibilityResults.errors && compatibilityResults.errors.length > 0" 
                         class="text-red-600 text-sm font-medium">
                        ❌ Fix compatibility issues to proceed
                    </div>
                    <div class="flex gap-3 ml-auto">
                        <button 
                            type="button" 
                            @click="showModal = false"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :class="{
                                'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700': !(compatibilityResults && compatibilityResults.errors && compatibilityResults.errors.length > 0),
                                'bg-gray-400 cursor-not-allowed': compatibilityResults && compatibilityResults.errors && compatibilityResults.errors.length > 0
                            }"
                            class="text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50"
                            id="submit-button"
                            x-text="submitButtonText">
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <main class="main-content header !m-0">
        <div class="ext-icons">
            @if (auth()->user() && auth()->user()->role === 'Customer')
                <form action="{{ route('home') }}">
                    <button>
                        <x-icons.arrow class="ext-arrow"/>
                    </button>
                </form>
                <button @click="openModal('save')">
                    <x-icons.save class="ext-save"/>
                </button>
                <button @click="openModal('order')">
                    <x-icons.cart class="ext-cart"/>
                </button>
                <button id="reloadButton">
                    <x-icons.reload class="ext-reload" />
                </button>
            @else
                <form action="{{ route('techboxx.build') }}">
                    <button>
                        <x-icons.arrow class="build-arrow"/>
                    </button>
                </form>
                <button id="reloadButton">
                    <x-icons.reload class="ext-reload" />
                </button>
            @endif
        </div>
        
        <form class="enter-build-name">
            <input type="text" value="YOUR PC" x-model="buildName">
        </form>

        <section class="model-section">
            <div id="canvas-container"></div>
        </section>

        <div class="layout-container">
            {{-- STEPS --}}
            <section class="steps-section">
                <div>
                    <h3>VIRTUAL PC BUILD GUIDE</h3>
                    <p>1. Install the Motherboard</p>
                    <p>2. Install the CPU</p>
                    <p>3. Install the RAM</p>
                    <p>4. Install the SSD</p>
                    <p>5. Install the HDD</p>
                    <p>6. Install the CPU Cooler</p>
                    <p>7. Install the GPU</p>
                    <p>8. Install the PSU</p>
                    <p>9. Power On</p>
                </div>
            </section>

            {{-- COMPATIBILITY --}}
            <section class="compatibility-section">
                <div class="flex gap-2 items-center">
                    <button id="validateBuild" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                        Validate Build
                    </button>

                    <a href="{{ route('techboxx.build.software') }}" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    SOFTWARE
                    </a>
                </div>

            </section>



            {{-- COMPONENTS --}}
            
        </div>
    </main>
    <section class="catalog-wrapper">
        <div class="slide-container">
            <div class="component-section">
                <x-icons.arrow class="component-arrow" />
                <x-component data-type="case">Case</x-component>
                <x-component data-type="motherboard">Motherboard</x-component>
                <x-component data-type="cpu">CPU</x-component>
                <x-component data-type="ram">RAM</x-component>
                <x-component data-type="ssd">SSD</x-component>
                <x-component data-type="hdd">HDD</x-component>
                <x-component data-type="cooler">Cooler</x-component>
                <x-component data-type="gpu">GPU</x-component>
                <x-component data-type="psu">PSU</x-component>
            </div>

            <div class="catalog-section" id="catalogSection">
                @foreach ($components as $component)
                    <x-buildcatalog :component="$component"/>
                @endforeach
            </div>
        </div>
    </section>
    <div x-show="showViewModal" x-cloak x-transition class="modal view-specs modal-scroll">
        <div class="view-component" @click.away="showViewModal = false">
            <div x-show="selectedComponent.component_type === 'motherboard'">
                @include('staff.componentdetails.view.motherboard')
            </div>

            <div x-show="selectedComponent.component_type === 'gpu'">
                @include('staff.componentdetails.view.gpu')
            </div>

            <div x-show="selectedComponent.component_type === 'case'">
                @include('staff.componentdetails.view.case')
            </div>

            <div x-show="selectedComponent.component_type === 'psu'">
                @include('staff.componentdetails.view.psu')
            </div>

            <div x-show="selectedComponent.component_type === 'ram'">
                @include('staff.componentdetails.view.ram')
            </div>

            <div x-show="selectedComponent.component_type === 'ssd' || selectedComponent.component_type === 'hdd'">
                @include('staff.componentdetails.view.storage')
            </div>

            <div x-show="selectedComponent.component_type === 'cpu'">
                @include('staff.componentdetails.view.cpu')
            </div>

            <div x-show="selectedComponent.component_type === 'cooler'">
                @include('staff.componentdetails.view.cooler')
            </div>
        </div>
    </div>
</body>

<script>
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

        // Get the total price from Alpine.js data
        if (typeof Alpine !== 'undefined' && Alpine.$data) {
            window.totalPrice = Alpine.$data.totalPrice || 0;
        }
        
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

    // Initialize payment buttons on load
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure all payment buttons have initial gray style
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.style.backgroundColor = '#e5e7eb';
            btn.style.color = '#374151';
            btn.style.border = '2px solid transparent';
        });
        
        console.log('Payment module loaded for buildext');
    });
</script>
</html>