<div class="relative !m-0">
    <h2 class="text-center w-[100%]">
        ADD NEW COMPONENT
        <x-icons.close class="close" @click="showAddModal = false"/>    
    </h2>
</div>
    <div>
        <button @click="componentModal = 'cpu'; showAddModal = false">
            <x-icons.addcomponents.cpu/>
            CPU
        </button>
        <button @click="componentModal = 'motherboard'; showAddModal = false">
            <x-icons.addcomponents.mobo />
            Motherboard
        </button>
        <button @click="componentModal = 'ram'; showAddModal = false">
            <x-icons.addcomponents.ram/>
            RAM
        </button>
        <button @click="componentModal = 'gpu'; showAddModal = false">
            <x-icons.addcomponents.gpu/>
            GPU
        </button>
        <button @click="componentModal = 'storage'; showAddModal = false">
            <x-icons.addcomponents.storage/>
            Storage
        </button>
        <button @click="componentModal = 'psu'; showAddModal = false">
            <x-icons.addcomponents.psu/>
            PSU
        </button>
        <button @click="componentModal = 'case'; showAddModal = false">
            <x-icons.addcomponents.case/>
            Case
        </button>
        <button @click="componentModal = 'cooler'; showAddModal = false">
            <x-icons.addcomponents.cooling/>
            Cooler
        </button>
    </div>