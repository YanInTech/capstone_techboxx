// 3D JS
import * as THREE from 'https://esm.sh/three@0.155.0';
import { OrbitControls } from 'https://esm.sh/three@0.155.0/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'https://esm.sh/three@0.155.0/examples/jsm/loaders/GLTFLoader.js';
import interact from 'https://esm.sh/interactjs@1.10.17';

let scene, camera, renderer, controls;
let caseModel = null;
let moboModel = null;
let cpuModel = null;
let psuModel = null;
let coolerModel = null;
let ssdModel = null;
let gpuModel = null;
let ramModels = [];
let moboSlotPosition = null;
let cpuSlotPosition = null;
let psuSlotPosition = null;
let coolerSlotPosition = null;
let ssdSlotPosition = null;
let gpuSlotPosition = null;
let selectedCaseModelUrl = null;
let selectedMoboModelUrl = null;
let selectedCpuModelUrl = null;
let selectedPsuModelUrl = null;
let selectedCoolerModelUrl = null;
let selectedSsdModelUrl = null;
let selectedGpuModelUrl = null;
let selectedRamModelUrl = null;

let caseMarker = null;
let moboMarker = null;
let draggingId = null;
let draggingEl = null;

init();
setupCatalogClickHandlers();
animate();

function init() {
    // Scene
    scene = new THREE.Scene();
    scene.background = null; // transparent background

    // Camera
    const container = document.getElementById('canvas-container');
    const width = container.clientWidth;
    const height = container.clientHeight;

    camera = new THREE.PerspectiveCamera(30, width / height, 0.1, 1000);
    camera.position.set(20, 0, 0);

    // Renderer
    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    container.appendChild(renderer.domElement);

    // Controls
    controls = new OrbitControls(camera, renderer.domElement);

    // Lights
    scene.add(new THREE.AmbientLight(0xffffff, 0.5));
    const dirLight = new THREE.DirectionalLight(0xffffff, 7.0);
    dirLight.position.set(2, 10, 5);
    scene.add(dirLight);

    // Resize listener
    window.addEventListener('resize', () => {
        const width = container.clientWidth;
        const height = container.clientHeight;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    });
}

function animate() {
    requestAnimationFrame(animate);
    renderer.render(scene, camera);
}

async function loadGLTFModel(url) {
    const loader = new GLTFLoader();
    const gltf = await loader.loadAsync(url);
    return gltf.scene;
}

// Catalog click (optional: preselect URLs)
function setupCatalogClickHandlers() {
  document.querySelectorAll('.build-catalog, .component-button').forEach(item => {
    item.addEventListener('click', () => {
      const modelUrl = item.getAttribute('data-model');
      const type = item.getAttribute('data-type');

      if (!modelUrl) {
        console.log('Model not available for this component.');
        return;
      }
      if (type === 'case') {
        selectedCaseModelUrl = modelUrl;
        console.log('Selected case URL:', selectedCaseModelUrl);
      } else if (type === 'motherboard') {
        selectedMoboModelUrl = modelUrl;
        console.log('Selected motherboard URL:', selectedMoboModelUrl);
      } else if (type === 'cpu') {
        selectedCpuModelUrl = modelUrl;
        console.log('Selected CPU URL:', selectedCpuModelUrl);
      } else if (type === 'psu') {
        selectedPsuModelUrl = modelUrl;
        console.log('Selected PSU URL:', selectedPsuModelUrl);
      } else if (type === 'cooler') {
        selectedCoolerModelUrl = modelUrl;
        console.log('Selected cooler URL:', selectedCoolerModelUrl);
      } else if (type === 'ssd') {
        selectedSsdModelUrl = modelUrl;
        console.log('Selected SSD URL:', selectedSsdModelUrl);
      } else if (type === 'gpu') {
        selectedGpuModelUrl = modelUrl;
        console.log('Selected GPU URL:', selectedGpuModelUrl);
      } else if (type === 'ram') {
        selectedRamModelUrl = modelUrl;
        console.log('Selected RAM URL:', selectedRamModelUrl);
      }
    });
  });
}

