            </div>
            </main>
            </div>

            <!-- JavaScript -->
            <script>
                // Mobile menu toggle
                document.getElementById('mobileMenuButton').addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('active');
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    const sidebar = document.querySelector('.sidebar');
                    const mobileButton = document.getElementById('mobileMenuButton');

                    if (window.innerWidth <= 768 &&
                        !sidebar.contains(e.target) &&
                        !mobileButton.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                });

                // Confirm delete actions
                document.addEventListener('DOMContentLoaded', function() {
                    const deleteButtons = document.querySelectorAll('.delete-btn');
                    deleteButtons.forEach(button => {
                        button.addEventListener('click', function(e) {
                            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                                e.preventDefault();
                            }
                        });
                    });
                });

                // Image preview for file inputs
                function previewImage(input, previewId) {
                    const preview = document.getElementById(previewId);
                    const file = input.files[0];
                    const reader = new FileReader();

                    reader.onloadend = function() {
                        preview.src = reader.result;
                        preview.classList.remove('hidden');
                    }

                    if (file) {
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '';
                        preview.classList.add('hidden');
                    }
                }

                // Form validation
                function validateForm(form) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;

                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('border-red-500');
                            isValid = false;
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });

                    return isValid;
                }
            </script>
            </body>

            </html>