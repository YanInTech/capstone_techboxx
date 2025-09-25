<pre x-text="JSON.stringify(selectedComponent, null, 2)"></pre>
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
            <p>Form Factor Support</p>
            <p x-html="selectedComponent.form_factor_support"></p>
        </div>
        <div>
            <p>Max GPU Lenght</p>
            <p x-text="selectedComponent.max_gpu_length_mm + ' mm'"></p>
        </div>
        <div>
            <p>Max Cooler Height</p>
            <p x-text="selectedComponent.max_cooler_height_mm + ' mm'"></p>
        </div>
        <div>
            <p>Radiator Support</p>
            <p x-html="selectedComponent.radiator_display"></p>
        </div>
        <div>
            <p>No. of Drive Bays</p>
            <p x-html="selectedComponent.drive_display"></p>
        </div>
        <div>
            <p>No  of Fan Mounts</p>
            <p x-html="selectedComponent.fan_mounts"></p>
        </div>
        <div>
            <p>No of Front USB Ports</p>
            <p x-html="selectedComponent.usb_display"></p>
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