<?php
$page_title = "Contact Us - Get in Touch with TechHaven";
include '../includes/header.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_POST) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);

    // Basic validation
    $errors = [];

    if (empty($name)) {
        $errors[] = "Please enter your name.";
    }

    if (empty($email)) {
        $errors[] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($subject)) {
        $errors[] = "Please enter a subject.";
    }

    if (empty($message)) {
        $errors[] = "Please enter your message.";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message should be at least 10 characters long.";
    }

    if (empty($errors)) {
        // In a real application, you would:
        // 1. Send an email
        // 2. Save to database
        // 3. Send notification to admin

        // For now, we'll just show a success message
        $success_message = "Thank you for your message, $name! We'll get back to you within 24 hours.";

        // Clear form
        $_POST = [];
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!-- Hero Section -->
<section class="py-16 text-white bg-gradient-to-r from-blue-600 to-purple-700">
    <div class="container px-4 mx-auto text-center">
        <h1 class="mb-4 text-4xl font-bold">Contact Us</h1>
        <p class="max-w-2xl mx-auto text-xl">Have questions? We're here to help! Get in touch with our team.</p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 bg-white">
    <div class="container px-4 mx-auto">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
            <!-- Contact Information -->
            <div class="lg:col-span-1">
                <div class="sticky p-8 bg-gray-50 rounded-2xl top-8">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900">Get in Touch</h2>

                    <div class="space-y-6">
                        <!-- Address -->
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl">
                                <i class="text-lg text-blue-600 fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Our Office</h3>
                                <p class="mt-1 text-gray-600">
                                    Makuza Peace Plaza<br>
                                    KN 48 St<br>
                                    Kigali, Rwanda
                                </p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-green-100 rounded-xl">
                                <i class="text-lg text-green-600 fas fa-phone"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Phone Number</h3>
                                <p class="mt-1 text-gray-600">
                                    +250 (783) 818-521<br>
                                    <span class="text-sm text-gray-500">Mon-Fri from 9am to 6pm</span>
                                </p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-purple-100 rounded-xl">
                                <i class="text-lg text-purple-600 fas fa-envelope"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Email Address</h3>
                                <p class="mt-1 text-gray-600">
                                    info@techhaven.com<br>
                                    support@techhaven.com
                                </p>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div class="flex items-start space-x-4">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-orange-100 rounded-xl">
                                <i class="text-lg text-orange-600 fas fa-clock"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Business Hours</h3>
                                <p class="mt-1 text-gray-600">
                                    Monday - Friday: 9:00 AM - 6:00 PM<br>
                                    Saturday: 10:00 AM - 4:00 PM<br>
                                    Sunday: Closed
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="pt-6 mt-8 border-t border-gray-200">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Follow Us</h3>
                        <div class="flex space-x-3">
                            <a href="#" class="flex items-center justify-center w-10 h-10 text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="flex items-center justify-center w-10 h-10 text-white transition duration-300 bg-blue-400 rounded-lg hover:bg-blue-500">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="flex items-center justify-center w-10 h-10 text-white transition duration-300 bg-pink-600 rounded-lg hover:bg-pink-700">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="flex items-center justify-center w-10 h-10 text-white transition duration-300 bg-blue-700 rounded-lg hover:bg-blue-800">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="p-8 bg-white border border-gray-200 rounded-2xl">
                    <h2 class="mb-2 text-2xl font-bold text-gray-900">Send us a Message</h2>
                    <p class="mb-8 text-gray-600">Fill out the form below and we'll get back to you as soon as possible.</p>

                    <!-- Notifications -->
                    <?php if ($success_message): ?>
                        <div class="flex items-start px-6 py-4 mb-6 text-green-700 border border-green-200 bg-green-50 rounded-xl">
                            <i class="fas fa-check-circle text-green-500 text-lg mt-0.5 mr-3"></i>
                            <div>
                                <p class="font-medium">Message Sent Successfully!</p>
                                <p class="mt-1 text-sm"><?= $success_message ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="flex items-start px-6 py-4 mb-6 text-red-700 border border-red-200 bg-red-50 rounded-xl">
                            <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5 mr-3"></i>
                            <div>
                                <p class="font-medium">Please fix the following errors:</p>
                                <p class="mt-1 text-sm"><?= $error_message ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" id="name" name="name" required
                                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                    class="w-full px-4 py-3 transition duration-300 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter your full name">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    class="w-full px-4 py-3 transition duration-300 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter your email address">
                            </div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block mb-2 text-sm font-medium text-gray-700">Subject *</label>
                            <input type="text" id="subject" name="subject" required
                                value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                                class="w-full px-4 py-3 transition duration-300 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="What is this regarding?">
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block mb-2 text-sm font-medium text-gray-700">Message *</label>
                            <textarea id="message" name="message" required rows="6"
                                class="w-full px-4 py-3 transition duration-300 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Tell us how we can help you..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            <p class="mt-2 text-sm text-gray-500">Minimum 10 characters required</p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full px-6 py-4 text-lg font-semibold text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="mr-2 fas fa-paper-plane"></i> Send Message
                        </button>

                        <p class="text-sm text-center text-gray-500">
                            We typically respond within 24 hours during business days.
                        </p>
                    </form>
                </div>

                <!-- FAQ Section -->
                <div class="mt-12">
                    <h2 class="mb-8 text-2xl font-bold text-center text-gray-900">Frequently Asked Questions</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- FAQ Item 1 -->
                        <div class="p-6 bg-gray-50 rounded-xl">
                            <h3 class="flex items-center mb-2 text-lg font-semibold text-gray-900">
                                <i class="mr-3 text-blue-600 fas fa-shipping-fast"></i>
                                Do you offer international shipping?
                            </h3>
                            <p class="text-gray-600">Currently, we only ship within the United States. We're working on expanding our international shipping options in the near future.</p>
                        </div>

                        <!-- FAQ Item 2 -->
                        <div class="p-6 bg-gray-50 rounded-xl">
                            <h3 class="flex items-center mb-2 text-lg font-semibold text-gray-900">
                                <i class="mr-3 text-green-600 fas fa-undo-alt"></i>
                                What is your return policy?
                            </h3>
                            <p class="text-gray-600">We offer a 30-day return policy for all products. Items must be in original condition with all accessories and packaging included.</p>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div class="p-6 bg-gray-50 rounded-xl">
                            <h3 class="flex items-center mb-2 text-lg font-semibold text-gray-900">
                                <i class="mr-3 text-purple-600 fas fa-truck"></i>
                                How long does shipping take?
                            </h3>
                            <p class="text-gray-600">Standard shipping takes 3-5 business days. Express shipping is available for delivery within 1-2 business days for an additional fee.</p>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div class="p-6 bg-gray-50 rounded-xl">
                            <h3 class="flex items-center mb-2 text-lg font-semibold text-gray-900">
                                <i class="mr-3 text-orange-600 fas fa-headset"></i>
                                Do you offer technical support?
                            </h3>
                            <p class="text-gray-600">Yes! We provide free technical support for all products purchased from TechHaven. Contact us via phone or email for assistance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-16 bg-gray-50">
    <div class="container px-4 mx-auto">
        <h2 class="mb-8 text-2xl font-bold text-center text-gray-900">Find Our Store</h2>
        <div class="p-8 bg-white border border-gray-200 rounded-2xl">
            <!-- Google Maps Embed for Makuza Peace Plaza -->
            <div class="overflow-hidden rounded-xl">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.738348996089!2d30.0569905!3d-1.9465656!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca4240db7b3f5%3A0x5256fd511623ef15!2sMakuza%20Peace%20Plaza!5e0!3m2!1sen!2srw!4v1730549659051!5m2!1sen!2srw"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="shadow-md rounded-xl">
                </iframe>
            </div>

            <!-- Location Info -->
            <div class="max-w-md p-6 mx-auto mt-8 text-center rounded-lg bg-blue-50">
                <i class="mb-4 text-4xl text-blue-600 fas fa-map-marker-alt"></i>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">Our Location</h3>
                <p class="text-gray-700">
                    Makuza Peace Plaza,<br>
                    KN 4 Ave, Kigali, Rwanda
                </p>
            </div>
        </div>
    </div>
</section>


<!-- Quick Contact Banner -->
<section class="py-12 text-white bg-blue-600">
    <div class="container px-4 mx-auto text-center">
        <h2 class="mb-4 text-2xl font-bold">Still Have Questions?</h2>
        <p class="max-w-2xl mx-auto mb-6 text-xl">We're always happy to help! Reach out to us through any of these methods.</p>
        <div class="flex flex-col items-center justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-6">
            <a href="tel:+15551234567" class="px-6 py-3 font-semibold text-blue-600 transition duration-300 bg-white rounded-lg hover:bg-gray-100">
                <i class="mr-2 fas fa-phone"></i> Call Now
            </a>
            <a href="mailto:info@techhaven.com" class="px-6 py-3 font-semibold text-white transition duration-300 bg-transparent border-2 border-white rounded-lg hover:bg-white hover:text-blue-600">
                <i class="mr-2 fas fa-envelope"></i> Email Us
            </a>
            <a href="https://wa.me/15551234567" target="_blank" class="px-6 py-3 font-semibold text-white transition duration-300 bg-green-500 rounded-lg hover:bg-green-600">
                <i class="mr-2 fab fa-whatsapp"></i> WhatsApp
            </a>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation enhancement
        const contactForm = document.querySelector('form');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const subjectInput = document.getElementById('subject');
        const messageInput = document.getElementById('message');

        // Real-time validation
        const inputs = [nameInput, emailInput, subjectInput, messageInput];

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });

        function validateField(field) {
            const value = field.value.trim();
            clearFieldError(field);

            if (field.hasAttribute('required') && !value) {
                markFieldError(field, 'This field is required.');
                return false;
            }

            if (field.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    markFieldError(field, 'Please enter a valid email address.');
                    return false;
                }
            }

            if (field.id === 'message' && value.length < 10) {
                markFieldError(field, 'Message must be at least 10 characters long.');
                return false;
            }

            markFieldSuccess(field);
            return true;
        }

        function markFieldError(field, message) {
            field.classList.add('border-red-500');
            field.classList.remove('border-green-500');

            let errorElement = field.parentNode.querySelector('.field-error');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'field-error text-sm text-red-600 mt-1';
                field.parentNode.appendChild(errorElement);
            }
            errorElement.textContent = message;
        }

        function markFieldSuccess(field) {
            field.classList.remove('border-red-500');
            field.classList.add('border-green-500');

            const errorElement = field.parentNode.querySelector('.field-error');
            if (errorElement) {
                errorElement.remove();
            }
        }

        function clearFieldError(field) {
            field.classList.remove('border-red-500', 'border-green-500');
            const errorElement = field.parentNode.querySelector('.field-error');
            if (errorElement) {
                errorElement.remove();
            }
        }

        // Character counter for message
        messageInput.addEventListener('input', function() {
            const charCount = this.value.length;
            const minChars = 10;

            const counter = document.getElementById('charCounter') || createCharCounter();
            counter.textContent = `${charCount} characters (minimum ${minChars})`;

            if (charCount < minChars) {
                counter.classList.add('text-red-600');
                counter.classList.remove('text-green-600');
            } else {
                counter.classList.remove('text-red-600');
                counter.classList.add('text-green-600');
            }
        });

        function createCharCounter() {
            const counter = document.createElement('div');
            counter.id = 'charCounter';
            counter.className = 'text-sm text-gray-500 mt-1';
            messageInput.parentNode.appendChild(counter);
            return counter;
        }

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
</script>

<style>
    /* Custom styles for contact page */
    .field-error {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Smooth scrolling for anchor links */
    html {
        scroll-behavior: smooth;
    }

    /* Enhanced focus styles */
    input:focus,
    textarea:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
</style>

<?php include '../includes/footer.php'; ?>