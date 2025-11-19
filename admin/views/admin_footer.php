<?php
// admin_footer.php - closes admin layout
// No session logic here; assumes admin_header was included earlier
?>

            </main> <!-- .admin-main -->
    </div> <!-- .admin-shell -->

    <script>
        // minimal client JS for admin area
        document.addEventListener('DOMContentLoaded', function(){
            // highlight current nav item
            const items = document.querySelectorAll('.admin-sidebar nav a');
            const current = window.location.pathname.split('/').pop();
            items.forEach(a => {
                if (a.getAttribute('href') === current) {
                    a.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
