document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById('delete-session-btn');
    const qrContainer = document.getElementById('qr-container');
    const qrImg = document.getElementById('qr-img');
    const waStatus = document.getElementById('wa-status');

    // Modal elements
    const logoutModal = document.getElementById('logout-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelLogoutBtn = document.getElementById('cancel-logout-btn');
    const confirmLogoutBtn = document.getElementById('confirm-logout-btn');

    // Server variables (injected via template)
    const userId = window.DASHBOARD_VARS?.userId || "";
    const apiKey = window.DASHBOARD_VARS?.apiKey || "";
    const nodeServerURL = window.DASHBOARD_VARS?.nodeServerURL || "";
    const planNotActivated = window.DASHBOARD_VARS?.planNotActivated ?? true;

    // Hide delete button by default
    if (deleteBtn) deleteBtn.classList.add('hidden');

    // Initial status fetch (for page load)
    if (userId && apiKey && nodeServerURL) {
        fetch(`${nodeServerURL}/status/${encodeURIComponent(userId)}/${encodeURIComponent(apiKey)}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.ready) {
                    if (waStatus) waStatus.textContent = "✅ WhatsApp Connected";
                    if (qrContainer) qrContainer.classList.add('hidden');
                    if (deleteBtn) deleteBtn.classList.remove('hidden');
                } else {
                    if (waStatus) waStatus.textContent = "🔒 Not connected. Please scan QR code.";
                    if (qrContainer) qrContainer.classList.remove('hidden');
                    if (deleteBtn) deleteBtn.classList.add('hidden');
                }
            });
    }

    if (planNotActivated) {
        // If plan not active, skip websocket and copy logic
        console.warn("Plan is inactive or expired. Socket not initialized.");
    } else if (userId && nodeServerURL) {
        // --- Socket.IO QR and Status updates (Realtime WA connection section update) ---
        // Assumes socket.io.js is loaded in page
        const socket = window.io(nodeServerURL, { transports: ['websocket'] });

        socket.emit('register-user', userId);

        // Receive QR code and show QR UI
        socket.on(`qr-${userId}`, (qr) => {
            if (qrImg && qrContainer) {
                qrImg.src = qr;
                qrContainer.classList.remove('hidden');
                qrImg.classList.remove('hidden');
            }
            if (waStatus) waStatus.textContent = "🔒 Not connected. Please scan QR code.";
            if (deleteBtn) deleteBtn.classList.add('hidden');
        });

        // Listen for WhatsApp connection status changes
        socket.on(`status-${userId}`, ({ ready, apiKey }) => {
            if (ready) {
                if (waStatus) waStatus.textContent = "✅ WhatsApp Connected";
                if (qrContainer) qrContainer.classList.add('hidden');
                if (deleteBtn) deleteBtn.classList.remove('hidden');
            } else {
                if (waStatus) waStatus.textContent = "🔒 Not connected. Please scan QR code.";
                if (qrContainer) qrContainer.classList.remove('hidden');
                if (deleteBtn) deleteBtn.classList.add('hidden');
            }
        });

        socket.on('connect_error', () => {
            if (waStatus) waStatus.textContent = "❌ Cannot connect to WhatsApp server.";
            if (qrContainer) qrContainer.classList.add('hidden');
            if (deleteBtn) deleteBtn.classList.add('hidden');
        });

        // Copy to clipboard function (attach globally for inline html)
        window.copyToClipboard = function(elementId) {
            const element = document.getElementById(elementId);
            const text = element?.innerText || '';
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Copied to clipboard!', 'success');
            }).catch(() => {
                showNotification('Failed to copy', 'error');
            });
        };

        // Notification display
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `p-3 ${type === 'success' ? 'bg-green-800/90' : 'bg-blue-800/90'} text-white rounded-lg border ${type === 'success' ? 'border-green-600' : 'border-blue-600'} fade-in`;
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} mr-2"></i>${message}`;

            document.getElementById('notifications').appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        window.showNotification = showNotification;
    }

    // Show modal on delete click
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (logoutModal) logoutModal.classList.remove('hidden');
        });
    }

    // Close modal actions
    [closeModalBtn, cancelLogoutBtn].forEach(btn => {
        if (btn) btn.addEventListener('click', function() {
            if (logoutModal) logoutModal.classList.add('hidden');
        });
    });

    // Confirm logout action
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', async function() {
            if (deleteBtn) {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="fas fa-trash-alt animate-spin"></i> Deleting...';
            }
            if (logoutModal) logoutModal.classList.add('hidden');
            try {
                const res = await fetch(`${nodeServerURL}/logout/${encodeURIComponent(userId)}/${encodeURIComponent(apiKey)}`, { method: "POST" });
                const data = await res.json();
                if (data.success) {
                    alert('Session deleted successfully!');
                    // SPA behavior: re-register the user for new WhatsApp session, QR, and status
                    if (!planNotActivated && typeof io !== "undefined") {
                        // Re-register the socket for this user to start new WhatsApp session
                        const socket = window.io?.(nodeServerURL, { transports: ['websocket'] });
                        socket?.emit('register-user', userId);
                        // Optionally reset UI state
                        if (waStatus) waStatus.textContent = "🔒 Not connected. Please scan QR code.";
                        if (qrContainer) qrContainer.classList.remove('hidden');
                        if (qrImg) qrImg.classList.add('hidden');
                        if (deleteBtn) deleteBtn.classList.add('hidden');
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Failed to delete session.');
                }
            } catch (err) {
                alert('Failed to delete session. Please try again.');
            } finally {
                if (deleteBtn) {
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete WhatsApp Session';
                }
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
  const getApiKeyBtn = document.getElementById('get-api-key-btn');
  const apiKeyModal = document.getElementById('api-key-modal');
  const closeApiKeyModal = document.getElementById('close-api-key-modal');
  const apiKeyForm = document.getElementById('api-key-form');
  const apiKeyPassword = document.getElementById('api-key-password');
  const apiKeyValue = document.getElementById('api-key-value');
  const apiKeyPlaceholder = document.getElementById('api-key-placeholder');
  const apiKeyError = document.getElementById('api-key-error');
  const copyApiKeyBtn = document.getElementById('copy-api-key-btn');
  const userId = window.DASHBOARD_VARS?.userId;

  if (getApiKeyBtn) {
    getApiKeyBtn.addEventListener('click', function() {
      apiKeyModal.classList.remove('hidden');
      apiKeyPassword.value = '';
      apiKeyError.classList.add('hidden');
      apiKeyError.textContent = '';
      setTimeout(() => apiKeyPassword.focus(), 200);
    });
  }

  if (closeApiKeyModal) {
    closeApiKeyModal.addEventListener('click', () => apiKeyModal.classList.add('hidden'));
  }
  if (apiKeyModal) {
    apiKeyModal.addEventListener('click', (e) => {
      if (e.target === apiKeyModal) apiKeyModal.classList.add('hidden');
    });
  }

  if (apiKeyForm) {
    apiKeyForm.addEventListener('submit', function(e) {
      e.preventDefault();
      apiKeyError.classList.add('hidden');
      apiKeyError.textContent = '';

      fetch('get_api_key.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          user_id: userId,
          password: apiKeyPassword.value
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && data.apiKey) {
          apiKeyValue.textContent = data.apiKey;
          apiKeyValue.style.display = '';
          apiKeyPlaceholder.style.display = 'none';
          copyApiKeyBtn.classList.remove('hidden');
          apiKeyModal.classList.add('hidden');
        } else {
          apiKeyError.textContent = data.message || 'Incorrect password';
          apiKeyError.classList.remove('hidden');
        }
      })
      .catch(() => {
        apiKeyError.textContent = 'Server error. Please try again.';
        apiKeyError.classList.remove('hidden');
      });
    });
  }

  if (copyApiKeyBtn) {
    copyApiKeyBtn.addEventListener('click', function() {
      navigator.clipboard.writeText(apiKeyValue.textContent).then(() => {
        if (window.showNotification) window.showNotification('Copied to clipboard!', 'success');
      });
    });
  }
});