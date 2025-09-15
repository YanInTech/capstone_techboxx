import * as THREE from 'https://esm.sh/three@0.155.0';
import { OrbitControls } from 'https://esm.sh/three@0.155.0/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'https://esm.sh/three@0.155.0/examples/jsm/loaders/GLTFLoader.js';
import interact from 'https://esm.sh/interactjs@1.10.17';


let scene, camera, renderer, controls;
let caseModel = null;
let gpuModel = null;
let gpuSlotPosition = null;
let selectedCaseModelUrl = null;
let selectedGpuModelUrl = null;

function setupCatalogClickHandlers() {
  document.querySelectorAll('.catalog-item').forEach(item => {
    item.addEventListener('click', async () => {
      const modelUrl = item.getAttribute('data-model');
      const type = item.getAttribute('data-type');

      if (!modelUrl) {
        alert('Model not available for this component.');
        return;
      }

      if (type === 'case') {
        selectedCaseModelUrl = modelUrl; // Save selected model
        console.log('Selected model URL for dragging:', selectedCaseModelUrl);

        // SPAWNS CASE IN THE SCENE
        spawnCase(new THREE.Vector3(0,0,0), selectedCaseModelUrl);
      } else if (type === 'motherboard') {
        selectedGpuModelUrl = modelUrl;
        console.log('Selected model URL for dragging:', selectedGpuModelUrl);
      }

    })
  })
}

init();
setupCatalogClickHandlers();
animate();

