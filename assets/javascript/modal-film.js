const modalButtons = document.querySelectorAll('.modal-button');

const dynamicContent = document.getElementById('search_results');
dynamicContent.addEventListener('click', function(event) {
    if (event.target && event.target.classList.contains('modal-button')) {
        const filmId = event.target.getAttribute('data-id');
        fetch(`/api/film/${filmId}/modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau détectée : ' + response.statusText);
                }
                return response.json();  // Convertir la réponse en JSON
            })
            .then(data => {
                document.getElementById('modal-container').innerHTML = data;
                const button = document.getElementById('btn-modal');
                button.click();
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des données :', error);
            });
    }

});