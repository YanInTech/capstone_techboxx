@props(['psuSpecs'])
<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>
<form x-bind:action="'/staff/component-details/psu/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Brand</label>
                <select required name="brand" x-model="selectedComponent.brand" class="brand-select" disabled>
                    <option disabled selected hidden value="">Select a supplier first</option>
                    @foreach ($psuSpecs['brands'] as $brand)
                        <option value="{{ $brand->name }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

             <div>
                <label for="">Model</label>
                <input name="model" required type="text" placeholder="Enter model" x-model="selectedComponent.model">
            </div>

            <div>
                <label for="">Wattage</label>
                <input required name="wattage" id="wattage" type="number" placeholder="00 W" x-model="selectedComponent.wattage" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Efficiency Rating</label>
                <select required name="efficiency_rating" id="efficiency_rating" x-model="selectedComponent.efficiency_rating">
                    <option disabled selected hidden value="">Rating</option>
                    @foreach ($psuSpecs['efficiency_ratings'] as $efficiency_rating)
                        <option value="{{ $efficiency_rating }}">{{ $efficiency_rating }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="">Modular</label>
                <select required name="modular" id="modular" x-model="selectedComponent.modular">
                    <option disabled selected hidden value="">Select modular</option>
                    @foreach ($psuSpecs['modulars'] as $modular)
                        <option value="{{ $modular }}">{{ $modular }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">PCIe Connectors</label>
                <input required name="pcie_connectors" id="pcie_connectors" type="number" placeholder="00 W" x-model="selectedComponent.pcie_connectors" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Sata Connectors</label>
                <input required name="sata_connectors" id="sata_connectors" type="number" placeholder="00 W" x-model="selectedComponent.sata_connectors" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
        </div>

        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter price" x-model="selectedComponent.price" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id" x-model="selectedComponent.build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($psuSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>

            <div>
                <label for="">Stock</label>
                <input required name="stock" id="stock" type="number" placeholder="Enter stock" x-model="selectedComponent.stock" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
        </div>    
    </div>
    
    <button>Add Component</button>
</form>