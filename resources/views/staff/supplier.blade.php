<x-dashboardlayout>
    <h2>Supplier</h2>

    <div class="header-container" x-data="{ showAddModal: false, showAddBrandModal: false }">
        <div>
            <button class="modal-button" @click="showAddModal = true">
                Add Supplier
            </button>
            <button class="modal-button" @click="showAddBrandModal = true">
                Add Brand
            </button>    
        </div>
        
        {{-- STOCK-IN MODAL --}}
        <div x-show="showAddModal" x-cloak x-transition class="modal">
            <div class="add-component" @click.away="showAddModal = false">
                <h2 class="text-center">SUPPLIER FORM</h2>
                <form class="inventory-form" method="POST" action="{{ route('staff.supplier.store') }}">
                    @csrf
                    <div>
                        <label for="">Supplier Name</label>
                        <input required type="text" name="name">
                    </div>
                    <div>
                        <label for="">Contact Person</label>
                        <input required type="text" name="contact_person">
                    </div>
                    <div>
                        <label for="">Email</label>
                        <input required type="email" name="email">
                    </div>
                    <div>
                        <label for="">Contact number</label>
                        <input required name="phone" id="phone" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                    </div>    
                    <div>
                        <button>Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ADD BRAND MODAL --}}
        <div x-show="showAddBrandModal" x-cloak x-transition class="modal">
            <div class="add-component" @click.away="showAddBrandModal = false">
                <h2 class="text-center">BRAND FORM</h2>
                <form class="inventory-form" method="POST" action="{{ route('staff.supplier.store.brand') }}">
                    @csrf
                    <div>
                        <label for="">Supplier</label>
                        <select name="supplier_id" id="supplier_id">
                            @foreach ($suppliers as $supplier)
                                <option value="{{$supplier->id}}">{{$supplier->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="">Brand</label>
                        <input required type="text" name="name" id="name">
                    </div>
                    <div>
                        <button>Add Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="section-style !pl-0 !h-[65vh]">
        <div x-data="{ showViewModal: false, currentSupplier: null, showEditModal: false }" class="h-[55vh]">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr @if(!$supplier->is_active) class="bg-gray-200 opacity-60" @endif>
                            <td>{{$supplier->name}}</td>
                            <td>{{$supplier->contact_person}}</td>
                            <td>{{$supplier->email}}</td>
                            <td>{{$supplier->phone}}</td>
                            <td>{{$supplier->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <div class="flex justify-center gap-2">
                                    @if (!$supplier->is_active)
                                        <form action="{{ route('staff.supplier.restore', $supplier->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Activate">
                                                <x-icons.restore />
                                            </button>
                                        </form>
                                    @else
                                        <button @click="
                                            currentSupplier = {{ $supplier->toJson() }};
                                            showViewModal = true
                                        ">
                                            <x-icons.view/>    
                                        </button>
                                        <button @click="
                                            currentSupplier = {{ $supplier->toJson() }};
                                            showEditModal = true
                                        ">
                                            <x-icons.edit/>    
                                        </button>
                                        <form action="{{ route('staff.supplier.delete', $supplier->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">
                                                <x-icons.delete/>    
                                            </button>
                                        </form>                                        
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>    

            {{-- VIEW MODAL --}}
            <div x-show="showViewModal" x-cloak x-transition class="modal">
                <div class="modal-form" @click.away="showViewModal = false">
                    <div class="flex">
                        <h2 class="text-center">Supplier Details</h2>
                        <x-icons.close class="close" @click="showViewModal = false"/>    
                    </div>

                    <div class="supplier-container">
                        <div>
                            <p>Supplier Name</p>
                            <p x-text="currentSupplier ? currentSupplier.name : ''"></p>    
                        </div>
                        <div>
                            <p>Contact Person</p>
                            <p x-text="currentSupplier ? currentSupplier.contact_person : ''"></p>    
                        </div>
                        <div>
                            <p>Email</p>
                            <p x-text="currentSupplier ? currentSupplier.email : ''"></p>    
                        </div>
                        <div>
                            <p>Phone</p>
                            <p x-text="currentSupplier ? currentSupplier.phone : ''"></p>    
                        </div>
                        <div>
                            <p>Brands Supplied</p>
                            <template x-if="currentSupplier && currentSupplier.brands && currentSupplier.brands.length">
                                <p x-html="currentSupplier.brands.map(brand => brand.name).join('<br>')"></p>
                            </template>
                            <template x-if="currentSupplier && (!currentSupplier.brands || currentSupplier.brands.length === 0)">
                                <p>No brands associated.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EDIT MODAL --}}
            <div x-show="showEditModal" x-cloak x-transition class="modal">
                <div class="add-component" @click.away="showEditModal = false">
                    <div class="relative">
                        <h2 class="text-center w-[100%]">
                            Edit Details
                            <x-icons.close class="close" @click="showEditModal = false"/>    
                        </h2>
                    </div>

                    <form class="inventory-form" method="POST" :action="'{{ url('staff/supplier/update') }}/' + currentSupplier.id">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" x-model="currentSupplier.id">
                        <div>
                            <label for="">Supplier Name</label>
                            <input required type="text" name="name" x-model="currentSupplier.name">
                        </div>
                        <div>
                            <label for="">Contact Person</label>
                            <input required type="text" name="contact_person" x-model="currentSupplier.contact_person">
                        </div>
                        <div>
                            <label for="">Email</label>
                            <input required type="email" name="email" x-model="currentSupplier.email">
                        </div>
                        <div>
                            <label for="">Contact number</label>
                            <input required name="phone" id="phone" type="number" x-model="currentSupplier.phone" onkeydown="return !['e','E','+','-'].includes(event.key)">
                        </div>    
                        <div>
                            <label>Brands</label>
                            <template x-if="currentSupplier && currentSupplier.brands && currentSupplier.brands.length">
                                <template x-for="(brand, index) in currentSupplier.brands" :key="brand.id">
                                    <div>
                                        <label :for="'brand_' + index" x-text="'Brand ' + (index + 1)"></label>
                                        <input 
                                            type="text" 
                                            :name="'brands[' + index + '][name]'" 
                                            :id="'brand_' + index"
                                            x-model="brand.name"
                                        >
                                        <!-- Optional: Include hidden input for brand ID -->
                                        <input 
                                            type="hidden" 
                                            :name="'brands[' + index + '][id]'" 
                                            :value="brand.id"
                                        >
                                    </div>
                                </template>
                            </template>

                            <template x-if="currentSupplier && (!currentSupplier.brands || currentSupplier.brands.length === 0)">
                                <p class="text-gray-500">No brands associated with this supplier.</p>
                            </template>
                        </div>
                        <div>
                            <button>Update Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{ $suppliers->links() }}
    </section>
</x-dashboardlayout>