// Interact.js drag/drop
interact('.component-button').draggable({
    listeners: {
        start(event) {
            draggingId = event.target.getAttribute('data-type') || event.target.id;
            draggingEl = event.target;
            draggingEl.style.opacity = '0.5';
            document.body.style.cursor = 'grabbing';

            if (draggingId === 'case' && !caseModel) {
                caseMarker = new THREE.Mesh(
                    new THREE.BoxGeometry(4, 4, 4),
                    new THREE.MeshStandardMaterial({
                    color: 0x0000ff,
                    emissive: 0x000066,
                    transparent: true,
                    opacity: 0.3,
                    })
                );
                
                caseMarker.position.set(0, 0, 0);
                scene.add(caseMarker);
            }

            if (draggingId === 'motherboard' && caseModel) {
                const moboSlot = caseModel.getObjectByName('Slot_Mobo');
                if (moboSlot) {
                    moboMarker = new THREE.Mesh(
                    new THREE.BoxGeometry(3, 3, 0.1),
                    new THREE.MeshStandardMaterial({
                        color: 0x00ff00,
                        emissive: 0x003300,
                        transparent: true,
                        opacity: 0.4,
                    })
                    );

                    moboMarker.rotation.x = 0;
                    moboMarker.rotation.y = Math.PI / 2;
                    moboMarker.rotation.z = 0;
                    moboMarker.position.set(moboSlotPosition.x, moboSlotPosition.y + -1.4, moboSlotPosition.z + -2);
                    scene.add(moboMarker);
                }
            }

            // Add markers for other components
            if (draggingId === 'cpu' && moboModel) {
                const cpuSlot = moboModel.getObjectByName('Slot_Cpu');
                if (cpuSlot) {
                    const cpumarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    cpumarker.rotation.x = 0;
                    cpumarker.rotation.y = Math.PI / 2;
                    cpumarker.rotation.z = 0;
                    
                    const cpuSlotPosition = new THREE.Vector3();
                    cpuSlot.getWorldPosition(cpuSlotPosition);
                    cpumarker.position.set(cpuSlotPosition.x, cpuSlotPosition.y + -1, cpuSlotPosition.z + -1.4);
                    scene.add(cpumarker);
                }
            }

            if (draggingId === 'psu' && caseModel) {
                const psuSlot = caseModel.getObjectByName('Slot_Psu');
                if (psuSlot) {
                    const psumarker = new THREE.Mesh(
                        new THREE.BoxGeometry(1, 0.8, 2),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    psumarker.rotation.x = 0;
                    psumarker.rotation.y = Math.PI / 2;
                    psumarker.rotation.z = 0;
                    
                    const psuSlotPosition = new THREE.Vector3();
                    psuSlot.getWorldPosition(psuSlotPosition);
                    psumarker.position.set(psuSlotPosition.x + 1.4, psuSlotPosition.y + 0.4, psuSlotPosition.z + -1);
                    scene.add(psumarker);
                }
            }

            if (draggingId === 'cooler' && moboModel) {
                const coolerSlot = moboModel.getObjectByName('Slot_Cooler');
                if (coolerSlot) {
                    const coolermarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    coolermarker.rotation.x = 0;
                    coolermarker.rotation.y = Math.PI / 2;
                    coolermarker.rotation.z = 0;
                    
                    const coolerSlotPosition = new THREE.Vector3();
                    coolerSlot.getWorldPosition(coolerSlotPosition);
                    coolermarker.position.set(coolerSlotPosition.x, coolerSlotPosition.y + -1, coolerSlotPosition.z + -1.4);
                    scene.add(coolermarker);
                }
            }

            if (draggingId === 'ssd' && moboModel) {
                const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
                if (ssdSlot) {
                    const ssdmarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    ssdmarker.rotation.x = 0;
                    ssdmarker.rotation.y = Math.PI / 2;
                    ssdmarker.rotation.z = 0;
                    
                    const ssdSlotPosition = new THREE.Vector3();
                    ssdSlot.getWorldPosition(ssdSlotPosition);
                    ssdmarker.position.set(ssdSlotPosition.x, ssdSlotPosition.y + -1, ssdSlotPosition.z + -1.4);
                    scene.add(ssdmarker);
                }
            }

            if (draggingId === 'gpu' && moboModel) {
                const gpuSlot = moboModel.getObjectByName('Slot_Gpu');
                if (gpuSlot) {
                    const gpumarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    gpumarker.rotation.x = 0;
                    gpumarker.rotation.y = Math.PI / 2;
                    gpumarker.rotation.z = 0;
                    
                    const gpuSlotPosition = new THREE.Vector3();
                    gpuSlot.getWorldPosition(gpuSlotPosition);
                    gpumarker.position.set(gpuSlotPosition.x, gpuSlotPosition.y + -1, gpuSlotPosition.z + -1.4);
                    scene.add(gpumarker);
                }
            }

            if (draggingId === 'ram' && moboModel) {
                const ramSlot01 = moboModel.getObjectByName('Slot_Ram1');
                const ramSlot02 = moboModel.getObjectByName('Slot_Ram2');
                
                if (ramSlot01 || ramSlot02) {
                    const rammarker = new THREE.Mesh(
                        new THREE.BoxGeometry(2, 2, 0.1),
                        new THREE.MeshStandardMaterial({
                            color: 0x00ff00,
                            emissive: 0x003300,
                            transparent: true,
                            opacity: 0.4,
                        })
                    );
                    rammarker.rotation.x = 0;
                    rammarker.rotation.y = Math.PI / 2;
                    rammarker.rotation.z = 0;
                    
                    const firstRamSlot = ramSlot01 || ramSlot02;
                    const ramSlotPosition = new THREE.Vector3();
                    firstRamSlot.getWorldPosition(ramSlotPosition);
                    rammarker.position.set(ramSlotPosition.x, ramSlotPosition.y + -1, ramSlotPosition.z + -1.4);
                    scene.add(rammarker);
                }
            }
        },
        move(event) {
        // Optional: visual feedback while dragging
        },
        async end(event) {
            draggingEl.style.opacity = '1';
            document.body.style.cursor = 'grab';

            const dropPos = getCanvasDropPosition(event.clientX, event.clientY);

            if (dropPos && draggingId === 'case' && !caseModel) {
                if (selectedCaseModelUrl) {
                await spawnCase(dropPos, selectedCaseModelUrl);
                }
            }
            if (dropPos && draggingId === 'motherboard' && caseModel) {
                await spawnMoboAtSlot();
            }
            if (dropPos && draggingId === 'cpu' && moboModel) {
                await spawnCpuAtSlot();
            }
            if (dropPos && draggingId === 'psu' && caseModel) {
                await spawnPsuAtSlot();
            }
            if (dropPos && draggingId === 'cooler' && moboModel) {
                await spawnCoolerAtSlot();
            }
            if (dropPos && draggingId === 'ssd' && moboModel) {
                await spawnSsdAtSlot();
            }
            if (dropPos && draggingId === 'gpu' && moboModel) {
                await spawnGpuAtSlot();
            }
            if (dropPos && draggingId === 'ram' && moboModel) {
                await spawnRamAtSlot();
            }

            // Clean up markers
            if (caseMarker) {
                scene.remove(caseMarker);
                caseMarker = null;
            }
            if (moboMarker) {
                scene.remove(moboMarker);
                moboMarker = null;
            }

            // Clean up other markers
            scene.traverse((object) => {
                if (object.isMesh && object.material && object.material.emissive && 
                    object.material.emissive.equals(new THREE.Color(0x003300))) {
                    scene.remove(object);
                }
            });

            draggingId = null;
            draggingEl = null;
        }
    }
});

function getCanvasDropPosition(clientX, clientY) {
    const rect = renderer.domElement.getBoundingClientRect();
    if (
        clientX < rect.left || clientX > rect.right ||
        clientY < rect.top || clientY > rect.bottom
    ) {
        return null;
    }
    const x = ((clientX - rect.left) / rect.width) * 2 - 1;
    const y = -((clientY - rect.top) / rect.height) * 2 + 1;

    const mouseVector = new THREE.Vector2(x, y);
    const raycaster = new THREE.Raycaster();
    raycaster.setFromCamera(mouseVector, camera);

    const planeZ = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
    const intersectionPoint = new THREE.Vector3();
    raycaster.ray.intersectPlane(planeZ, intersectionPoint);
    return intersectionPoint;
}

async function spawnCase(position, modelUrl) {
    if (caseModel) return;
    try {
        const model = await loadGLTFModel(modelUrl);
        model.position.copy(position);
        model.scale.setScalar(1);
        scene.add(model);
        caseModel = model;

        // Focus controls on the case
        controls.target.copy(model.position);
        controls.update();

        // Get all slot positions
        const moboSlot = model.getObjectByName('Slot_Mobo');
        if (moboSlot) {
            moboSlotPosition = new THREE.Vector3();
            moboSlot.getWorldPosition(moboSlotPosition);
            console.log('Motherboard slot position:', moboSlotPosition);
        } else {
            moboSlotPosition = new THREE.Vector3(0, 0, 0);
            console.warn('Motherboard slot not found in case model');
        }

        const psuSlot = model.getObjectByName('Slot_Psu');
        if (psuSlot) {
            psuSlotPosition = new THREE.Vector3();
            psuSlot.getWorldPosition(psuSlotPosition);
            console.log('PSU slot position:', psuSlotPosition);
        } else {
            psuSlotPosition = new THREE.Vector3(0, 0, 0);
            console.warn('PSU slot not found in case model');
        }

    } catch (err) {
        console.error('Failed to load case model', err);
    }
}

async function spawnMoboAtSlot() {
    if (!moboSlotPosition) {
        alert('Motherboard slot position unknown');
        return;
    }
    if (!selectedMoboModelUrl) {
        alert('Please select a motherboard model first.');
        return;
    }
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedMoboModelUrl);
        model.position.copy(moboSlotPosition);
        
        // Apply rotation from slot
        const moboSlot = caseModel.getObjectByName('Slot_Mobo');
        if (moboSlot) {
            model.rotation.copy(moboSlot.rotation);
        }
        
        scene.add(model);
        moboModel = model;

        // Get motherboard component slots
        const cpuSlot = moboModel.getObjectByName('Slot_Cpu');
        if (cpuSlot) {
            cpuSlotPosition = new THREE.Vector3();
            cpuSlot.getWorldPosition(cpuSlotPosition);
            console.log('CPU slot position:', cpuSlotPosition);
        }

        const coolerSlot = moboModel.getObjectByName('Slot_Cooler');
        if (coolerSlot) {
            coolerSlotPosition = new THREE.Vector3();
            coolerSlot.getWorldPosition(coolerSlotPosition);
            console.log('Cooler slot position:', coolerSlotPosition);
        }

        const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
        if (ssdSlot) {
            ssdSlotPosition = new THREE.Vector3();
            ssdSlot.getWorldPosition(ssdSlotPosition);
            console.log('SSD slot position:', ssdSlotPosition);
        }

        const gpuSlot = moboModel.getObjectByName('Slot_Gpu');
        if (gpuSlot) {
            gpuSlotPosition = new THREE.Vector3();
            gpuSlot.getWorldPosition(gpuSlotPosition);
            console.log('GPU slot position:', gpuSlotPosition);
        }

    } catch (err) {
        console.error('Failed to load motherboard model', err);
    }
}

