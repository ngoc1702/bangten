(function () {
  const data = window.photobookDemoData;
  const root = document.querySelector("[data-photobook-builder]");

  if (!data || !root) {
    return;
  }

  const templateGrid = root.querySelector("[data-template-grid]");
  const editorList = root.querySelector("[data-page-editor-list]");
  const previewGrid = root.querySelector("[data-preview-grid]");
  const titleTarget = document.querySelector("[data-current-template-title]");
  const subtitleTarget = document.querySelector("[data-current-template-subtitle]");
  const configField = document.querySelector("[data-photobook-config-field]");
  const themeField = document.querySelector("[data-photobook-theme-field]");
  const submitButton = document.querySelector(".photobook-order-form__submit");
  const orderForm = document.querySelector(".photobook-order-form");
  const templates = data.templates || {};
  const templateIds = Object.keys(templates);

  if (!templateIds.length) {
    return;
  }

  const state = { activeTemplateId: templateIds[0], pages: [] };

  function cloneTemplatePages(template) {
    return (template.pages || []).map(function (page) {
      return {
        title: page.title || "",
        background: page.background || "#ffffff",
        text: page.default_text || "",
        imageId: 0,
        imageName: "",
        imageUrl: "",
      };
    });
  }

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function getSerializedState() {
    return {
      theme: state.activeTemplateId,
      pages: state.pages.map(function (page) {
        return {
          title: page.title,
          text: page.text,
          background: page.background,
          imageId: page.imageId,
          imageName: page.imageName,
          imageUrl: page.imageUrl,
        };
      }),
    };
  }

  function saveDraft() {
    try {
      window.localStorage.setItem(data.storageKey, JSON.stringify(getSerializedState()));
    } catch (error) {
      // The builder remains usable if browser storage is unavailable.
    }
  }

  function clearDraft() {
    try {
      window.localStorage.removeItem(data.storageKey);
    } catch (error) {
      // The next builder session can still start from in-memory defaults.
    }
  }

  function syncFormFields() {
    if (configField) {
      configField.value = JSON.stringify(getSerializedState());
    }
    if (themeField) {
      themeField.value = state.activeTemplateId;
    }
    saveDraft();
  }

  function hasAllImages() {
    return state.pages.length > 0 && state.pages.every(function (page) {
      return Number(page.imageId || 0) > 0 && Boolean(page.imageUrl);
    });
  }

  function renderCheckoutButton() {
    if (!submitButton) {
      return;
    }

    const hasProduct = Number(templates[state.activeTemplateId].product_id || 0) > 0;
    const ready = hasProduct && hasAllImages();
    submitButton.disabled = !ready;
    submitButton.setAttribute("aria-disabled", String(!ready));
    submitButton.textContent = !hasProduct
      ? "Template chưa gắn sản phẩm"
      : ready
        ? "Thêm photobook vào giỏ hàng"
        : "Vui lòng upload đủ ảnh";
  }

  function renderTemplateGrid() {
    templateGrid.innerHTML = templateIds.map(function (templateId) {
      const template = templates[templateId];
      return '<button class="photobook-template-card' + (templateId === state.activeTemplateId ? " is-active" : "") +
        '" type="button" data-template-id="' + escapeHtml(templateId) + '" style="--photobook-accent:' +
        escapeHtml(template.accent || "#d96c82") + ';">' +
        '<span class="photobook-template-card__tag">' + escapeHtml(template.price_label || "") + "</span>" +
        "<h3>" + escapeHtml(template.title || "") + "</h3>" +
        "<p>" + escapeHtml(template.subtitle || "") + "</p>" +
        '<span class="photobook-template-card__meta">' + (template.pages || []).length + " trang</span>" +
        "</button>";
    }).join("");

    templateGrid.querySelectorAll("[data-template-id]").forEach(function (button) {
      button.addEventListener("click", function () {
        setActiveTemplate(button.getAttribute("data-template-id"));
      });
    });
  }

  function renderEditorList() {
    editorList.innerHTML = state.pages.map(function (page, index) {
      const uploaded = Number(page.imageId || 0) > 0;
      return '<article class="photobook-editor-card">' +
        '<span class="photobook-editor-card__index" aria-label="Trang ' + (index + 1) + '">0' + (index + 1) + "</span>" +
        '<label class="photobook-upload' + (uploaded ? " is-uploaded" : "") + '">' +
        '<input class="photobook-upload__input" type="file" accept="image/*" data-page-file="' + index + '">' +
        '<span class="photobook-upload__copy">' + (uploaded ? "Đã tải ảnh ✓" : "Upload ảnh") + "</span>" +
        "</label>" +
        '<label class="photobook-text-field"><textarea rows="2" aria-label="Nội dung trang ' + (index + 1) +
        '" data-page-text="' + index + '">' + escapeHtml(page.text) + "</textarea></label>" +
        "</article>";
    }).join("");

    editorList.querySelectorAll("[data-page-file]").forEach(function (input) {
      input.addEventListener("change", function (event) {
        const pageIndex = Number(event.currentTarget.getAttribute("data-page-file"));
        const file = event.currentTarget.files && event.currentTarget.files[0];
        if (!file || !state.pages[pageIndex]) {
          return;
        }
        uploadImage(pageIndex, file, event.currentTarget.parentElement);
      });
    });

    editorList.querySelectorAll("[data-page-text]").forEach(function (textarea) {
      textarea.addEventListener("input", function (event) {
        const pageIndex = Number(event.currentTarget.getAttribute("data-page-text"));
        state.pages[pageIndex].text = event.currentTarget.value;
        renderPreviewGrid();
        syncFormFields();
      });
    });
  }

  function uploadImage(pageIndex, file, uploadControl) {
    const copy = uploadControl.querySelector(".photobook-upload__copy");
    uploadControl.classList.add("is-uploading");
    copy.textContent = "Đang tải ảnh...";

    const formData = new FormData();
    formData.append("action", "caia_upload_photobook_demo_image");
    formData.append("nonce", data.uploadNonce);
    formData.append("photobook_image", file);

    window.fetch(data.ajaxUrl, { method: "POST", body: formData })
      .then(function (response) { return response.json(); })
      .then(function (response) {
        if (!response.success || !response.data || !response.data.id || !response.data.url) {
          throw new Error((response.data && response.data.message) || "Không thể tải ảnh.");
        }
        state.pages[pageIndex].imageId = Number(response.data.id);
        state.pages[pageIndex].imageName = response.data.name || file.name;
        state.pages[pageIndex].imageUrl = response.data.url;
        render();
      })
      .catch(function (error) {
        uploadControl.classList.remove("is-uploading");
        copy.textContent = "Tải lại ảnh";
        window.alert(error.message || "Không thể tải ảnh. Vui lòng thử lại.");
      });
  }

  function renderPreviewGrid() {
    previewGrid.innerHTML = state.pages.map(function (page, index) {
      const imageMarkup = page.imageUrl
        ? '<img src="' + escapeHtml(page.imageUrl) + '" alt="Ảnh trang ' + (index + 1) + '">'
        : '<div class="photobook-preview-card__empty">' + escapeHtml(data.strings.noImage) + "</div>";
      return '<article class="photobook-preview-card" style="--preview-background:' + escapeHtml(page.background) + ';">' +
        '<span class="photobook-preview-card__number">0' + (index + 1) + "</span>" +
        '<div class="photobook-preview-card__media">' + imageMarkup + "</div>" +
        '<div class="photobook-preview-card__content"><p>' +
        escapeHtml(page.text || "Thêm nội dung cho trang này") + "</p></div></article>";
    }).join("");
  }

  function renderHeading() {
    const template = templates[state.activeTemplateId];
    if (titleTarget) titleTarget.textContent = template.title || "";
    if (subtitleTarget) subtitleTarget.textContent = template.subtitle || "";
  }

  function render() {
    renderTemplateGrid();
    renderHeading();
    renderEditorList();
    renderPreviewGrid();
    renderCheckoutButton();
    syncFormFields();
  }

  function setActiveTemplate(templateId) {
    state.activeTemplateId = templateId;
    state.pages = cloneTemplatePages(templates[templateId]);
    render();
  }

  if (orderForm) {
    orderForm.addEventListener("submit", function (event) {
      if (!hasAllImages()) {
		  event.preventDefault();
        window.alert(data.strings.allImagesRequired);
		  return;
      }

      // Submit normally so WooCommerce creates the cart in the browser's own session.
      syncFormFields();
      clearDraft();
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Đang thêm vào giỏ hàng...";
      }
    });
  }

  // A completed cart item is immutable; each return to the builder starts fresh.
  clearDraft();
  setActiveTemplate(templateIds[0]);

  window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
      clearDraft();
      setActiveTemplate(templateIds[0]);
    }
  });
})();
