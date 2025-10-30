// TechHaven Main JavaScript

document.addEventListener("DOMContentLoaded", function () {
  initializeApp();
});

function initializeApp() {
  initializeMobileMenu();
  initializeBackToTop();
  initializeImageZoom();
  initializeProductFilters();
  initializeSearch();
  initializeWhatsAppButtons();
  initializeFormValidations();
  initializeLazyLoading();
}

// Mobile Menu Functionality
function initializeMobileMenu() {
  const mobileMenuButton = document.getElementById("mobileMenuButton");
  const mobileMenu = document.getElementById("mobileMenu");
  const mobileMenuClose = document.getElementById("mobileMenuClose");

  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener("click", function () {
      mobileMenu.classList.toggle("active");
      document.body.style.overflow = mobileMenu.classList.contains("active")
        ? "hidden"
        : "";
    });
  }

  if (mobileMenuClose) {
    mobileMenuClose.addEventListener("click", function () {
      mobileMenu.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  // Close mobile menu when clicking on a link
  const mobileMenuLinks = document.querySelectorAll("#mobileMenu a");
  mobileMenuLinks.forEach((link) => {
    link.addEventListener("click", function () {
      mobileMenu.classList.remove("active");
      document.body.style.overflow = "";
    });
  });
}

// Back to Top Button
function initializeBackToTop() {
  const backToTopButton = document.createElement("button");
  backToTopButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
  backToTopButton.className = "back-to-top no-print";
  backToTopButton.setAttribute("aria-label", "Back to top");
  document.body.appendChild(backToTopButton);

  window.addEventListener("scroll", function () {
    if (window.pageYOffset > 300) {
      backToTopButton.classList.add("visible");
    } else {
      backToTopButton.classList.remove("visible");
    }
  });

  backToTopButton.addEventListener("click", function () {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
}

// Image Zoom Functionality
function initializeImageZoom() {
  const zoomableImages = document.querySelectorAll(".image-zoom");

  zoomableImages.forEach((img) => {
    img.addEventListener("click", function () {
      this.classList.toggle("zoomed");
    });
  });
}

// Product Filters
function initializeProductFilters() {
  const filterForms = document.querySelectorAll(".filter-form");

  filterForms.forEach((form) => {
    const inputs = form.querySelectorAll("input, select");

    inputs.forEach((input) => {
      input.addEventListener("change", function () {
        // Add loading state
        form.classList.add("loading");

        // Submit form after a short delay to show loading state
        setTimeout(() => {
          form.submit();
        }, 300);
      });
    });
  });
}

// Search Functionality
function initializeSearch() {
  const searchForm = document.querySelector(".search-form");
  const searchInput = document.querySelector(".search-input");
  const searchResults = document.querySelector(".search-results");

  if (searchInput && searchResults) {
    let searchTimeout;

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);
      const query = this.value.trim();

      if (query.length < 2) {
        searchResults.classList.add("hidden");
        return;
      }

      searchTimeout = setTimeout(() => {
        performSearch(query);
      }, 500);
    });

    // Close results when clicking outside
    document.addEventListener("click", function (e) {
      if (!searchForm.contains(e.target)) {
        searchResults.classList.add("hidden");
      }
    });
  }
}

// Perform AJAX Search
async function performSearch(query) {
  try {
    const response = await fetch(
      `/api/search.php?q=${encodeURIComponent(query)}`
    );
    const data = await response.json();

    displaySearchResults(data);
  } catch (error) {
    console.error("Search error:", error);
  }
}

// Display Search Results
function displaySearchResults(results) {
  const searchResults = document.querySelector(".search-results");

  if (results.length === 0) {
    searchResults.innerHTML =
      '<div class="p-4 text-gray-500">No products found</div>';
  } else {
    let html = "";
    results.forEach((product) => {
      html += `
                <a href="/product-detail.php?slug=${
                  product.slug
                }" class="flex items-center p-3 hover:bg-gray-50 transition duration-200">
                    <img src="/assets/uploads/products/${
                      product.featured_image
                    }" alt="${
        product.name
      }" class="w-12 h-12 object-cover rounded">
                    <div class="ml-3">
                        <div class="font-medium text-gray-900">${
                          product.name
                        }</div>
                        <div class="text-sm text-gray-500">${formatPrice(
                          product.price
                        )}</div>
                    </div>
                </a>
            `;
    });
    searchResults.innerHTML = html;
  }

  searchResults.classList.remove("hidden");
}

// WhatsApp Integration
function initializeWhatsAppButtons() {
  const whatsappButtons = document.querySelectorAll(".whatsapp-button");

  whatsappButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const productSlug = this.dataset.slug;
      const productName = this.dataset.name;

      shareOnWhatsApp(productSlug, productName);
    });
  });
}

