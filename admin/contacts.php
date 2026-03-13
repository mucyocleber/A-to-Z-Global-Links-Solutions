<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Customer Chat';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="chat-header">
        <h1>Messages</h1>
        <div class="online-users" id="onlineUsers">
            <div class="loading-users">
                <div class="loading-circle"></div>
            </div>
        </div>
    </div>
    
    <div class="chat-layout">
        <div class="conversations-panel">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search conversations..." id="searchInput">
            </div>
            <div class="conversations-list" id="conversationsList">
                <div class="loading-conversations">
                    <div class="loading-spinner"></div>
                    <p>Loading conversations...</p>
                </div>
            </div>
        </div>
        
        <div class="chat-panel" id="chatPanel">
            <div class="no-chat-selected">
                <i class="fas fa-comments"></i>
                <h3>Select a conversation</h3>
                <p>Choose from your existing conversations or start a new one</p>
            </div>
        </div>
    </div>
    
    <!-- Chat Window Template -->
    <div class="chat-window" id="chatWindow" style="display: none;">
        <div class="chat-window-header" id="chatWindowHeader">
            <div class="chat-user-info">
                <div class="chat-user-avatar"></div>
                <div class="chat-user-details">
                    <h4></h4>
                    <span class="status">Online</span>
                </div>
            </div>
            <button class="close-chat" onclick="closeChatWindow()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be loaded here -->
        </div>
        
        <div class="chat-input-area">
            <form id="messageForm">
                <div class="message-input-container">
                    <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off">
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.main-content {
    margin-left: 260px;
    margin-top: 70px;
    padding: 0;
    min-height: calc(100vh - 70px);
    background: #f0f2f5;
}

.chat-header {
    background: white;
    padding: 1rem 2rem;
    border-bottom: 1px solid #e4e6ea;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chat-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1c1e21;
    margin: 0 0 1rem 0;
}

.online-users {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding: 0.5rem 0;
}

.online-users::-webkit-scrollbar {
    height: 4px;
}

.online-users::-webkit-scrollbar-track {
    background: #f0f2f5;
}

.online-users::-webkit-scrollbar-thumb {
    background: #bcc0c4;
    border-radius: 2px;
}

.loading-users {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.loading-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #e4e6ea;
    animation: pulse 1.5s ease-in-out infinite;
}

.user-circle {
    position: relative;
    cursor: pointer;
    transition: transform 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.user-circle:hover {
    transform: scale(1.05);
}

.user-circle-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1877f2, #42a5f5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    margin-bottom: 0.25rem;
}

.user-circle.active .user-circle-avatar {
    border-color: #1877f2;
}

.user-circle-name {
    font-size: 0.7rem;
    color: #1c1e21;
    font-weight: 500;
    max-width: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    background: #42b883;
    border: 2px solid white;
    border-radius: 50%;
}

.chat-layout {
    display: grid;
    grid-template-columns: 360px 1fr;
    height: calc(100vh - 140px);
}

.conversations-panel {
    background: white;
    border-right: 1px solid #e4e6ea;
    display: flex;
    flex-direction: column;
}

.search-box {
    padding: 1rem;
    border-bottom: 1px solid #e4e6ea;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 1.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #65676b;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: none;
    background: #f0f2f5;
    border-radius: 20px;
    outline: none;
    font-size: 0.9rem;
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.conversations-list::-webkit-scrollbar {
    width: 6px;
}

.conversations-list::-webkit-scrollbar-track {
    background: transparent;
}

.conversations-list::-webkit-scrollbar-thumb {
    background: #bcc0c4;
    border-radius: 3px;
}

.loading-conversations {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: #65676b;
}

.loading-spinner {
    width: 24px;
    height: 24px;
    border: 2px solid #e4e6ea;
    border-top: 2px solid #1877f2;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f0f2f5;
}

.conversation-item:hover {
    background: #f0f2f5;
}

.conversation-item.active {
    background: #e7f3ff;
}

.conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1877f2, #42a5f5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    font-weight: 600;
    color: #1c1e21;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.conversation-preview {
    color: #65676b;
    font-size: 0.8rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conversation-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.conversation-time {
    color: #65676b;
    font-size: 0.75rem;
}

.unread-count {
    background: #1877f2;
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    font-weight: 600;
}

.chat-panel {
    background: white;
    display: flex;
    flex-direction: column;
}

.chat-header-panel {
    background: #1877f2;
    color: white;
    padding: 1rem;
    border-bottom: 1px solid #e4e6ea;
}

.chat-header-panel .chat-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.chat-header-panel .chat-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
}

.chat-header-panel .chat-user-details h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.chat-header-panel .status {
    font-size: 0.8rem;
    opacity: 0.8;
}

.no-chat-selected {
    text-align: center;
    color: #65676b;
}

