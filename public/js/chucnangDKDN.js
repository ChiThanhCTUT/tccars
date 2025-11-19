// Xử lý đăng nhập và kiểm tra trạng thái đăng nhập khi người dùng nhấn "Thuê ngay"
document.addEventListener("DOMContentLoaded", function () {
  // Tìm tất cả các button có nội dung là "Thuê ngay"
  var buttons = Array.from(document.querySelectorAll("button")).filter(
    (btn) => btn.textContent.trim().toLowerCase() === "thuê ngay"
  );

  // Thêm sự kiện click cho mỗi button "Thuê ngay"
  buttons.forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      // Kiểm tra trạng thái đăng nhập bằng localStorage
      if (localStorage.getItem("isLoggedIn") !== "true") {
        alert("Bạn cần đăng nhập để thuê xe!");
        window.location.href = "http://localhost:8080/tyqgwsgr_DbXe/app/views/dangnhap.php";
        return;
      }

      // Nếu đã đăng nhập, mở modal thanh toán
      var payModal = document.getElementById("paymentModal");
      var modal = document.getElementById("carModal");
      var modalImg = document.getElementById("modalImg");
      var modalName = document.getElementById("modalName");
      var modalPrice = document.getElementById("modalPrice");

      // Các phần tử trong modal thanh toán
      var payImg = document.getElementById("payImg");
      var payName = document.getElementById("payName");
      var payPrice = document.getElementById("payPrice");
      var totalPrice = document.getElementById("totalPrice");
      var finalPrice = document.getElementById("finalPrice");

      // Hiển thị modal thanh toán
      payModal.style.display = "flex";
      payImg.src = modalImg.src;
      payName.textContent = modalName.textContent;
      var priceStr = modalPrice.textContent.replace(/[^\d]/g, "");
      var price = parseInt(priceStr);

      // Hiển thị đơn giá trên modal thanh toán
      payPrice.textContent = `Đơn giá: ${price.toLocaleString()} đ/ngày`;
      var pickup = document.getElementById("pickupDate");
      var ret = document.getElementById("returnDate");
      var insurance = document.getElementById("insurance");
      var extra = document.getElementById("extraInsurance");

      // Hàm tính tổng tiền thuê xe
      function calcTotal() {
        // Guard: ensure date values exist and are valid
        if (!pickup || !ret || !pickup.value || !ret.value) {
          totalPrice.textContent = '0';
          finalPrice.textContent = '0';
          return;
        }
        var start = new Date(pickup.value);
        var end = new Date(ret.value);
        // Tính số ngày thuê, tối thiểu là 1 ngày
        var days = Math.max(
          1,
          Math.ceil((end - start) / (1000 * 60 * 60 * 24))
        );
        var total = price * days;

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

      // Tính tổng tiền lần đầu khi mở modal
      calcTotal();
    });
  });
});
