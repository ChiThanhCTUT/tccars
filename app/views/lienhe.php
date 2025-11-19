<?php 
include __DIR__ . '/partials/header.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Liên Hệ</title>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .contact-form { max-width: 600px; margin: auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .contact-info { margin-top: 30px; }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Liên Hệ Với Chúng Tôi</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="contact-form">
                    <h3>Gửi Tin Nhắn</h3>
                    <form action="#" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và Tên</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Chủ Đề</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Tin Nhắn</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi Tin Nhắn</button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="contact-info">
                    <h3>Thông Tin Liên Hệ</h3>
                    <p><strong>Địa Chỉ:</strong> Khu II, Đường 3/2, Phường Xuân Khánh, Quận Ninh Kiều, Thành Phố Cần Thơ</p>
                    <p><strong>Số Điện Thoại:</strong> (0292) 1234-5678</p>
                    <p><strong>Email:</strong> DVThueXeKTPM@gmail.com</p>
                    <p><strong>Giờ Làm Việc:</strong> Thứ Hai - Thứ Sáu, 7:00 AM - 5:00 PM</p>
                    
                    <div class="mt-3">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7857.2501680494015!2d105.7702531!3d10.047764!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0880f08006ffb%3A0x9a745510330faf4e!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBL4bu5IHRoveG6rXQgLSBDw7RuZyBuZ2jhu4cgQ-G6p24gVGjGoA!5e0!3m2!1svi!2s!4v1762422108858!5m2!1svi!2s" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm.');
        });
    </script>
</body>
</html>