async function spawnCpuAtSlot() {
    if (!cpuSlotPosition) {
        alert('CPU slot position unknown');
        return;
    }
    if (!selectedCpuModelUrl) {
        alert('Please select a CPU model first.');
        return;
    }
    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedCpuModelUrl);
        model.position.copy(cpuSlotPosition);
        scene.add(model);
        cpuModel = model;
    } catch (err) {
        console.error('Failed to load CPU model', err);
    }
}

async function spawnPsuAtSlot() {
    if (!psuSlotPosition) {
        alert('PSU slot position unknown');
        return;
    }
    if (!selectedPsuModelUrl) {
        alert('Please select a PSU model first.');
        return;
    }
    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedPsuModelUrl);
        model.position.copy(psuSlotPosition);
        
        // Apply rotation from slot
        const psuSlot = caseModel.getObjectByName('Slot_Psu');
        if (psuSlot) {
            model.rotation.copy(psuSlot.rotation);
        }
        
        scene.add(model);
        psuModel = model;
    } catch (err) {
        console.error('Failed to load PSU model', err);
    }
}

async function spawnCoolerAtSlot() {
    if (!coolerSlotPosition) {
        alert('Cooler slot position unknown');
        return;
    }
    if (!selectedCoolerModelUrl) {
        alert('Please select a cooler model first.');
        return;
    }
    if (coolerModel) {
        scene.remove(coolerModel);
        coolerModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedCoolerModelUrl);
        model.position.copy(coolerSlotPosition);
        scene.add(model);
        coolerModel = model;
    } catch (err) {
        console.error('Failed to load cooler model', err);
    }
}

