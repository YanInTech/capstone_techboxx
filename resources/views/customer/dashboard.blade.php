<x-dashboardlayout>
<h1>Dashboard</h1>

{{-- Customer Profile --}}
<section class="customer-profile" x-data="{ user: @js(Auth::user()), showEditModal: false }">
    <div class="profile">
        <div>
            <x-icons.profile />
        </div>
        <div class="profile-details">
            <div>
                <span>Name</span>
                <span>:</span>
                <span>{{ Auth::user()->first_name }} {{ Auth::user()->middle_name }} {{ Auth::user()->last_name }}</span>
            </div>
            <div >
                <span>Email</span>
                <span>:</span>
                <span>{{ Auth::user()->email }}</span>
                <span class="email-row">
                    @if (! Auth::user()->hasVerifiedEmail())
                        <form action="{{ route('verification.send') }}" method="POST">
                            @csrf
                            <button type="submit">
                                <u>(Verifiy Email)</u>
                            </button>
                        </form>
                    @endif    
                </span>
            </div>
            <div>
                <span>Address</span>
                <span>:</span>
                <span>{{ Auth::user()->address }}</span>
            </div>
            <div>
                <span>Contact</span>
                <span>:</span>
                <span>{{ Auth::user()->phone_number ? Auth::user()->phone_number : '-'}}</span>
                <span class="email-row">
                    @if (Auth::user()->phone_number === null)
                        <u @click="showEditModal = true ">(Add Contact Number)</u>
                    @endif    
                </span>
            </div>
            <div>
                {{-- change status to Verified --}}
                <span>Status</span>
                <span>:</span>
                <span>{{ Auth::user()->status }}</span> 
            </div>
        </div>
    </div>

    <div>
        <button @click="showEditModal = true">
            <x-icons.edit />
        </button>
    </div>
    
    {{-- Edit Modal --}}
    <div x-show="showEditModal" x-cloak x-transition class="modal">
        <div class="modal-form" @click.away="showEditModal = false">
            <div class="relative !m-0">
                <h2 class="text-center w-[100%]">
                    Edit Information
                    <x-icons.close class="close" @click="showEditModal = false"/>    
                </h2>
            </div>

            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name"  x-model="user.first_name">
                </div>
                <div>
                    <label for="middle_name">Middle Name</label>
                    <input type="text" name="middle_name"  x-model="user.middle_name">
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" x-model="user.last_name">
                </div>
                <div>
                    <label for="address">Address</label>
                    <input type="text" name="address" x-model="user.address">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" x-model="user.email">
                </div>
                <div>
                    <label for="phone_number">Phone Number</label>
                    <input required name="phone_number" id="phone_number" x-model="user.phone_number" type="tel" pattern="0[0-9]{10}" minlength="11" maxlength="11" oninput="this.value = this.value.slice(0, 11)">
                </div>

                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</section>

