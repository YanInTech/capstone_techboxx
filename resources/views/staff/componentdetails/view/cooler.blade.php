{{-- <pre x-text="JSON.stringify(selectedComponent, null, 2)"></pre> --}}
<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        VIEW
        <x-icons.close class="close" @click="showViewModal = false"/>    
    </h2>
</div>
{{-- <div class="view-container"> --}}
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
            <p>Cooler Type</p>
            <p x-html="selectedComponent.cooler_type"></p>
        </div>
        <div>
            <p>Compatible Socket</p>
            <p x-html="selectedComponent.socket_display"></p>
        </div>
        <div>
            <p>Max Tdp</p>
            <p x-text="selectedComponent.max_tdp + ' W'"></p>
        </div>
        <div>
            <p>Radiator Size</p>
            <p x-html="selectedComponent.radiator_size_mm ? selectedComponent.radiator_size_mm + ' mm' : 'N/A'"></p>
        </div>
        <div>
            <p>No. of Fan</p>
            <p x-html="selectedComponent.fan_count"></p>
        </div>
        <div>
            <p>Height</p>
            <p x-html="selectedComponent.height_mm + ' mm'"></p>
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