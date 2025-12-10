document.addEventListener("DOMContentLoaded", () => {
    const rows = document.querySelectorAll(".table tbody tr");

    rows.forEach(row => {
        row.addEventListener("click", () => {
            const description = row.dataset.description;
            const company = row.dataset.company;
            const offerType = row.dataset.offertype;

            // Redirection vers la page détail avec les données encodées
            const url = `job_detail.html?description=${encodeURIComponent(description)}&company=${encodeURIComponent(company)}&offerType=${encodeURIComponent(offerType)}`;
            window.location.href = url;
        });
    });
});
