/**
 * Handles ROM and BIOS file upload functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // ROM upload functionality
    const romUploadBox = document.getElementById('rom-upload-box');
    const romFiles = document.getElementById('rom-files');
    const romUploadForm = document.getElementById('rom-upload-form');
    
    if (romUploadBox && romFiles) {
        // Trigger file input when clicking on the upload box
        romUploadBox.addEventListener('click', function() {
            romFiles.click();
        });
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            romUploadBox.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Highlight drop area when item is dragged over
        ['dragenter', 'dragover'].forEach(eventName => {
            romUploadBox.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            romUploadBox.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            romUploadBox.classList.add('highlight');
        }
        
        function unhighlight() {
            romUploadBox.classList.remove('highlight');
        }
        
        // Handle dropped files
        romUploadBox.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            romFiles.files = files;
            
            // Submit the form to upload the files
            romUploadForm.submit();
        }
        
        // Auto-submit when files are selected
        romFiles.addEventListener('change', function() {
            if (romFiles.files.length > 0) {
                romUploadForm.submit();
            }
        });
    }
    
    // BIOS file input styling
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
});