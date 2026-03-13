    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section company-info">
                <div class="footer-logo">
                    <h3>A to Z Global Link Solution</h3>
                    <div class="logo-accent"></div>
                </div>
                <p>Your trusted partner for visa and immigration services worldwide. Professional guidance, proven results, seamless experience.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/share/1Duu3nzGk3/" target="_blank" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/atozgloballinksolutions?igsh=MWVla2p4dms1YXVxaA==" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://youtube.com/@atozgloba?si=aQA1shFRWa8i12dG" target="_blank" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://wa.me/250796597936" target="_blank" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="application.php">Apply Now</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Services</h4>
                <ul>
                    <li><a href="services.php?type=tourist">Tourist Visa</a></li>
                    <li><a href="services.php?type=business">Business Visa</a></li>
                    <li><a href="services.php?type=student">Student Visa</a></li>
                    <li><a href="services.php?type=work">Work Permit</a></li>
                    <li><a href="services.php?type=family">Family Visa</a></li>
                </ul>
            </div>
            
            <div class="footer-section contact-section">
                <h4>Contact Info</h4>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Kabuga Center, Kicukiro<br>Kigali, Rwanda</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+250 796 597 936</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info.atozgloballinksolutions@gmail.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon-Sat: 8:00 AM - 6:00 PM</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2026 A to Z Global Link Solution. All rights reserved.</p>
                <div class="footer-links">
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1 0 auto;
        }
        
        .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e40af 100%) !important;
            color: white;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.5), transparent);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            position: relative;
            z-index: 2;
        }
        
        .footer-logo {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .footer-logo h3 {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo-accent {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            border-radius: 2px;
        }
        
        .footer-section h4 {
            color: #f1f5f9;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            position: relative;
        }
        
        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 30px;
            height: 2px;
            background: #3b82f6;
            border-radius: 1px;
        }
        
        .company-info p {
            color: #cbd5e1;
            line-height: 1.7;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .social-links a {
            width: 45px;
            height: 45px;
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .social-links a:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 0.75rem;
        }
        
        .footer-section ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section ul li a:hover {
            color: #3b82f6;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            color: #cbd5e1;
            transition: color 0.3s ease;
        }
        
        .contact-item:hover {
            color: #3b82f6;
        }
        
        .contact-item i {
            color: #3b82f6;
            width: 18px;
            font-size: 1.1rem;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .contact-item span {
            line-height: 1.5;
            color: #cbd5e1;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(59, 130, 246, 0.2);
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .footer-bottom-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-bottom p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        .footer-links {
            display: flex;
            gap: 2rem;
        }
        
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #3b82f6;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                padding: 3rem 1.5rem 2rem;
                gap: 3rem;
                text-align: left;
            }
            
            .company-info {
                text-align: center;
                padding-bottom: 1rem;
                border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            }
            
            .footer-section h4::after {
                left: 0;
                transform: none;
            }
            
            .footer-section:not(.company-info) {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
                align-items: start;
            }
            
            .footer-section:not(.company-info) h4 {
                grid-column: 1 / -1;
                margin-bottom: 1rem;
            }
            
            .footer-section ul {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .contact-section .contact-info {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            
            .contact-item {
                justify-content: flex-start;
                text-align: left;
            }
            
            .social-links {
                justify-content: center;
                margin-top: 1rem;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1.5rem 1.5rem;
            }
            
            .footer-links {
                gap: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .footer-section:not(.company-info) {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .contact-section .contact-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>