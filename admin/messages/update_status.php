<?php
// admin/messages/update_status.php

// =============================================
// COMPLETE OUTPUT CONTROL - NO HTML, NO ERRORS
// =============================================

// Turn off ALL error reporting and display
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Start output buffering with callback to clean any output
ob_start(function ($buffer) {
    // If anything gets output, return empty string
    return '';
});

// Prevent any session errors
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config with error suppression
@require_once '../../includes/config.php';

// Set JSON header immediately
header('Content-Type: application/json; charset=utf-8');

// Initialize response array
$response = ['success' => false, 'message' => 'Unknown error'];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Not authenticated. Please log in.');
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed. Use POST.');
    }

    // Get and validate input
    $message_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    $valid_statuses = ['new', 'read', 'replied', 'closed'];

    if ($message_id <= 0) {
        throw new Exception('Invalid message ID.');
    }

    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Invalid status. Must be: ' . implode(', ', $valid_statuses));
    }

    // Check if database connection exists
    if (!isset($pdo)) {
        throw new Exception('Database connection failed.');
    }

    // Verify message exists
    $check_stmt = $pdo->prepare("SELECT id, status FROM contact_messages WHERE id = ?");
    if (!$check_stmt->execute([$message_id])) {
        throw new Exception('Database query failed.');
    }

    $message = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        throw new Exception('Message not found.');
    }

    // Update the status
    $update_stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
    if (!$update_stmt->execute([$status, $message_id])) {
        throw new Exception('Failed to update status.');
    }

    // Success
    $response = [
        'success' => true,
        'message' => 'Status updated successfully from ' . ucfirst($message['status']) . ' to ' . ucfirst($status),
        'new_status' => $status,
        'old_status' => $message['status']
    ];
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Clear ALL output buffers and send clean JSON
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Send JSON response
echo json_encode($response);
exit;
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Update Message Status</h1>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" id="statusForm">
                <div class="mb-4">
                    <label for="message_id" class="block text-sm font-medium text-gray-700 mb-2">Message ID</label>
                    <input type="number" id="message_id" name="id" value="<?= $message_id ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Status</option>
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex-1">
                        Update Status
                    </button>
                    <a href="index.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-300 flex items-center justify-center">
                        Back to List
                    </a>
                </div>
            </form>
        </div>

        <!-- Quick Status Buttons -->
        <?php if ($message_id): ?>
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Quick Actions for Message #<?= $message_id ?></h3>
                <div class="grid grid-cols-2 gap-3">
                    <form method="POST" class="inline">
                        <input type="hidden" name="id" value="<?= $message_id ?>">
                        <input type="hidden" name="status" value="read">
                        <button type="submit" class="w-full bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition duration-300 text-sm">
                            Mark as Read
                        </button>
                    </form>
                    <form method="POST" class="inline">
                        <input type="hidden" name="id" value="<?= $message_id ?>">
                        <input type="hidden" name="status" value="replied">
                        <button type="submit" class="w-full bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition duration-300 text-sm">
                            Mark as Replied
                        </button>
                    </form>
                    <form method="POST" class="inline">
                        <input type="hidden" name="id" value="<?= $message_id ?>">
                        <input type="hidden" name="status" value="new">
                        <button type="submit" class="w-full bg-yellow-600 text-white px-3 py-2 rounded-lg hover:bg-yellow-700 transition duration-300 text-sm">
                            Mark as New
                        </button>
                    </form>
                    <form method="POST" class="inline">
                        <input type="hidden" name="id" value="<?= $message_id ?>">
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="w-full bg-gray-600 text-white px-3 py-2 rounded-lg hover:bg-gray-700 transition duration-300 text-sm">
                            Close Message
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // AJAX version for the form
    document.getElementById('statusForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Redirect to view page if we have a message ID
                    const messageId = document.getElementById('message_id').value;
                    if (messageId) {
                        window.location.href = 'view_message.php?id=' + messageId;
                    }
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred');
            });
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>