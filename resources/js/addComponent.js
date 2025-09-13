document.querySelectorAll('.supplier-select').forEach(supplierSelect => {
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        // Find the corresponding brand-select within the same form/container
        const form = this.closest('form'); // find nearest form element
        const brandSelect = form.querySelector('.brand-select');

        if (!supplierId) {
            brandSelect.innerHTML = '';
            brandSelect.disabled = true;
            return;
        }

        brandSelect.disabled = false;

        fetch(`/brands-by-supplier/${supplierId}`)
            .then(response => response.json())
            .then(brands => {
                brandSelect.innerHTML = '';

                brands.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.name;
                    option.textContent = brand.name;
                    brandSelect.appendChild(option);
                });
            })
            .catch(() => {
                brandSelect.innerHTML = '';
                brandSelect.disabled = true;
            });
    });
});