async function spawnSsdAtSlot() {
    if (!ssdSlotPosition) {
        alert('SSD slot position unknown');
        return;
    }
    if (!selectedSsdModelUrl) {
        alert('Please select an SSD model first.');
        return;
    }
    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedSsdModelUrl);
        model.position.copy(ssdSlotPosition);
        
        // Apply rotation from slot
        const ssdSlot = moboModel.getObjectByName('Slot_Ssd');
        if (ssdSlot) {
            model.rotation.copy(ssdSlot.rotation);
        }
        
        scene.add(model);
        ssdModel = model;
    } catch (err) {
        console.error('Failed to load SSD model', err);
    }
}

async function spawnGpuAtSlot() {
    if (!gpuSlotPosition) {
        alert('GPU slot position unknown');
        return;
    }
    if (!selectedGpuModelUrl) {
        alert('Please select a GPU model first.');
        return;
    }
    if (gpuModel) {
        scene.remove(gpuModel);
        gpuModel = null;
    }
    try {
        const model = await loadGLTFModel(selectedGpuModelUrl);
        model.position.copy(gpuSlotPosition);
        scene.add(model);
        gpuModel = model;
    } catch (err) {
        console.error('Failed to load GPU model', err);
    }
}

async function spawnRamAtSlot() {
    if (!selectedRamModelUrl) {
        alert('Please select a RAM model first.');
        return;
    }

    // Remove existing RAM models if any
    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }
    
    try {
        const baseRamModel = await loadGLTFModel(selectedRamModelUrl);
        
        // Get all RAM slots from the motherboard
        const slotRam01 = moboModel.getObjectByName('Slot_Ram1');
        const slotRam02 = moboModel.getObjectByName('Slot_Ram2');
        
        // Clone and position RAM sticks in available slots
        if (slotRam01) {
            const ram1 = baseRamModel.clone();
            const worldPosition = new THREE.Vector3();
            slotRam01.getWorldPosition(worldPosition);
            ram1.position.copy(worldPosition);
            ram1.rotation.copy(slotRam01.rotation);
            scene.add(ram1);
            ramModels.push(ram1);
        }
        
        if (slotRam02) {
            const ram2 = baseRamModel.clone();
            const worldPosition = new THREE.Vector3();
            slotRam02.getWorldPosition(worldPosition);
            ram2.position.copy(worldPosition);
            ram2.rotation.copy(slotRam02.rotation);
            scene.add(ram2);
            ramModels.push(ram2);
        }
        
        if (ramModels.length === 0) {
            alert('No RAM slots found on the motherboard');
            return;
        }
        
        console.log(`Spawned ${ramModels.length} RAM sticks`);
        
    } catch (err) {
        console.error('Failed to load RAM model', err);
    }
}

