 <script>
     function permissionsData() {
         return {
             // Toggle all permissions
             toggleAllPermissions(event) {
                 let allPermissions = document.querySelectorAll('[x-ref=permissionCheckbox]');
                 allPermissions.forEach((checkbox) => {
                     checkbox.checked = event.target.checked;
                 });
             },

             // Toggle permissions in a specific group
             toggleGroup(group, event) {
                 let groupPermissions = document.querySelectorAll(`[data-group="${group}"]`);
                 groupPermissions.forEach((checkbox) => {
                     checkbox.checked = event.target.checked;
                 });
             }
         }
     }
 </script>
