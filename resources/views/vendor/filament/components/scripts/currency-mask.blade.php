<script>
  document.addEventListener('livewire:load', function () {
    document.querySelectorAll('.currency-input').forEach(function (el) {
      el.addEventListener('input', function () {
        let value = el.value.replace(/\./g, '').replace(/\D/g, '');
        el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      });
    });
  });
</script>