function reloadScene() {
    // Remove case model
    if (caseModel) {
        scene.remove(caseModel);
        caseModel = null;
    }

    // Remove motherboard model
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }

    // Remove CPU model
    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }

    // Remove PSU model
    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }

    // Remove cooler model
    if (coolerModel) {
        scene.remove(coolerModel);
        coolerModel = null;
    }

    // Remove SSD model
    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }

    // Remove GPU model
    if (gpuModel) {
        scene.remove(gpuModel);
        gpuModel = null;
    }

    // Remove ALL ram models
    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }

    // Reset the camera controls target to the origin
    controls.target.set(0, 0, 0);
    controls.update();

    // Log to confirm scene is reset
    console.log('Scene has been reloaded.');
}

// Add reload button event listener if it exists
if (document.getElementById('reloadButton')) {
    document.getElementById('reloadButton').addEventListener('click', function() {
        reloadScene();
    });
}


// LAYOUT / FUNCTIONALITY JS
// Initialize global selectedComponents object
window.selectedComponents = window.selectedComponents || {};
// Function to handle component selection from catalog
function selectComponent(componentData) {
    const componentType = componentData.type.toLowerCase();
    
    // CALL TOGGLE STORAGE IF SSD OR HDD IS SELECTED - DO THIS FIRST
    if (componentType === 'ssd' || componentType === 'hdd') {
        toggleStorage(componentType);
    }
    
    window.selectedComponents[componentType] = {
        componentId: componentData.id,
        name: componentData.name,
        price: componentData.price,
        type: componentType
    };
    
    console.log('Selected component:', componentType, window.selectedComponents[componentType]);

    // Update session in backend
    updateSession(window.selectedComponents);
}


