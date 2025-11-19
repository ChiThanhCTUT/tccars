document.addEventListener("DOMContentLoaded", function () {
  // Xử lý tìm kiếm xe
  const form = document.getElementById("searchForm");
  const input = document.getElementById("searchInput");
  const priceFilter = document.getElementById("priceFilter");
  const carItems = document.querySelectorAll(".car-item");

  // Replace client-side filtering with server-side search (AJAX)
  function renderCarGrid(cars) {
    const carGrid = document.querySelector(".car-grid");
    if (!carGrid) return;
    carGrid.innerHTML = "";
    if (!cars || cars.length === 0) {
      carGrid.innerHTML =
        '<p style="text-align: center; color: #666;">Không tìm thấy xe phù hợp.</p>';
      return;
    }
    cars.forEach((car) => {
      const priceFormatted =
        Number(car.price_per_day).toLocaleString("vi-VN") + " đ/ngày";
      const name = (car.brand || "") + " " + (car.model || "");
      const img =
        car.image_url ||
        "http://localhost:8080/tyqgwsgr_DbXe/public/images/placeholder.png";
      const desc = car.description || "";

      const div = document.createElement("div");
      div.className = "car-item";
      div.setAttribute("data-name", name);
      div.setAttribute("data-price", priceFormatted);
      div.setAttribute("data-img", img);
      div.setAttribute("data-desc", desc);

      const carId = car.car_id || "";
      div.innerHTML = `
        <img src="${img}" alt="${name}" class="car-item-img" />
        <div class="car-item-content">
          <h3>${name}</h3>
          <p class="price">Giá: ${priceFormatted}</p>
          <div class="actions">
            <a href="chitetxe.php?car_id=${carId}" class="btn view-details">Chi tiết</a>
            <a href="booking.php?car_id=${carId}" class="btn book-now">Đặt ngay</a>
          </div>
        </div>
      `;
      carGrid.appendChild(div);
    });
  }

  async function doServerSearch(keyword, priceValue) {
    const url =
      "http://localhost:8080/tyqgwsgr_DbXe/app/models/search_cars.php?q=" +
      encodeURIComponent(keyword) +
      "&price=" +
      encodeURIComponent(priceValue);
    try {
      const res = await fetch(url, { credentials: "same-origin" });
      const json = await res.json();
      if (json && json.data) {
        renderCarGrid(json.data);
        attachCarItemButtons();
        // scroll to first result
        const first = document.querySelector(".car-item");
        if (first)
          first.scrollIntoView({ behavior: "smooth", block: "center" });
      } else {
        renderCarGrid([]);
      }
    } catch (err) {
      console.error("Lỗi khi tìm kiếm máy chủ:", err);
    }
  }

  if (form && input) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      const keyword = input.value.trim();
      const priceValue = priceFilter ? priceFilter.value : "";
      doServerSearch(keyword, priceValue);
    });
  }
  //Thanh- Xử lý hiển thị chi tiết xe khi click
  const modal = document.getElementById("carModal");
  const modalImg = document.getElementById("modalImg");
  const modalName = document.getElementById("modalName");
  const modalDesc = document.getElementById("modalDesc");
  const modalPrice = document.getElementById("modalPrice");
  const closeBtn = document.querySelector(".modal .close");

  if (modal && modalImg && modalName && modalDesc && modalPrice) {
    function attachCarItemButtons() {
      document.querySelectorAll(".car-item .actions .btn").forEach((button) => {
        // Avoid double-binding by storing the handler on the element
        if (button._carClickHandler) {
          button.removeEventListener("click", button._carClickHandler);
        }
        const handler = function (e) {
          e.stopPropagation(); // Ngăn click lan ra ngoài
          const item = this.closest(".car-item");
          modal.style.display = "flex";
          modalImg.src = item.dataset.img;
          modalName.textContent = item.dataset.name;
          modalDesc.innerHTML = item.dataset.desc;
          modalPrice.textContent = "Giá thuê: " + item.dataset.price;

          // Luôn gán lại sự kiện cho nút 'Thuê ngay' mỗi lần mở popup, xoá sự kiện cũ trước khi gán mới
          const rentBtn = document.querySelector("#carModal .rent-btn");
          if (rentBtn) {
            rentBtn.replaceWith(rentBtn.cloneNode(true));
            const newRentBtn = document.querySelector("#carModal .rent-btn");
            newRentBtn.onclick = function () {
              // Kiểm tra đăng nhập
              if (localStorage.getItem("isLoggedIn") !== "true") {
                alert("Bạn cần đăng nhập để thuê xe!");
                window.location.href =
                  "http://localhost:8080/xulydangky/dangnhap.php";
                return;
              }
              const payModal = document.getElementById("paymentModal");
              const payImg = document.getElementById("payImg");
              const payName = document.getElementById("payName");
              const payPrice = document.getElementById("payPrice");
              const totalPrice = document.getElementById("totalPrice");
              const finalPrice = document.getElementById("finalPrice");

              payModal.style.display = "flex";
              payImg.src = modalImg.src;
              payName.textContent = modalName.textContent;

              // Lấy giá trị số từ modalPrice
              const priceStr = modalPrice.textContent.replace(/[^\d]/g, "");
              const price = parseInt(priceStr);
              payPrice.textContent = `Đơn giá: ${price.toLocaleString()} đ/ngày`;

              const pickup = document.getElementById("pickupDate");
              const ret = document.getElementById("returnDate");
              const insurance = document.getElementById("insurance");
              const extra = document.getElementById("extraInsurance");

              function calcTotal() {
                const start = new Date(pickup.value);
                const end = new Date(ret.value);
                const days = Math.max(
                  1,
                  Math.ceil((end - start) / (1000 * 60 * 60 * 24))
                );
                let total = price * days;
                if (insurance.checked) total += 93564 * days;
                if (extra.checked) total += 100000 * days;
                totalPrice.textContent = total.toLocaleString();
                finalPrice.textContent = (total - 120000).toLocaleString();
              }

              pickup.onchange =
                ret.onchange =
                insurance.onchange =
                extra.onchange =
                  calcTotal;
              calcTotal();
            };
          }
        };
        button._carClickHandler = handler;
        button.addEventListener("click", handler);
      });
    }

    // Attach handlers for any existing items on page load
    attachCarItemButtons();

    // Đóng popup khi bấm X
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
      });
    }
    // Đóng popup khi bấm ra ngoài modal-content
    window.addEventListener("click", function (event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
    // Đóng popup khi bấm phím ESC
    window.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        modal.style.display = "none";
      }
    });
  } else {
    console.error("Không tìm thấy popup/modal hoặc các thành phần bên trong.");
  }
});
// Thanh-xử lý thanh toán

