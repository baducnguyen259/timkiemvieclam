// Thu gọn hoặc mở rộng thanh bên
const btnToggleSidebar = document.querySelector(".btn-toggle-sidebar");
const adminSidebar = document.querySelector(".admin-sidebar");
const adminMain = document.querySelector(".admin-main");

if (btnToggleSidebar) {
  btnToggleSidebar.addEventListener("click", () => {
    // Đồng bộ trạng thái thu gọn giữa thanh bên và vùng nội dung chính.
    adminSidebar.classList.toggle("collapsed");
    adminMain.classList.toggle("expanded");
  });
}

// Menu thả xuống của người dùng
const userDropdownToggle = document.querySelector(".user-dropdown-toggle");
const userDropdownMenu = document.querySelector(".user-dropdown-menu");

if (userDropdownToggle) {
  userDropdownToggle.addEventListener("click", (e) => {
    // Giữ sự kiện tại nút bấm để click mở menu không bị listener toàn trang đóng lại ngay.
    e.stopPropagation();
    userDropdownMenu.classList.toggle("show");
  });

  document.addEventListener("click", () => {
    // Bất kỳ click nào bên ngoài nút đều đóng menu người dùng.
    userDropdownMenu.classList.remove("show");
  });
}

// Chọn hoặc bỏ chọn toàn bộ mục
const checkboxAll = document.getElementById("checkbox-all");
if (checkboxAll) {
  checkboxAll.addEventListener("change", function () {
    const checkboxes = document.querySelectorAll(".checkbox-item");
    // Áp cùng trạng thái cho toàn bộ checkbox con trong bảng.
    checkboxes.forEach((cb) => {
      cb.checked = this.checked;
    });
  });
}

// Xử lý thao tác hàng loạt
const formChangeMulti = document.getElementById("form-change-multi");
if (formChangeMulti) {
  formChangeMulti.addEventListener("submit", function (e) {
    const type = document.getElementById("select-type").value;

    if (!type) {
      e.preventDefault();
      alert("Vui lòng chọn hành động");
      return;
    }

    const checkedBoxes = document.querySelectorAll(".checkbox-item:checked");

    if (checkedBoxes.length === 0) {
      e.preventDefault();
      alert("Vui lòng chọn ít nhất một mục");
      return;
    }

    let ids = [];

    if (type === "change-position") {
      // Với thao tác đổi vị trí, backend cần cả id và vị trí mới theo dạng "id-position".
      checkedBoxes.forEach((cb) => {
        const id = cb.value;
        const position = document.querySelector(
          `.input-position[data-id="${id}"]`,
        ).value;
        ids.push(`${id}-${position}`);
      });
    } else {
      // Các thao tác còn lại chỉ cần danh sách id bản ghi đã chọn.
      checkedBoxes.forEach((cb) => {
        ids.push(cb.value);
      });
    }

    // Ghi dữ liệu đã chuẩn hóa vào input ẩn để form gửi về server.
    document.getElementById("input-ids").value = ids.join(", ");

    // Xác nhận trước khi xóa hàng loạt
    if (type === "delete-all") {
      if (!confirm(`Bạn có chắc muốn xóa ${ids.length} mục đã chọn?`)) {
        e.preventDefault();
      }
    }
  });
}

// Tự ẩn thông báo sau một khoảng thời gian
document.addEventListener("DOMContentLoaded", function () {
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    // Cho người dùng đủ thời gian đọc thông báo trước khi tự ẩn.
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });
});

// Xác nhận trước khi xóa từng mục
document.querySelectorAll('form[onsubmit*="confirm"]').forEach((form) => {
  form.addEventListener("submit", function (e) {
    if (!confirm("Bạn có chắc muốn xóa?")) {
      e.preventDefault();
    }
  });
});

// Xem trước ảnh tải lên
const imageInputs = document.querySelectorAll(
  'input[type="file"][accept*="image"]',
);
imageInputs.forEach((input) => {
  input.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      // FileReader tạo preview cục bộ, chưa cần upload lên server.
      const reader = new FileReader();
      reader.onload = function (e) {
        let preview = input.parentElement.querySelector(".image-preview");
        if (!preview) {
          // Tái sử dụng ảnh preview nếu có, chỉ tạo mới ở lần chọn đầu tiên.
          preview = document.createElement("img");
          preview.className = "image-preview";
          preview.style.maxWidth = "200px";
          preview.style.marginTop = "10px";
          input.parentElement.appendChild(preview);
        }
        preview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });
});