// BUILD CART FORM SUBMISSION - UPDATED VERSION
function handleFormSubmit(e) {
    console.log('=== FORM SUBMISSION INTERCEPTED ===');
    e.preventDefault(); // PREVENT DEFAULT IMMEDIATELY
    
    // Get the form to check its action (order vs save)
    const form = e.target;
    const isOrder = form.action.includes('order');
    console.log('Form type:', isOrder ? 'ORDER' : 'SAVE');
    
    // Payment method validation - ONLY FOR ORDERS
    if (isOrder) {
        const paymentMethod = document.getElementById('payment_method').value;
        console.log('Payment method:', paymentMethod);
        if (!paymentMethod) {
            alert('Please select a payment method.');
            return false;
        }
    }

    // Build name validation
    const buildNameInput = document.querySelector('input[name="build_name"]');
    const buildName = buildNameInput ? buildNameInput.value : '';
    console.log('Build name:', buildName);
    if (!buildName.trim()) {
        alert('Please enter a build name.');
        return false;
    }

    // Check if any components are selected
    console.log('Selected components:', window.selectedComponents);
    if (Object.keys(window.selectedComponents).length === 0) {
        alert('Please select at least one component.');
        return false;
    }

    // Update all hidden inputs before submission
    console.log('=== UPDATING HIDDEN INPUTS ===');
    for (const [type, component] of Object.entries(window.selectedComponents)) {
        const hiddenInput = document.getElementById(`hidden_${type}`);
        if (hiddenInput && component && component.componentId) {
            hiddenInput.value = component.componentId;
            console.log(`Set ${type} to:`, component.componentId);
        } else {
            console.log(`Missing hidden input or component for: ${type}`);
        }
    }

    // Update storage components specifically
    const storageInput = document.getElementById('hidden_storage');
    if (storageInput) {
        if (window.selectedComponents.hdd && window.selectedComponents.hdd.componentId) {
            storageInput.value = window.selectedComponents.hdd.componentId;
        } else if (window.selectedComponents.ssd && window.selectedComponents.ssd.componentId) {
            storageInput.value = window.selectedComponents.ssd.componentId;
        }
        console.log('Storage set to:', storageInput.value);
    }

    // Update total price hidden input
    const totalPriceInput = document.getElementById('hidden_total_price');
    if (totalPriceInput) {
        let totalPrice = 0;
        for (const [type, component] of Object.entries(window.selectedComponents)) {
            if (component && component.price) {
                totalPrice += component.price;
            }
        }
        totalPriceInput.value = totalPrice.toFixed(2);
        console.log('Total price set to:', totalPriceInput.value);
    }

    // VALIDATE IF ALL COMPONENTS ARE SELECTED
    console.log('=== VALIDATING COMPONENTS ===');
    const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case', 'storage'];
    const allComponentsSelected = requiredComponents.every(type => {
        if (type === 'storage') {
            return (window.selectedComponents.hdd && window.selectedComponents.hdd.componentId) || 
                   (window.selectedComponents.ssd && window.selectedComponents.ssd.componentId);
        }
        return window.selectedComponents[type] && window.selectedComponents[type].componentId;
    });

    console.log('All components selected:', allComponentsSelected);

    if (!allComponentsSelected) {
        const missingComponents = [];
        requiredComponents.forEach(type => {
            if (type === 'storage') {
                if (!window.selectedComponents.hdd?.componentId && !window.selectedComponents.ssd?.componentId) {
                    missingComponents.push('Storage (HDD or SSD)');
                }
            } else if (!window.selectedComponents[type]?.componentId) {
                const componentNames = {
                    'gpu': 'GPU',
                    'motherboard': 'Motherboard',
                    'cpu': 'CPU',
                    'psu': 'Power Supply',
                    'ram': 'RAM',
                    'cooler': 'Cooler',
                    'case': 'Case'
                };
                missingComponents.push(componentNames[type]);
            }
        });

        alert(`Please select the following components:\n\n${missingComponents.join('\n')}`);
        return false;
    }

    console.log('=== FORM VALIDATION PASSED - SUBMITTING ===');
    
    // If all validations pass, submit the form programmatically
    console.log('Submitting form...');
    form.submit(); // Use form.submit() instead of this.submit()
}

