// TechHaven Admin JavaScript

document.addEventListener("DOMContentLoaded", function () {
  initializeAdminApp();
});

function initializeAdminApp() {
  initializeAdminSidebar();
  initializeDataTables();
  initializeImageUploads();
  initializeFormValidations();
  initializeModalHandlers();
  initializeChartjs();
  initializeDeleteConfirmations();
  initializeRichTextEditor();
}

// Admin Sidebar Toggle
function initializeAdminSidebar() {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("sidebar-mini");

      // Save preference to localStorage
      const isMini = sidebar.classList.contains("sidebar-mini");
      localStorage.setItem("adminSidebarMini", isMini);
    });

    // Load saved preference
    const savedPreference = localStorage.getItem("adminSidebarMini");
    if (savedPreference === "true") {
      sidebar.classList.add("sidebar-mini");
    }
  }
}

// Data Tables Initialization
function initializeDataTables() {
  const dataTables = document.querySelectorAll(".data-table");

  dataTables.forEach((table) => {
    // Simple client-side sorting and searching
    const headers = table.querySelectorAll("th[data-sort]");

    headers.forEach((header) => {
      header.style.cursor = "pointer";
      header.addEventListener("click", function () {
        sortTable(table, this.dataset.sort, this.dataset.sortDirection);

        // Toggle sort direction
        this.dataset.sortDirection =
          this.dataset.sortDirection === "asc" ? "desc" : "asc";

        // Update sort indicators
        headers.forEach((h) => h.classList.remove("sort-asc", "sort-desc"));
        this.classList.add(
          this.dataset.sortDirection === "asc" ? "sort-asc" : "sort-desc"
        );
      });
    });

    // Search functionality
    const searchInput = table.parentNode.querySelector(".table-search");
    if (searchInput) {
      searchInput.addEventListener("input", function () {
        filterTable(table, this.value);
      });
    }
  });
}

// Sort Table Function
function sortTable(table, columnIndex, direction) {
  const tbody = table.querySelector("tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));

  rows.sort((a, b) => {
    const aValue = a.cells[columnIndex].textContent.trim();
    const bValue = b.cells[columnIndex].textContent.trim();

    if (direction === "asc") {
      return aValue.localeCompare(bValue, undefined, { numeric: true });
    } else {
      return bValue.localeCompare(aValue, undefined, { numeric: true });
    }
  });

  // Clear and re-append sorted rows
  rows.forEach((row) => tbody.appendChild(row));
}

// Filter Table Function
function filterTable(table, searchTerm) {
  const rows = table.querySelectorAll("tbody tr");
  const lowerSearchTerm = searchTerm.toLowerCase();

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(lowerSearchTerm) ? "" : "none";
  });
}

// Image Upload Handling
function initializeImageUploads() {
  const imageUploads = document.querySelectorAll(".image-upload");

  imageUploads.forEach((upload) => {
    const input = upload.querySelector('input[type="file"]');
    const preview = upload.querySelector(".image-preview");
    const dropZone = upload.querySelector(".image-upload-area");

    if (input && dropZone) {
      // Click to upload
      dropZone.addEventListener("click", () => input.click());

      // Drag and drop
      ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
        dropZone.addEventListener(eventName, preventDefaults, false);
      });

      function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }

      ["dragenter", "dragover"].forEach((eventName) => {
        dropZone.addEventListener(eventName, highlight, false);
      });

      ["dragleave", "drop"].forEach((eventName) => {
        dropZone.addEventListener(eventName, unhighlight, false);
      });

      function highlight() {
        dropZone.classList.add("dragover");
      }

      function unhighlight() {
        dropZone.classList.remove("dragover");
      }

      dropZone.addEventListener("drop", handleDrop, false);

      function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        input.files = files;
        handleFiles(files);
      }

      input.addEventListener("change", function () {
        handleFiles(this.files);
      });

      function handleFiles(files) {
        if (files.length > 0) {
          const file = files[0];

          // Validate file type
          if (!file.type.match("image.*")) {
            showAdminNotification("Please select an image file", "error");
            return;
          }

          // Validate file size (5MB)
          if (file.size > 5 * 1024 * 1024) {
            showAdminNotification("Image must be less than 5MB", "error");
            return;
          }

          // Preview image
          const reader = new FileReader();
          reader.onload = function (e) {
            if (preview) {
              preview.src = e.target.result;
              preview.classList.remove("hidden");
            }

            // Update drop zone text
            const textElement = dropZone.querySelector("p");
            if (textElement) {
              textElement.textContent = file.name;
            }
          };
          reader.readAsDataURL(file);
        }
      }
    }
  });
}

// Form Validations for Admin
function initializeFormValidations() {
  const forms = document.querySelectorAll("form[data-validate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!validateAdminForm(this)) {
        e.preventDefault();
        showAdminNotification("Please fix the errors in the form", "error");
      }
    });

    // Real-time validation
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this);
      });
    });
  });
}

function validateAdminForm(form) {
  let isValid = true;
  const fields = form.querySelectorAll("[required], [data-validate]");

  fields.forEach((field) => {
    if (!validateField(field)) {
      isValid = false;
    }
  });

  return isValid;
}

