<?php
$pageTitle = 'About Us - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content {
    margin-top: 70px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: calc(100vh - 70px);
}

.hero-about {
    padding: 4rem 5% 3rem 5%;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #1e40af 75%, #3b82f6 100%);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-about::before {
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

.hero-about::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.hero-about h1 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    color: white;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 2;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.hero-about p {
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    color: rgba(255, 255, 255, 0.9);
    max-width: 700px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
    line-height: 1.6;
}

.about-section {
    padding: 4rem 5%;
    background: white;
}

.about-container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: clamp(2rem, 4vw, 2.5rem);
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 2px;
}

.mission-vision {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2.5rem;
    margin-bottom: 5rem;
}

.mv-card {
    background: white;
    padding: 3rem 2.5rem;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.1);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.mv-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.mv-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 30px 80px rgba(59, 130, 246, 0.2);
    border-color: #3b82f6;
}

.mv-card h2 {
    color: #1e40af;
    font-size: 1.6rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mv-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.mv-card p {
    color: #64748b;
    line-height: 1.8;
    font-size: 1.05rem;
    font-weight: 500;
}

.values-section {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.08) 100%);
    padding: 4rem 3rem;
    border-radius: 32px;
    margin-bottom: 5rem;
    position: relative;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.value-card {
    background: white;
    padding: 2rem 1.5rem;
    border-radius: 20px;
    text-align: center;
    border: 2px solid rgba(59, 130, 246, 0.1);
    transition: all 0.4s ease;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.value-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.05), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
    opacity: 0;
}

.value-card:hover::before {
    opacity: 1;
    top: -25%;
    right: -25%;
}

.value-card:hover {
    border-color: #3b82f6;
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
}

.value-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.4rem;
    margin: 0 auto 1.5rem auto;
    box-shadow: 0 12px 30px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.value-card:hover .value-icon {
    transform: scale(1.1) rotate(5deg);
}

.value-card h3 {
    color: #1e40af;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.value-card p {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
    font-weight: 500;
}

.stats-section {
    background: white;
    padding: 3.5rem 3rem;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.1);
    margin-bottom: 5rem;
    position: relative;
    overflow: hidden;
}

.stats-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2.5rem;
}

