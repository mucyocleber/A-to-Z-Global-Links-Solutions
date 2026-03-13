<?php
session_start();

// Database connection with error handling
$services = [];
$countries = [];
try {
    require_once 'config/database.php';
    $pdo = getConnection();
    $services = $pdo->query("
        SELECT s.*, sc.name as category_name 
        FROM services s 
        LEFT JOIN service_categories sc ON s.category_id = sc.id 
        WHERE s.is_active = 1 
        ORDER BY s.sort_order, s.name
    ")->fetchAll();
    
    $countries = $pdo->query("
        SELECT c.name, c.iso_code_2, c.region, dc.flag_emoji, dc.services_offered
        FROM destination_countries dc
        JOIN countries c ON dc.country_id = c.id
        WHERE dc.is_featured = 1 AND c.is_active = 1
        ORDER BY dc.sort_order
    ")->fetchAll();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Keep arrays as empty to show fallback content
}

$pageTitle = 'A to Z Global Link - Professional Visa & Immigration Services';
include 'includes/header.php';
?>

<style>
/* Reset body padding for hero */
body { padding-bottom: 80px; }

/* WhatsApp Floating Button */
.whatsapp-float {
    position: fixed;
    bottom: 100px;
    right: 20px;
    z-index: 1000;
    background: #25d366;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.whatsapp-float:hover {
    background: #128c7e;
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(37, 211, 102, 0.6);
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .whatsapp-float {
        bottom: 90px;
        right: 15px;
        width: 55px;
        height: 55px;
        font-size: 1.6rem;
    }
}

/* Main content wrapper */
main {
    flex: 1 0 auto;
}

/* Modern Hero Section */
.hero {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(59, 130, 246, 0.85) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('images/global link.jpeg') center/cover;
    opacity: 0.3;
    z-index: 1;
    animation: slideBackground 15s infinite;
}

@keyframes slideBackground {
    0%, 25% { background-image: url('images/global link.jpeg'); }
    25%, 50% { background-image: url('images/office.jpeg'); }
    50%, 75% { background-image: url('images/Visa-Application.jpg'); }
    75%, 100% { background-image: url('images/visa approved.jpeg'); }
}

.hero-container {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    text-align: center;
    color: #1e40af;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 2rem;
    border: 1px solid rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.hero h1 {
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1.5rem;
    color: #1e40af;
}

.hero-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    line-height: 1.6;
    margin-bottom: 3rem;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    color: #000000;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.hero-btn {
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 180px;
    justify-content: center;
}

.hero-btn.primary {
    background: #3b82f6;
    color: white;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
}

.hero-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
    color: white;
    text-decoration: none;
    background: #1d4ed8;
}

.hero-btn.secondary {
    background: white;
    color: #3b82f6;
    border: 2px solid #3b82f6;
}

.hero-btn.secondary:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-2px);
    text-decoration: none;
}

@media (max-width: 768px) {
    .hero {
        min-height: 90vh;
        padding-top: 60px;
    }
    
    .hero-container {
        padding: 1rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-btn {
        width: 100%;
        max-width: 280px;
    }
}

/* About Section */
.about {
    padding: 5rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
}

.about-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.about-content h2 {
    font-size: clamp(2.2rem, 5vw, 3.2rem);
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 1.5rem;
    line-height: 1.1;
}

.about-content p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 2.5rem;
}

.about-features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.2rem;
    margin-bottom: 2.5rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem;
    background: white;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
    box-shadow: 0 2px 10px rgba(59, 130, 246, 0.08);
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.15);
}

.feature-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.feature-text {
    font-weight: 600;
    color: #1e40af;
    font-size: 1rem;
}

.learn-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #3b82f6;
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.learn-more-btn:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    text-decoration: none;
    color: white;
}

.about-image {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(59, 130, 246, 0.15);
    transition: transform 0.3s ease;
}

.about-image:hover {
    transform: translateY(-5px);
}

.about-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

@media (max-width: 1024px) {
    .about-container {
        gap: 3rem;
    }
    
    .about-image img {
        height: 350px;
    }
}