.no-chat-selected i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.no-chat-selected h3 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: #1c1e21;
}

.chat-window {
    position: fixed;
    bottom: 0;
    right: 2rem;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 8px 8px 0 0;
    box-shadow: 0 -2px 20px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

.chat-window-header {
    background: #1877f2;
    color: white;
    padding: 1rem;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.chat-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.chat-user-details h4 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.status {
    font-size: 0.75rem;
    opacity: 0.8;
}

.close-chat {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: background 0.2s;
}

.close-chat:hover {
    background: rgba(255,255,255,0.1);
}

.chat-messages {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    background: #f0f2f5;
    max-height: calc(100vh - 280px);
    min-height: 400px;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #bcc0c4;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #8e9297;
}

.message {
    margin-bottom: 0.75rem;
    display: flex;
    flex-direction: column;
}

.message.admin {
    align-items: flex-end;
}

.message-bubble {
    max-width: 80%;
    padding: 0.75rem 1rem;
    border-radius: 18px;
    background: #e4e6ea;
    color: #1c1e21;
    font-size: 0.9rem;
    line-height: 1.3;
}

.message.admin .message-bubble {
    background: #1877f2;
    color: white;
}

.message-time {
    font-size: 0.7rem;
    color: #65676b;
    margin-top: 0.25rem;
    padding: 0 0.5rem;
}

.chat-input-area {
    padding: 1rem;
    border-top: 1px solid #e4e6ea;
}

.message-input-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f0f2f5;
    border-radius: 20px;
    padding: 0.5rem;
}

.message-input-container input {
    flex: 1;
    border: none;
    background: none;
    outline: none;
    padding: 0.5rem;
    font-size: 0.9rem;
}

.send-btn {
    background: #1877f2;
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.send-btn:hover {
    background: #166fe5;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        margin-top: 70px;
        padding: 0;
    }
    
    .chat-header {
        padding: 1rem;
        background: white;
        border-bottom: 1px solid #e4e6ea;
    }
    
    .chat-header h1 {
        font-size: 1.2rem;
        margin-bottom: 0.75rem;
    }
    
    .online-users {
        padding: 0.5rem 0;
        gap: 0.75rem;
    }
    
    .user-circle-avatar {
        width: 50px;
        height: 50px;
        font-size: 1rem;
    }
    
    .chat-layout {
        grid-template-columns: 1fr;
        height: calc(100vh - 200px);
    }
    
    .conversations-panel {
        display: none;
    }
    
    .chat-panel {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .chat-header-panel {
        padding: 1rem;
        flex-shrink: 0;
    }
    
    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        background: #f0f2f5;
        min-height: 0;
    }
    
    .chat-input-area {
        padding: 1rem;
        background: white;
        border-top: 1px solid #e4e6ea;
        flex-shrink: 0;
    }
    
    .message-input-container {
        padding: 0.5rem;
    }
    
    .message-input-container input {
        padding: 0.75rem;
        font-size: 1rem;
    }
    
    .send-btn {
        width: 32px;
        height: 32px;
    }
    
    .chat-window {
        display: none !important;
    }
    
    .no-chat-selected {
        padding: 2rem 1rem;
        text-align: center;
    }
    
    .no-chat-selected i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
}
</style>

<script>
let currentUserId = null;
let currentUserName = '';
let currentUserEmail = '';
let messagePollingInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadConversations();
    
    // Handle message form
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Handle Enter key
    document.getElementById('messageInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });
});

