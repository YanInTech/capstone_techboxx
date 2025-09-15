<x-dashboardlayout>
    <h2>Component Details</h2>

    <div class="header-container" x-data="{ showAddModal: false, componentModal: null }">
        <button class="modal-button" @click="showAddModal = true">
            Add New Component
        </button>

        <div>
            <form action=" {{ route('staff.componentdetails.search') }}" method="GET">
                <input 
                    type="text"
                    name="search"
                    placeholder="Search components"
                    value="{{ request('search') }}"
                    class="search-bar"
                >
                <button type='submit'>
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>
    
        {{-- ADD COMPONENT MODAL --}}
        <div x-show="showAddModal" x-cloak x-transition class="modal">
            <div class="add-component" @click.away="showAddModal = false">
                @include('staff.componentdetails.add.addnewcomponent')
            </div>
        </div>

        @foreach (['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'case', 'cooler'] as $type)
            <div x-show="componentModal === '{{ $type }}'" x-cloak x-transition class="modal modal-scroll">
                <div class="new-component" @click.away="componentModal = null; showAddModal = true;">
                    

                    @include('staff.componentdetails.add.' . $type, [
                        'moboSpecs' => $moboSpecs,
                        'gpuSpecs' => $gpuSpecs,
                        'caseSpecs' => $caseSpecs,
                        'psuSpecs' => $psuSpecs,
                        'ramSpecs' => $ramSpecs,
                        'storageSpecs' => $storageSpecs,
                        'cpuSpecs' => $cpuSpecs,
                        'coolerSpecs' => $coolerSpecs,
                    ])
                </div>
            </div>
        @endforeach
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div x-data="{ showViewModal: false, showEditModal: false, selectedComponent:{} }" class=" h-[55vh]">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($components as $component)
                    <tr class="{{ $component->deleted_at ? 'bg-gray-300 opacity-50 cursor-not-allowed' : '' }}" >
                        <td>{{ $component->brand}} {{ $component->model }}</td>
                        <td>{{ $component->buildCategory->name}}</td>
                        <td>₱{{ number_format($component->price, 2) }}</td>
                        <td>{{ $component->stock }}</td>
                        <td class="align-middle text-center">
                            <div class="flex justify-center gap-2">
                                @if($component->deleted_at)
                                    {{-- Restore Button for Soft Deleted Components --}}
                                    <form action="{{ route('staff.componentdetails.restore', ['type' => $component->component_type, 'id' => $component->id]) }}" method="POST">
                                        @csrf
                                        @method('PATCH') <!-- or any method you use for restoring -->
                                        <button type="submit">
                                            <x-icons.restore />
                                        </button>
                                    </form>
                                @else
                                    {{-- View Button for Active Components --}}
                                    <button @click="showViewModal = true; selectedComponent = {{ $component->toJson() }};">
                                        <x-icons.view />
                                    </button>

                                    {{-- Edit Button for Active Components --}}
                                    <button @click="showEditModal = true; selectedComponent = {{ $component->toJson() }};">
                                        <x-icons.edit />
                                    </button>

                                    {{-- Delete Button for Active Components --}}
                                    <form action="{{ route('staff.componentdetails.delete', ['type' => $component->component_type, 'id' => $component->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">
                                            <x-icons.delete />
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
            <div x-show="showViewModal" x-cloak x-transition class="modal modal-scroll">
                <div class="view-component" @click.away="showViewModal = false">
                    <div x-show="selectedComponent.component_type === 'motherboard'">
                        @include('staff.componentdetails.view.motherboard')
                    </div>

                    <div x-show="selectedComponent.component_type === 'gpu'">
                        @include('staff.componentdetails.view.gpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'case'">
                        @include('staff.componentdetails.view.case')
                    </div>

                    <div x-show="selectedComponent.component_type === 'psu'">
                        @include('staff.componentdetails.view.psu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'ram'">
                        @include('staff.componentdetails.view.ram')
                    </div>

                    <div x-show="selectedComponent.component_type === 'storage'">
                        @include('staff.componentdetails.view.storage')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cpu'">
                        @include('staff.componentdetails.view.cpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cooler'">
                        @include('staff.componentdetails.view.cooler')
                    </div>
                </div>
            </div>

            {{-- EDIT MODAL --}}
            <div x-show="showEditModal" x-cloak x-transition class="modal modal-scroll">
                <div class="new-component" @click.away="showEditModal = false">
                    <div x-show="selectedComponent.component_type === 'motherboard'">
                        @include('staff.componentdetails.edit.motherboard')
                    </div>

                    <div x-show="selectedComponent.component_type === 'gpu'">
                        @include('staff.componentdetails.edit.gpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'case'">
                        @include('staff.componentdetails.edit.case')
                    </div>

                    <div x-show="selectedComponent.component_type === 'psu'">
                        @include('staff.componentdetails.edit.psu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'ram'">
                        @include('staff.componentdetails.edit.ram')
                    </div>

                    <div x-show="selectedComponent.component_type === 'storage'">
                        @include('staff.componentdetails.edit.storage')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cpu'">
                        @include('staff.componentdetails.edit.cpu')
                    </div>

                    <div x-show="selectedComponent.component_type === 'cooler'">
                        @include('staff.componentdetails.edit.cooler')
                    </div>
                </div>
            </div>
        </div>

    {{ $components->links() }}
        
    </section>

</x-dashboardlayout>

{{-- 3D MODEL VIEW --}}
{{-- <script type="module">
        import * as THREE from 'https://esm.sh/three@0.155.0';
        import { GLTFLoader } from 'https://esm.sh/three@0.155.0/examples/jsm/loaders/GLTFLoader.js';

        // Store Three.js stuff to global scope
        window.THREE = THREE;
        window.GLTFLoader = GLTFLoader;
</script>

<script>

    window.loadModel = function (canvasId, modelPath) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.warn(`Canvas with ID "${canvasId}" not found.`);
            return;
        }

        if (!modelPath) {
            console.warn("No 3D model path provided.");
            return;
        }

        const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
        renderer.setSize(canvas.width, canvas.height);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, canvas.width / canvas.height, 0.1, 1000);
        camera.position.z = 5;

        const light = new THREE.HemisphereLight(0xffffff, 0x444444, 1.5);
        scene.add(light);

        const loader = new GLTFLoader();

        loader.load(`/storage/${modelPath}`, function (gltf) {
            const model = gltf.scene;
            scene.add(model);
            model.scale.setScalar(.5);
            model.rotation.y = Math.PI;

            function animate() {
                requestAnimationFrame(animate);
                model.rotation.y += 0.005;
                renderer.render(scene, camera);
            }

            animate();
        }, undefined, function (error) {
            console.error("Error loading GLTF model:", error);
        });
    };


</script> --}}