@media (max-width: 768px) {
    .about {
        padding: 2rem 1rem;
    }
    
    .about-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .about-content {
        order: 2;
    }
    
    .about-image {
        order: 1;
    }
    
    .about-content h2 {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .about-content p {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .about-features {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.8rem;
        margin-bottom: 2rem;
    }
    
    .feature-item {
        padding: 0.8rem;
        justify-content: center;
        text-align: center;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .feature-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .feature-text {
        font-size: 0.9rem;
    }
    
    .about-image {
        margin: 0 auto;
        max-width: 100%;
    }
    
    .about-image img {
        height: 250px;
        border-radius: 16px;
    }
    
    .learn-more-btn {
        display: flex;
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
        justify-content: center;
    }
}

/* Services Section */
.services {
    padding: 5rem 2rem;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

.services-container {
    max-width: 1200px;
    margin: 0 auto;
}

.services-header {
    text-align: center;
    margin-bottom: 4rem;
}

.services-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #1e40af;
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(30, 64, 175, 0.3);
}

.services h2 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    color: #1e40af;
    margin-bottom: 1rem;
    line-height: 1.1;
}

.services-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.3rem);
    color: #475569;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 3rem;
}

.service-card {
    aspect-ratio: 1;
    background: white;
    border-radius: 20px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.service-card:nth-child(1) { background: linear-gradient(135deg, #fef3c7, #fbbf24); }
.service-card:nth-child(2) { background: linear-gradient(135deg, #dbeafe, #3b82f6); }
.service-card:nth-child(3) { background: linear-gradient(135deg, #d1fae5, #10b981); }
.service-card:nth-child(4) { background: linear-gradient(135deg, #fce7f3, #ec4899); }
.service-card:nth-child(5) { background: linear-gradient(135deg, #e0e7ff, #8b5cf6); }
.service-card:nth-child(6) { background: linear-gradient(135deg, #fed7d7, #ef4444); }

.service-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
}

.service-card:nth-child(1) .service-icon { color: #d97706; }
.service-card:nth-child(2) .service-icon { color: #1d4ed8; }
.service-card:nth-child(3) .service-icon { color: #059669; }
.service-card:nth-child(4) .service-icon { color: #be185d; }
.service-card:nth-child(5) .service-icon { color: #7c3aed; }
.service-card:nth-child(6) .service-icon { color: #dc2626; }

.service-card h3 {
    font-size: 1.4rem;
    font-weight: 800;
    color: rgba(0, 0, 0, 0.8);
    margin-bottom: 1rem;
    line-height: 1.2;
}

.service-price {
    font-size: 1.8rem;
    font-weight: 900;
    color: rgba(0, 0, 0, 0.9);
    margin-bottom: 1.5rem;
}

.service-btn {
    background: rgba(255, 255, 255, 0.9);
    color: #1e40af;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.service-btn:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: #1e40af;
}

.services-cta {
    text-align: center;
    margin-top: 4rem;
}

.view-more-btn {
    background: #1e40af;
    color: white;
    padding: 1.2rem 3rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.8rem;
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
}

.view-more-btn:hover {
    background: #1d4ed8;
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(30, 64, 175, 0.4);
    text-decoration: none;
    color: white;
}

.empty-services {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.empty-icon {
    width: 100px;
    height: 100px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 3rem;
    margin: 0 auto 2rem;
}

.empty-services h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 1rem;
}

.empty-services p {
    color: #64748b;
    margin-bottom: 2rem;
}

.contact-btn {
    background: #1e40af;
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.contact-btn:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

@media (max-width: 1024px) {
    .services-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .services {
        padding: 3rem 1rem;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .service-card {
        aspect-ratio: auto;
        padding: 2rem 1.5rem;
        min-height: 200px;
    }
    
    .service-icon {
        width: 70px;
        height: 70px;
        font-size: 2.2rem;
        margin-bottom: 1.2rem;
    }
    
    .service-card h3 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
        line-height: 1.3;
    }
    
    .service-price {
        font-size: 1.6rem;
        margin-bottom: 1.2rem;
    }
    
    .service-btn {
        padding: 0.9rem 2rem;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .view-more-btn {
        width: 100%;
        max-width: 320px;
        justify-content: center;
        padding: 1rem 2rem;
    }
}

/* Process Section */
.process {
    padding: 5rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
    overflow: hidden;
}

.process::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(30, 64, 175, 0.05));
    border-radius: 50%;
    z-index: 1;
}

.process::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 200px;
    height: 200px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(30, 64, 175, 0.03));
    border-radius: 50%;
    z-index: 1;
}

.process-container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.process-header {
    text-align: center;
    margin-bottom: 4rem;
}

.process-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.process h2 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    color: #1e40af;
    margin-bottom: 1rem;
    line-height: 1.1;
}

.process-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.3rem);
    color: #475569;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3rem;
    margin-top: 4rem;
    position: relative;
}

.steps-grid::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 16.66%;
    right: 16.66%;
    height: 2px;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8, #3b82f6);
    z-index: 1;
    opacity: 0.3;
}

.step-card {
    background: white;
    padding: 3rem 2rem;
    border-radius: 25px;
    text-align: center;
    box-shadow: 0 15px 50px rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.1);
    position: relative;
    transition: all 0.4s ease;
    z-index: 2;
    transform: perspective(1000px) rotateY(0deg);
}

.step-card:hover {
    transform: perspective(1000px) rotateY(-5deg) translateY(-10px);
    box-shadow: 0 25px 60px rgba(59, 130, 246, 0.2);
    border-color: #3b82f6;
}

.step-card:nth-child(2):hover {
    transform: perspective(1000px) rotateY(0deg) translateY(-15px) scale(1.05);
}

.step-card:nth-child(3):hover {
    transform: perspective(1000px) rotateY(5deg) translateY(-10px);
}

.step-number {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.5rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    border: 4px solid white;
}

.step-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(30, 64, 175, 0.05));
    border-radius: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 2.5rem;
    margin: 2rem auto 2rem;
    border: 2px solid rgba(59, 130, 246, 0.2);
    position: relative;
    overflow: hidden;
}

.step-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transform: rotate(45deg);
    transition: all 0.6s ease;
    opacity: 0;
}

.step-card:hover .step-icon::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.step-card h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 1.2rem;
    line-height: 1.2;
}

.step-card p {
    color: #64748b;
    line-height: 1.7;
    font-size: 1.05rem;
}

@media (max-width: 1024px) {
    .steps-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
        max-width: 500px;
        margin: 3rem auto 0;
    }
    
    .steps-grid::before {
        display: none;
    }
    
    .step-card:hover {
        transform: translateY(-8px) scale(1.02);
    }
    
    .step-card:nth-child(2):hover,
    .step-card:nth-child(3):hover {
        transform: translateY(-8px) scale(1.02);
    }
}

@media (max-width: 768px) {
    .process {
        padding: 3rem 1rem;
    }
    
    .step-card {
        padding: 2.5rem 1.5rem;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
        top: -20px;
    }
    
    .step-icon {
        width: 80px;
        height: 80px;
        font-size: 2rem;
        margin: 1.5rem auto 1.5rem;
    }
    
    .step-card h3 {
        font-size: 1.3rem;
    }
    
    .step-card p {
        font-size: 1rem;
    }
}

/* Countries Section */
.countries {
    padding: 5rem 2rem;
    background: white;
    position: relative;
}

.countries-container {
    max-width: 1200px;
    margin: 0 auto;
}

.countries-header {
    text-align: center;
    margin-bottom: 4rem;
}

.countries-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.countries h2 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    color: #1e40af;
    margin-bottom: 1rem;
    line-height: 1.1;
}

.countries-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.3rem);
    color: #475569;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.countries-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    margin-top: 3rem;
}

.country-card {
    aspect-ratio: 1;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(59, 130, 246, 0.08);
    border: 2px solid #f1f5f9;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.country-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.6s ease;
}

.country-card:hover::before {
    left: 100%;
}

.country-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
}

.country-flag {
    font-size: 4rem;
    margin-bottom: 1rem;
    display: block;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    font-family: 'Apple Color Emoji', 'Segoe UI Emoji', 'Noto Color Emoji', 'Android Emoji', sans-serif;
    line-height: 1;
}

.country-card h3 {
    font-size: 1.2rem;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 0.8rem;
    line-height: 1.2;
}

.country-services {
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 1rem;
    flex: 1;
    display: flex;
    align-items: center;
}

.country-region {
    display: inline-block;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    color: #3b82f6;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.empty-countries {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: #f8fafc;
    border-radius: 20px;
    border: 2px dashed #e2e8f0;
}

.empty-countries-icon {
    width: 100px;
    height: 100px;
    background: #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 3rem;
    margin: 0 auto 2rem;
}

.empty-countries h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 1rem;
}

.empty-countries p {
    color: #64748b;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

.contact-countries-btn {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.contact-countries-btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

@media (max-width: 1024px) {
    .countries-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .countries {
        padding: 3rem 1rem;
    }
    
    .countries-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        max-width: 400px;
        margin: 3rem auto 0;
    }
}

@media (max-width: 480px) {
    .countries-grid {
        grid-template-columns: 1fr;
        gap: 1.2rem;
        max-width: 350px;
    }
}

/* Why Choose Us Section */
.why-choose {
    padding: 6rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

@media (max-width: 1024px) {
    .reasons-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

.why-choose-container {
    max-width: 1200px;
    margin: 0 auto;
}

.why-choose-header {
    text-align: center;
    margin-bottom: 4rem;
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.8s ease;
}

.why-choose-header.animate {
    opacity: 1;
    transform: translateY(0);
}

.why-choose-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.why-choose h2 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    color: #1e40af;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.why-choose-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.3rem);
    color: #64748b;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

.reasons-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    margin-top: 3rem;
}

.reason-card {
    aspect-ratio: 1;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(59, 130, 246, 0.08);
    border: 2px solid #f1f5f9;
    position: relative;
    transition: all 0.4s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.reason-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
}

.reason-number {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    width: 50px;
    height: 30px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.reason-card h3 {
    font-size: 1.3rem;
    font-weight: 800;
    color: #1e40af;
    margin: 1.5rem 0 1rem 0;
    line-height: 1.2;
}

.reason-card p {
    color: #64748b;
    line-height: 1.5;
    font-size: 0.95rem;
    flex: 1;
    display: flex;
    align-items: center;
}

/* Contact CTA Section */
.contact-cta {
    padding: 6rem 2rem;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

@media (max-width: 768px) {
    .contact-cta {
        padding: 4rem 1rem;
    }
    
    .contact-cta-buttons {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .cta-btn {
        width: 100%;
        max-width: 300px;
        padding: 1rem 2rem;
    }
}

.contact-cta::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
    border-radius: 50%;
    z-index: 1;
}

.contact-cta::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
    border-radius: 50%;
    z-index: 1;
}

.contact-cta-container {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.contact-cta-content {
    transition: all 0.8s ease;
}

.contact-cta h2 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.contact-cta p {
    font-size: clamp(1.2rem, 2.5vw, 1.4rem);
    margin-bottom: 3rem;
    opacity: 0.95;
    text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
}

.contact-cta-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-btn {
    padding: 1.2rem 3rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.4s ease;
    display: inline-block;
    min-width: 200px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.cta-btn.primary {
    background: white;
    color: #1e40af;
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
}

.cta-btn.primary:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 15px 35px rgba(255, 255, 255, 0.4);
    text-decoration: none;
    color: #1e40af;
}

.cta-btn.secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
}

.cta-btn.secondary:hover {
    background: white;
    color: #1e40af;
    transform: translateY(-4px) scale(1.05);
    text-decoration: none;
    box-shadow: 0 15px 35px rgba(255, 255, 255, 0.4);
}

@media (max-width: 768px) {
    .steps-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .contact-cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .cta-btn {
        width: 100%;
        max-width: 300px;
    }
    
    .flow-steps {
        flex-direction: column;
        gap: 2rem;
    }
    
    .flow-arrow {
        transform: rotate(90deg);
        margin: 0;
    }
    
    .process-flow {
        padding: 2rem 1.5rem;
    }
    
    .reasons-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        max-width: 400px;
        margin: 3rem auto 0;
    }
    
    .reason-card {
        padding: 2rem 1.5rem;
    }
}
</style>

<!-- Modern Hero Section -->
<main>
<section class="hero">
    <div class="hero-container">
        <div class="hero-badge">
            <i class="fas fa-globe"></i>
            <span>Professional Immigration Services</span>
        </div>
        
        <h1>Your Gateway to Global Opportunities</h1>
        
        <p class="hero-subtitle">
            Expert visa and immigration services connecting Rwanda to the world. 
            Professional guidance, proven results, seamless experience.
        </p>
        
        <div class="hero-actions">
            <a href="application" class="hero-btn primary">
                <i class="fas fa-rocket"></i>
                <span>Start Application</span>
            </a>
            <a href="services" class="hero-btn secondary">
                <i class="fas fa-list"></i>
                <span>View Services</span>
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about">
    <div class="about-container">
        <div class="about-content">
            <h2>Rwanda's Premier Immigration Partner</h2>
            <p>A to Z Global Link Solutions specializes in transforming complex immigration processes into seamless journeys. Our certified experts provide personalized visa solutions with proven results and professional excellence.</p>
            
            <div class="about-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <span class="feature-text">Expert Consultation</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="feature-text">Fast Processing</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span class="feature-text">Secure & Confidential</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <span class="feature-text">Global Network</span>
                </div>
            </div>
            
            <a href="about" class="learn-more-btn">
                <span>Learn More About Us</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="about-image">
            <img src="images/office.jpeg" alt="A to Z Global Link Professional Office">
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services">
    <div class="services-container">
        <div class="services-header">
            <div class="services-badge">
                <i class="fas fa-briefcase"></i>
                <span>Our Services</span>
            </div>
            <h2>Professional Visa Solutions</h2>
            <p class="services-subtitle">Comprehensive immigration services tailored to your destination and travel purpose</p>
        </div>
        
        <div class="services-grid">
            <?php if (!empty($services) && count($services) > 0): ?>
                <?php 
                $displayServices = array_slice($services, 0, 6);
                foreach ($displayServices as $index => $service): 
                ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <?php 
                            $icons = ['fas fa-passport', 'fas fa-briefcase', 'fas fa-graduation-cap', 'fas fa-plane', 'fas fa-users', 'fas fa-globe'];
                            echo '<i class="' . ($icons[$index % count($icons)] ?? 'fas fa-passport') . '"></i>';
                            ?>
                        </div>
                        
                        <h3><?= htmlspecialchars($service['name']) ?></h3>
                        
                        <div class="service-price">
                            <?= number_format($service['base_price']) ?> <?= $service['currency'] ?? 'RWF' ?>
                        </div>
                        
                        <a href="services?id=<?= $service['id'] ?>" class="service-btn">
                            Apply Now
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-services">
                    <div class="empty-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Services Coming Soon</h3>
                    <p>We're preparing our comprehensive visa and immigration services. Contact us for assistance.</p>
                    <a href="contact" class="contact-btn">Contact Us</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="services-cta">
            <a href="services" class="view-more-btn">
                <span>View All Services</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Process Steps Section -->
<section class="process">
    <div class="process-container">
        <div class="process-header">
            <div class="process-badge">
                <i class="fas fa-clipboard-list"></i>
                <span>Simple Process</span>
            </div>
            <h2>How It Works</h2>
            <p class="process-subtitle">Complete your visa application in 3 simple steps</p>
        </div>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon"><i class="fas fa-file-alt"></i></div>
                <h3>Submit Application</h3>
                <p>Choose your service, fill in travel details, select payment plan, and submit your application online</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon"><i class="fas fa-upload"></i></div>
                <h3>Upload Documents</h3>
                <p>Upload required documents like passport, photos, and supporting documents based on your visa type</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                <h3>Get Your Visa</h3>
                <p>Complete payment, we process your application, and you receive your approved visa documents</p>
            </div>
        </div>
    </div>
</section>

<!-- Countries Section -->
<section class="countries">
    <div class="countries-container">
        <div class="countries-header">
            <div class="countries-badge">
                <i class="fas fa-globe-americas"></i>
                <span>Popular Destinations</span>
            </div>
            <h2>Countries We Serve</h2>
            <p class="countries-subtitle">Professional visa services for top destinations worldwide</p>
        </div>
        
        <div class="countries-grid">
            <?php if (!empty($countries) && count($countries) > 0): ?>
                <?php foreach ($countries as $country): ?>
                    <div class="country-card">
                        <div class="country-flag"><?= htmlspecialchars($country['flag_emoji'] ?? '🌍') ?></div>
                        <h3><?= htmlspecialchars($country['name']) ?></h3>
                        <p class="country-services"><?= htmlspecialchars($country['services_offered'] ?? 'Visa services available') ?></p>
                        <span class="country-region"><?= htmlspecialchars($country['region'] ?? 'Global') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-countries">
                    <div class="empty-countries-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3>Destinations Coming Soon</h3>
                    <p>We're expanding our network of destination countries. Contact us for specific country requirements.</p>
                    <a href="contact" class="contact-countries-btn">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Us</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose">
    <div class="why-choose-container">
        <div class="why-choose-header">
            <div class="why-choose-badge">✨ Why Choose Us</div>
            <h2>Your Success is Our Priority</h2>
            <p class="why-choose-subtitle">What makes us Rwanda's trusted visa partner</p>
        </div>
        
        <div class="reasons-grid">
            <div class="reason-card">
                <div class="reason-number">01</div>
                <h3>High Success Rate</h3>
                <p>95% visa approval rate with expert application handling and embassy relationships</p>
            </div>
            
            <div class="reason-card">
                <div class="reason-number">02</div>
                <h3>Expert Team</h3>
                <p>Certified immigration consultants with years of experience in visa processing</p>
            </div>
            
            <div class="reason-card">
                <div class="reason-number">03</div>
                <h3>Fast Processing</h3>
                <p>Quick turnaround times with efficient document handling and embassy coordination</p>
            </div>
            
            <div class="reason-card">
                <div class="reason-number">04</div>
                <h3>Transparent Pricing</h3>
                <p>No hidden fees, clear pricing structure with flexible payment options available</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="contact-cta">
    <div class="contact-cta-container">
        <div class="contact-cta-content">
            <h2>Ready to Start Your Journey?</h2>
            <p>Get expert guidance for your visa application today</p>
            <div class="contact-cta-buttons">
                <a href="application.php" class="cta-btn primary">Apply Now</a>
                <a href="contact.php" class="cta-btn secondary">Contact Us</a>
            </div>
        </div>
    </div>
</section>
</main>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/250796597936?text=Hello%20A%20to%20Z%20Global%20Link!%20I'm%20interested%20in%20your%20visa%20and%20immigration%20services.%20Could%20you%20please%20provide%20me%20with%20more%20information%20about%20your%20services%20and%20how%20you%20can%20help%20me%20with%20my%20travel%20plans?%20Thank%20you!" 
   target="_blank" 
   class="whatsapp-float" 
   title="Chat with us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
let slideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function showSlide(n) {
    // Remove active class from all slides and dots
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    if (n >= slides.length) slideIndex = 0;
    if (n < 0) slideIndex = slides.length - 1;
    
    // Add active class with slight delay for smooth transition
    setTimeout(() => {
        slides[slideIndex].classList.add('active');
        dots[slideIndex].classList.add('active');
    }, 100);
}

function nextSlide() {
    slideIndex++;
    showSlide(slideIndex);
}

function prevSlide() {
    slideIndex--;
    showSlide(slideIndex);
}

function currentSlide(n) {
    slideIndex = n - 1;
    showSlide(slideIndex);
}

// Auto slide every 6 seconds for better cinematic timing
setInterval(nextSlide, 6000);

// Scroll animations
function animateOnScroll() {
    const elements = document.querySelectorAll('.about-header, .about-content, .about-image, .stat-item, .feature-card, .services-header, .service-card, .services-cta, .process-header, .step-card, .process-flow, .countries-header, .country-card, .why-choose-header, .reason-card, .contact-cta-content');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.classList.add('animate');
        }
    });
}

// Staggered animation for cards
function staggerCards() {
    const cardSections = [
        { selector: '.feature-card', delay: 0.1 },
        { selector: '.service-card', delay: 0.1 },
        { selector: '.step-card', delay: 0.15 },
        { selector: '.country-card', delay: 0.1 },
        { selector: '.reason-card', delay: 0.15 }
    ];
    
    cardSections.forEach(section => {
        const cards = document.querySelectorAll(section.selector);
        cards.forEach((card, index) => {
            if (card.classList.contains('animate')) {
                const delay = card.dataset.delay || (index * section.delay);
                card.style.transitionDelay = `${delay}s`;
            }
        });
    });
}

window.addEventListener('scroll', () => {
    animateOnScroll();
    staggerCards();
});

// Initial check
animateOnScroll();
</script>

<?php include 'includes/footer.php'; ?>