function setComponentImage(componentData) {
    const targetButton = document.querySelector(`.component-button[data-type="${componentData.type}"]`);
    if (targetButton) {
        const imgTag = targetButton.querySelector('img');
        if (imgTag && componentData.image) {
            imgTag.src = componentData.image;
            imgTag.style.display = 'block';
        }
        // Set the selected ID on the button to indicate the item has been selected
        targetButton.setAttribute('data-selected-id', componentData.id);
    }
}

// Storage toggle function to clear the other storage type
function toggleStorage(selectedType) {
    const otherType = selectedType === 'ssd' ? 'hdd' : 'ssd';
    
    console.log(`Toggle storage: ${selectedType} selected, checking ${otherType}`);
    
    // If the other type was previously selected, clear it
    if (window.selectedComponents[otherType]) {
        // Clear from selectedComponents
        delete window.selectedComponents[otherType];
        
        // Clear the UI for the other storage type
        const otherComponent = document.querySelector(`.component-button[data-type="${otherType}"]`);
        if (otherComponent) {
            // Reset the component button
            otherComponent.innerHTML = `
                <img src="" alt="" style="display: none;">
                <p>${otherType.toUpperCase()}</p>
            `;
            otherComponent.removeAttribute('data-selected-id');
        }
        
        // Clear hidden input
        const otherHiddenInput = document.getElementById(`hidden_${otherType}`);
        if (otherHiddenInput) otherHiddenInput.value = '';
        
        console.log(`Cleared ${otherType} from selection`);
    } else {
        console.log(`No ${otherType} to clear`);
    }
}

// Payment method function (same as build.js)
window.selectPayment = function(method, button) {
    console.log('Selecting payment:', method);
    
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb';
        btn.style.color = '#374151';
        btn.style.border = '2px solid transparent';
    });
    
    button.style.backgroundColor = '#fbbf24';
    button.style.color = '#1f2937';
    button.style.border = '2px solid #f59e0b';
    
    document.getElementById('payment_method').value = method;
    console.log('Payment method set to:', document.getElementById('payment_method').value);
};

