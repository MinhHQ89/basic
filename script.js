// ========================================
// CRUD APPLICATION - MAIN SCRIPT
// ========================================

// ========================================
// TABLE OF CONTENTS
// 1) GLOBAL VARIABLES & DOM ELEMENTS
// 2) INITIALIZATION & BOOTSTRAP
// 3) CONTROLLERS
// 4) SERVICES / API
// 5) UI FUNCTIONS
// 6) VALIDATION FUNCTIONS
// 7) HELPER FUNCTIONS
// ========================================

// ========================================
// GLOBAL VARIABLES & DOM ELEMENTS
// ========================================
let isEditing = false;
let currentUserId = null;

let userForm, userIdInput, nameInput, emailInput, phoneInput;
let submitBtn, clearBtn, usersTableBody;
let confirmModal, confirmMessage, confirmYes, confirmNo;
let notification;

// ========================================
// INITIALIZATION & BOOTSTRAP
// ========================================

// Initialize when page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    getElements();
    loadUsers();
    setupEventListeners();
});

// Get all required HTML elements
function getElements() {
    userForm = document.getElementById('userForm');
    userIdInput = document.getElementById('userId');
    nameInput = document.getElementById('name');
    emailInput = document.getElementById('email');
    phoneInput = document.getElementById('phone');
    submitBtn = document.getElementById('submitBtn');
    clearBtn = document.getElementById('clearBtn');
    usersTableBody = document.getElementById('usersTableBody');
    confirmModal = document.getElementById('confirmModal');
    confirmMessage = document.getElementById('confirmMessage');
    confirmYes = document.getElementById('confirmYes');
    confirmNo = document.getElementById('confirmNo');
    notification = document.getElementById('notification');
}

// Setup event listeners
function setupEventListeners() {
    // Form submit event
    userForm.addEventListener('submit', handleFormSubmit);
    
    // Clear button event
    clearBtn.addEventListener('click', clearForm);
    
    // Modal Yes button event
    confirmYes.addEventListener('click', confirmAction);
    
    // Modal No button event
    confirmNo.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === confirmModal) {
            closeModal();
        }
    });
}

// ========================================
// CONTROLLERS
// ========================================

// Handle form submission (create new or update user)
async function handleFormSubmit(e) {
    e.preventDefault();
    
    // Clear previous errors
    clearAllErrors();
    
    // Validate form before submitting
    if (!validateForm()) {
        return;
    }
    
    try {
        // Step 1: Get data from form
        const formData = new FormData(e.target);
        
        // Step 2: Determine action (create new or update)
        const userId = formData.get('id');
        let result;
        
        if (userId) {
            // Update existing user
            result = await updateUser(formData);
        } else {
            // Create new user
            result = await createUser(formData);
        }
        
        // Step 3: Process result
        if (result) {
            clearForm();
            loadUsers();
        }
    } catch (error) {
        showMessage('An error occurred: ' + error.message, 'error');
    }
}

// Edit user
async function editUser(userId) {
    try {
        // Step 1: Call API to get user information
        const response = await fetch(`operations.php?action=get&id=${userId}`);
        
        // Step 2: Convert response to JSON
        const result = await response.json();
        
        // Step 3: Check result and fill form
        if (result.success) {
            const user = result.data;
            fillFormWithUserData(user);
            setEditMode(true);
        } else {
            showMessage(result.message, 'error');
        }
    } catch (error) {
        showMessage('Failed to get user information: ' + error.message, 'error');
    }
}

// Open delete confirm modal
function openDeleteConfirm(userId) {
    currentUserId = userId;
    confirmMessage.textContent = 'Delete this user?';
    confirmModal.style.display = 'block';
}

// Confirm delete action
async function confirmAction() {
    if (currentUserId) {
        const isDeleted = await deleteUser(currentUserId);
        if (isDeleted) {
            loadUsers(); // Reload list
        }
    }
    closeModal();
}

// ========================================
// SERVICES / API
// ========================================

// Load users list from database
async function loadUsers() {
    try {
        // Step 1: Call API to get users list
        const response = await fetch('operations.php?action=list');
        
        // Step 2: Convert response to JSON
        const result = await response.json();
        
        // Step 3: Check result and display
        if (result.success) {
            displayUsers(result.data);
        } else {
            showMessage(result.message, 'error');
        }
    } catch (error) {
        // Step 4: Handle errors if any
        showMessage('Failed to load users list: ' + error.message, 'error');
    }
}

// Create new user
async function createUser(formData) {
    try {
        // Step 1: Call API to create new user
        const response = await fetch('operations.php?action=create', {
            method: 'POST',
            body: formData
        });
        
        // Step 2: Convert response to JSON
        const result = await response.json();
        
        // Step 3: Check result
        if (result.success) {
            showMessage(result.message);
            return true;
        } else {
            showMessage(result.message, 'error');
            return false;
        }
    } catch (error) {
        showMessage('Failed to create user: ' + error.message, 'error');
        return false;
    }
}

