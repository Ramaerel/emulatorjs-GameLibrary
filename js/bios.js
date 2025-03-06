/**
 * BIOS management functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // File input styling
    const biosFileInput = document.getElementById('bios-file');
    const selectedFileName = document.querySelector('.selected-file-name');
    
    if (biosFileInput && selectedFileName) {
        biosFileInput.addEventListener('change', function() {
            if (biosFileInput.files.length > 0) {
                selectedFileName.textContent = biosFileInput.files[0].name;
            } else {
                selectedFileName.textContent = 'No file selected';
            }
        });
    }
    
    // Console type dropdown
    const consoleTypeSelect = document.getElementById('console-type');
    
    if (consoleTypeSelect) {
        consoleTypeSelect.addEventListener('change', function() {
            // Update any UI based on console selection if needed
            const selectedConsole = consoleTypeSelect.value;
            
            // You can add functionality here to show specific information 
            // based on the selected console type if desired
        });
    }
    
    // BIOS file deletion confirmation
    const biosDeleteButtons = document.querySelectorAll('.bios-delete-btn');
    
    if (biosDeleteButtons.length > 0) {
        biosDeleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this BIOS file?')) {
                    e.preventDefault();
                }
            });
        });
    }
});