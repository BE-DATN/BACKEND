document.addEventListener('DOMContentLoaded', function() {
    const notificationContainer = document.getElementById('notificationContainer');
    const notification = document.getElementById('notification');
    const close = document.getElementById('close');

    // Show notification
    notification.style.display = 'flex';

    // Close notification when close button is clicked
    close.addEventListener('click', function() {
      notificationContainer.style.display = 'none';
    });
  });