.stat-item {
    text-align: center;
    padding: 2rem 1.5rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.08) 100%);
    border-radius: 20px;
    border: 2px solid rgba(59, 130, 246, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.stat-item:hover::before {
    transform: scaleX(1);
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
}

.stat-number {
    font-size: 3rem;
    font-weight: 900;
    color: #3b82f6;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 10px rgba(59, 130, 246, 0.2);
}

.stat-label {
    color: #64748b;
    font-weight: 700;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.08);
    border: 2px solid rgba(59, 130, 246, 0.1);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-card:hover {
    border-color: #3b82f6;
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(59, 130, 246, 0.15);
}

.feature-header {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.feature-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.feature-card h3 {
    color: #1e40af;
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0;
}

.feature-card p {
    color: #64748b;
    font-size: 1rem;
    line-height: 1.7;
    margin: 0;
    font-weight: 500;
}

@media (max-width: 1024px) {
    .values-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
    
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-top: 60px;
        padding-bottom: 80px;
    }
    
    .hero-about {
        padding: 2.5rem 1rem 2rem 1rem;
    }
    
    .hero-about h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .hero-about p {
        font-size: 1.1rem;
        padding: 0 1rem;
    }
    
    .about-section {
        padding: 2rem 1rem;
    }
    
    .section-header {
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .mission-vision {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .mv-card {
        padding: 2rem 1.5rem;
        border-radius: 20px;
    }
    
    .mv-card h2 {
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }
    
    .mv-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    
    .values-section {
        padding: 2.5rem 1.5rem;
        margin-bottom: 3rem;
        border-radius: 24px;
    }
    
    .values-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    
    .value-card {
        aspect-ratio: auto;
        padding: 1.75rem 1.5rem;
        border-radius: 16px;
        display: flex;
        flex-direction: row;
        align-items: center;
        text-align: left;
        gap: 1.25rem;
    }
    
    .value-icon {
        width: 55px;
        height: 55px;
        margin: 0;
        flex-shrink: 0;
        font-size: 1.3rem;
    }
    
    .value-content {
        flex: 1;
    }
    
    .value-card h3 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .value-card p {
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .stats-section {
        padding: 2.5rem 1.5rem;
        margin-bottom: 3rem;
        border-radius: 20px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }
    
    .stat-item {
        padding: 1.5rem 1rem;
        border-radius: 16px;
    }
    
    .stat-number {
        font-size: 2.2rem;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.85rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    
    .feature-card {
        padding: 1.75rem 1.5rem;
        border-radius: 16px;
    }
    
    .feature-header {
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .feature-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    
    .feature-card h3 {
        font-size: 1.1rem;
    }
    
    .feature-card p {
        font-size: 0.95rem;
        line-height: 1.6;
    }
}

@media (max-width: 480px) {
    .hero-about {
        padding: 2rem 1rem 1.5rem 1rem;
    }
    
    .hero-about h1 {
        font-size: 1.75rem;
        line-height: 1.3;
    }
    
    .hero-about p {
        font-size: 1rem;
        padding: 0;
    }
    
    .about-section {
        padding: 1.5rem 1rem;
    }
    
    .section-title {
        font-size: 1.6rem;
    }
    
    .mission-vision {
        margin-bottom: 2.5rem;
    }
    
    .mv-card {
        padding: 1.75rem 1.25rem;
    }
    
    .mv-card h2 {
        font-size: 1.3rem;
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
    
    .mv-card p {
        font-size: 0.95rem;
        text-align: center;
    }
    
    .values-section {
        padding: 2rem 1.25rem;
        margin-bottom: 2.5rem;
    }
    
    .value-card {
        padding: 1.5rem 1.25rem;
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .value-content {
        text-align: center;
    }
    
    .value-card h3 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .value-card p {
        font-size: 0.9rem;
    }
    
    .stats-section {
        padding: 2rem 1.25rem;
        margin-bottom: 2.5rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-item {
        padding: 1.5rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
    }
    
    .feature-card {
        padding: 1.5rem 1.25rem;
    }
    
    .feature-header {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .feature-card h3 {
        font-size: 1rem;
        text-align: center;
    }
    
    .feature-card p {
        font-size: 0.9rem;
        text-align: center;
    }
}
</style>

<main class="main-content">
    <section class="hero-about">
        <h1>About A to Z Global Link Solutions</h1>
        <p>Your trusted partner for professional visa and immigration services worldwide</p>
    </section>

    <section class="about-section">
        <div class="about-container">
            <!-- Mission & Vision -->
            <div class="section-header">
                <h2 class="section-title">Our Foundation</h2>
            </div>
            <div class="mission-vision">
                <div class="mv-card">
                    <h2>
                        <div class="mv-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        Our Mission
                    </h2>
                    <p>To provide comprehensive, reliable, and efficient visa and immigration services that connect Rwanda to global opportunities while maintaining the highest standards of professionalism and customer satisfaction.</p>
                </div>

                <div class="mv-card">
                    <h2>
                        <div class="mv-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        Our Vision
                    </h2>
                    <p>To be the leading visa and immigration consultancy in Rwanda, recognized for our expertise, integrity, and commitment to helping clients achieve their international travel and immigration goals.</p>
                </div>
            </div>

            <!-- Values -->
            <div class="values-section">
                <div class="section-header">
                    <h2 class="section-title">Our Core Values</h2>
                </div>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="value-content">
                            <h3>Professional Excellence</h3>
                            <p>Certified experts with years of experience in immigration services</p>
                        </div>
                    </div>

                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="value-content">
                            <h3>Trust & Security</h3>
                            <p>Your documents and personal information handled with utmost security</p>
                        </div>
                    </div>

                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="value-content">
                            <h3>Timely Service</h3>
                            <p>Quick processing times and efficient application handling</p>
                        </div>
                    </div>

                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="value-content">
                            <h3>Customer First</h3>
                            <p>Personalized service tailored to your specific needs</p>
                        </div>
                    </div>

                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-globe-africa"></i>
                        </div>
                        <div class="value-content">
                            <h3>Global Reach</h3>
                            <p>Visa services for 50+ countries worldwide with local expertise</p>
                        </div>
                    </div>

                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="value-content">
                            <h3>Digital Innovation</h3>
                            <p>Modern online platform with real-time tracking capabilities</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-section">
                <div class="section-header">
                    <h2 class="section-title">Our Achievements</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Happy Clients</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Countries Served</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support Available</div>
                    </div>
                </div>
            </div>

            <!-- Key Features -->
            <div class="features-section">
                <div class="section-header">
                    <h2 class="section-title">Why Choose Us</h2>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <h3>Secure Document Upload</h3>
                        </div>
                        <p>Advanced document upload system with automatic verification and secure processing</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h3>Multiple Payment Options</h3>
                        </div>
                        <p>MTN Mobile Money, Airtel Money, and bank transfers with instant confirmation</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>Real-time Chat Support</h3>
                        </div>
                        <p>Direct communication with our experts through integrated messaging system</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Application Tracking</h3>
                        </div>
                        <p>Live status updates and progress tracking for all your applications</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h3>Secure Dashboard</h3>
                        </div>
                        <p>Personal dashboard with encrypted data storage and secure access controls</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3>Expert Consultation</h3>
                        </div>
                        <p>Professional guidance from certified immigration consultants and visa experts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>