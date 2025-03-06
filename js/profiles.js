/**
 * Profile management functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown toggle
    const profileToggle = document.getElementById('profile-toggle');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && e.target !== profileToggle) {
                profileDropdown.classList.remove('active');
            }
        });
        
        // Profile selection
        const profileItems = document.querySelectorAll('.profile-item');
        profileItems.forEach(item => {
            item.addEventListener('click', function() {
                const profileId = this.dataset.profileId;
                
                // Make AJAX request to switch profile
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'profile_action.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Reload the page to reflect the new profile
                        window.location.reload();
                    } else {
                        console.error('Error switching profile');
                    }
                };
                xhr.send(`action=switch&profile_id=${profileId}`);
            });
        });
    }
    
    // Profile modal handling
    const addProfileBtn = document.getElementById('add-profile-btn');
    const profileModal = document.getElementById('profile-modal');
    const closeProfileModal = document.getElementById('close-profile-modal');
    const cancelProfile = document.getElementById('cancel-profile');
    const saveProfile = document.getElementById('save-profile');
    const profileForm = document.getElementById('profile-form');
    const avatarOptions = document.querySelectorAll('.avatar-option');
    const selectedAvatarInput = document.getElementById('selected-avatar');
    
    if (addProfileBtn && profileModal) {
        // Open modal
        addProfileBtn.addEventListener('click', function() {
            profileModal.classList.add('active');
            profileDropdown.classList.remove('active');
        });
        
        // Close modal
        function closeModal() {
            profileModal.classList.remove('active');
            profileForm.reset();
            // Reset selected avatar
            avatarOptions.forEach(option => {
                option.classList.remove('selected');
            });
            avatarOptions[0].classList.add('selected');
            selectedAvatarInput.value = avatarOptions[0].dataset.avatar;
        }
        
        if (closeProfileModal) closeProfileModal.addEventListener('click', closeModal);
        if (cancelProfile) cancelProfile.addEventListener('click', closeModal);
        
        // Avatar selection
        avatarOptions.forEach(option => {
            option.addEventListener('click', function() {
                avatarOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectedAvatarInput.value = this.dataset.avatar;
            });
        });
        
        // Save new profile
        if (saveProfile) {
            saveProfile.addEventListener('click', function() {
                const profileName = document.getElementById('profile-name');
                
                if (!profileName.value.trim()) {
                    alert('Please enter a profile name');
                    profileName.focus();
                    return;
                }
                
                // Submit the form to create a new profile
                const formData = new FormData(profileForm);
                formData.append('action', 'create');
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'profile_action.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Reload the page to show the new profile
                        window.location.reload();
                    } else {
                        alert('Error creating profile: ' + xhr.responseText);
                    }
                };
                xhr.send(formData);
            });
        }
    }
});

// Add to the bottom of js/profiles.js or create a new file

/**
 * Profile editing functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Profile edit functionality
    const editProfileBtns = document.querySelectorAll('.edit-profile-btn');
    const editProfileModal = document.getElementById('edit-profile-modal');
    const closeEditProfileModal = document.getElementById('close-edit-profile-modal');
    const cancelEditProfile = document.getElementById('cancel-edit-profile');
    const updateProfile = document.getElementById('update-profile');
    const editProfileForm = document.getElementById('edit-profile-form');
    const editProfileId = document.getElementById('edit-profile-id');
    const editProfileName = document.getElementById('edit-profile-name');
    const editAvatarSelector = document.getElementById('edit-avatar-selector');
    const editSelectedAvatar = document.getElementById('edit-selected-avatar');
    
    if (editProfileBtns.length > 0 && editProfileModal) {
        // Open modal when Edit button is clicked
        editProfileBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const profileId = this.dataset.profileId;
                
                // Set profile ID in the form
                if (editProfileId) {
                    editProfileId.value = profileId;
                }
                
                // Manually fetch profile data since we already have it in the DOM
                const profileItem = document.querySelector(`.profile-item[data-profile-id="${profileId}"]`);
                if (profileItem) {
                    const profileName = profileItem.querySelector('.profile-name').textContent;
                    const profileAvatar = profileItem.querySelector('img').src.split('/').pop();
                    
                    // Set profile name
                    if (editProfileName) {
                        editProfileName.value = profileName;
                    }
                    
                    // Set selected avatar
                    if (editAvatarSelector) {
                        const avatarOptions = editAvatarSelector.querySelectorAll('.avatar-option');
                        avatarOptions.forEach(option => {
                            option.classList.remove('selected');
                            
                            const optionAvatar = option.dataset.avatar;
                            if (profileAvatar.includes(optionAvatar)) {
                                option.classList.add('selected');
                                if (editSelectedAvatar) {
                                    editSelectedAvatar.value = optionAvatar;
                                }
                            }
                        });
                    }
                    
                    // Show the modal
                    editProfileModal.classList.add('active');
                }
            });
        });
        
        // Close modal
        function closeEditProfileModalFn() {
            if (editProfileModal) {
                editProfileModal.classList.remove('active');
            }
        }
        
        if (closeEditProfileModal) {
            closeEditProfileModal.addEventListener('click', closeEditProfileModalFn);
        }
        
        if (cancelEditProfile) {
            cancelEditProfile.addEventListener('click', closeEditProfileModalFn);
        }
        
        // Handle avatar selection in edit modal
        if (editAvatarSelector) {
            const avatarOptions = editAvatarSelector.querySelectorAll('.avatar-option');
            avatarOptions.forEach(option => {
                option.addEventListener('click', function() {
                    avatarOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    if (editSelectedAvatar) {
                        editSelectedAvatar.value = this.dataset.avatar;
                    }
                });
            });
        }
        
        // Handle form submission
        if (updateProfile && editProfileForm) {
            updateProfile.addEventListener('click', function() {
                // Manual validation
                if (editProfileName && !editProfileName.value.trim()) {
                    alert('Please enter a profile name');
                    editProfileName.focus();
                    return;
                }
                
                // Submit the form
                editProfileForm.submit();
            });
        }
    }
});