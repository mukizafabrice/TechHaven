// Advanced Search Functionality
class AdvancedSearch {
  constructor() {
    this.init();
  }

  init() {
    this.initializeSearchFilters();
    this.initializePriceRange();
    this.initializeCategoryFilters();
    this.initializeSorting();
  }

  initializeSearchFilters() {
    const filterToggles = document.querySelectorAll(".filter-toggle");

    filterToggles.forEach((toggle) => {
      toggle.addEventListener("click", function () {
        const filterSection = this.closest(".filter-section");
        filterSection.classList.toggle("active");
      });
    });
  }

  initializePriceRange() {
    const priceRange = document.getElementById("priceRange");
    const priceMin = document.getElementById("priceMin");
    const priceMax = document.getElementById("priceMax");
    const priceDisplay = document.getElementById("priceDisplay");

    if (priceRange && priceDisplay) {
      priceRange.addEventListener("input", function () {
        const value = this.value;
        priceDisplay.textContent = `Up to $${value}`;

        if (priceMax) {
          priceMax.value = value;
        }
      });
    }

    // Sync min and max inputs
    if (priceMin && priceMax) {
      [priceMin, priceMax].forEach((input) => {
        input.addEventListener("change", function () {
          this.dispatchEvent(new Event("input"));
        });
      });
    }
  }

  initializeCategoryFilters() {
    const categoryCheckboxes = document.querySelectorAll(".category-checkbox");

    categoryCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", this.updateSearchResults.bind(this));
    });
  }

  initializeSorting() {
    const sortSelect = document.querySelector(".sort-select");

    if (sortSelect) {
      sortSelect.addEventListener(
        "change",
        this.updateSearchResults.bind(this)
      );
    }
  }

  updateSearchResults() {
    const form = document.querySelector(".search-filters-form");
    if (form) {
      // Add loading state
      form.classList.add("loading");

      // Submit form via AJAX
      const formData = new FormData(form);

      fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          this.displaySearchResults(data);
        })
        .catch((error) => {
          console.error("Search update error:", error);
        })
        .finally(() => {
          form.classList.remove("loading");
        });
    }
  }

  displaySearchResults(data) {
    const resultsContainer = document.querySelector(
      ".search-results-container"
    );
    if (resultsContainer && data.html) {
      resultsContainer.innerHTML = data.html;
    }
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  new AdvancedSearch();
});
