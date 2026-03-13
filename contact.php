<?php
$pageTitle = 'Contact Us - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content {
    margin-top: 70px;
}

.contact-hero {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
    padding: 4rem 5% 3rem 5%;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.contact-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.contact-hero h1 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.contact-hero p {
    font-size: clamp(1.1rem, 2.5vw, 1.3rem);
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
    line-height: 1.6;
}

.contact-section {
    padding: 4rem 5% 5rem 5%;
    max-width: 1200px;
    margin: 0 auto;
}

.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.contact-card {
    background: white;
    padding: 2.5rem 2rem;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.1);
    text-align: center;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.contact-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.05), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
    opacity: 0;
}

.contact-card:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.contact-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 25px 60px rgba(59, 130, 246, 0.2);
    border-color: #3b82f6;
}

.contact-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.6rem;
    margin: 0 auto 1.5rem auto;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.contact-card:hover .contact-icon {
    transform: scale(1.1) rotate(5deg);
}

.contact-card h3 {
    color: #1e40af;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.contact-card p {
    color: #6b7280;
    margin: 0;
    line-height: 1.6;
}

.contact-card a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
}

.contact-card a:hover {
    color: #1d4ed8;
}

.why-choose-section {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 4rem 3rem;
    border-radius: 24px;
    margin-bottom: 4rem;
    text-align: center;
}

.why-choose-section h2 {
    color: #1e40af;
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 3rem;
    position: relative;
}

.why-choose-section h2::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 2px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.feature-item {
    background: white;
    padding: 2rem 1.5rem;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(59, 130, 246, 0.1);
}

.feature-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.15);
}

.feature-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    margin: 0 auto 1rem auto;
}

.feature-item h4 {
    color: #1e40af;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.feature-item p {
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0;
}

.social-section {
    background: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.1);
    text-align: center;
    border: 2px solid rgba(59, 130, 246, 0.1);
}

.social-section h2 {
    color: #1e40af;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 2rem;
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.social-link {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.social-link:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.social-link.facebook { background: linear-gradient(135deg, #1877f2, #0d5dbf); }
.social-link.instagram { background: linear-gradient(135deg, #f09433, #dc2743, #bc1888); }
.social-link.whatsapp { background: linear-gradient(135deg, #25d366, #1ebe57); }
.social-link.youtube { background: linear-gradient(135deg, #ff0000, #cc0000); }

@media (max-width: 768px) {
    .main-content {
        margin-top: 60px;
        padding-bottom: 100px;
    }
    
    .contact-hero {
        padding: 3rem 1rem 2.5rem 1rem;
    }
    
    .contact-section {
        padding: 3rem 1rem 4rem 1rem;
    }
    
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .why-choose-section {
        padding: 3rem 2rem;
        margin-bottom: 3rem;
    }
    
    .contact-card {
        padding: 2rem 1.5rem;
    }
    
    .social-section {
        padding: 2.5rem 1.5rem;
    }
    
    .social-links {
        gap: 1rem;
    }
    
    .social-link {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>

<main class="main-content">
    <section class="contact-hero">
        <h1>Contact Us</h1>
        <p>Get in touch with our expert team for professional visa and immigration services</p>
    </section>

    <section class="contact-section">
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Our Office</h3>
                <p><strong>Kabuga Center, Kicukiro</strong><br>Kigali, Rwanda<br><small>Ground Floor, Office 12</small></p>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Call Us</h3>
                <p><a href="tel:+250796597936">+250 796 597 936</a><br><strong>Business Hours:</strong><br>Monday - Saturday<br>8:00 AM - 6:00 PM<br><small>Emergency consultations available</small></p>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email Us</h3>
                <p><a href="mailto:info.atozgloballinksolutions@gmail.com">info.atozgloballinksolutions@gmail.com</a><br><small>Response within 24 hours</small></p>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>Services Available</h3>
                <p><strong>Visa Processing</strong><br>Tourist • Business • Student<br>Work Permits • Immigration<br><small>Serving 50+ countries worldwide</small></p>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Expert Team</h3>
                <p><strong>Licensed Consultants</strong><br>5+ Years Experience<br>Multilingual Support<br><small>English, French, Kinyarwanda</small></p>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Quick Processing</h3>
                <p><strong>Fast Track Services</strong><br>Express Applications<br>Document Verification<br><small>Same-day consultation available</small></p>
            </div>
        </div>

        <!-- Why Choose Us Section -->
        <div class="why-choose-section">
            <h2>Why Choose A to Z Global Link Solutions?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h4>Licensed & Certified</h4>
                    <p>Officially registered immigration consultancy with proper licensing</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>High Success Rate</h4>
                    <p>95% visa approval rate with transparent process tracking</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>24/7 Support</h4>
                    <p>Round-the-clock assistance for all your immigration needs</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Secure Process</h4>
                    <p>Your documents and personal information are completely secure</p>
                </div>
            </div>
        </div>

        <div class="social-section">
            <h2>Follow Us</h2>
            <div class="social-links">
                <a href="https://www.facebook.com/share/1Duu3nzGk3/" target="_blank" class="social-link facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.instagram.com/atozgloballinksolutions?igsh=MWVla2p4dms1YXVxaA==" target="_blank" class="social-link instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://youtube.com/@atozgloba?si=aQA1shFRWa8i12dG" target="_blank" class="social-link youtube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="https://wa.me/250796597936" target="_blank" class="social-link whatsapp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>