// Update user
async function updateUser(formData) {
    try {
        // Step 1: Call API to update user
        const response = await fetch('operations.php?action=update', {
            method: 'POST',
            body: formData
        });
        
        // Step 2: Convert response to JSON
        const result = await response.json();
        
        // Step 3: Check result
        if (result.success) {
            showMessage(result.message);
            return true;
        } else {
            showMessage(result.message, 'error');
            return false;
        }
    } catch (error) {
        showMessage('Failed to update user: ' + error.message, 'error');
        return false;
    }
}

// Delete user
async function deleteUser(id) {
    try {
        // Step 1: Create FormData with user ID
        const formData = new FormData();
        formData.append('id', id);

        // Step 2: Call API to delete user
        const response = await fetch('operations.php?action=delete', {
            method: 'POST',
            body: formData
        });

        // Step 3: Convert response to JSON
        const result = await response.json();

        // Step 4: Check result
        if (result.success) {
            showMessage(result.message);
            return true;
        } else {
            showMessage(result.message, 'error');
            return false;
        }
    } catch (error) {
        showMessage('Failed to delete user: ' + error.message, 'error');
        return false;
    }
}

// ========================================
// UI FUNCTIONS
// ========================================

// --- UI/Feedback: Notifications ---
function showMessage(message, type = 'info') {
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// --- UI/Table: Render users list ---
function displayUsers(users) {
    usersTableBody.innerHTML = '';
    
    if (users.length === 0) {
        usersTableBody.innerHTML = '<tr><td colspan="6" class="no-data">No users found</td></tr>';
        return;
    }
    
    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.phone || '-'}</td>
            <td>${formatDate(user.created_at)}</td>
            <td class="actions">
                <button onclick="editUser(${user.id})" class="action-btn edit">Edit</button>
                <button onclick="openDeleteConfirm(${user.id})" class="action-btn delete">Delete</button>
            </td>
        `;
        usersTableBody.appendChild(row);
    });
}

// --- UI/Form: Fill & state ---
function fillFormWithUserData(user) {
    currentUserId = user.id;
    isEditing = true;
    
    // Fill form with user information
    userIdInput.value = user.id;
    nameInput.value = user.name;
    emailInput.value = user.email;
    phoneInput.value = user.phone || '';
    
    // Update interface
    submitBtn.textContent = 'Update';
    userForm.scrollIntoView({ behavior: 'smooth' });
}

// Set edit mode
function setEditMode(editing) {
    isEditing = editing;
    if (editing) {
        submitBtn.textContent = 'Update';
    } else {
        submitBtn.textContent = 'Save';
    }
}

// Clear form
function clearForm() {
    userForm.reset();
    userIdInput.value = '';
    currentUserId = null;
    isEditing = false;
    submitBtn.textContent = 'Save';
    
    // Clear all error messages
    clearAllErrors();
}

// --- UI/Modal: Confirm dialog ---
function closeModal() {
    confirmModal.style.display = 'none';
    currentUserId = null;
}

// ========================================
// VALIDATION FUNCTIONS
// ========================================

// Validate name field
function validateName() {
    const nameInput = document.getElementById('name');
    const nameError = document.getElementById('nameError');
    const name = nameInput.value.trim();
    
    // Clear previous errors
    nameError.textContent = '';
    nameError.classList.remove('show');
    
    // Check if empty
    if (name === '') {
        nameError.textContent = 'Name is required';
        nameError.classList.add('show');
        return false;
    }
    
    // Check for special characters
    if (!/^[a-zA-Z\s]+$/.test(name)) {
        nameError.textContent = 'Name can only contain letters and spaces';
        nameError.classList.add('show');
        return false;
    }
    
    return true;
}

// Validate email field
function validateEmail() {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const email = emailInput.value.trim();
    
    // Clear previous errors
    emailError.textContent = '';
    emailError.classList.remove('show');
    
    // Check if empty
    if (email === '') {
        emailError.textContent = 'Email is required';
        emailError.classList.add('show');
        return false;
    }
    
    // Check email format
    if (!email.includes('@') || !email.includes('.')) {
        emailError.textContent = 'Please enter a valid email';
        emailError.classList.add('show');
        return false;
    }
    
    return true;
}

// Validate phone field
function validatePhone() {
    const phoneInput = document.getElementById('phone');
    const phoneError = document.getElementById('phoneError');
    const phone = phoneInput.value.trim();
    
    // Clear previous errors
    phoneError.textContent = '';
    phoneError.classList.remove('show');
    
    // Phone is not required, but validate if provided
    if (phone !== '') {
        // Check for valid phone characters
        if (!/^[\d\s\-\+\(\)]+$/.test(phone)) {
            phoneError.textContent = 'Phone can only contain numbers and phone characters';
            phoneError.classList.add('show');
            return false;
        }
    }
    
    return true;
}

// Validate entire form
function validateForm() {
    const nameValid = validateName();
    const emailValid = validateEmail();
    const phoneValid = validatePhone();
    
    // Return true if all fields are valid
    return nameValid && emailValid && phoneValid;
}

// Clear all error messages
function clearAllErrors() {
    const errors = document.querySelectorAll('.error-message');
    
    errors.forEach(error => {
        error.textContent = '';
        error.classList.remove('show');
    });
}

// ========================================
// HELPER FUNCTIONS
// ========================================

// Format date and time
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}
