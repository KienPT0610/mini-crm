</div><!-- /.container -->

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 for confirmations -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JavaScript -->
<script src="assets/js/script.js"></script>

<!-- JavaScript cho xác nhận xóa khách hàng -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Cấu hình SweetAlert2 cho xác nhận xóa khách hàng
  const deleteButtons = document.querySelectorAll('.delete-customer');
  if (deleteButtons) {
    deleteButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const customerId = this.getAttribute('data-id');
        const csrfToken = this.getAttribute('data-csrf');
        const customerName = this.getAttribute('data-name');

        Swal.fire({
          title: 'Xác nhận xóa?',
          text: `Bạn có chắc chắn muốn xóa khách hàng "${customerName}"?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Xóa',
          cancelButtonText: 'Hủy'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href =
              `index.php?page=delete_customer&id=${customerId}&csrf_token=${csrfToken}`;
          }
        });
      });
    });
  }

  // Khởi tạo biểu đồ thống kê nếu tồn tại
  const ctx = document.getElementById('monthlyChart');
  if (ctx && typeof monthlyData !== 'undefined') {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: monthlyData.labels,
        datasets: [{
          label: 'Khách hàng theo tháng',
          data: monthlyData.values,
          backgroundColor: 'rgba(0, 123, 255, 0.5)',
          borderColor: 'rgba(0, 123, 255, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  }
});
</script>
</body>

</html>