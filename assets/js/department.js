function deleteDepartement(event, button, token) {
    event.preventDefault();

    if (!confirm("Êtes-vous sûr de vouloir supprimer ce département ?")) {
        return;
    }

    const card = button.closest('.departement-card');
    const url = card.dataset.deleteUrl;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: '_token=' + encodeURIComponent(token)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            card.remove(); // ✅ suppression visuelle
        } else {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        alert('Erreur lors de la suppression');
    });
}
