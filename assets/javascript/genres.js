const checkboxes = document.querySelectorAll('.checkbox_genre');

checkboxes.forEach(checkbox => {
    checkbox.addEventListener('click', (event) => {
        const selectedCheckboxes = getCheckedCheckboxes();
        if (Array.isArray(selectedCheckboxes) && selectedCheckboxes.length === 0) {
            document.getElementById('search_results').innerHTML = '';
        } else {
            fetch(`/api/genres/${selectedCheckboxes.toString()}/movies`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau détectée : ' + response.statusText);
                    }
                    return response.json();  // Convertir la réponse en JSON
                })
                .then(data => {
                    document.getElementById('search_results').innerHTML = data;
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des données :', error);
                });
        }
    });
});

function getCheckedCheckboxes() {
    const checkedValues = [];

    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            checkedValues.push(checkbox.value);
        }
    });

    return checkedValues;
}