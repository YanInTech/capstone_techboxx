{{-- <pre x-text="JSON.stringify(selectedComponent, null, 2)"></pre> --}}
<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        VIEW
        <x-icons.close class="close" @click="showViewModal = false"/>    
    </h2>
</div>
<div class="view-container">
    {{-- IMAGE --}}
    <div class="image-container">
        <img :src="`/${selectedComponent.image}`" alt="Product Image" >
    </div>

    <div x-show="!selectedComponent.image || selectedComponent.image.length === 0">
        <p>No image uploaded.</p>
    </div>


    {{-- SPECS --}}
    <div class="specs-container">
        <div>
            <p>Brand</p>
            <p x-text="selectedComponent.brand"></p>
        </div>
        <div>
            <p>Model</p>
            <p x-text="selectedComponent.model"></p>
        </div>
        <div>
            <p>Storage Type</p>
            <p x-text="selectedComponent.storage_type"></p>
        </div>
        <div>
            <p>Interface</p>
            <p x-text="selectedComponent.interface"></p>
        </div>
        <div>
            <p>Capacity</p>
            <p x-text="selectedComponent.capacity_gb + ' GB'"></p>
        </div>
        <div>
            <p>Form Factor</p>
            <p x-text="selectedComponent.form_factor"></p>
        </div>
        <div>
            <p>Read Speed</p>
            <p x-text="selectedComponent.read_speed_mbps + ' MHz'"></p>
        </div>
        <div>
            <p>Write Speed</p>
            <p x-text="selectedComponent.write_speed_mbps + 'MHz'"></p>
        </div>
        <div>
            <p>Price </p>
            <p x-text="selectedComponent.price_display"></p>
        </div>
        <div>
            <p>Stock </p>
            <p x-text="selectedComponent.stock"></p>
        </div>
    </div>
</div>