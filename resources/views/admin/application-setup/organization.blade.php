 {{-- Organization Info Tab --}}
 <div class="tab-pane fade" id="org" role="tabpanel" aria-labelledby="org-tab">
   <x-data-entry.input type="text" name="app_name" label="Organization Name" placeholder="App Name"
                       value="{{ $applicationSetup->where('type', 'app_name')->first()->value ?? '' }}" required />
   <x-data-entry.input type="email" name="app_email" label="Organization Email" placeholder="App Email"
                       value="{{ $applicationSetup->where('type', 'app_email')->first()->value ?? '' }}" required />
   <x-data-entry.input type="tel" name="app_phone" label="Organization Phone" placeholder="App Phone"
                       value="{{ $applicationSetup->where('type', 'app_phone')->first()->value ?? '' }}" required />
   <x-data-entry.text-area name="app_address" label="Organization Address" placeholder="App Address" rows="3"
                           value="{{ $applicationSetup->where('type', 'app_address')->first()->value ?? '' }}">
   </x-data-entry.text-area>
