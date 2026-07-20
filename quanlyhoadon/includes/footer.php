<?php
?>
    </div> 
</div> 

<footer class="mt-auto">
  <div class="container">
    <small>&copy; <?php echo date('Y'); ?> Quản lý thanh toán</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="toast-container"></div>
<script>

document.addEventListener('DOMContentLoaded', function(){
  var editModal = document.getElementById('productModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      var mode = button.getAttribute('data-mode');
      var modalTitle = editModal.querySelector('.modal-title');
      var form = editModal.querySelector('form');
      if (mode === 'add') {
        modalTitle.textContent = 'Thêm sản phẩm mới';
        form.action = '/quanlyhoadon/sanpham/add.php';
        form.reset();
        editModal.querySelector('input[name="id"]').readOnly = false;
      } else if (mode === 'edit') {
        modalTitle.textContent = 'Sửa sản phẩm';
        form.action = '/quanlyhoadon/sanpham/edit.php?id=' + encodeURIComponent(button.getAttribute('data-id'));
        editModal.querySelector('input[name="id"]').value = button.getAttribute('data-id');
        editModal.querySelector('input[name="id"]').readOnly = true;
        editModal.querySelector('input[name="barcode"]').value = button.getAttribute('data-barcode');
        editModal.querySelector('input[name="name"]').value = button.getAttribute('data-name');
        editModal.querySelector('input[name="price"]').value = button.getAttribute('data-price');
        editModal.querySelector('input[name="quantity"]').value = button.getAttribute('data-quantity');
      }
    });
  }

  
  var params = new URLSearchParams(window.location.search);
  if (params.has('msg')) {
    var msg = params.get('msg');
    var status = params.get('status') || 'info';
    showToast(decodeURIComponent(msg), status);
  }
});

function showToast(message, status) {
  var container = document.querySelector('.toast-container');
  if (!container) return;
  var toastEl = document.createElement('div');
  toastEl.className = 'toast align-items-center text-bg-white border shadow-sm';
  toastEl.setAttribute('role','alert');
  toastEl.setAttribute('aria-live','assertive');
  toastEl.setAttribute('aria-atomic','true');
  var color = 'text-muted';
  if (status === 'success') color = 'text-success';
  if (status === 'danger') color = 'text-danger';
  toastEl.innerHTML = '<div class="d-flex"><div class="toast-body '+color+'">'+escapeHtml(message)+'</div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
  container.appendChild(toastEl);
  var btoast = new bootstrap.Toast(toastEl, { delay: 4000 });
  btoast.show();
}

function escapeHtml(unsafe) {
  return unsafe.replace(/[&<>"'`=\/]/g, function(s) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'\/','`':'&#96;','=':'&#61;'})[s]; });
}
</script>
</body>
</html>
