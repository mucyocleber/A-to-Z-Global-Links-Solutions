    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('mobile-open');
                } else {
                    sidebar.classList.toggle('collapsed');
                }
            });
        }
        
        // User dropdown functionality
        const userAvatar = document.getElementById('userAvatar');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userAvatar && userDropdown) {
            userAvatar.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
                // Close notification dropdown if open
                const notificationDropdown = document.getElementById('notificationDropdown');
                if (notificationDropdown) {
                    notificationDropdown.classList.remove('show');
                }
            });
        }
        
        // Notification dropdown functionality
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
                // Close user dropdown if open
                if (userDropdown) {
                    userDropdown.classList.remove('show');
                }
                
                // Mark notifications as read (remove badge)
                if (notificationDropdown.classList.contains('show')) {
                    setTimeout(() => {
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            badge.style.display = 'none';
                        }
                        // Mark items as read
                        const unreadItems = document.querySelectorAll('.notification-item.unread');
                        unreadItems.forEach(item => {
                            item.classList.remove('unread');
                        });
                    }, 1000);
                }
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (userDropdown && !userDropdown.contains(e.target) && !userAvatar.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
            if (notificationDropdown && !notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
            
            // Close mobile sidebar when clicking outside
            if (window.innerWidth <= 768 && sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
            }
        });
        
        // Simulate real-time notifications
        function addNotification(title, text) {
            const notificationList = document.getElementById('notificationList');
            const badge = document.getElementById('notificationBadge');
            
            if (notificationList && badge) {
                const newNotification = document.createElement('div');
                newNotification.className = 'notification-item unread';
                newNotification.innerHTML = `
                    <div class="notification-title">${title}</div>
                    <div class="notification-text">${text}</div>
                    <div class="notification-time">Just now</div>
                `;
                
                notificationList.insertBefore(newNotification, notificationList.firstChild);
                
                // Update badge count
                const currentCount = parseInt(badge.textContent) || 0;
                badge.textContent = currentCount + 1;
                badge.style.display = 'block';
            }
        }
        
        // Example: Add a new notification every 30 seconds (for demo)
        // setInterval(() => {
        //     const notifications = [
        //         { title: 'New Application', text: 'A new visa application has been submitted' },
        //         { title: 'Payment Received', text: 'Payment confirmation received' },
        //         { title: 'Document Uploaded', text: 'New document uploaded by user' }
        //     ];
        //     const randomNotification = notifications[Math.floor(Math.random() * notifications.length)];
        //     addNotification(randomNotification.title, randomNotification.text);
        // }, 30000);
    </script>
</body>
</html>