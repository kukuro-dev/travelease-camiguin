<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'tourist') {
    header("Location: ../users/user-login.php");
    exit;
}

$tourist_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// -------------------------
// 1. Handle Message Deletion
if (isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    
    // Verify the message belongs to the current user
    $verify_stmt = $conn->prepare("SELECT id FROM messages WHERE id=? AND sender_id=?");
    $verify_stmt->bind_param("is", $message_id, $tourist_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if($verify_result->num_rows > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM messages WHERE id=?");
        $delete_stmt->bind_param("i", $message_id);
        if($delete_stmt->execute()) {
            $success_msg = "Message deleted successfully!";
        } else {
            $error_msg = "Failed to delete message.";
        }
    } else {
        $error_msg = "You can only delete your own messages.";
    }
}

// -------------------------
// 2. Notify tourist if booking is approved (auto-message)
$notify_sql = "SELECT b.id, b.rental_id, r.title, r.provider_id
               FROM booking b
               INNER JOIN rentals r ON b.rental_id = r.id
               WHERE b.tourist_id = ? AND b.status = 'approved'";

$stmt_notify = $conn->prepare($notify_sql);
$stmt_notify->bind_param("s", $tourist_id);
$stmt_notify->execute();
$result_notify = $stmt_notify->get_result();

while($row = $result_notify->fetch_assoc()){
    $provider_id = $row['provider_id'];
    $message = "Your booking for '{$row['title']}' has been approved!";

    // Check if this system message already exists
    $check_sql = "SELECT id FROM messages WHERE sender_id=? AND receiver_id=? AND message=? AND rental_id=?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ssis", $provider_id, $tourist_id, $message, $row['rental_id']);
    $stmt_check->execute();
    $stmt_check->store_result();

    if($stmt_check->num_rows == 0){
        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, rental_id, message, is_read, date_created) 
                       VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("ssis", $provider_id, $tourist_id, $row['rental_id'], $message);
        $stmt_insert->execute();
    }
}

// -------------------------
// 3. Send message from tourist to provider
if (isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $rental_id = $_POST['rental_id'];
    $message_text = trim($_POST['message']);

    if ($message_text != '') {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, rental_id, message, is_read, date_created) 
                                VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("ssis", $tourist_id, $receiver_id, $rental_id, $message_text);
        if($stmt->execute()) {
            $success_msg = "Message sent!";
        }
    }
}

// -------------------------
// 4. Fetch contacts (owners of bookings)
$contacts = $conn->prepare("
    SELECT DISTINCT u.user_id, u.fullname, r.id AS rental_id, r.title AS rental_title
    FROM users u
    INNER JOIN rentals r ON u.user_id = r.provider_id
    INNER JOIN booking b ON b.rental_id = r.id
    WHERE b.tourist_id=?
");
$contacts->bind_param("s", $tourist_id);
$contacts->execute();
$contacts_result = $contacts->get_result();

// -------------------------
// 5. Fetch selected chat
$selected_provider = isset($_GET['provider_id']) ? $_GET['provider_id'] : '';
$selected_rental = isset($_GET['rental_id']) ? $_GET['rental_id'] : '';
$chat_messages = [];
$provider_name = '';

if ($selected_provider != '' && $selected_rental != '') {
    // Get provider name
    $name_stmt = $conn->prepare("SELECT fullname FROM users WHERE user_id=?");
    $name_stmt->bind_param("s", $selected_provider);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result();
    if($name_row = $name_result->fetch_assoc()) {
        $provider_name = $name_row['fullname'];
    }
    
    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE rental_id=? AND ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?))
        ORDER BY date_created ASC
    ");
    $stmt->bind_param("issss", $selected_rental, $tourist_id, $selected_provider, $selected_provider, $tourist_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $chat_messages[] = $row;
    }

    // Mark messages as read
    $stmt = $conn->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND rental_id=?");
    $stmt->bind_param("ssi", $selected_provider, $tourist_id, $selected_rental);
    $stmt->execute();
}
?>

<?php include "tourist-header.php"; ?>

<style>
/* Professional Messaging Design */
.messages-container {
    padding: 30px 0;
    background: #f8f9fa;
    min-height: calc(100vh - 100px);
}

/* Contacts Sidebar */
.contacts-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
    overflow: hidden;
}

.contacts-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 20px;
    font-size: 18px;
    font-weight: 600;
}

.contacts-header i {
    margin-right: 10px;
    color: #ffc107;
}

.contacts-list {
    max-height: 600px;
    overflow-y: auto;
}

.contact-item {
    padding: 18px 20px;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #495057;
    display: block;
}

.contact-item:hover {
    background: #f8f9fa;
    color: #2c3e50;
    padding-left: 25px;
}

.contact-item.active {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    border-left: 4px solid #ffc107;
}

.contact-name {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 5px;
}

.contact-rental {
    font-size: 12px;
    opacity: 0.8;
}

.no-contacts {
    padding: 40px 20px;
    text-align: center;
    color: #6c757d;
}

