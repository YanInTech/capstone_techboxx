@props(['gpuSpecs'])

<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        EDIT
        <x-icons.close class="close" @click="showEditModal = false"/>    
    </h2>
</div>

<form x-bind:action="'/staff/component-details/gpu/' + selectedComponent.id" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="_method" value="PUT">
    <div class="form-container">
        {{-- SPECS --}}
        <div class="form-divider gpu">
            <div>
                <label for="">Brand</label>
                <select required name="brand" x-model="selectedComponent.brand" class="brand-select" disabled>
                    <option disabled selected hidden value="">Select a supplier first</option>
                    @foreach ($gpuSpecs['brands'] as $brand)
                        <option value="{{ $brand->name }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Models</label>
                <input name="model" required type="text" x-model="selectedComponent.model" placeholder="Enter Model">
            </div>

            <div>
                <label for="">Video RAM GB</label>
                <input required type="number" name="vram_gb" placeholder="00 GB" x-model="selectedComponent.vram_gb" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Power Draw Watts</label>
                <input required type="number" name="power_draw_watts" placeholder="00 W TDP" x-model="selectedComponent.power_draw_watts" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label  for="">Recommended PSU Watt</label>
                <input required type="number" name="recommended_psu_watt" placeholder="00 W" x-model="selectedComponent.recommended_psu_watt" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Length</label>
                <input required type="number" name="length_mm" placeholder="00 mm" x-model="selectedComponent.length_mm" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">PCIe Interface</label>
                <select required name="pcie_interface" id="pcie_interface" x-model="selectedComponent.pcie_interface">
                    <option disabled selected hidden value="">Select a PCIe interface</option>
                    @foreach ($gpuSpecs['pcie_interfaces'] as $pcie_interface)
                        <option value="{{ $pcie_interface }}">{{ $pcie_interface }}</option>
                    @endforeach
                </select> 
            </div>

            <div>
                <label for="">Connectors Required</label>
                <select required name="connectors_required" id="connectors_required" x-model="selectedComponent.connectors_required">
                    <option disabled selected hidden value="">Select connectors</option>
                    @foreach ($gpuSpecs['connectors_requireds'] as $connectors_required)
                        <option value="{{ $connectors_required }}">{{ $connectors_required }}</option>
                    @endforeach
                </select> 
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
                    @foreach ($gpuSpecs['buildCategories'] as $buildCategory)
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
    
    <button>Update Component</button>
</form>