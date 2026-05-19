// JavaScript cho giao diện người dùng

document.addEventListener("DOMContentLoaded", function () {
  // Tự ẩn thông báo sau khi người dùng đã có thời gian đọc.
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });

  // Mở hoặc đóng menu điều hướng trên màn hình nhỏ.
  const menuToggle = document.getElementById("menu-toggle");
  const mainNav = document.getElementById("main-nav");
  if (menuToggle && mainNav) {
    menuToggle.addEventListener("click", () => {
      menuToggle.classList.toggle("active");
      mainNav.classList.toggle("active");
    });
  }

  // Điều khiển các menu thả xuống bằng thao tác click.
  const dropdowns = document.querySelectorAll(".dropdown");
  dropdowns.forEach((dropdown) => {
    const trigger = dropdown.querySelector(":scope > a, :scope > .user-avatar-btn");
    const menu = dropdown.querySelector(":scope > .dropdown-menu");
    if (!trigger || !menu) return;

    trigger.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      // Chỉ giữ một menu mở tại cùng một thời điểm.
      dropdowns.forEach((d) => {
        if (d !== dropdown) {
          d.querySelector(":scope > .dropdown-menu")?.classList.remove("show");
        }
      });
      menu.classList.toggle("show");
    });
  });

  // Click ra ngoài vùng dropdown sẽ đóng mọi menu đang mở.
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".dropdown")) {
      document.querySelectorAll(".dropdown-menu.show").forEach((m) => {
        m.classList.remove("show");
      });
    }
  });

  // Kiểm tra nhanh các trường bắt buộc trước khi gửi form.
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const requiredFields = form.querySelectorAll("[required]");
      let isValid = true;
      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add("error");
        } else {
          field.classList.remove("error");
        }
      });
      if (!isValid) {
        e.preventDefault();
        alert("Vui lòng điền đầy đủ thông tin bắt buộc");
      }
    });
  });

  // Đổi trạng thái header khi người dùng cuộn trang.
  const header = document.getElementById("main-header");
  if (header) {
    window.addEventListener("scroll", () => {
      header.classList.toggle("scrolled", window.scrollY > 10);
    });
  }
});
