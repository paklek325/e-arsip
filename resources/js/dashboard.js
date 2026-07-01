document.addEventListener("DOMContentLoaded", () => {
    // Animasi angka di card
    document.querySelectorAll(".dashboard-number").forEach(el => {
        const target = +el.getAttribute("data-count") || 0;
        let count = 0;
        const duration = 800;
        const step = target === 0 ? 0 : Math.ceil(target / (duration / 16));

        if (!step) {
            el.textContent = target.toLocaleString();
            return;
        }

        const counter = setInterval(() => {
            count += step;
            if (count >= target) {
                count = target;
                clearInterval(counter);
            }
            el.textContent = count.toLocaleString();
        }, 16);
    });

    // Klik card → redirect
    document.querySelectorAll(".clickable-card").forEach(card => {
        card.addEventListener("click", () => {
            const url = card.dataset.url;
            if (url) window.location.href = url;
        });
    });
});
