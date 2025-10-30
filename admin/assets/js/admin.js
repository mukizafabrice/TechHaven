// TechHaven Admin JavaScript
class TechHavenAdmin {
  constructor() {
    this.init();
  }

  init() {
    this.initializeSidebar();
    this.initializeDataTables();
    this.initializeImageUploads();
    this.initializeModals();
    this.initializeFormValidations();
    this.initializeNotifications();
    this.initializeBulkActions();
    this.initializeSearch();
  }

  // Sidebar functionality
  initializeSidebar() {
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".admin-sidebar");
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    const mobileMenuClose = document.getElementById("mobileMenuClose");

    // Desktop sidebar toggle
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("mini");
        document.querySelector(".admin-main").classList.toggle("expanded");

        // Save preference to localStorage
        const isMini = sidebar.classList.contains("mini");
        localStorage.setItem("adminSidebarMini", isMini);
      });

      // Load saved preference
      const savedPreference = localStorage.getItem("adminSidebarMini");
      if (savedPreference === "true") {
        sidebar.classList.add("mini");
        document.querySelector(".admin-main").classList.add("expanded");
      }
    }

    // Mobile menu toggle
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("click", () => {
        sidebar.classList.add("mobile-open");
      });
    }

    if (mobileMenuClose) {
      mobileMenuClose.addEventListener("click", () => {
        sidebar.classList.remove("mobile-open");
      });
    }

    // Close mobile menu when clicking outside
    document.addEventListener("click", (e) => {
      if (
        !sidebar.contains(e.target) &&
        !mobileMenuToggle?.contains(e.target)
      ) {
        sidebar.classList.remove("mobile-open");
      }
    });
  }

  // Data tables functionality
  initializeDataTables() {
    const tables = document.querySelectorAll(".data-table");

    tables.forEach((table) => {
      this.enhanceTable(table);
    });
  }

  enhanceTable(table) {
    // Add sorting functionality
    const sortableHeaders = table.querySelectorAll("th[data-sort]");

    sortableHeaders.forEach((header) => {
      header.style.cursor = "pointer";
      header.addEventListener("click", () => {
        this.sortTable(
          table,
          header.dataset.sort,
          header.dataset.sortDirection
        );

        // Toggle sort direction
        header.dataset.sortDirection =
          header.dataset.sortDirection === "asc" ? "desc" : "asc";

        // Update sort indicators
        sortableHeaders.forEach((h) =>
          h.classList.remove("sort-asc", "sort-desc")
        );
        header.classList.add(
          header.dataset.sortDirection === "asc" ? "sort-asc" : "sort-desc"
        );
      });
    });

    // Add row selection
    const selectAll = table.querySelector(".select-all");
    const rowCheckboxes = table.querySelectorAll(".row-checkbox");

    if (selectAll && rowCheckboxes.length > 0) {
      selectAll.addEventListener("change", (e) => {
        rowCheckboxes.forEach((checkbox) => {
          checkbox.checked = e.target.checked;
        });
        this.updateBulkActions(table);
      });

      rowCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", () => {
          this.updateBulkActions(table);
        });
      });
    }
  }

  sortTable(table, columnIndex, direction) {
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    rows.sort((a, b) => {
      let aValue = a.cells[columnIndex]?.textContent?.trim() || "";
      let bValue = b.cells[columnIndex]?.textContent?.trim() || "";

      // Handle numeric sorting
      if (!isNaN(aValue) && !isNaN(bValue)) {
        aValue = parseFloat(aValue);
        bValue = parseFloat(bValue);
      }

      if (direction === "asc") {
        return aValue < bValue ? -1 : aValue > bValue ? 1 : 0;
      } else {
        return aValue > bValue ? -1 : aValue < bValue ? 1 : 0;
      }
    });

    // Clear and re-append sorted rows
    tbody.innerHTML = "";
    rows.forEach((row) => tbody.appendChild(row));
  }

  // Image upload functionality
  initializeImageUploads() {
    const uploadAreas = document.querySelectorAll(".image-upload-area");

    uploadAreas.forEach((area) => {
      const input = area.parentNode.querySelector('input[type="file"]');
      const preview = area.parentNode.querySelector(".image-preview");

      if (!input) return;

      // Click to upload
      area.addEventListener("click", () => input.click());

      // Drag and drop
      ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
        area.addEventListener(eventName, this.preventDefaults, false);
      });

      ["dragenter", "dragover"].forEach((eventName) => {
        area.addEventListener(eventName, () => this.highlight(area), false);
      });

      ["dragleave", "drop"].forEach((eventName) => {
        area.addEventListener(eventName, () => this.unhighlight(area), false);
      });

      area.addEventListener(
        "drop",
        (e) => this.handleDrop(e, input, preview),
        false
      );

      input.addEventListener("change", (e) =>
        this.handleFiles(e.target.files, preview, area)
      );
    });
  }

  preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  highlight(area) {
    area.classList.add("dragover");
  }

  unhighlight(area) {
    area.classList.remove("dragover");
  }

  handleDrop(e, input, preview) {
    const dt = e.dataTransfer;
    const files = dt.files;
    input.files = files;
    this.handleFiles(files, preview, e.target);
  }

  handleFiles(files, preview, area) {
    if (files.length > 0) {
      const file = files[0];

      // Validate file type
      if (!file.type.match("image.*")) {
        this.showNotification(
          "Please select an image file (JPG, PNG, GIF, WebP).",
          "error"
        );
        return;
      }

      // Validate file size (5MB)
      if (file.size > 5 * 1024 * 1024) {
        this.showNotification("Image must be less than 5MB.", "error");
        return;
      }

      // Preview image
      const reader = new FileReader();
      reader.onload = (e) => {
        if (preview) {
          preview.src = e.target.result;
          preview.classList.remove("hidden");
        }

        // Update area text
        const textElement = area.querySelector("p");
        if (textElement) {
          textElement.textContent = file.name;
        }
      };
      reader.readAsDataURL(file);
    }
  }

  // Modal functionality
  initializeModals() {
    // Open modal
    document.querySelectorAll("[data-modal-toggle]").forEach((trigger) => {
      trigger.addEventListener("click", () => {
        const modalId = trigger.dataset.modalToggle;
        const modal = document.getElementById(modalId);
        if (modal) {
          this.openModal(modal);
        }
      });
    });

    // Close modal
    document.querySelectorAll("[data-modal-hide]").forEach((close) => {
      close.addEventListener("click", () => {
        const modalId = close.dataset.modalHide;
        const modal = document.getElementById(modalId);
        if (modal) {
          this.closeModal(modal);
        }
      });
    });

    // Close modal when clicking outside
    document.querySelectorAll(".modal-overlay").forEach((overlay) => {
      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
          this.closeModal(overlay);
        }
      });
    });

    // Close modal with Escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        document.querySelectorAll(".modal-overlay").forEach((modal) => {
          this.closeModal(modal);
        });
      }
    });
  }

  openModal(modal) {
    modal.classList.remove("hidden");
    document.body.style.overflow = "hidden";
  }

  closeModal(modal) {
    modal.classList.add("hidden");
    document.body.style.overflow = "";
  }

  // Form validation
  initializeFormValidations() {
    const forms = document.querySelectorAll("form[data-validate]");

    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (!this.validateForm(form)) {
          e.preventDefault();
        }
      });

      // Real-time validation
      const inputs = form.querySelectorAll("input, select, textarea");
      inputs.forEach((input) => {
        input.addEventListener("blur", () => {
          this.validateField(input);
        });

        input.addEventListener("input", () => {
          this.clearFieldError(input);
        });
      });
    });
  }

  validateForm(form) {
    let isValid = true;
    const fields = form.querySelectorAll("[required], [data-validate]");

    fields.forEach((field) => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  validateField(field) {
    let isValid = true;
    const value = field.value.trim();

    this.clearFieldError(field);

    // Required validation
    if (field.hasAttribute("required") && !value) {
      this.markFieldError(field, "This field is required.");
      isValid = false;
    }

    // Email validation
    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        this.markFieldError(field, "Please enter a valid email address.");
        isValid = false;
      }
    }

    // Number validation
    if (field.type === "number" && value) {
      if (
        field.hasAttribute("min") &&
        parseFloat(value) < parseFloat(field.min)
      ) {
        this.markFieldError(field, `Value must be at least ${field.min}.`);
        isValid = false;
      }
      if (
        field.hasAttribute("max") &&
        parseFloat(value) > parseFloat(field.max)
      ) {
        this.markFieldError(field, `Value must be at most ${field.max}.`);
        isValid = false;
      }
    }

    // URL validation
    if (field.type === "url" && value) {
      try {
        new URL(value);
      } catch {
        this.markFieldError(field, "Please enter a valid URL.");
        isValid = false;
      }
    }

    // Custom validation
    if (field.dataset.validate === "slug" && value) {
      const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
      if (!slugRegex.test(value)) {
        this.markFieldError(
          field,
          "Slug can only contain lowercase letters, numbers, and hyphens."
        );
        isValid = false;
      }
    }

    if (isValid) {
      this.markFieldSuccess(field);
    }

    return isValid;
  }

  markFieldError(field, message) {
    field.classList.add("error");
    field.classList.remove("success");

    let errorElement = field.parentNode.querySelector(".field-error");
    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "field-error text-sm text-red-600 mt-1";
      field.parentNode.appendChild(errorElement);
    }
    errorElement.textContent = message;
  }

  markFieldSuccess(field) {
    field.classList.add("success");
    field.classList.remove("error");
  }

  clearFieldError(field) {
    field.classList.remove("error", "success");
    const errorElement = field.parentNode.querySelector(".field-error");
    if (errorElement) {
      errorElement.remove();
    }
  }

  // Notification system
  initializeNotifications() {
    // Auto-remove notifications after 5 seconds
    document.querySelectorAll(".alert").forEach((alert) => {
      setTimeout(() => {
        if (alert.parentNode) {
          alert.style.opacity = "0";
          setTimeout(() => {
            if (alert.parentNode) {
              alert.parentNode.removeChild(alert);
            }
          }, 300);
        }
      }, 5000);
    });
  }

  showNotification(message, type = "info", duration = 5000) {
    const notification = document.createElement("div");
    notification.className = `alert alert-${type} fixed top-4 right-4 z-50 max-w-sm`;
    notification.innerHTML = `
            <div class="alert-icon">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
            </div>
            <div class="alert-content">
                <div class="alert-message">${message}</div>
            </div>
            <button class="alert-close text-lg opacity-70 hover:opacity-100" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

    document.body.appendChild(notification);

    // Auto remove
    if (duration > 0) {
      setTimeout(() => {
        if (notification.parentNode) {
          notification.style.opacity = "0";
          setTimeout(() => {
            if (notification.parentNode) {
              notification.parentNode.removeChild(notification);
            }
          }, 300);
        }
      }, duration);
    }

    return notification;
  }

  getNotificationIcon(type) {
    const icons = {
      success: "check-circle",
      error: "exclamation-circle",
      warning: "exclamation-triangle",
      info: "info-circle",
    };
    return icons[type] || "info-circle";
  }

  // Bulk actions
  initializeBulkActions() {
    const bulkForms = document.querySelectorAll(".bulk-action-form");

    bulkForms.forEach((form) => {
      const bulkAction = form.querySelector(".bulk-action");
      const applyButton = form.querySelector(".bulk-apply");

      if (applyButton) {
        applyButton.addEventListener("click", (e) => {
          const selectedItems = form.querySelectorAll(".row-checkbox:checked");

          if (selectedItems.length === 0) {
            e.preventDefault();
            this.showNotification(
              "Please select at least one item.",
              "warning"
            );
            return;
          }

          if (!bulkAction.value) {
            e.preventDefault();
            this.showNotification("Please select an action.", "warning");
            return;
          }

          if (
            !confirm(
              `Are you sure you want to ${bulkAction.value} ${selectedItems.length} item(s)?`
            )
          ) {
            e.preventDefault();
          }
        });
      }
    });
  }

  updateBulkActions(table) {
    const selectedCount = table.querySelectorAll(
      ".row-checkbox:checked"
    ).length;
    const bulkAction = table.querySelector(".bulk-action");
    const applyButton = table.querySelector(".bulk-apply");

    if (bulkAction && applyButton) {
      if (selectedCount === 0) {
        bulkAction.disabled = true;
        applyButton.disabled = true;
      } else {
        bulkAction.disabled = false;
        applyButton.disabled = false;
      }
    }
  }

  // Search functionality
  initializeSearch() {
    const searchInputs = document.querySelectorAll(".table-search");

    searchInputs.forEach((input) => {
      input.addEventListener("input", (e) => {
        this.filterTable(e.target);
      });
    });
  }

  filterTable(searchInput) {
    const table = searchInput
      .closest(".bulk-action-form")
      ?.querySelector(".data-table");
    if (!table) return;

    const searchTerm = searchInput.value.toLowerCase();
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row) => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? "" : "none";
    });
  }

  // Utility methods
  formatPrice(price) {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
    }).format(price);
  }

  formatDate(dateString) {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  }

  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  window.TechHavenAdmin = new TechHavenAdmin();
});

// Export for global use
if (typeof module !== "undefined" && module.exports) {
  module.exports = TechHavenAdmin;
}
