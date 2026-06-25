function goHome() {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
}

// Xóa hàm goCategory() vì không cần dùng nữa

function goHighlight() {
  const el = document.getElementById("highlight");
  if (el) el.scrollIntoView({ behavior: "smooth" });
}

function goSale() {
  const el = document.getElementById("sale");
  if (el) el.scrollIntoView({ behavior: "smooth" });
}


// Popup open/close handlers
function openPopup() {
  const popup = document.getElementById("popup");
  if (popup) popup.style.display = "flex";
}

function closePopup() {
  const popup = document.getElementById("popup");
  if (popup) popup.style.display = "none";
}

// Close popup when clicking outside content or pressing Escape
function _installPopupHandlers() {
  const popup = document.getElementById("popup");
  if (!popup) return;

  popup.addEventListener("click", (e) => {
    if (e.target === popup) closePopup();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closePopup();
  });
}

// ========== LỌC THEO DANH MỤC BẰNG AJAX (giữ lại nếu cần) ==========
function filterCategory(type) {
  // Chuyển đến trang sản phẩm với bộ lọc
  window.location.href = "products.php?type=" + type;
}

// ========== EVENT CLICK CHO DANH MỤC ==========
document.addEventListener("DOMContentLoaded", () => {
  _installPopupHandlers();

  // Observe contact section
  const contact = document.getElementById("contact");
  if (contact) {
    const obs = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            contact.classList.add("visible");
            obs.unobserve(contact);
          }
        });
      },
      { threshold: 0.15 }
    );
    obs.observe(contact);
  }
});
