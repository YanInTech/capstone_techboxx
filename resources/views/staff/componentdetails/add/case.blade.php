@props(['caseSpecs'])

<div class="flex flex-row justify-between">
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.arrow class="rotate-90 hover:opacity-50 w-[24px] h-[24px]"/>
    </button>
    <h2 class="text-center">Case</h2>
    <button @click="componentModal = null; showAddModal = true;">
        <x-icons.close/>
    </button>
</div>

<form action="{{ route('staff.componentdetails.case.store') }}" method="POST" class="new-component-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="component_type" value="case">

    <div class="form-container">
        {{-- INVENTORY --}}
        <div class="form-divider">
            <div>
                <label for="">Build Category</label>
                <select required name="build_category_id" id="build_category_id">
                    <option disabled selected hidden value="">Select build category</option>   
                    @foreach ($caseSpecs['buildCategories'] as $buildCategory)
                        <option value="{{ $buildCategory->id }}">{{ $buildCategory->name }}</option>
                    @endforeach 
                </select>  
            </div>

            <div>
                <label for="">Price</label>
                <input required name="price" id="price" type="number" step="0.01" placeholder="Enter price" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Stock</label>
                <input required name="stock" id="stock" type="number" placeholder="Enter stock" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Upload product image</label>
                <input type="file" name="image" multiple accept="image/*">
            </div>

            <div>
                <label for="">Upload product 3d model</label>
                <input type="file" name="model_3d" accept=".glb">
            </div>
        </div>  
        
        {{-- SPECS --}}
        <div class="form-divider">
            <div>
                <label for="">Supplier</label>
                <select required name="supplier_id" class="supplier-select">
                    <option disabled selected hidden value="">Select a supplier</option>
                    @foreach ($caseSpecs['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="">Brand</label>
                <input name="brand" required type="text" placeholder="Enter Brand">
            </div>
            <div>
                <label for="">Model</label>
                <input name="model" required type="text" placeholder="Enter Model">
            </div>

            <div>
                <label for="">Form Factor Support</label>
                <select required name="form_factor_support" id="form_factor_support">
                    <option disabled selected hidden value="">Select a form factor support</option>
                    @foreach ($caseSpecs['form_factor_supports'] as $form_factor_support)
                        <option value="{{ $form_factor_support }}">{{ $form_factor_support }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="">Max GPU Lenght mm</label>
                <input required name="max_gpu_length_mm" id="max_gpu_length_mm" type="number" placeholder="Enter Max GPU Length" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Max Cooler Height mm</label>
                <input required name="max_cooler_height_mm" id="max_cooler_height_mm" type="number" placeholder="Enter Max Cooler Height" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>

            <div>
                <label for="">Fan Mount</label>
                <input required name="fan_mounts" id="fan_mounts" type="number" placeholder="00 GB" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            
            <div>
                <label for="">Drive Bay</label>
                <div class="w-[80%]">
                    <input required name='3_5_bays' id='3_5_bays' type="number" placeholder='No. of 3.5" bays' onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='2_5_bays' id='2_5_bays' type="number" placeholder='No. of 2.5" bays' onkeydown="return !['e','E','+','-'].includes(event.key)">
                </div>
            </div>

            <div>
                <label for="">Front USB Port</label>
                <div class="w-[80%]">
                    <input required name='usb_3_0_type-A' id='usb_3_0_type-A' type="number" placeholder='USB 3.0' onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='usb_2_0' id='usb_2_0' type="number" placeholder='USB 2.0' onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='usb-c' id='usb-c' type="number" placeholder='USB-C' onkeydown="return !['e','E','+','-'].includes(event.key)">
                    <input required name='audio_jacks' id='audio_jacks' type="number" placeholder='Audio jacks' onkeydown="return !['e','E','+','-'].includes(event.key)">
                </div>
            </div>
            

            <div class="flex flex-col"
                 x-data="{ slots:[{}] }">
                <template x-for="(slot, index) in slots" 
                          :key="index">
                    <div >
                        <label for="">Radiator Support <span x-text="index + 1"></span></label>
                        <div class="w-[80%]">
                            <select required :name="'radiator_support[' + index + '][location]'" id="radiatorlocation">
                                <option disabled selected hidden value="">Location</option>
                                @foreach ($caseSpecs['locations'] as $location)
                                    <option value="{{ $location }}">{{ $location }}</option>
                                @endforeach
                            </select>
                            <input required :name="'radiator_support[' + index + '][size_mm]'"  id="size_mm" type="number"  placeholder="Size 00 mm" onkeydown="return !['e','E','+','-'].includes(event.key)">
                        </div>
                        
                        <template x-if="index > 0">
                            <button type="button"
                                class="remove-add"
                                @click="slots.splice(index, 1)">
                                x
                            </button>    
                        </template>
                    </div>
                </template>
                
                {{-- ADD RADIATOR SUPPORT BUTTON --}}
                <button type="button"
                        @click="slots.push({})"
                        class="add-pcie">
                    + Add Radiator Support
                </button>
            </div>
        </div>
    </div>
    
    <button>Add Component</button>
</form>

