   <x-layouts.admin.master>
     <x-data-display.card>
       <x-slot name="header">
         <div class="d-flex justify-content-between align-items-center">
           <h5 class="card-title">{{ __('Create User Account') }}</h5>
           <x-action.link href="{{ route('user-accounts.index') }}"
                          icon="ri-add-line">{{ __('User Account List') }}</x-action.link>
         </div>
       </x-slot>
       <x-data-entry.form action="{{ route('user-accounts.store') }}">
         <x-data-entry.input type="text" name="email" label="Email" placeholder="Email" required />
         <x-data-entry.input type="text" name="owner_name" label="Owner Name" placeholder="Owner Name" required />
         <x-data-entry.input type="password" name="password" label="Password" placeholder="Password" required />
       </x-data-entry.form>
     </x-data-display.card>
   </x-layouts.admin.master>