{{-- Builds Table --}}
<section class="section-style !pl-0 !h-[50vh]">
    <div class="builds-table">
        <x-icons.build />
        <p>Builds</p>
    </div>

    <div>
        <table class="table">
            <thead>
                <tr class="border-t border-black">
                    <th class="pt-2">Build Name/Name</th>
                    <th class="pt-2">Details</th>
                    <th class="pt-2">Date Created</th>
                    <th class="pt-2">Total Cost</th>
                    <th class="pt-2">Status</th>
                    <th class="pt-2">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    <div x-data="{ showModal: false, orderModal: false, selectedBuild:{} }"
        class="table-body">
        <table class="table">
            <tbody>
                @foreach ($userBuilds as $userBuild)
                    <tr @click="showModal = true; selectedBuild = {{ $userBuild->toJson() }};"
                        class="hover:opacity-50">
                        <td>{{ $userBuild->build_name }}</td>
                        <td class="text-center !pr-[2.5%]">View</td>
                        <td class="text-center !pr-[1.5%]">{{ $userBuild->created_at ? $userBuild->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center">₱ {{ $userBuild->total_price }}</td>
                        <td class="text-center !pr-[.6%]">{{ $userBuild->status }}</td>
                        <td class="text-center">
                            @if ($userBuild->status !== 'Ordered')
                                <button type="submit" 
                                    @click.stop 
                                    @click="orderModal = true; selectedBuild = {{ $userBuild->toJson() }};"
                                    class="cursor-pointer">
                                    <u>Order</u>
                                </button>
                            @else
                                <button disable 
                                    class="cursor-not-allowed text-gray-400 opacity-50">
                                    <u>Order</u>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- VIEW MODAL --}}
        <div x-show="showModal" x-cloak x-transition 
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="showModal = false">
                {{-- Header --}}
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl z-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-800">Build Details</h2>
                        <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200">
                            <svg class="w-6 h-6 text-gray-500 hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-6">
                    {{-- Build Information --}}
                    <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                        <div class="border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-800">Build Information</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-600">Build Name</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.build_name"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Components --}}
                    <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                        <div class="border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-800">Components</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Case</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.case.brand + ' ' + selectedBuild.case.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">CPU</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.cpu.brand + ' ' + selectedBuild.cpu.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">RAM</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.ram.brand + ' ' + selectedBuild.ram.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Storage</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.storage.brand + ' ' + selectedBuild.storage.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Motherboard</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.motherboard.brand + ' ' + selectedBuild.motherboard.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">GPU</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.gpu.brand + ' ' + selectedBuild.gpu.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">PSU</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.psu.brand + ' ' + selectedBuild.psu.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Cooler</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.cooler.brand + ' ' + selectedBuild.cooler.model"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Build Price --}}
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xl font-bold text-gray-800">Build Price:</h4>
                            <h4 class="text-2xl font-bold text-green-600" x-text="'₱' + (parseFloat(selectedBuild.total_price)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                        </div>
                    </div>

                    {{-- Close Button --}}
                    <div class="flex justify-end items-center pt-4 border-t border-gray-200">
                        <button 
                            type="button" 
                            @click="showModal = false"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ORDER MODAL --}}
        <div x-show="orderModal" x-cloak x-transition 
            x-init="if (orderModal) { $nextTick(() => initializeSavedBuildPayment(selectedBuild.total_price)); }"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="orderModal = false; resetSavedBuildPayment();">
                {{-- Header --}}
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl z-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-800">Build Details</h2>
                        <button @click="orderModal = false; resetSavedBuildPayment();" class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200">
                            <svg class="w-6 h-6 text-gray-500 hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Content --}}
                <form id="saved-build-order-form" action="{{ route('build.save.order')}}" method="POST" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="user_build_id" x-bind:value="selectedBuild.id">
                    <input type="hidden" name="total_price" x-bind:value="selectedBuild.total_price">
                    
                    {{-- Customer Information --}}
                    <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                        <div class="border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-800">Customer Information</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Name</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.user.first_name + ' ' + selectedBuild.user.last_name"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Contact No</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.user.phone_number"></p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-600">Email</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.user.email"></p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-600">Build Name</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.build_name"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Components --}}
                    <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                        <div class="border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-800">Components</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Case</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.case.brand + ' ' + selectedBuild.case.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">CPU</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.cpu.brand + ' ' + selectedBuild.cpu.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">RAM</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.ram.brand + ' ' + selectedBuild.ram.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">SSD</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.storage.brand + ' ' + selectedBuild.storage.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Motherboard</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.motherboard.brand + ' ' + selectedBuild.motherboard.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">GPU</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.gpu.brand + ' ' + selectedBuild.gpu.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">HDD</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.storage.brand + ' ' + selectedBuild.storage.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">PSU</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.psu.brand + ' ' + selectedBuild.psu.model"></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Cooler</p>
                                <p class="text-gray-800 font-semibold" x-text="selectedBuild.cooler.brand + ' ' + selectedBuild.cooler.model"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Build Price --}}
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xl font-bold text-gray-800">Build Price:</h4>
                            <h4 class="text-2xl font-bold text-green-600" x-text="'₱' + (parseFloat(selectedBuild.total_price)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></h4>
                        </div>
                    </div>

                    {{-- Payment Method Section --}}
                    <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                        <div class="border-b border-gray-200 pb-3">
                            <h4 class="text-lg font-semibold text-gray-800">Payment Method</h4>
                        </div>
                        
                        <div class="space-y-4">
                            {{-- Payment Method Buttons --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="hidden" name="payment_method" id="payment_method" required>
                                <button
                                    type="button"
                                    onclick="selectPaymentSavedBuild('PayPal', this)"
                                    class="payment-btn-saved bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg border-2 border-transparent hover:bg-yellow-400 hover:border-yellow-500 transition-all duration-200 transform hover:scale-105 flex flex-col items-center justify-center">
                                    <span class="font-bold">PayPal</span>
                                    <span class="text-xs text-gray-600 mt-1">Full Payment</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="selectPaymentSavedBuild('PayPal_Downpayment', this)"
                                    class="payment-btn-saved bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg border-2 border-transparent hover:bg-purple-400 hover:border-purple-500 transition-all duration-200 transform hover:scale-105 flex flex-col items-center justify-center">
                                    <span class="font-bold">PayPal</span>
                                    <span class="text-xs text-gray-600 mt-1">50% Downpayment</span>
                                </button>
                            </div>

                            {{-- Downpayment Information --}}
                            <div id="downpayment-section-saved" class="hidden bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Downpayment (50%):</span>
                                    <span id="downpayment-amount-saved" class="text-lg font-bold text-purple-600"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Remaining Balance:</span>
                                    <span id="remaining-balance-saved" class="text-lg font-bold text-orange-600"></span>
                                </div>
                                <p class="text-xs text-purple-600 mt-2 text-center">
                                    💡 Pay 50% now, settle the remaining 50% upon pickup
                                </p>
                            </div>

                            {{-- Payment Summary --}}
                            <div id="payment-summary-saved" class="bg-white rounded-lg p-4 border border-gray-200 hidden">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-gray-800">Amount to Pay:</span>
                                    <span id="payment-amount-saved" class="text-xl font-bold text-green-600"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Submit Button --}}
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <div class="flex gap-3 ml-auto">
                            <button 
                                type="button" 
                                @click="orderModal = false; resetSavedBuildPayment();"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                id="submit-button-saved"
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50">
                                Order Build
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{ $userBuilds->links() }}
</section>

</x-dashboardlayout>