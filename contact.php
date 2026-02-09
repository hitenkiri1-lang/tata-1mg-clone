<?php
/**
 * Contact Us Page
 */
$page_title = "Contact Us";
include 'includes/header.php';

$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } else {
        // In a real application, you would send an email or save to database
        // For academic project, we'll just show success message
        $success = "Thank you for contacting us! We will get back to you soon.";
        
        // Clear form
        $name = $email = $subject = $message = '';
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto" data-aos="fade-up">
            <h1 class="mb-4 text-center">Contact Us</h1>
            <p class="text-center text-muted mb-5">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4 mb-5">
                <div class="col-md-4" data-aos="zoom-in">
                    <div class="card shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-envelope fa-3x text-success"></i>
                            </div>
                            <h5>Email Us</h5>
                            <p class="text-muted mb-0">support@1mg.com</p>
                            <p class="text-muted">info@1mg.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-phone fa-3x text-success"></i>
                            </div>
                            <h5>Call Us</h5>
                            <p class="text-muted mb-0">1800-123-4567</p>
                            <p class="text-muted">Mon-Sat: 9AM - 9PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-map-marker-alt fa-3x text-success"></i>
                            </div>
                            <h5>Visit Us</h5>
                            <p class="text-muted mb-0">123 Healthcare Street</p>
                            <p class="text-muted">Mumbai, Maharashtra 400001</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm" data-aos="fade-up">
                <div class="card-body p-4">
                    <h3 class="mb-4">Send Us a Message</h3>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Your Email *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Subject *</label>
                                <input type="text" name="subject" class="form-control" 
                                       value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="6" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4" data-aos="fade-up">
                <div class="card-body bg-light">
                    <h5 class="mb-3">Frequently Asked Questions</h5>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I place an order?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Simply browse our medicines, add items to your cart, and proceed to checkout. You can pay online or choose cash on delivery.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What is your delivery time?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We deliver within 2-5 business days depending on your location. Express delivery options are also available.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you accept returns?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we have a hassle-free return policy. Contact us within 7 days of delivery for returns or exchanges.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
