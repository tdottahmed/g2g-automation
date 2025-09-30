 @push('styles')
   <style>
     .scheduler-window {
       transition: all 0.3s ease;
     }

     .scheduler-window:hover {
       background-color: #f8f9fa !important;
     }

     .nav-tabs .nav-link {
       font-weight: 500;
     }

     .form-text {
       font-size: 0.875rem;
     }

     /* Custom styling for AM/PM time picker */
     .flatpickr-time .flatpickr-am-pm {
       padding: 0 6px;
       font-weight: 500;
     }
   </style>
 @endpush

 @push('scripts')
   <script>
     $(document).ready(function() {
       let windowIndex = {{ count($savedWindows) }};
       const maxWindows = 5;

       // Convert 24h time to 12h format for display
       function convertTo12Hour(time24) {
         if (!time24) return '';
         const [hours, minutes] = time24.split(':');
         const hour = parseInt(hours);
         const ampm = hour >= 12 ? 'PM' : 'AM';
         const hour12 = hour % 12 || 12;
         return `${hour12}:${minutes} ${ampm}`;
       }

       // Convert 12h time to 24h format for storage
       function convertTo24Hour(time12) {
         if (!time12) return '';
         const [time, period] = time12.split(' ');
         let [hours, minutes] = time.split(':');
         hours = parseInt(hours);
         if (period === 'PM' && hours < 12) hours += 12;
         if (period === 'AM' && hours === 12) hours = 0;
         return `${hours.toString().padStart(2, '0')}:${minutes}`;
       }

       // Initialize Flatpickr for time inputs with AM/PM
       function initializeTimePickers(container) {
         $(container).find('.time-picker').each(function() {
           const $input = $(this);

           // Initialize Flatpickr with 12-hour format
           $input.flatpickr({
             enableTime: true,
             noCalendar: true,
             dateFormat: "h:i K", // 12-hour format with AM/PM
             altInput: true,
             altFormat: "h:i K", // Display format
             time_24hr: false, // Show AM/PM
             minuteIncrement: 5,
             static: true,
             onReady: function(selectedDates, dateStr, instance) {
               // Ensure AM/PM is visible
               instance.amPM = instance.amPM || instance._.amPM;
             }
           });

           // Convert existing 24h values to 12h format
           if ($input.val() && $input.val().includes(':')) {
             const time24 = $input.val();
             if (!time24.includes('AM') && !time24.includes('PM')) {
               const time12 = convertTo12Hour(time24);
               $input.val(time12);
             }
           }
         });
       }

       // Add new scheduler window
       $('#addSchedulerWindow').on('click', function() {
         if (windowIndex >= maxWindows) {
           showAlert('warning', `{{ __('Maximum') }} ${maxWindows} {{ __('time windows allowed') }}`);
           return;
         }

         const newWindow = `
          <div class="row g-3 align-items-center scheduler-window mb-3 p-3 border rounded bg-light">
            <div class="col-md-5">
              <div class="form-group">
                <label class="form-label">{{ __('Start Time') }}</label>
                <input type="text" name="scheduler_windows[${windowIndex}][start]" 
                       class="form-control time-picker" placeholder="Select start time" required>
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label class="form-label">{{ __('End Time') }}</label>
                <input type="text" name="scheduler_windows[${windowIndex}][end]" 
                       class="form-control time-picker" placeholder="Select end time" required>
              </div>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-outline-danger remove-window w-100 mt-4" 
                      title="{{ __('Remove window') }}">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
        `;

         $('#schedulerWindowsWrapper').append(newWindow);
         initializeTimePickers($('#schedulerWindowsWrapper .scheduler-window').last());
         updateRemoveButtons();
         windowIndex++;
       });

       // Remove scheduler window
       $('#schedulerWindowsWrapper').on('click', '.remove-window', function() {
         if ($('.scheduler-window').length > 1) {
           $(this).closest('.scheduler-window').fadeOut(300, function() {
             $(this).remove();
             updateRemoveButtons();
             reindexWindows();
           });
         }
       });

       // Update remove buttons state
       function updateRemoveButtons() {
         const windows = $('.scheduler-window');
         $('.remove-window').prop('disabled', windows.length <= 1);
       }

       // Reindex windows after removal
       function reindexWindows() {
         windowIndex = 0;
         $('.scheduler-window').each(function(index) {
           $(this).find('input[name^="scheduler_windows"]').each(function() {
             const name = $(this).attr('name');
             const newName = name.replace(/scheduler_windows\[\d+\]/, `scheduler_windows[${index}]`);
             $(this).attr('name', newName);
           });
           windowIndex++;
         });
       }

       // Show alert message
       function showAlert(type, message) {
         // Remove existing alerts
         $('.alert-dismissible').remove();

         const alertClass = type === 'error' ? 'danger' : type;
         const icon = type === 'error' ? 'ri-error-warning-line' : 'ri-information-line';

         const alert = `
          <div class="alert alert-${alertClass} alert-dismissible fade show mb-4" role="alert">
            <i class="${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        `;

         $('#setupForm').prepend(alert);
       }

       // Initialize time pickers on page load
       initializeTimePickers($('#schedulerWindowsWrapper'));
       updateRemoveButtons();
     });
   </script>
 @endpush