function validateField(field) {
  let isValid = true;
  const value = field.value.trim();

  // Clear previous errors
  field.classList.remove("error-border");
  const existingError = field.parentNode.querySelector(".error-text");
  if (existingError) {
    existingError.remove();
  }

  // Required validation
  if (field.hasAttribute("required") && !value) {
    markFieldInvalid(field, "This field is required");
    isValid = false;
  }

  // Email validation
  if (field.type === "email" && value) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      markFieldInvalid(field, "Please enter a valid email address");
      isValid = false;
    }
  }

  // Number validation
  if (field.type === "number" && value) {
    if (
      field.hasAttribute("min") &&
      parseFloat(value) < parseFloat(field.min)
    ) {
      markFieldInvalid(field, `Value must be at least ${field.min}`);
      isValid = false;
    }
    if (
      field.hasAttribute("max") &&
      parseFloat(value) > parseFloat(field.max)
    ) {
      markFieldInvalid(field, `Value must be at most ${field.max}`);
      isValid = false;
    }
  }

  // URL validation
  if (field.type === "url" && value) {
    try {
      new URL(value);
    } catch {
      markFieldInvalid(field, "Please enter a valid URL");
      isValid = false;
    }
  }

  // Custom validation
  if (field.dataset.validate === "slug" && value) {
    const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
    if (!slugRegex.test(value)) {
      markFieldInvalid(
        field,
        "Slug can only contain lowercase letters, numbers, and hyphens"
      );
      isValid = false;
    }
  }

  if (isValid) {
    field.classList.add("success-border");
  }

  return isValid;
}

function markFieldInvalid(field, message) {
  field.classList.add("error-border");

  const errorElement = document.createElement("div");
  errorElement.className = "error-text";
  errorElement.textContent = message;
  field.parentNode.appendChild(errorElement);
}

// Modal Handlers
function initializeModalHandlers() {
  // Open modal
  const modalTriggers = document.querySelectorAll("[data-modal-toggle]");
  modalTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function () {
      const modalId = this.dataset.modalToggle;
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
      }
    });
  });

  // Close modal
  const modalCloses = document.querySelectorAll("[data-modal-hide]");
  modalCloses.forEach((close) => {
    close.addEventListener("click", function () {
      const modalId = this.dataset.modalHide;
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "";
      }
    });
  });

  // Close modal when clicking outside
  const modals = document.querySelectorAll(".modal-overlay");
  modals.forEach((modal) => {
    modal.addEventListener("click", function (e) {
      if (e.target === this) {
        this.classList.add("hidden");
        document.body.style.overflow = "";
      }
    });
  });
}

// Chart.js Initialization
function initializeChartjs() {
  const chartCanvases = document.querySelectorAll(".chart-canvas");

  chartCanvases.forEach((canvas) => {
    const ctx = canvas.getContext("2d");
    const chartType = canvas.dataset.chartType || "bar";
    const chartData = JSON.parse(canvas.dataset.chartData || "{}");

    new Chart(ctx, {
      type: chartType,
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
          },
          title: {
            display: true,
            text: canvas.dataset.chartTitle || "",
          },
        },
      },
    });
  });
}

// Delete Confirmation
function initializeDeleteConfirmations() {
  const deleteButtons = document.querySelectorAll(".delete-btn");

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (
        !confirm(
          "Are you sure you want to delete this item? This action cannot be undone."
        )
      ) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });
}

// Rich Text Editor
function initializeRichTextEditor() {
  const textareas = document.querySelectorAll(".rich-text-editor");

  textareas.forEach((textarea) => {
    // Simple rich text functionality
    const toolbar = document.createElement("div");
    toolbar.className = "rich-text-toolbar mb-2 flex space-x-2";
    toolbar.innerHTML = `
            <button type="button" class="px-3 py-1 border rounded" data-command="bold"><strong>B</strong></button>
            <button type="button" class="px-3 py-1 border rounded" data-command="italic"><em>I</em></button>
            <button type="button" class="px-3 py-1 border rounded" data-command="insertUnorderedList">• List</button>
        `;

    textarea.parentNode.insertBefore(toolbar, textarea);

    toolbar.addEventListener("click", function (e) {
      if (e.target.tagName === "BUTTON") {
        e.preventDefault();
        const command = e.target.dataset.command;
        document.execCommand(command, false, null);
        textarea.focus();
      }
    });
  });
}

// Admin Notification System
function showAdminNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
    type === "success"
      ? "alert-success"
      : type === "error"
      ? "alert-error"
      : type === "warning"
      ? "alert-warning"
      : "alert-info"
  }`;
  notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button class="ml-4 text-lg" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;

  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification);
    }
  }, 5000);
}

// Bulk Actions
function initializeBulkActions() {
  const bulkActionForm = document.querySelector(".bulk-action-form");
  if (bulkActionForm) {
    const bulkSelect = bulkActionForm.querySelector(".bulk-select");
    const bulkAction = bulkActionForm.querySelector(".bulk-action");
    const itemCheckboxes = bulkActionForm.querySelectorAll(".item-checkbox");
    const selectAllCheckbox = bulkActionForm.querySelector(".select-all");

    // Select all functionality
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener("change", function () {
        itemCheckboxes.forEach((checkbox) => {
          checkbox.checked = this.checked;
        });
        updateBulkActionState();
      });
    }

    // Update bulk action state
    itemCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", updateBulkActionState);
    });

    function updateBulkActionState() {
      const checkedCount = Array.from(itemCheckboxes).filter(
        (cb) => cb.checked
      ).length;
      bulkSelect.disabled = checkedCount === 0;
      bulkAction.disabled = checkedCount === 0;

      if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedCount === itemCheckboxes.length;
        selectAllCheckbox.indeterminate =
          checkedCount > 0 && checkedCount < itemCheckboxes.length;
      }
    }
  }
}

// Export Admin Functions
window.Admin = {
  showNotification: showAdminNotification,
  validateForm: validateAdminForm,
};