function loadUsers() {
    fetch('ajax/get_chat_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOnlineUsers(data.users || []);
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

function displayOnlineUsers(users) {
    const container = document.getElementById('onlineUsers');
    if (users.length === 0) {
        container.innerHTML = '<div class="loading-circle"></div>';
        return;
    }
    
    let html = '';
    users.forEach(user => {
        const initial = user.first_name ? user.first_name.charAt(0).toUpperCase() : 'U';
        const firstName = user.first_name || 'User';
        const profilePic = user.profile_picture ? `../uploads/profiles/${user.profile_picture}` : null;
        
        html += `
            <div class="user-circle" onclick="selectUserFromCircle(${user.user_id}, '${user.first_name} ${user.last_name}', '${user.email}')">
                <div class="user-circle-avatar">
                    ${profilePic ? `<img src="${profilePic}" alt="${firstName}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : initial}
                </div>
                <div class="user-circle-name">${firstName}</div>
                <div class="online-indicator"></div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function loadConversations() {
    fetch('ajax/get_chat_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayConversations(data.users || []);
            }
        })
        .catch(error => console.error('Error loading conversations:', error));
}

function displayConversations(users) {
    const container = document.getElementById('conversationsList');
    if (users.length === 0) {
        container.innerHTML = '<div class="loading-conversations"><div class="loading-spinner"></div><p>No conversations</p></div>';
        return;
    }
    
    let html = '';
    users.forEach(user => {
        const initial = user.first_name ? user.first_name.charAt(0).toUpperCase() : 'U';
        const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unknown User';
        const unreadBadge = user.unread_count > 0 ? `<div class="unread-count">${user.unread_count}</div>` : '';
        const lastMessage = user.last_message || 'No messages yet';
        const timeDisplay = user.formatted_time || '';
        const profilePic = user.profile_picture ? `../uploads/profiles/${user.profile_picture}` : null;
        
        html += `
            <div class="conversation-item" onclick="selectConversation(${user.user_id}, '${fullName}', '${user.email}')">
                <div class="conversation-avatar">
                    ${profilePic ? `<img src="${profilePic}" alt="${fullName}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : initial}
                </div>
                <div class="conversation-info">
                    <div class="conversation-name">${fullName}</div>
                    <div class="conversation-preview">${lastMessage}</div>
                </div>
                <div class="conversation-meta">
                    <div class="conversation-time">${timeDisplay}</div>
                    ${unreadBadge}
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function selectUserFromCircle(userId, userName, userEmail) {
    openChatInPanel(userId, userName, userEmail);
}

function selectConversation(userId, userName, userEmail) {
    // Remove active class
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class
    event.currentTarget.classList.add('active');
    
    openChatInPanel(userId, userName, userEmail);
}

function openChatInPanel(userId, userName, userEmail) {
    currentUserId = userId;
    currentUserName = userName;
    currentUserEmail = userEmail;
    
    const chatPanel = document.getElementById('chatPanel');
    const initial = userName.charAt(0).toUpperCase();
    
    // Get user data to check for profile picture
    fetch('ajax/get_chat_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.users.find(u => u.user_id == userId);
                const profilePic = user && user.profile_picture ? `../uploads/profiles/${user.profile_picture}` : null;
                
                chatPanel.innerHTML = `
                    <div class="chat-header-panel">
                        <div class="chat-user-info">
                            <div class="chat-user-avatar">
                                ${profilePic ? `<img src="${profilePic}" alt="${userName}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : initial}
                            </div>
                            <div class="chat-user-details">
                                <h4>${userName}</h4>
                                <span class="status">Online</span>
                            </div>
                        </div>
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        <!-- Messages will be loaded here -->
                    </div>
                    <div class="chat-input-area">
                        <form id="messageForm">
                            <div class="message-input-container">
                                <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off">
                                <button type="submit" class="send-btn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                `;
                
                // Re-attach event listeners
                document.getElementById('messageForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    sendMessage();
                });
                
                document.getElementById('messageInput').addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendMessage();
                    }
                });
                
                // Load messages
                loadMessages(userId);
                
                // Start polling
                if (messagePollingInterval) {
                    clearInterval(messagePollingInterval);
                }
                messagePollingInterval = setInterval(() => loadMessages(userId), 3000);
            }
        });
}

function openChatWindow(userId, userName, userEmail) {
    currentUserId = userId;
    currentUserName = userName;
    currentUserEmail = userEmail;
    
    // Update chat window header
    const header = document.getElementById('chatWindowHeader');
    const avatar = header.querySelector('.chat-user-avatar');
    const nameEl = header.querySelector('h4');
    
    avatar.textContent = userName.charAt(0).toUpperCase();
    nameEl.textContent = userName;
    
    // Show chat window
    document.getElementById('chatWindow').style.display = 'flex';
    
    // Load messages
    loadMessages(userId);
    
    // Start polling
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    messagePollingInterval = setInterval(() => loadMessages(userId), 3000);
}

function closeChatWindow() {
    document.getElementById('chatWindow').style.display = 'none';
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
}

function loadMessages(userId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    
    fetch('ajax/get_user_messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayMessages(data.messages || []);
        }
    })
    .catch(error => console.error('Error loading messages:', error));
}

function displayMessages(messages) {
    const container = document.getElementById('chatMessages');
    if (messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: #65676b; padding: 2rem;">No messages yet</div>';
        return;
    }
    
    let html = '';
    messages.forEach(msg => {
        const isAdmin = msg.sender_type === 'admin';
        const time = new Date(msg.created_at).toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        html += `
            <div class="message ${isAdmin ? 'admin' : 'user'}">
                <div class="message-bubble">${msg.message}</div>
                <div class="message-time">${time}</div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    container.scrollTop = container.scrollHeight;
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message || !currentUserId) return;
    
    const formData = new FormData();
    formData.append('user_id', currentUserId);
    formData.append('message', message);
    
    fetch('ajax/send_user_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentUserId);
        }
    })
    .catch(error => console.error('Error sending message:', error));
}

// Auto-refresh
setInterval(() => {
    loadUsers();
    loadConversations();
}, 5000);
</script>

<?php include 'includes/footer.php'; ?>