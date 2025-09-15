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
            <p>Socket Type</p>
            <p x-text="selectedComponent.socket_type"></p>
        </div>
        <div>
            <p>Cores</p>
            <p x-text="selectedComponent.cores"></p>
        </div>
        <div>
            <p>Threads </p>
            <p x-text="selectedComponent.threads"></p>
        </div>
        <div>
            <p>Base Clock</p>
            <p x-text="selectedComponent.base_clock + ' GHz'"></p>
        </div>
        <div>
            <p>Boost Clock</p>
            <p x-text="selectedComponent.boost_clock + ' GHz'"></p>
        </div>
        <div>
            <p>TDP</p>
            <p x-text="selectedComponent.tdp + ' W'"></p>
        </div>
        <div>
            <p>Integerated Graphics</p>
            <p x-html="selectedComponent.integrated_display"></p>
        </div>
        <div>
            <p>Generation</p>
            <p x-html="selectedComponent.generation"></p>
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