/* Chat Window */
.chat-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 700px;
}

.chat-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 20px 25px;
    font-size: 18px;
    font-weight: 600;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.chat-header i {
    margin-right: 10px;
    color: #ffc107;
}

.chat-body {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
    background: #f8f9fa;
}

.chat-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
}

.chat-empty i {
    font-size: 64px;
    margin-bottom: 15px;
    opacity: 0.3;
}

/* Message Bubbles */
.message-wrapper {
    display: flex;
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease;
}

.message-wrapper.sent {
    justify-content: flex-end;
}

.message-wrapper.received {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    position: relative;
    word-wrap: break-word;
}

.message-wrapper.sent .message-bubble {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

.message-wrapper.received .message-bubble {
    background: #ffffff;
    color: #2c3e50;
    border: 1px solid #e0e0e0;
    border-bottom-left-radius: 4px;
}

.message-text {
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 5px;
}

.message-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-top: 5px;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
}

.message-actions {
    display: flex;
    gap: 5px;
}

.btn-delete-message {
    background: transparent;
    border: none;
    color: inherit;
    opacity: 0.6;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 12px;
}

.btn-delete-message:hover {
    opacity: 1;
    background: rgba(255,255,255,0.2);
}

.message-wrapper.sent .btn-delete-message:hover {
    background: rgba(255,255,255,0.2);
}

.message-wrapper.received .btn-delete-message:hover {
    background: rgba(0,0,0,0.05);
}

/* Chat Footer */
.chat-footer {
    padding: 20px 25px;
    background: #ffffff;
    border-top: 1px solid #e9ecef;
}

.message-form {
    display: flex;
    gap: 12px;
}

.message-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.message-input:focus {
    outline: none;
    border-color: #2c3e50;
    box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1);
}

.btn-send {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #ffffff;
    border: none;
    padding: 12px 28px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-send:hover {
    background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

/* Alert Messages */
.alert {
    border-radius: 8px;
    border: none;
    padding: 12px 18px;
    margin-bottom: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .chat-card {
        height: 600px;
    }
    
    .message-bubble {
        max-width: 85%;
    }
}
</style>

<div class="messages-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Contacts List -->
            <div class="col-lg-3 col-md-4 mb-3">
                <div class="contacts-card">
                    <div class="contacts-header">
                        <i class="fas fa-users"></i> Contacts
                    </div>
                    <div class="contacts-list">
                        <?php if($contacts_result->num_rows > 0): ?>
                            <?php while($contact = $contacts_result->fetch_assoc()): ?>
                                <a href="?provider_id=<?= $contact['user_id'] ?>&rental_id=<?= $contact['rental_id'] ?>" 
                                   class="contact-item <?= ($selected_provider==$contact['user_id'] && $selected_rental==$contact['rental_id'])?'active':'' ?>">
                                   <div class="contact-name">
                                       <i class="fas fa-user-circle"></i> <?= htmlspecialchars($contact['fullname']) ?>
                                   </div>
                                   <div class="contact-rental"><?= htmlspecialchars($contact['rental_title']) ?></div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-contacts">
                                <i class="fas fa-user-slash"></i>
                                <p>No contacts available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="col-lg-9 col-md-8">
                <div class="chat-card">
                    <div class="chat-header">
                        <i class="fas fa-comments"></i> 
                        <?= $provider_name ? htmlspecialchars($provider_name) : 'Messages' ?>
                    </div>
                    
                    <div class="chat-body" id="chat-box">
                        <?php if(!empty($success_msg)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $success_msg ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($error_msg)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($chat_messages)): ?>
                            <?php foreach($chat_messages as $msg): ?>
                                <div class="message-wrapper <?= ($msg['sender_id']==$tourist_id) ? 'sent' : 'received' ?>">
                                    <div class="message-bubble">
                                        <div class="message-text">
                                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        </div>
                                        <div class="message-meta">
                                            <span class="message-time">
                                                <?= date('h:i A', strtotime($msg['date_created'])) ?>
                                            </span>
                                            <?php if($msg['sender_id']==$tourist_id): ?>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                                    <button type="submit" name="delete_message" class="btn-delete-message" title="Delete message">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="chat-empty">
                                <i class="fas fa-comment-slash"></i>
                                <p>Select a contact to start chatting</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if($selected_provider != ''): ?>
                        <div class="chat-footer">
                            <form method="post" class="message-form">
                                <input type="hidden" name="receiver_id" value="<?= $selected_provider ?>">
                                <input type="hidden" name="rental_id" value="<?= $selected_rental ?>">
                                <input type="text" name="message" class="message-input" placeholder="Type your message..." required>
                                <button type="submit" name="send_message" class="btn-send">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-scroll to bottom
    var chatBox = document.getElementById('chat-box');
    if(chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    
    // Smooth scroll on new messages
    document.addEventListener('DOMContentLoaded', function() {
        if(chatBox && chatBox.children.length > 0) {
            chatBox.lastElementChild.scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>

<?php include "tourist-footer.php"; ?>