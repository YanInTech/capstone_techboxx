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
            <p>RAM Type</p>
            <p x-text="selectedComponent.ram_type"></p>
        </div>
        <div>
            <p>Speed</p>
            <p x-text="selectedComponent.speed_mhz + ' MHz'"></p>
        </div>
        <div>
            <p>Size per module</p>
            <p x-text="selectedComponent.size_per_module_gb + ' GB'"></p>
        </div>
        <div>
            <p>Total Capacity</p>
            <p x-text="selectedComponent.total_capacity_gb + ' GB'"></p>
        </div>
        <div>
            <p>Module Count</p>
            <p x-text="selectedComponent.module_count"></p>
        </div>
        <div>
            <p>ECC</p>
            <p x-text="selectedComponent.ecc_display"></p>
        </div>
        <div>
            <p>RGB</p>
            <p X-text="selectedComponent.rgb_display"></p>
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