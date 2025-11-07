// Moduł kolekcji i budowania talii

const Collection = {
    render: async function() {
        const user = Auth.getUser();
        if (!user) return;

        const container = document.getElementById('collection-container');
        container.innerHTML = '<p>Ładowanie kolekcji...</p>';

        try {
            const collectionData = await Api.getCollection(user.id);
            if (collectionData.records && collectionData.records.length > 0) {
                container.innerHTML = collectionData.records.map(card => this.createCardHTML(card)).join('');
            } else {
                container.innerHTML = '<p>Twoja kolekcja jest pusta.</p>';
            }
        } catch (error) {
            container.innerHTML = `<p>Błąd ładowania kolekcji: ${error.message}</p>`;
        }
    },

    createCardHTML: function(card) {
        return `
            <div class="card-display" data-card-id="${card.id}">
                <div class="card-name">${card.name} (x${card.quantity})</div>
                <div class="card-cost">${card.cost}</div>
                <div class="card-stats">${card.attack || ''}/${card.health || ''}</div>
                <div class="card-description">${card.description}</div>
            </div>
        `;
    }

    // TODO: Dodać logikę do tworzenia i edycji talii
};
