<x-dashboardlayout>
    <h2>Software Dashboard</h2>

    <div class="header-container" x-data="{ showAddModal: false }">
        <button class="modal-button" @click="showAddModal = true">
            Add Software
        </button>

        <div>
            <form action=" {{ route('staff.inventory.search') }}" method="GET">
                <input 
                    type="text"
                    name="search"
                    placeholder="Search software"
                    value="{{ request('search') }}"
                    class="search-bar"
                >
                <button type='submit'>
                    <x-icons.search class="search-icon"/>
                </button>
            </form>
        </div>
    
        {{-- ADD MODAL --}}
        <div x-show="showAddModal" x-cloak x-transition class="modal">
            <div class="add-software" @click.away="showAddModal = false">
                <div class="relative !m-0">
                    <h2 class="text-center w-[100%]">
                        ADD SOFTWARE
                        <x-icons.close class="close" @click="showAddModal = false"/>    
                    </h2>
                </div>
                <form class="software-form" 
                method="POST" action="{{ route('staff.software-details.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="software-details">
                        <div class="software-info">
                            <div>
                                <div class="software-input">
                                    <label for="">Software Name</label>
                                    <input placeholder="Enter software name" required type="text" name="name">
                                </div>

                                <div class="software-input">
                                    <label for="">Software Icon</label>
                                    <input type="file" name="icon" accept="image/*">
                                </div>
                            </div>
                            <div>
                                <div class="software-input">
                                    <label for="build_category_id">Category</label>
                                    <select name="build_category_id" id="build_category_id" class="pt-0 pb-0 pl-1">
                                        <option disabled selected hidden value="">Select build category</option>   
                                        @foreach ($buildCategories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>    
                            </div>
                        </div>
                        <div>
                            <p>Minimum System Requirements</p>

                            <div class="software-input">
                                <label for="">Operating System</label>
                                <input placeholder="Enter software name" type="text" name="os_min">
                            </div>   
                            
                            <div class="software-input">
                                <label for="">CPU</label>
                                <input placeholder="Enter software name" type="text" name="cpu_min">
                            </div>   

                            <div class="software-input">
                                <label for="">GPU</label>
                                <input placeholder="Enter software name" type="text" name="gpu_min">
                            </div>   

                            <div class="software-input">
                                <label for="">RAM</label>
                                <input placeholder="00 GB" name="ram_min" id="ram_min" type="number" step="4" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>   

                            <div class="software-input">
                                <label for="">Storage</label>
                                <input placeholder="00 GB" name="storage_min" id="storage_min" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>

                            <p>Recommended System Requirements</p>
                            
                            <div class="software-input">
                                <label for="">CPU</label>
                                <input placeholder="Enter software name" type="text" name="cpu_reco">
                            </div>   

                            <div class="software-input">
                                <label for="">GPU</label>
                                <input placeholder="Enter software name" type="text" name="gpu_reco">
                            </div>   

                            <div class="software-input">
                                <label for="">RAM</label>
                                <input placeholder="00 GB" name="ram_reco" id="ram_reco" type="number" step="4" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>   

                            <div class="software-input">
                                <label for="">Storage</label>
                                <input placeholder="00 GB" name="storage_reco" id="storage_reco" type="number" onkeydown="return !['e','E','+','-'].includes(event.key)">
                            </div>
                        </div>
                    </div>
                    <div>
                        <button>Add Software</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <section class="section-style !pl-0 !h-[65vh]">
        <div class="h-[55vh]"
        x-data="{ viewModal: false, selectedSoftware:{} }"> 
            <table class="table mb-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($softwares as $software)
                        <tr class="hover:opacity-50" 
                        @click="viewModal = true; selectedSoftware = {{ $software->toJson() }}">
                            <td>{{ $software->name }}</td>
                            <td>{{ $software->buildCategory->name }}</td>
                            <td>Ambot</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>   
            
            {{-- VIEW MODAL --}}
            <div x-show="viewModal" x-cloak x-transition class="modal overflow-y-scroll p-5">
                <div class="add-software" 
                @click.away="viewModal = false">
                    <div class="relative !m-0">
                        <h2 class="text-center w-[100%]">
                            Software Details
                            <x-icons.close class="close" 
                            @click="viewModal = false"/>    
                        </h2>
                    </div>
                    <pre x-text="JSON.stringify(selectedSoftware, null, 2)"></pre>

                    <div class="software-name">
                        <div>
                            <img :src="'/storage/' + selectedSoftware.icon" alt="Software Icon" alt="Software Icon">
                        </div>
                        <div class="flex gap-3">
                            <p x-text="selectedSoftware.name"></p>
                        </div>
                    </div>
                    <div>
                        
                    </div>
                </div>
            </div>
        </div>
        {{ $softwares->links() }}
    </section>

</x-dashboardlayout>
