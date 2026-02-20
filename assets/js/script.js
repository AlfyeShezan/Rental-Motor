// Custom Scripts for Rental Motor Website

document.addEventListener('DOMContentLoaded', function() {
    console.log('Rental Motor Website Loaded');
    
    // Auto-calculate duration and total price in booking modal/form if any
    const pickupDate = document.getElementById('pickup_date');
    const returnDate = document.getElementById('return_date');
    const pricePerDay = document.getElementById('price_per_day');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const durationInput = document.getElementById('duration');

    if (pickupDate && returnDate && pricePerDay) {
        function calculateTotal() {
            if (pickupDate.value && returnDate.value) {
                const start = new Date(pickupDate.value);
                const end = new Date(returnDate.value);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    const total = diffDays * pricePerDay.dataset.price;
                    if (totalPriceDisplay) totalPriceDisplay.innerText = formatRupiah(total);
                    if (durationInput) durationInput.value = diffDays;
                }
            }
        }

        pickupDate.addEventListener('change', calculateTotal);
        returnDate.addEventListener('change', calculateTotal);
    }
});

function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}
