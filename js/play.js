/**
 * Game player and save state functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Save state modal
    const saveStateBtn = document.getElementById('save-state-btn');
    const saveStateModal = document.getElementById('save-state-modal');
    const closeSaveModal = document.getElementById('close-save-modal');
    const cancelSave = document.getElementById('cancel-save');
    const saveGameState = document.getElementById('save-game-state');
    
    if (saveStateBtn && saveStateModal) {
        // Open modal
        saveStateBtn.addEventListener('click', function() {
            saveStateModal.classList.add('active');
        });
        
        // Close modal
        function closeSaveStateModal() {
            saveStateModal.classList.remove('active');
        }
        
        if (closeSaveModal) closeSaveModal.addEventListener('click', closeSaveStateModal);
        if (cancelSave) cancelSave.addEventListener('click', closeSaveStateModal);
        
        // Save game state
        if (saveGameState) {
            saveGameState.addEventListener('click', function() {
                // The actual saving is handled by EJS_onSaveState
                // This just triggers the emulator's save state function
                if (typeof EJS_emulator !== 'undefined') {
                    try {
                        EJS_emulator.gameManager.saveState();
                    } catch (e) {
                        alert('Error triggering save state: ' + e.message);
                    }
                } else {
                    alert('Emulator not ready. Please wait and try again.');
                }
            });
        }
    }
    
    // Load save state
    const loadStateButtons = document.querySelectorAll('.load-state');
    
    if (loadStateButtons.length > 0) {
        loadStateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const slot = this.dataset.slot;
                
                if (typeof loadState === 'function') {
                    loadState(slot);
                } else {
                    alert('Load state function not available');
                }
            });
        });
    }
    
    // Delete save state
    const deleteStateButtons = document.querySelectorAll('.delete-state');
    
    if (deleteStateButtons.length > 0) {
        deleteStateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const slot = this.dataset.slot;
                const profileId = document.querySelector('.current-profile').dataset.profileId;
                const gameName = document.querySelector('.page-title').textContent;
                
                if (confirm('Are you sure you want to delete this save state?')) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_state.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            // Reload the page to update the save states list
                            window.location.reload();
                        } else {
                            alert('Error deleting save state: ' + xhr.responseText);
                        }
                    };
                    xhr.send(`profile_id=${profileId}&game=${gameName}&slot=${slot}`);
                }
            });
        });
    }
});