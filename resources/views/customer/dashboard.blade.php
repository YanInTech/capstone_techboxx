<x-dashboardlayout>
<h1>Dashboard</h1>

{{-- Customer Profile --}}
<section class="customer-profile" x-data="{ user: @js(Auth::user()), showEditModal: false }">
    <div class="profile">
        <div>
            <x-icons.profile />
        </div>
        <div class="profile-details">
            <div>
                <span>Name</span>
                <span>:</span>
                <span>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
            </div>
            <div >
                <span>Email</span>
                <span>:</span>
                <span>{{ Auth::user()->email }}</span>
                <span class="email-row">
                    @if (! Auth::user()->hasVerifiedEmail())
                        <form action="{{ route('verification.send') }}" method="POST">
                            @csrf
                            <button type="submit">
                                <u>(Verifiy Email)</u>
                            </button>
                        </form>
                    @endif    
                </span>
            </div>
            <div>
                <span>Contact</span>
                <span>:</span>
                <span>{{ Auth::user()->phone_number }}</span>
            </div>
            <div>
                {{-- change status to Verified --}}
                <span>Status</span>
                <span>:</span>
                <span>{{ Auth::user()->status }}</span> 
            </div>
        </div>
    </div>

    <div>
        <button @click="showEditModal = true">
            <x-icons.edit />
        </button>
    </div>
    
    <hr>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" x-cloak x-transition class="modal">
        <div class="modal-form" @click.away="showEditModal = false">
            <h2 class="text-center">Edit User</h2>

            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name"  x-model="user.first_name">
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" x-model="user.last_name">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" x-model="user.email" readonly>
                </div>
                <div>
                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" x-model="user.phone_number">
                </div>

                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</section>

{{-- Builds Table --}}
<section class="section-style !pl-0 !h-[50vh]">
    <div class="builds-table">
        <x-icons.build />
        <p>Builds</p>
    </div>

    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>Build Name/Name</th>
                    <th>Details</th>
                    <th>Date Created</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="table-body">
        <table class="table">
            <tbody>
                @foreach ($userBuilds as $userBuild)
                    <tr>
                        <td>{{ $userBuild->build_name }}</td>
                        <td class="text-center !pr-[2.5%]">View</td>
                        <td class="text-center !pr-[1.5%]">{{ $userBuild->created_at ? $userBuild->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center">₱ {{ $userBuild->total_price }}</td>
                        <td class="text-center !pr-[.6%]">{{ $userBuild->status }}</td>
                        <td class="text-center">Order</td>
                    </tr>
                @endforeach

                
            </tbody>
        </table>
    </div>
</section>

</x-dashboardlayout>