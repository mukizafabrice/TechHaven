<?php
// admin/messages/view_message.php

// Start session and include config first
session_start();
require_once '../../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get message ID from URL
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$message_id) {
    $_SESSION['error_message'] = "Invalid message ID.";
    header('Location: index.php');
    exit;
}

try {
    // Get the message
    $stmt = $pdo->prepare("
        SELECT cm.*, 
               DATE_FORMAT(cm.created_at, '%W, %M %e, %Y at %l:%i %p') as formatted_date
        FROM contact_messages cm 
        WHERE cm.id = ?
    ");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        $_SESSION['error_message'] = "Message #$message_id not found.";
        header('Location: index.php');
        exit;
    }

    // Mark message as read if it's new
    if ($message['status'] == 'new') {
        $update_stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $update_stmt->execute([$message_id]);
        $message['status'] = 'read'; // Update local variable
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error loading message: " . $e->getMessage();
    header('Location: index.php');
    exit;
}

// Now include the header after we have the message data
$page_title = "View Message - #" . $message['id'];
include '../../includes/admin-header.php';
?>

<div class="container px-4 py-6 mx-auto">
    <!-- Display any error messages from session -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="px-4 py-3 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
            <?= $_SESSION['error_message'] ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <nav class="flex items-center mb-2 space-x-2 text-sm text-gray-600">
                <a href="../index.php" class="transition duration-300 hover:text-blue-600">
                    <i class="fas fa-home"></i>
                </a>
                <span class="text-gray-400">/</span>
                <a href="index.php" class="transition duration-300 hover:text-blue-600">Messages</a>
                <span class="text-gray-400">/</span>
                <span class="font-medium text-gray-800">View Message #<?= $message['id'] ?></span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($message['subject']) ?></h1>
            <p class="mt-1 text-gray-600">From: <?= htmlspecialchars($message['name']) ?> &lt;<?= htmlspecialchars($message['email']) ?>&gt;</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="index.php" class="flex items-center px-4 py-2 text-gray-700 transition duration-300 bg-gray-300 rounded-lg hover:bg-gray-400">
                <i class="mr-2 fas fa-arrow-left"></i> Back to Messages
            </a>
            <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= htmlspecialchars($message['subject']) ?>"
                class="flex items-center px-4 py-2 text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700">
                <i class="mr-2 fas fa-reply"></i> Reply
            </a>
        </div>
    </div>

    <!-- Message Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Main Content -->
        <div class="lg:col-span-3">
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                <!-- Status Bar -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                <?= $message['status'] == 'new' ? 'bg-green-100 text-green-800' : ($message['status'] == 'read' ? 'bg-blue-100 text-blue-800' : ($message['status'] == 'replied' ? 'bg-purple-100 text-purple-800' :
                                    'bg-gray-100 text-gray-800')) ?>">
                                <?= ucfirst($message['status']) ?>
                            </span>
                            <span class="text-sm text-gray-600">Message ID: #<?= $message['id'] ?></span>
                            <span class="text-sm text-gray-600">Received: <?= $message['formatted_date'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="mb-3 text-lg font-semibold text-gray-800">Message Content:</h3>
                        <div class="p-6 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="leading-relaxed text-gray-700 whitespace-pre-wrap">
                                <?= nl2br(htmlspecialchars($message['message'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Information -->
            <div class="mt-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Technical Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">IP Address:</label>
                            <p class="font-mono text-gray-600"><?= htmlspecialchars($message['ip_address'] ?? 'Not available') ?></p>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">User Agent:</label>
                            <p class="font-mono text-xs text-gray-600 truncate"><?= htmlspecialchars($message['user_agent'] ?? 'Not available') ?></p>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Submission Date:</label>
                            <p class="text-gray-600"><?= $message['formatted_date'] ?></p>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Message Status:</label>
                            <p class="text-gray-600"><?= ucfirst($message['status']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Actions -->
            <div class="p-6 mb-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h3 class="mb-4 font-semibold text-gray-800">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= urlencode($message['subject']) ?>"
                        class="flex items-center justify-center w-full px-4 py-3 text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700">
                        <i class="mr-2 fas fa-reply"></i> Reply via Email
                    </a>

                    <button onclick="changeStatus(<?= $message['id'] ?>, '<?= $message['status'] == 'read' ? 'new' : 'read' ?>')"
                        class="flex items-center justify-center w-full px-4 py-3 text-white transition duration-300 bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="fas fa-<?= $message['status'] == 'read' ? 'envelope' : 'check' ?> mr-2"></i>
                        Mark as <?= $message['status'] == 'read' ? 'Unread' : 'Read' ?>
                    </button>

                    <button onclick="changeStatus(<?= $message['id'] ?>, 'replied')"
                        class="flex items-center justify-center w-full px-4 py-3 text-white transition duration-300 bg-purple-600 rounded-lg hover:bg-purple-700">
                        <i class="mr-2 fas fa-check-double"></i> Mark as Replied
                    </button>

                    <button onclick="changeStatus(<?= $message['id'] ?>, 'closed')"
                        class="flex items-center justify-center w-full px-4 py-3 text-white transition duration-300 bg-gray-600 rounded-lg hover:bg-gray-700">
                        <i class="mr-2 fas fa-archive"></i> Close Message
                    </button>
                </div>
            </div>

            <!-- Sender Information -->
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h3 class="mb-4 font-semibold text-gray-800">Sender Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-600 uppercase">Full Name</label>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($message['name']) ?></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-600 uppercase">Email Address</label>
                        <p class="text-gray-900 break-all"><?= htmlspecialchars($message['email']) ?></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium tracking-wide text-gray-600 uppercase">Subject</label>
                        <p class="text-gray-900"><?= htmlspecialchars($message['subject']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Ultra-robust status management
    function changeStatus(messageId, newStatus) {
        console.log('🔧 changeStatus called:', {
            messageId,
            newStatus
        });

        if (!confirm(`Change status to "${newStatus}"?`)) {
            return;
        }

        // Store button reference safely
        const button = event?.target || document.querySelector(`button[onclick*="${newStatus}"]`);
        let originalHTML = '';
        let originalDisabled = false;

        if (button) {
            originalHTML = button.innerHTML;
            originalDisabled = button.disabled;
            button.innerHTML = '<i class="mr-2 fas fa-spinner fa-spin"></i> Updating...';
            button.disabled = true;
        }

        // Create the request
        const formData = new FormData();
        formData.append('id', messageId.toString());
        formData.append('status', newStatus);

        fetch('update_status.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(handleResponse)
            .then(data => {
                console.log('✅ Success:', data);
                if (data.success) {
                    showNotification(`✅ ${data.message}`, 'success');
                    updateStatusDisplay(newStatus);
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('❌ Fetch error:', error);
                showNotification(`❌ ${error.message}`, 'error');
            })
            .finally(() => {
                // Restore button state
                if (button) {
                    button.innerHTML = originalHTML;
                    button.disabled = originalDisabled;
                }
            });
    }

    // Handle fetch response with extreme care
    function handleResponse(response) {
        console.log('📨 Response status:', response.status, response.statusText);

        return response.text().then(text => {
            console.log('📄 Raw response:', text);

            // Clean the text - remove any non-JSON content
            const cleanText = text.trim();

            // Check if it's empty
            if (!cleanText) {
                throw new Error('Empty response from server');
            }

            // Check if it starts with { (JSON object)
            if (!cleanText.startsWith('{')) {
                // Find the first { and take everything from there
                const jsonStart = cleanText.indexOf('{');
                if (jsonStart === -1) {
                    throw new Error('No JSON found in response: ' + cleanText.substring(0, 100));
                }
                const jsonText = cleanText.substring(jsonStart);
                return JSON.parse(jsonText);
            }

            // Try to parse as clean JSON
            try {
                return JSON.parse(cleanText);
            } catch (parseError) {
                console.error('❌ JSON parse error:', parseError);
                throw new Error('Invalid JSON: ' + cleanText.substring(0, 100));
            }
        });
    }

    // Update UI without page reload
    function updateStatusDisplay(newStatus) {
        console.log('🎨 Updating UI for status:', newStatus);

        // Update the main status badge
        const statusSelectors = [
            '.bg-gray-50 .px-3.py-1',
            '[class*="bg-"].px-3.py-1',
            '.status-badge'
        ];

        let statusBadge = null;
        for (const selector of statusSelectors) {
            statusBadge = document.querySelector(selector);
            if (statusBadge) break;
        }

        if (statusBadge) {
            // Remove all background and text color classes
            const classes = statusBadge.className.split(' ').filter(cls =>
                !cls.startsWith('bg-') && !cls.startsWith('text-')
            );

            // Add new status-specific classes
            const statusStyles = {
                'new': ['bg-green-100', 'text-green-800'],
                'read': ['bg-blue-100', 'text-blue-800'],
                'replied': ['bg-purple-100', 'text-purple-800'],
                'closed': ['bg-gray-100', 'text-gray-800']
            };

            const newStyles = statusStyles[newStatus] || ['bg-gray-100', 'text-gray-800'];
            statusBadge.className = [...classes, ...newStyles].join(' ');
            statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

            console.log('✅ Status badge updated');
        }

        // Update any other status displays
        document.querySelectorAll('[data-status]').forEach(el => {
            el.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        });
    }

    // Simple notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        document.querySelectorAll('.custom-notification').forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `custom-notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
        notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button class="ml-3 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                ×
            </button>
        </div>
    `;

        document.body.appendChild(notification);

        // Auto-remove after 4 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 4000);
    }

    // Delete function
    function deleteMessage(messageId) {
        if (!confirm('Permanently delete this message?')) return;

        const formData = new FormData();
        formData.append('id', messageId.toString());

        fetch('delete.php', {
                method: 'POST',
                body: formData
            })
            .then(handleResponse)
            .then(data => {
                if (data.success) {
                    showNotification('✅ Message deleted', 'success');
                    setTimeout(() => window.location.href = 'index.php', 1000);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                showNotification('❌ ' + error.message, 'error');
            });
    }

    // Safe initialization
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Message view loaded');

        // Add safe event listeners
        document.addEventListener('keydown', function(e) {
            // Escape to go back
            if (e.key === 'Escape') {
                window.location.href = 'index.php';
            }
            // R to reply (safe check)
            if (e.key === 'r' && !e.ctrlKey && !e.altKey) {
                const activeTag = document.activeElement?.tagName;
                if (activeTag !== 'INPUT' && activeTag !== 'TEXTAREA') {
                    const replyBtn = document.querySelector('a[href^="mailto"]');
                    if (replyBtn) replyBtn.click();
                }
            }
        });
    });

    // Add minimal CSS for notifications
    if (!document.querySelector('#notification-css')) {
        const style = document.createElement('style');
        style.id = 'notification-css';
        style.textContent = `
    .custom-notification {
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    `;
        document.head.appendChild(style);
    }
</script>
<?php include '../../includes/admin-footer.php'; ?>