// Enhanced WhatsApp Share Function
function shareOnWhatsApp(productSlug, productName) {
  const message = `Hi, I'm interested in: ${productName}\n\nProduct Link: ${window.location.origin}/product-detail.php?slug=${productSlug}\n\nPlease provide more details about availability and pricing.`;
  const encodedMessage = encodeURIComponent(message);
  const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;

  // Open in new window with proper dimensions
  const width = 600;
  const height = 700;
  const left = (window.innerWidth - width) / 2;
  const top = (window.innerHeight - height) / 2;

  window.open(
    whatsappUrl,
    "whatsapp-share",
    `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
  );
}

// Form Validations
function initializeFormValidations() {
  const forms = document.querySelectorAll("form[data-validate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!validateForm(this)) {
        e.preventDefault();
      }
    });
  });
}

// Form Validation Function
function validateForm(form) {
  let isValid = true;
  const requiredFields = form.querySelectorAll("[required]");

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      markFieldInvalid(field, "This field is required");
      isValid = false;
    } else {
      markFieldValid(field);
    }

    // Email validation
    if (field.type === "email" && field.value.trim()) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(field.value)) {
        markFieldInvalid(field, "Please enter a valid email address");
        isValid = false;
      }
    }

    // Phone validation
    if (field.type === "tel" && field.value.trim()) {
      const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
      if (!phoneRegex.test(field.value.replace(/[\s\-\(\)]/g, ""))) {
        markFieldInvalid(field, "Please enter a valid phone number");
        isValid = false;
      }
    }
  });

  return isValid;
}

// Mark field as invalid
function markFieldInvalid(field, message) {
  field.classList.add("error-border");

  // Remove existing error message
  const existingError = field.parentNode.querySelector(".error-text");
  if (existingError) {
    existingError.remove();
  }

  // Add error message
  const errorElement = document.createElement("div");
  errorElement.className = "error-text";
  errorElement.textContent = message;
  field.parentNode.appendChild(errorElement);
}

// Mark field as valid
function markFieldValid(field) {
  field.classList.remove("error-border");

  // Remove error message
  const errorElement = field.parentNode.querySelector(".error-text");
  if (errorElement) {
    errorElement.remove();
  }
}

// Lazy Loading for Images
function initializeLazyLoading() {
  if ("IntersectionObserver" in window) {
    const lazyImages = document.querySelectorAll("img[data-src]");

    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove("lazy");
          imageObserver.unobserve(img);
        }
      });
    });

    lazyImages.forEach((img) => imageObserver.observe(img));
  } else {
    // Fallback for older browsers
    const lazyImages = document.querySelectorAll("img[data-src]");
    lazyImages.forEach((img) => {
      img.src = img.dataset.src;
    });
  }
}

// Price Formatting Utility
function formatPrice(price) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(price);
}

// Stock Status Check
function checkStockStatus(productId) {
  return fetch(`/api/stock.php?id=${productId}`)
    .then((response) => response.json())
    .then((data) => data.stock)
    .catch((error) => {
      console.error("Stock check error:", error);
      return 0;
    });
}

// Add to Cart Functionality (if implemented later)
function addToCart(productId, quantity = 1) {
  const cart = getCart();
  const existingItem = cart.find((item) => item.id === productId);

  if (existingItem) {
    existingItem.quantity += quantity;
  } else {
    cart.push({
      id: productId,
      quantity: quantity,
      addedAt: new Date().toISOString(),
    });
  }

  saveCart(cart);
  updateCartCounter();
  showNotification("Product added to cart", "success");
}

// Cart Management
function getCart() {
  return JSON.parse(localStorage.getItem("techhaven_cart") || "[]");
}

function saveCart(cart) {
  localStorage.setItem("techhaven_cart", JSON.stringify(cart));
}

function updateCartCounter() {
  const cartCounter = document.querySelector(".cart-counter");
  if (cartCounter) {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCounter.textContent = totalItems;
    cartCounter.style.display = totalItems > 0 ? "flex" : "none";
  }
}

// Notification System
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-transform duration-300 ${
    type === "success"
      ? "bg-green-500 text-white"
      : type === "error"
      ? "bg-red-500 text-white"
      : type === "warning"
      ? "bg-yellow-500 text-black"
      : "bg-blue-500 text-white"
  }`;
  notification.textContent = message;

  document.body.appendChild(notification);

  // Animate in
  setTimeout(() => {
    notification.classList.add("translate-x-0");
  }, 10);

  // Remove after 5 seconds
  setTimeout(() => {
    notification.classList.remove("translate-x-0");
    notification.classList.add("translate-x-full");

    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 5000);
}

// Product Comparison
function addToComparison(productId) {
  const comparison = getComparison();

  if (comparison.includes(productId)) {
    showNotification("Product already in comparison", "warning");
    return;
  }

  if (comparison.length >= 4) {
    showNotification("Maximum 4 products can be compared", "warning");
    return;
  }

  comparison.push(productId);
  saveComparison(comparison);
  showNotification("Product added to comparison", "success");
}

function getComparison() {
  return JSON.parse(localStorage.getItem("techhaven_comparison") || "[]");
}

function saveComparison(comparison) {
  localStorage.setItem("techhaven_comparison", JSON.stringify(comparison));
}

// Wishlist Functionality
function toggleWishlist(productId) {
  const wishlist = getWishlist();
  const index = wishlist.indexOf(productId);

  if (index > -1) {
    wishlist.splice(index, 1);
    showNotification("Product removed from wishlist", "info");
  } else {
    wishlist.push(productId);
    showNotification("Product added to wishlist", "success");
  }

  saveWishlist(wishlist);
  updateWishlistButton(productId, index === -1);
}

function getWishlist() {
  return JSON.parse(localStorage.getItem("techhaven_wishlist") || "[]");
}

function saveWishlist(wishlist) {
  localStorage.setItem("techhaven_wishlist", JSON.stringify(wishlist));
}

function updateWishlistButton(productId, isInWishlist) {
  const button = document.querySelector(
    `[data-product-id="${productId}"] .wishlist-icon`
  );
  if (button) {
    if (isInWishlist) {
      button.classList.remove("far");
      button.classList.add("fas", "text-red-500");
    } else {
      button.classList.remove("fas", "text-red-500");
      button.classList.add("far");
    }
  }
}

// Export functions for global use
window.TechHaven = {
  addToCart,
  addToComparison,
  toggleWishlist,
  shareOnWhatsApp,
  showNotification,
  formatPrice,
};
