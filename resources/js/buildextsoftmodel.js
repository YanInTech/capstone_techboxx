// buildextsoft.js - Display sessioned components in 3D viewer
import * as THREE from 'https://esm.sh/three@0.155.0';
import { OrbitControls } from 'https://esm.sh/three@0.155.0/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'https://esm.sh/three@0.155.0/examples/jsm/loaders/GLTFLoader.js';

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

// Initialize the 3D viewer
init();
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

    // Initialize from session data after a short delay to ensure DOM is ready
    setTimeout(() => {
        initializeFromSession();
    }, 500);
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

// Main function to load all sessioned components
async function initializeFromSession() {
    console.log('Initializing from session data:', window.selectedComponents);
    
    if (!window.selectedComponents) {
        console.log('No session data found');
        return;
    }
    
    try {
        // Load components in dependency order
        if (window.selectedComponents.case && window.selectedComponents.case.modelUrl) {
            console.log('Loading case from session:', window.selectedComponents.case.modelUrl);
            await spawnCase(new THREE.Vector3(0, 0, 0), window.selectedComponents.case.modelUrl);
        }
        
        // Load motherboard if selected (after case)
        if (window.selectedComponents.motherboard && window.selectedComponents.motherboard.modelUrl && caseModel) {
            console.log('Loading motherboard from session:', window.selectedComponents.motherboard.modelUrl);
            await spawnMoboAtSlot(window.selectedComponents.motherboard.modelUrl);
        }
        
        // Load other components in dependency order
        if (window.selectedComponents.cpu && window.selectedComponents.cpu.modelUrl && moboModel) {
            console.log('Loading CPU from session:', window.selectedComponents.cpu.modelUrl);
            await spawnCpuAtSlot(window.selectedComponents.cpu.modelUrl);
        }
        
        if (window.selectedComponents.psu && window.selectedComponents.psu.modelUrl && caseModel) {
            console.log('Loading PSU from session:', window.selectedComponents.psu.modelUrl);
            await spawnPsuAtSlot(window.selectedComponents.psu.modelUrl);
        }
        
        if (window.selectedComponents.cooler && window.selectedComponents.cooler.modelUrl && moboModel) {
            console.log('Loading cooler from session:', window.selectedComponents.cooler.modelUrl);
            await spawnCoolerAtSlot(window.selectedComponents.cooler.modelUrl);
        }
        
        if (window.selectedComponents.ssd && window.selectedComponents.ssd.modelUrl && moboModel) {
            console.log('Loading SSD from session:', window.selectedComponents.ssd.modelUrl);
            await spawnSsdAtSlot(window.selectedComponents.ssd.modelUrl);
        }
        
        if (window.selectedComponents.gpu && window.selectedComponents.gpu.modelUrl && moboModel) {
            console.log('Loading GPU from session:', window.selectedComponents.gpu.modelUrl);
            await spawnGpuAtSlot(window.selectedComponents.gpu.modelUrl);
        }
        
        if (window.selectedComponents.ram && window.selectedComponents.ram.modelUrl && moboModel) {
            console.log('Loading RAM from session:', window.selectedComponents.ram.modelUrl);
            await spawnRamAtSlot(window.selectedComponents.ram.modelUrl);
        }
        
        console.log('All session components loaded successfully');
        
    } catch (error) {
        console.error('Error loading session components:', error);
    }
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

async function spawnMoboAtSlot(modelUrl) {
    if (!moboSlotPosition) {
        console.error('Motherboard slot position unknown');
        return;
    }
    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }
    try {
        const model = await loadGLTFModel(modelUrl);
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

async function spawnCpuAtSlot(modelUrl) {
    if (!cpuSlotPosition) {
        console.error('CPU slot position unknown');
        return;
    }
    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }
    try {
        const model = await loadGLTFModel(modelUrl);
        model.position.copy(cpuSlotPosition);
        scene.add(model);
        cpuModel = model;
    } catch (err) {
        console.error('Failed to load CPU model', err);
    }
}

async function spawnPsuAtSlot(modelUrl) {
    if (!psuSlotPosition) {
        console.error('PSU slot position unknown');
        return;
    }
    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }
    try {
        const model = await loadGLTFModel(modelUrl);
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

async function spawnCoolerAtSlot(modelUrl) {
    if (!coolerSlotPosition) {
        console.error('Cooler slot position unknown');
        return;
    }
    if (coolerModel) {
        scene.remove(coolerModel);
        coolerModel = null;
    }
    try {
        const model = await loadGLTFModel(modelUrl);
        model.position.copy(coolerSlotPosition);
        scene.add(model);
        coolerModel = model;
    } catch (err) {
        console.error('Failed to load cooler model', err);
    }
}

async function spawnSsdAtSlot(modelUrl) {
    if (!ssdSlotPosition) {
        console.error('SSD slot position unknown');
        return;
    }
    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }
    try {
        const model = await loadGLTFModel(modelUrl);
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

async function spawnGpuAtSlot(modelUrl) {
    if (!gpuSlotPosition) {
        console.error('GPU slot position unknown');
        return;
    }
    if (gpuModel) {
        scene.remove(gpuModel);
        gpuModel = null;
    }
    try {
        const model = await loadGLTFModel(modelUrl);
        model.position.copy(gpuSlotPosition);
        scene.add(model);
        gpuModel = model;
    } catch (err) {
        console.error('Failed to load GPU model', err);
    }
}

async function spawnRamAtSlot(modelUrl) {
    if (!moboModel) {
        console.error('No motherboard found for RAM installation');
        return;
    }

    // Remove existing RAM models if any
    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }
    
    try {
        const baseRamModel = await loadGLTFModel(modelUrl);
        
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
            console.warn('No RAM slots found on the motherboard');
            return;
        }
        
        console.log(`Spawned ${ramModels.length} RAM sticks`);
        
    } catch (err) {
        console.error('Failed to load RAM model', err);
    }
}

function reloadScene() {
    // Remove all models from scene
    if (caseModel) {
        scene.remove(caseModel);
        caseModel = null;
    }

    if (moboModel) {
        scene.remove(moboModel);
        moboModel = null;
    }

    if (cpuModel) {
        scene.remove(cpuModel);
        cpuModel = null;
    }

    if (psuModel) {
        scene.remove(psuModel);
        psuModel = null;
    }

    if (coolerModel) {
        scene.remove(coolerModel);
        coolerModel = null;
    }

    if (ssdModel) {
        scene.remove(ssdModel);
        ssdModel = null;
    }

    if (gpuModel) {
        scene.remove(gpuModel);
        gpuModel = null;
    }

    if (ramModels.length > 0) {
        ramModels.forEach(ram => scene.remove(ram));
        ramModels = [];
    }

    // Reset slot positions
    moboSlotPosition = null;
    cpuSlotPosition = null;
    psuSlotPosition = null;
    coolerSlotPosition = null;
    ssdSlotPosition = null;
    gpuSlotPosition = null;

    // Reset camera controls
    controls.target.set(0, 0, 0);
    controls.update();

    console.log('Scene has been reloaded.');
}

// Add reload button event listener
document.addEventListener('DOMContentLoaded', function() {
    const reloadButton = document.getElementById('reloadButton');
    if (reloadButton) {
        reloadButton.addEventListener('click', function() {
            reloadScene();
            // Reload session components after a short delay
            setTimeout(() => {
                initializeFromSession();
            }, 100);
        });
    }
});

// Export functions for potential use elsewhere
window.buildViewer = {
    reloadScene,
    initializeFromSession
};