<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    $services = $pdo->query("
        SELECT s.*, sc.name as category_name, sc.icon as category_icon 
        FROM services s 
        LEFT JOIN service_categories sc ON s.category_id = sc.id 
        WHERE s.is_active = 1 
        ORDER BY sc.sort_order, s.sort_order, s.name
    ")->fetchAll();
    
    $categories = $pdo->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
} catch (Exception $e) {
    $services = [];
    $categories = [];
}

// Dynamic category gradients and icons based on database
$categoryStyles = [];
foreach ($categories as $category) {
    $categoryStyles[$category['id']] = [
        'gradient' => $category['color'] ?? 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
        'icon' => $category['icon'] ?? 'fas fa-passport'
    ];
}

$pageTitle = 'Our Services - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
.main-content {
    margin-top: 70px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: calc(100vh - 70px);
}

.hero-services {
    padding: 4rem 5% 3rem 5%;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #1e40af 75%, #3b82f6 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-services::before {
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

.hero-services::after {
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

.hero-services h1 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 2;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.hero-services p {
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    max-width: 700px;
    margin: 0 auto;
    opacity: 0.9;
    position: relative;
    z-index: 2;
    line-height: 1.6;
}

.services-section {
    padding: 4rem 5%;
    background: white;
}

.services-container {
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

.category-filter {
    margin-bottom: 3rem;
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding: 0.5rem 0;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.category-filter::-webkit-scrollbar {
    display: none;
}

.filter-btn {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    padding: 0.875rem 1.75rem;
    border: 2px solid rgba(59, 130, 246, 0.2);
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.95rem;
    position: relative;
    overflow: hidden;
    white-space: nowrap;
    flex-shrink: 0;
}

.filter-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.filter-btn:hover::before {
    left: 100%;
}

.filter-btn.active,
.filter-btn:hover {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.service-card {
    aspect-ratio: 1;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(59, 130, 246, 0.1);
    transition: all 0.4s ease;
    border: 2px solid rgba(59, 130, 246, 0.1);
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.service-card:nth-child(6n+1) { background: linear-gradient(135deg, #fef3c7, #fbbf24); }
.service-card:nth-child(6n+2) { background: linear-gradient(135deg, #dbeafe, #3b82f6); }
.service-card:nth-child(6n+3) { background: linear-gradient(135deg, #d1fae5, #10b981); }
.service-card:nth-child(6n+4) { background: linear-gradient(135deg, #fce7f3, #ec4899); }
.service-card:nth-child(6n+5) { background: linear-gradient(135deg, #e0e7ff, #8b5cf6); }
.service-card:nth-child(6n+6) { background: linear-gradient(135deg, #fed7d7, #ef4444); }

.service-card::before {
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

.service-card:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(50%, 50%);
}

.service-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 60px rgba(59, 130, 246, 0.2);
    border-color: #3b82f6;
}

.service-header {
    padding: 2rem 1.5rem 1rem 1.5rem;
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

.service-icon-wrapper {
    width: 70px;
    height: 70px;
    margin: 0 auto 1rem auto;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.service-card:hover .service-icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

.service-icon {
    font-size: 1.8rem;
}

.service-card:nth-child(6n+1) .service-icon { color: #d97706; }
.service-card:nth-child(6n+2) .service-icon { color: #1d4ed8; }
.service-card:nth-child(6n+3) .service-icon { color: #059669; }
.service-card:nth-child(6n+4) .service-icon { color: #be185d; }
.service-card:nth-child(6n+5) .service-icon { color: #7c3aed; }
.service-card:nth-child(6n+6) .service-icon { color: #dc2626; }

.service-card h3 {
    font-size: 1.1rem;
    color: rgba(0, 0, 0, 0.8);
    margin-bottom: 0.5rem;
    font-weight: 800;
    line-height: 1.2;
    text-align: center;
    flex-shrink: 0;
}

.service-card p {
    color: rgba(0, 0, 0, 0.7);
    line-height: 1.4;
    margin: 0;
    font-size: 0.85rem;
    font-weight: 500;
    text-align: center;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.service-footer {
    padding: 1rem 1.5rem 1.5rem 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.service-price {
    font-size: 1.2rem;
    font-weight: 900;
    color: rgba(0, 0, 0, 0.9);
    margin-bottom: 1rem;
    text-align: center;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.service-btn {
    background: rgba(255, 255, 255, 0.9);
    color: #1e40af;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.service-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.service-btn:hover::before {
    left: 100%;
}

.service-btn:hover {
    background: white;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: #1e40af;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
    background: white;
    border-radius: 24px;
    border: 2px dashed rgba(59, 130, 246, 0.2);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.3;
    color: #3b82f6;
}

.empty-state h3 {
    margin-bottom: 0.75rem;
    color: #1e40af;
    font-size: 1.5rem;
    font-weight: 700;
}

.empty-state p {
    font-size: 1.1rem;
    line-height: 1.6;
}

@media (max-width: 1024px) {
    .services-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-top: 60px;
        padding-bottom: 80px;
    }
    
    .hero-services {
        padding: 3rem 1rem 2rem 1rem;
    }
    
    .hero-services h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .hero-services p {
        font-size: 1.1rem;
        padding: 0 1rem;
    }
    
    .services-section {
        padding: 3rem 1rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .category-filter {
        margin: 0 -1rem 2rem -1rem;
        padding: 0.5rem 1rem;
        justify-content: flex-start;
    }
    
    .filter-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .service-card {
        aspect-ratio: auto;
        min-height: 300px;
    }
    
    .service-header {
        padding: 2rem 1.5rem 1.5rem 1.5rem;
    }
    
    .service-icon-wrapper {
        width: 70px;
        height: 70px;
        margin-bottom: 1.25rem;
    }
    
    .service-icon {
        font-size: 1.75rem;
    }
    
    .service-card h3 {
        font-size: 1.3rem;
        margin-bottom: 0.75rem;
    }
    
    .service-card p {
        font-size: 0.95rem;
    }
    
    .service-footer {
        padding: 1.5rem;
    }
    
    .service-price {
        font-size: 1.3rem;
        margin-bottom: 1.25rem;
    }
    
    .service-btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.95rem;
    }
    
    .empty-state {
        padding: 3rem 1.5rem;
    }
    
    .empty-state i {
        font-size: 3rem;
    }
    
    .empty-state h3 {
        font-size: 1.3rem;
    }
    
    .empty-state p {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .hero-services {
        padding: 2.5rem 1rem 2rem 1rem;
    }
    
    .hero-services h1 {
        font-size: 1.75rem;
        line-height: 1.3;
    }
    
    .hero-services p {
        font-size: 1rem;
        padding: 0;
    }
    
    .services-section {
        padding: 2.5rem 1rem;
    }
    
    .section-title {
        font-size: 1.6rem;
    }
    
    .filter-btn {
        padding: 0.75rem 1.5rem;
    }
    
    .service-card {
        min-height: 280px;
    }
    
    .service-header {
        padding: 1.75rem 1.25rem 1.25rem 1.25rem;
    }
    
    .service-icon-wrapper {
        width: 65px;
        height: 65px;
        margin-bottom: 1rem;
    }
    
    .service-icon {
        font-size: 1.5rem;
    }
    
    .service-card h3 {
        font-size: 1.2rem;
    }
    
    .service-card p {
        font-size: 0.9rem;
    }
    
    .service-footer {
        padding: 1.25rem;
    }
    
    .service-price {
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    
    .service-btn {
        padding: 0.875rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .empty-state {
        padding: 2.5rem 1.25rem;
    }
}
</style>

<main class="main-content">
    <section class="hero-services">
        <h1>Our Professional Services</h1>
        <p>Comprehensive visa and immigration solutions tailored to your needs</p>
    </section>

    <section class="services-section">
        <div class="services-container">
            <div class="section-header">
                <h2 class="section-title">Our Services</h2>
            </div>
            
            <div class="category-filter">
                <button class="filter-btn active" onclick="filterServices('all')">All Services</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" onclick="filterServices('<?= $category['id'] ?>')"><?= htmlspecialchars($category['name']) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="services-grid" id="servicesGrid">
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <?php 
                        $categoryId = $service['category_id'];
                        $gradient = $categoryStyles[$categoryId]['gradient'] ?? 'linear-gradient(135deg, #3b82f6, #1d4ed8)';
                        $icon = $categoryStyles[$categoryId]['icon'] ?? 'fas fa-passport';
                        ?>
                        <div class="service-card" data-category="<?= $service['category_id'] ?>">
                            <div class="service-header">
                                <div class="service-icon-wrapper" style="background: <?= $gradient ?>">
                                    <i class="service-icon <?= $icon ?>"></i>
                                </div>
                                <h3><?= htmlspecialchars($service['name']) ?></h3>
                                <p><?php 
                                    $description = $service['short_description'] ?? 'Professional service with expert guidance';
                                    echo htmlspecialchars(strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description);
                                ?></p>
                            </div>
                            <div class="service-footer">
                                <div class="service-price"><?= number_format($service['base_price']) ?> <?= $service['currency'] ?? 'RWF' ?></div>
                                <a href="application.php?service=<?= $service['id'] ?>" class="service-btn">
                                    <i class="fas fa-arrow-right"></i>
                                    Apply Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-cogs"></i>
                        <h3>No Services Available</h3>
                        <p>Services will be displayed here once they are added to the system.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
function filterServices(categoryId) {
    const cards = document.querySelectorAll('.service-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter cards
    cards.forEach(card => {
        if (categoryId === 'all' || card.dataset.category === categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>