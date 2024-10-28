const searchInput = document.getElementById('searchInput');
const suggestionsContainer = document.getElementById('suggestions');

async function fetchFilms(query) {
    const response = await fetch(`/api/search/${query}/movie/`);
    return await response.json(); // Retourne la liste des résultats
}

searchInput.addEventListener('input', async () => {
    const query = searchInput.value;

    if (!query) {
        suggestionsContainer.innerHTML = '';
        return;
    }

    const films = await fetchFilms(query);

    suggestionsContainer.innerHTML = '';

    films.forEach(film => {
        const suggestionItem = document.createElement('div');
        suggestionItem.classList.add('suggestion-item');
        suggestionItem.textContent = film.title;

        suggestionItem.addEventListener('click', () => {
            searchInput.value = film.title;
            suggestionsContainer.innerHTML = '';
            fetch(`/api/film/${film.id}/template`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau détectée : ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('search_results').innerHTML = data;
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des données :', error);
                });
        });

        suggestionsContainer.appendChild(suggestionItem);
    });
});


