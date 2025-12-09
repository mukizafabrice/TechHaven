    </main>

    <!-- Footer -->
    <footer class="mt-16 text-white bg-gray-800">
        <div class="container px-4 py-12 mx-auto">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="mb-4 text-2xl font-bold">Wima Store</h3>
                    <p class="mb-4 text-gray-300">
                        Your trusted partner for cutting-edge electronics and technology products.
                        We bring you the latest gadgets with competitive prices and excellent service.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 transition duration-300 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-300 transition duration-300 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-300 transition duration-300 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-300 transition duration-300 hover:text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-300 transition duration-300 hover:text-white">Home</a></li>
                        <li><a href="products.php" class="text-gray-300 transition duration-300 hover:text-white">All Products</a></li>
                        <li><a href="categories.php" class="text-gray-300 transition duration-300 hover:text-white">Categories</a></li>
                        <li><a href="contact.php" class="text-gray-300 transition duration-300 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="mb-4 text-lg font-semibold">Contact Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Makuza Peace Plaza - KN 48 St, Kigali, Rwanda</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-phone"></i>
                            <span>+250780088390</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-envelope"></i>
                            <span>info@wimastore.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="flex flex-col items-center justify-between pt-8 mt-8 border-t border-gray-700 md:flex-row">
                <p class="text-sm text-gray-300">
                    &copy; <?= date('Y') ?> Wima Store. All rights reserved.
                </p>
                <div class="flex mt-4 space-x-6 md:mt-0">
                    <a href="privacy-policy.php" class="text-sm text-gray-300 transition duration-300 hover:text-white">Privacy Policy</a>
                    <a href="terms-of-service.php" class="text-sm text-gray-300 transition duration-300 hover:text-white">Terms of Service</a>
                    <a href="returns-policy.php" class="text-sm text-gray-300 transition duration-300 hover:text-white">Returns Policy</a>
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