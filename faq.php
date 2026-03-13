<?php
$pageTitle = 'FAQ - A to Z Global Link Solutions';
include 'includes/header.php';
?>

<style>
    .main-content { margin-top: 70px; min-height: calc(100vh - 150px); }
    .hero-section { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); padding: 4rem 0; color: white; text-align: center; }
    .hero-section h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; }
    .hero-section p { font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 0 auto; }
    
    .faq-container { max-width: 1000px; margin: 0 auto; padding: 3rem 1rem; }
    
    /* Tab Navigation */
    .tab-nav { display: flex; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; overflow: hidden; }
    .tab-btn { flex: 1; padding: 1rem 1.5rem; background: none; border: none; font-size: 1rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all 0.3s; position: relative; }
    .tab-btn:hover { background: #f8fafc; color: #1e40af; }
    .tab-btn.active { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; }
    .tab-btn i { margin-right: 0.5rem; }
    
    /* Tab Content */
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    .faq-item { background: white; border-radius: 12px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); overflow: hidden; transition: all 0.3s; }
    .faq-item:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }
    
    .faq-question { padding: 1.5rem; background: linear-gradient(135deg, #f8fafc, #e2e8f0); cursor: pointer; display: flex; justify-content: space-between; align-items: center; border: none; width: 100%; text-align: left; font-size: 1.1rem; font-weight: 600; color: #1e40af; transition: all 0.3s; }
    .faq-question:hover { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); }
    .faq-question i { transition: transform 0.3s; }
    .faq-item.active .faq-question i { transform: rotate(180deg); }
    
    .faq-answer { padding: 0 1.5rem; max-height: 0; overflow: hidden; transition: all 0.3s; background: white; }
    .faq-item.active .faq-answer { padding: 1.5rem; max-height: 500px; }
    .faq-answer p { color: #4b5563; line-height: 1.6; margin-bottom: 1rem; }
    .faq-answer ul { color: #4b5563; margin-left: 1.5rem; }
    .faq-answer li { margin-bottom: 0.5rem; }
    
    .contact-cta { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 2rem; border-radius: 12px; text-align: center; margin-top: 2rem; }
    .contact-cta h3 { margin-bottom: 1rem; }
    .contact-cta p { margin-bottom: 1.5rem; opacity: 0.9; }
    .contact-btn { background: white; color: #1e40af; padding: 0.75rem 2rem; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-block; }
    .contact-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); }
    
    @media (max-width: 768px) {
        .hero-section { padding: 2rem 0; }
        .hero-section h1 { font-size: 2rem; }
        .faq-container { padding: 2rem 1rem; }
        .tab-nav { overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; }
        .tab-nav::-webkit-scrollbar { display: none; }
        .tab-btn { flex: 0 0 auto; padding: 0.75rem 1rem; font-size: 0.85rem; white-space: nowrap; min-width: 120px; }
        .faq-question { padding: 1rem; font-size: 1rem; }
        .faq-item.active .faq-answer { padding: 1rem; }
    }
</style>

<main class="main-content">
    <section class="hero-section">
        <div class="container">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about our visa and immigration services</p>
        </div>
    </section>

    <div class="faq-container">
        <!-- Tab Navigation -->
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('company')">
                <i class="fas fa-building"></i>Company
            </button>
            <button class="tab-btn" onclick="switchTab('visa')">
                <i class="fas fa-passport"></i>Visa Services
            </button>
            <button class="tab-btn" onclick="switchTab('process')">
                <i class="fas fa-cogs"></i>Process
            </button>
            <button class="tab-btn" onclick="switchTab('payment')">
                <i class="fas fa-credit-card"></i>Payment
            </button>
        </div>

        <!-- Company Tab -->
        <div class="tab-content active" id="company">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What is A to Z Global Link Solutions?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>A to Z Global Link Solutions is a professional visa and immigration consultancy firm based in Rwanda. We specialize in helping individuals and businesses navigate the complex world of international travel, visas, and immigration processes.</p>
                    <p>Our team of experienced consultants provides comprehensive services to ensure your visa applications are processed efficiently and successfully.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    How long has your company been in business?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>A to Z Global Link Solutions has been serving clients for over 5 years, building a strong reputation for reliability, professionalism, and high success rates in visa applications.</p>
                    <p>Our experience spans across multiple countries and visa types, giving us deep insights into various immigration systems.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What makes your company different from others?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Our key differentiators include:</p>
                    <ul>
                        <li>Personalized consultation for each client</li>
                        <li>High success rate in visa approvals</li>
                        <li>Transparent pricing with no hidden fees</li>
                        <li>Real-time application tracking system</li>
                        <li>Expert knowledge of multiple countries' requirements</li>
                        <li>Post-approval support and guidance</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Do you have physical offices?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Yes, we have a physical office located in Kigali, Rwanda. We also offer online consultations for clients who cannot visit our office in person.</p>
                    <p>Our office hours are Monday to Friday, 8:00 AM to 6:00 PM, and Saturday 9:00 AM to 2:00 PM.</p>
                </div>
            </div>
        </div>

        <!-- Visa Services Tab -->
        <div class="tab-content" id="visa">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What types of visas do you handle?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>We handle a comprehensive range of visa types including:</p>
                    <ul>
                        <li>Tourist/Visitor visas</li>
                        <li>Business visas</li>
                        <li>Student visas</li>
                        <li>Work permits and employment visas</li>
                        <li>Transit visas</li>
                        <li>Family reunion visas</li>
                        <li>Investment visas</li>
                        <li>Diplomatic and official visas</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Which countries do you provide visa services for?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>We provide visa services for major destinations including:</p>
                    <ul>
                        <li>United States</li>
                        <li>United Kingdom</li>
                        <li>Canada</li>
                        <li>European Union countries (Schengen)</li>
                        <li>Australia</li>
                        <li>China</li>
                        <li>Japan</li>
                        <li>South Africa</li>
                        <li>And many other countries worldwide</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Do you guarantee visa approval?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>While we cannot guarantee visa approval (as the final decision rests with the embassy/consulate), we:</p>
                    <ul>
                        <li>Thoroughly review your application before submission</li>
                        <li>Ensure all required documents are complete and accurate</li>
                        <li>Provide guidance on strengthening your application</li>
                        <li>Have a high success rate due to our expertise and experience</li>
                    </ul>
                    <p>We offer a consultation to assess your eligibility before proceeding with the application.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What if my visa application is rejected?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>In case of rejection, we provide:</p>
                    <ul>
                        <li>Detailed analysis of rejection reasons</li>
                        <li>Guidance on addressing the issues</li>
                        <li>Assistance with reapplication (if applicable)</li>
                        <li>Alternative visa options consultation</li>
                    </ul>
                    <p>Our service fee covers one resubmission if the rejection was due to our error. Embassy fees are non-refundable.</p>
                </div>
            </div>
        </div>

        <!-- Process Tab -->
        <div class="tab-content" id="process">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    How long does the visa application process take?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Processing times vary depending on the type of visa and destination country:</p>
                    <ul>
                        <li>Tourist visas: 7-14 business days</li>
                        <li>Business visas: 10-21 business days</li>
                        <li>Student visas: 14-30 business days</li>
                        <li>Work permits: 21-45 business days</li>
                    </ul>
                    <p>These are estimated timeframes. Actual processing times may vary based on embassy requirements and application complexity.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What documents do I need for a visa application?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Required documents vary by visa type and destination, but commonly include:</p>
                    <ul>
                        <li>Valid passport (minimum 6 months validity)</li>
                        <li>Completed application form</li>
                        <li>Passport-size photographs</li>
                        <li>Bank statements (last 3-6 months)</li>
                        <li>Travel itinerary or invitation letter</li>
                        <li>Employment letter or business registration</li>
                        <li>Travel insurance (if required)</li>
                    </ul>
                    <p>We provide a detailed checklist for each specific visa type during consultation.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Can I track my application status?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Yes! We provide multiple ways to track your application:</p>
                    <ul>
                        <li>Online dashboard with real-time updates</li>
                        <li>SMS notifications for status changes</li>
                        <li>Email updates at key milestones</li>
                        <li>Direct contact with your assigned case officer</li>
                    </ul>
                    <p>You'll receive login credentials to access your personal dashboard after application submission.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What happens after I submit my application?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>After submission, our process includes:</p>
                    <ul>
                        <li>Document verification and review</li>
                        <li>Application formatting and preparation</li>
                        <li>Submission to relevant embassy/consulate</li>
                        <li>Regular follow-up on application status</li>
                        <li>Notification of decision and next steps</li>
                        <li>Assistance with visa collection (if approved)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment Tab -->
        <div class="tab-content" id="payment">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What are your service fees?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Our service fees vary depending on the type of visa and complexity of the application:</p>
                    <ul>
                        <li>Tourist visas: Starting from 150,000 RWF</li>
                        <li>Business visas: Starting from 200,000 RWF</li>
                        <li>Student visas: Starting from 180,000 RWF</li>
                        <li>Consultation services: 50,000 RWF per session</li>
                    </ul>
                    <p>These fees are in addition to embassy/consulate fees. Contact us for a detailed quote based on your specific requirements.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    What payment methods do you accept?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>We accept multiple payment methods for your convenience:</p>
                    <ul>
                        <li>MTN Mobile Money</li>
                        <li>Airtel Money</li>
                        <li>Bank transfers (Bank of Kigali, Equity Bank)</li>
                        <li>Cash payments at our office</li>
                    </ul>
                    <p>Payment confirmation is required before we begin processing your application.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    When do I need to pay?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Payment is typically required:</p>
                    <ul>
                        <li>50% deposit upon application submission</li>
                        <li>Remaining 50% before document submission to embassy</li>
                        <li>Full payment upfront for express services</li>
                        <li>Consultation fees are paid at the time of booking</li>
                    </ul>
                    <p>We provide flexible payment plans for complex applications.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Are there any hidden fees?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>No, we believe in complete transparency. Our quote includes:</p>
                    <ul>
                        <li>All service fees clearly itemized</li>
                        <li>Embassy/consulate fees (where applicable)</li>
                        <li>Document processing charges</li>
                        <li>Any additional service costs</li>
                    </ul>
                    <p>You'll receive a detailed breakdown before making any payment.</p>
                </div>
            </div>
        </div>

        <div class="contact-cta">
            <h3>Still Have Questions?</h3>
            <p>Our expert team is here to help you with personalized assistance</p>
            <a href="contact.php" class="contact-btn">Contact Us Today</a>
        </div>
    </div>
</main>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked tab button
    event.target.classList.add('active');
    
    // Close all open FAQ items when switching tabs
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });
}

function toggleFaq(button) {
    const faqItem = button.parentElement;
    const isActive = faqItem.classList.contains('active');
    
    // Close all FAQ items in current tab
    const currentTab = button.closest('.tab-content');
    currentTab.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Open clicked item if it wasn't active
    if (!isActive) {
        faqItem.classList.add('active');
    }
}
</script>

<?php include 'includes/footer.php'; ?>