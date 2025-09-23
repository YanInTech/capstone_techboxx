<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Techboxx</title>

    @vite([
        'resources\css\app.css',
        'resources\css\landingpage\header.css',
        'resources\css\build.css',
        'resources\js\app.js',
        'resources\js\component-viewer.js',
        'resources\js\build.js',
        ])
    
</head>
<body class="flex">

    @if (session('message'))
        <x-message :type="session('type')">
            {{ session('message') }}
        </x-message>
    @endif

    <x-buildheader :name="Auth::user()?->first_name" />
    
    <div id="loadingSpinner" class="hidden">
        <div class="spinner-message">
            <pre id="loadingText">Loading...</pre>
        </div>
    </div>

    <main class="main-content flex justify-evenly h-[91vh] gap-1">
        

        <section class="preview-section header">
            <div id="sidebar">
                <h3 class="mb-3 text-center">BUILD COMPONENTS</h3>
                <div id="components">
                    <div id="gpu" class="draggable"><p>GPU</p></div>
                    <div id="motherboard" class="draggable"><p>Motherboard</p></div>
                    <div id="cpu" class="draggable"><p>CPU</p></div>
                    <div id="hdd" class="draggable"><p>HDD</p></div>
                    <div id="ssd" class="draggable"><p>SDD</p></div>
                    <div id="psu" class="draggable"><p>PSU</p></div>    
                    <div id="ram" class="draggable"><p>RAM</p></div>    
                    <div id="cooler" class="draggable"><p>Cooler</p></div>
                </div>
            </div>
            <div id="canvas-container"></div>
            <form action="{{ route('techboxx.build.extend') }}">
                <button>
                    <x-icons.expand />
                </button>
            </form>
            <button id="reloadButton">
                <x-icons.reload />
            </button>

        </section>
        <section class="buttons-section">
            <div data-group="buildType">
                <button id="customBuildBtn"><p>Custom Build</p></button>
                <button id="generateBuildBtn"><p>Generate Build</p></button>
            </div>
            <div data-group="cpuBrand">
                <button id="amdBtn"><p>AMD</p></button>
                <button id="intelBtn"><p>Intel</p></button>
            </div>
            <div data-group="useCase">
                <button id="generalUseBtn"><p>General Use</p></button>
                <button id="gamingBtn"><p>Gaming</p></button>
                <button id="graphicsIntensiveBtn"><p>Graphics Intensive</p></button>
            </div>
            <div class="budget-section">
                <button disabled><p>Budget</p></button>
                <input name="budget" id="budget" type="number" step="0.01" placeholder="Enter budget" onkeydown="return !['e','E','+','-'].includes(event.key)">
            </div>
            <div class="generate-button">
                <button id="generateBtn"><p>Generate Build</p></button>
            </div>
            

            {{-- THIS SECTION WILL SHOW WHEN GENERATE BUILD IS CLICKED --}}
            <div class="generate-build hidden" id="buildSection">
                <button data-type=""><p>Chipset <span class="selected-name">None</span></p></button>
                <button data-type="case"><p>Case <span class="selected-name">None</span></p></button>
                <button data-type="gpu"><p>GPU <span class="selected-name">None</span></p></button>
                <button data-type="motherboard"><p>Motherboard <span class="selected-name">None</span></p></button>
                <button data-type="cpu"><p>CPU <span class="selected-name">None</span></p></button>
                <button data-type="hdd"><p>HDD <span class="selected-name">None</span></p></button>
                <button data-type="ssd"><p>SSD <span class="selected-name">None</span></p></button>
                <button data-type="psu"><p>PSU <span class="selected-name">None</span></p></button>
                <button data-type="ram"><p>RAM <span class="selected-name">None</span></p></button>
                <button data-type="cooler"><p>Cooler <span class="selected-name">None</span></p></button>
            </div>
        </section>   
        <section class="catalog-section">
            <div class="catalog-button">
                <button id="componentsTab">Components</button>
                <button id="summaryTab">Summary</button>
            </div>

            {{-- COMPONENTS --}}
            <div id="componentsSection">
                <div class="catalog-header">
                    <div class="catalog-title">
                        <p id="catalogTitle">All Components</p>
                        <x-icons.info title="This is information about the processor"/>
                    </div> 
                    <div class="catalog-filter">
                        <button><p>filter</p></button>
                        <button><p>filter</p></button>    
                    </div>
                </div>
                <div class="catalog-list"> 
                    @foreach ($components as $component)
                        <div class="catalog-item" 
                             data-type="{{ strtolower($component->component_type) }}"
                             data-name="{{ ucfirst($component->brand )}} {{ ucfirst($component->model )}}"
                             data-category="{{ $component->buildCategory->name}}"
                             data-price="{{ $component->price }}"
                             data-image="{{ asset('storage/' . $component->image) }}"
                             data-model="{{ isset($component->model_3d) ? asset('storage/' . $component->model_3d) : '' }}"
                             data-id="{{ $component->id }}">
                            <div class="catalog-image">
                                @if (!empty($component->image))
                                    <img src="{{ asset('storage/' . $component->image )}}" alt="Product image">
                                @else
                                    <p>No image uploaded.</p>
                                @endif
                            </div>
                            <div class="catalog-specs">
                                <p>{{ ucfirst($component->component_type) }}</p>
                                <p><strong>{{ $component->brand}} {{ $component->model }}</strong></p>
                                <p>₱{{ number_format($component->price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>    
            </div>
            
            {{-- SUMMARY --}}
            <div class="summary-section hidden" id="summarySection">
                <div class="summary-date">
                    <p>Build Date: <span id="buildDate">01/01/2025 </span></p>
                </div>
                <div class="summary-table">
                    <table>
                        <thead>
                            <tr>
                                <th><p>Components</p></th>
                                <th><p>Quantity</p></th>
                                <th><p>Price</p></th>
                            </tr>
                        </thead>

                        <tbody id="summaryTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="build-details">
                </div>
            </div>
        </section>    
    </main>
</body>
</html>