function init() {
  scene = new THREE.Scene();
  // scene.background = new THREE.Color('white');

  const container = document.getElementById('canvas-container');
  const width = container.clientWidth;
  const height = container.clientHeight;

  camera = new THREE.PerspectiveCamera(30, width/height, 0.1, 1000);
  camera.position.set(20, 0, 0);

  renderer = new THREE.WebGLRenderer({alpha: true});
  renderer.setSize(width, height);
  container.appendChild(renderer.domElement);

  controls = new OrbitControls(camera, renderer.domElement);

  const ambientLight = new THREE.AmbientLight(0xffffff, 0.5); 
  scene.add(ambientLight);

  const directionalLight = new THREE.DirectionalLight(0xffffff, 7.0); // LIGHT SETTINGS HITTING THE MODEL
  directionalLight.position.set(2, 10, 5);
  scene.add(directionalLight);

  // RESIZE LISTENER USING CONTAINER SIZE
  window.addEventListener('resize', () => {
    const width = container.clientWidth;
    const height = container.clientHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
  });

  setupDragAndDrop();
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


function setupDragAndDrop() {
  let draggingId = null;
  let draggingEl = null;
  let originalSlotMaterial = null;
  let gpumarker = null;
  let casemarker = null;
  let wasDroppedSuccessfully = false; // Track if the drop was successful

  interact('.draggable').draggable({
    listeners: {
      start(event) {
        draggingId = event.target.id;
        draggingEl = event.target;
        draggingEl.style.opacity = '0.5';

        // Change the cursor to grabbing when drag starts
        document.body.style.cursor = 'grabbing'; 

        // If dragging GPU, highlight the GPU slot
        if (draggingId === 'motherboard' && caseModel) {
          const gpuSlot = caseModel.getObjectByName('Slot_GPU');
          if (gpuSlot) {
            // Save the original material and change to the highlighted one
            originalSlotMaterial = gpuSlot.material; // Store the original material
            gpuSlot.material = new THREE.MeshStandardMaterial({
              color: 0x00ff00,        // Bright green to show it's active
              emissive: 0x003300,     // A little glowing effect
              transparent: true,
              opacity: 0.4,           // Semi-transparent
            });

            // Optionally, create a visible marker at the slot position
            gpumarker = new THREE.Mesh(
              new THREE.BoxGeometry(2, 2, 0.1),
              new THREE.MeshStandardMaterial({
                color: 0x00ff00,
                emissive: 0x003300,
                transparent: true,
                opacity: 0.4,
              })
            );

            
            // Rotate 45 degrees on the X axis
            gpumarker.rotation.x = 0; // No rotation on the Y axis
            gpumarker.rotation.y = Math.PI / 2;  // 90 degrees        
            gpumarker.rotation.z = 0;          // No rotation on the Z axis
            gpumarker.position.set(gpuSlotPosition.x, gpuSlotPosition.y + -1, gpuSlotPosition.z + -1.4); // Position the gpumarker
            scene.add(gpumarker);
          }
        }
      },
      move(event) {
        // Optional: Add extra visual feedback during dragging if necessary
      },
      async end(event) {
        draggingEl.style.opacity = '1';

        // Reset the cursor to grab when dragging ends
        document.body.style.cursor = 'grab'; 

        const dropPos = getCanvasDropPosition(event.clientX, event.clientY);

        // If the drop position is valid, spawn the models
        if (dropPos && draggingId === 'case' && !caseModel) {
          const modelUrl = selectedCaseModelUrl;

          if (modelUrl) {
            await spawnCase(dropPos, selectedCaseModelUrl);
            wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
          } else {
            console.warn('No model Url provided for case');
          }
        } else if (dropPos && draggingId === 'motherboard' && caseModel) {
          spawnGPUAtSlot();
          wasDroppedSuccessfully = true;  // Mark that the case was dropped successfully
        }

        // If drop was unsuccessful, remove the dragged model (if any)
        if (!wasDroppedSuccessfully) {
          if (draggingId === 'case' && caseModel) {
            scene.remove(caseModel);  // Remove the case if it was dropped unsuccessfully
            caseModel = null;
          }
          if (draggingId === 'motherboard' && gpuModel) {
            scene.remove(gpuModel);  // Remove the GPU if it was dropped unsuccessfully
            gpuModel = null;
          }

          // Reset the marker to the center when the drop fails
          if (casemarker) {
            casemarker.position.set(0, 0, 0); // Reset position to center
          }
        }

        // Revert the slot highlight after dragging ends
        if (casemarker) {
          scene.remove(casemarker);
          casemarker = null;
        }
        if (gpumarker) {
          scene.remove(gpumarker);
          gpumarker = null;
        }

        if (originalSlotMaterial) {
          const gpuSlot = caseModel.getObjectByName('Slot_GPU');
          if (gpuSlot) {
            gpuSlot.material = originalSlotMaterial; // Restore the original material
          }
        }

        // Reset dragging state
        draggingId = null;
        draggingEl = null;
        originalSlotMaterial = null;
        wasDroppedSuccessfully = false;
      }
    }
  });
}

function getCanvasDropPosition(clientX, clientY) {
  const rect = renderer.domElement.getBoundingClientRect();

  if (
    clientX < rect.left || clientX > rect.right ||
    clientY < rect.top || clientY > rect.bottom
  ) {
    return null;
  }

  const x = ((clientX - rect.left) / rect.width) * 2 - 1;
  const y = - ((clientY - rect.top) / rect.height) * 2 + 1;

  const mouseVector = new THREE.Vector2(x, y);
  const raycaster = new THREE.Raycaster();

  raycaster.setFromCamera(mouseVector, camera);

  const planeZ = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
  const intersectionPoint = new THREE.Vector3();

  raycaster.ray.intersectPlane(planeZ, intersectionPoint);

  return intersectionPoint;
}

async function spawnCase(position, modelUrl) {
  if (caseModel) return; // only one case at a time

  try {
    const model = await loadGLTFModel(modelUrl);
    model.position.copy(position);
    model.scale.setScalar(1); // Shrinks uniformly
    scene.add(model);
    caseModel = model;

    // CONTROL THE ROTATION || FOCUS THE ROTATION ON THE MODEL
    controls.target.copy(model.position);
    controls.update();

    const gpuSlot = model.getObjectByName('Slot_GPU');
    if (gpuSlot) {
      gpuSlotPosition = new THREE.Vector3();
      gpuSlot.getWorldPosition(gpuSlotPosition);
      console.log('GPU slot position:', gpuSlotPosition);
    } else {
      gpuSlotPosition = new THREE.Vector3(0, 0, 0);
      console.warn('GPU slot not found in case model');
    }
  } catch (error) {
    console.error('Failed to load case model', error);
  }
}

async function spawnGPUAtSlot() {
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
  } catch (error) {
    console.error('Failed to load GPU model', error);
  }
}