document.addEventListener("DOMContentLoaded", function () {
  const payModal = document.getElementById("paymentModal");
  const payImg = document.getElementById("payImg");
  const payName = document.getElementById("payName");
  const payPrice = document.getElementById("payPrice");
  const totalPrice = document.getElementById("totalPrice");
  const finalPrice = document.getElementById("finalPrice");

  const carModal = document.getElementById("carModal");
  const modalImg = document.getElementById("modalImg");
  const modalName = document.getElementById("modalName");
  const modalPrice = document.getElementById("modalPrice");

  // Gán sự kiện cho nút "Thuê ngay" trong popup chi tiết xe
  const rentBtn = document.querySelector("#carModal .rent-btn");
  if (rentBtn) {
    rentBtn.addEventListener("click", function () {
      payModal.style.display = "flex";
      payImg.src = modalImg.src;
      payName.textContent = modalName.textContent;

      // Lấy giá trị số từ modalPrice
      const priceStr = modalPrice.textContent.replace(/[^\d]/g, "");
      const price = parseInt(priceStr);
      payPrice.textContent = `Đơn giá: ${price.toLocaleString()} đ/ngày`;

      const pickup = document.getElementById("pickupDate");
      const ret = document.getElementById("returnDate");
      const insurance = document.getElementById("insurance");
      const extra = document.getElementById("extraInsurance");

      function calcTotal() {
        const start = new Date(pickup.value);
        const end = new Date(ret.value);
        const days = Math.max(
          1,
          Math.ceil((end - start) / (1000 * 60 * 60 * 24))
        );
        let total = price * days;
        if (insurance.checked) total += 93564 * days;
        if (extra.checked) total += 100000 * days;
        totalPrice.textContent = total.toLocaleString();
        finalPrice.textContent = (total - 120000).toLocaleString();
      }

      pickup.onchange =
        ret.onchange =
        insurance.onchange =
        extra.onchange =
          calcTotal;
      calcTotal();
    });
  }

  document.querySelector(".close-payment").onclick = () =>
    (payModal.style.display = "none");
  window.addEventListener("click", (e) => {
    if (e.target === payModal) payModal.style.display = "none";
  });
  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") payModal.style.display = "none";
  });
});
