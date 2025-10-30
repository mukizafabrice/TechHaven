    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4">TechHaven</h3>
                    <p class="text-gray-300 mb-4">
                        Your trusted partner for cutting-edge electronics and technology products.
                        We bring you the latest gadgets with competitive prices and excellent service.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-300 hover:text-white transition duration-300">Home</a></li>
                        <li><a href="products.php" class="text-gray-300 hover:text-white transition duration-300">All Products</a></li>
                        <li><a href="categories.php" class="text-gray-300 hover:text-white transition duration-300">Categories</a></li>
                        <li><a href="contact.php" class="text-gray-300 hover:text-white transition duration-300">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Tech Street, Digital City</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-phone"></i>
                            <span>+1 (555) 123-4567</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-envelope"></i>
                            <span>info@techhaven.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-300 text-sm">
                    &copy; <?= date('Y') ?> TechHaven. All rights reserved.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-300 hover:text-white text-sm transition duration-300">Privacy Policy</a>
                    <a href="#" class="text-gray-300 hover:text-white text-sm transition duration-300">Terms of Service</a>
                    <a href="#" class="text-gray-300 hover:text-white text-sm transition duration-300">Returns Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('form[action="search.php"]');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const searchInput = this.querySelector('input[name="q"]');
                    if (!searchInput.value.trim()) {
                        e.preventDefault();
                        searchInput.focus();
                    }
                });
            }

            // Dropdown menus
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('mouseenter', function() {
                    this.querySelector('.dropdown-menu').classList.remove('hidden');
                });
                dropdown.addEventListener('mouseleave', function() {
                    this.querySelector('.dropdown-menu').classList.add('hidden');
                });
            });
        });

        // WhatsApp share function
        function shareOnWhatsApp(productSlug, productName) {
            const message = `Hi, I'm interested in: ${productName} - ${window.location.origin}/public/product-detail.php?slug=${productSlug}`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
    </body>

    </html>