document.addEventListener('DOMContentLoaded', () => {
    const cartForm = document.getElementById('cartForm');
    const arrow = document.querySelector('.component-arrow');
    const wrapper = document.querySelector('.catalog-wrapper');
    const componentButtons = document.querySelectorAll('.component-section .component-button');
    const catalogItems = document.querySelectorAll('#catalogSection .build-catalog');

    // CART
    if (cartForm) {
        cartForm.addEventListener('submit', handleFormSubmit);
    }

    arrow.addEventListener('click', () => {
        wrapper.classList.toggle('open');
        arrow.classList.toggle('rotated');
    });

    // FILTER BY TYPE
    componentButtons.forEach(button => {
        button.addEventListener('click', () => {
            const isActive = button.classList.contains('component-active');
            const selectedType = button.getAttribute('data-type');
            
            componentButtons.forEach(c => c.classList.remove('component-active'));
            
            if (isActive) {
                catalogItems.forEach(item => {
                    item.style.display = '';
                });
            } else {
                button.classList.add('component-active');
                catalogItems.forEach(item => {
                    const itemType = item.getAttribute('data-type');
                    item.style.display = (itemType === selectedType) ? '' : 'none';                    
                });    
            }
        });
    });

    // --- PRE-POPULATE SELECTED COMPONENTS FROM SESSION ---
    if (window.selectedComponents && Object.keys(window.selectedComponents).length > 0) {
        catalogItems.forEach(item => {
            const type = item.getAttribute('data-type');
            const id = item.getAttribute('data-id');

            // Check if this component is stored in session
            if (window.selectedComponents[type] && window.selectedComponents[type].componentId == id) {
                const componentData = {
                    id: id,
                    type: type,
                    name: item.getAttribute('data-name'),
                    price: parseFloat(item.getAttribute('data-price')) || 0,
                    image: item.getAttribute('data-image')
                };

                selectComponent(componentData);
                setComponentImage(componentData);

                // Highlight selected
                item.classList.add('selected-component');
            }
        });
    }


    // SINGLE EVENT LISTENER FOR CATALOG ITEMS - COMBINED FUNCTIONALITY
    catalogItems.forEach(item => {
        item.addEventListener('click', function() {
            const componentData = {
                id: this.getAttribute('data-id'),
                type: this.getAttribute('data-type'),
                name: this.getAttribute('data-name'),
                price: parseFloat(this.getAttribute('data-price')) || 0,
                image: this.getAttribute('data-image')
            };
            
            selectComponent(componentData);
            setComponentImage(componentData);
        });
    });

    // VALIDATION
    document.getElementById('validateBuild').addEventListener('click', () => {
        const selections = {};
        document.querySelectorAll('.component-button').forEach(button => {
            const type = button.getAttribute('data-type');
            const selectedId = button.getAttribute('data-selected-id');
            if (selectedId) {
                selections[type + "_id"] = selectedId;
            }
        });

        if (Object.keys(selections).length === 0) {
            alert("⚠️ No components selected.\nPlease choose at least one component before validating.");
            return;
        }

        fetch('/techboxx/build/validate', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(selections)
        })
        .then(res => res.json())
        .then(data => {
            let message = '';

            if (data.errors && data.errors.length > 0) {
                message += '❌ Compatibility Errors:\n' + data.errors.join("\n") + '\n\n';
            }

            if (data.warnings && data.warnings.length > 0) {
                message += '⚠️ Warnings:\n' + data.warnings.join("\n") + '\n\n';
            }

            if ((!data.errors || data.errors.length === 0) && (!data.warnings || data.warnings.length === 0)) {
                message = "✅ No issues found. However, make sure all components are added for a complete compatibility check.";
            }

            alert(message);
        })
        .catch(err => {
            console.error('Validation failed:', err);
            alert('❌ An error occurred while validating the build.');
        });
    });

    document.getElementById('reloadButton').addEventListener('click', function() {
        reloadScene();
    });
});
function updateSession(selectedComponents) {
    fetch('/techboxx/build/update-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ selected_components: selectedComponents })
    })
    .then(res => res.json())
    .then(data => {
        console.log('Session updated:', data);
    })
    .catch(err => {
        console.error('Failed to update session:', err);
    });
}
