// Account Page JS - A.2 / A.3 / A.4 / A.5
// Handles: toggling edit mode, saving profile edits via AJAX (A.3),
// showing/hiding the notification dropdown (A.4 stub), and logging out (A.5/A.5.1).

document.addEventListener('DOMContentLoaded', () => {
  const viewMode = document.getElementById('viewMode');
  const editMode = document.getElementById('editMode');
  const editBtn = document.getElementById('editBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const editForm = document.getElementById('editForm');

  const displayName = document.getElementById('displayName');
  const displayEmail = document.getElementById('displayEmail');
  const displaySkill = document.getElementById('displaySkill');

  const notifIcon = document.getElementById('notifIcon');
  const notifMenu = document.getElementById('notifMenu');

  const logoutBtn = document.getElementById('logoutBtn');
  const toast = document.getElementById('accountToast');

  function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
  }

  // A.3 - switch to edit mode
  editBtn.addEventListener('click', () => {
    viewMode.classList.add('hidden');
    editMode.classList.remove('hidden');
  });

  // A.3 - cancel edit, discard changes
  cancelEditBtn.addEventListener('click', () => {
    editForm.reset();
    editMode.classList.add('hidden');
    viewMode.classList.remove('hidden');
  });

  // A.3 - save edits to the real users table via php/update_profile.php
  editForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(editForm);
    const payload = {
      user_id: CURRENT_USER_ID,
      name: formData.get('name').trim(),
      email: formData.get('email').trim(),
      password: formData.get('password'), // may be blank - keep current password
      skillLevel: formData.get('skillLevel'),
    };

    if (!payload.name || !payload.email) {
      showToast('Name and email are required.');
      return;
    }

    try {
      const response = await fetch('php/update_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const result = await response.json();

      if (!result.success) {
        showToast(result.message || 'Could not save changes.');
        return;
      }

      // Reflect saved changes in the view without a full page reload.
      displayName.textContent = result.user.name;
      displayEmail.textContent = result.user.email;
      displaySkill.textContent = result.user.skillLevel;

      editMode.classList.add('hidden');
      viewMode.classList.remove('hidden');
      showToast('Account updated.');
    } catch (err) {
      showToast('Something went wrong saving your changes.');
    }
  });

  // A.4 - toggle notification dropdown (stub)
  notifIcon.addEventListener('click', () => {
    notifMenu.classList.toggle('hidden');
  });

  // Close the dropdown when clicking outside of it
  document.addEventListener('click', (e) => {
    if (!notifIcon.contains(e.target) && !notifMenu.contains(e.target)) {
      notifMenu.classList.add('hidden');
    }
  });

  // A.5 / A.5.1 - log out and redirect to main page
  logoutBtn.addEventListener('click', async () => {
    try {
      await fetch('php/logout.php', { method: 'POST' });
    } finally {
      window.location.href = 'main.php';
    }
  });
});
