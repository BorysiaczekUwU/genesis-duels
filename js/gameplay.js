// Moduł logiki rozgrywki na arenie

const Gameplay = {
    sessionId: null,
    gameState: null,

    init: function() {
        const urlParams = new URLSearchParams(window.location.search);
        this.sessionId = urlParams.get('session');

        if (!this.sessionId) {
            alert('Brak ID sesji!');
            window.location.href = 'dashboard.html';
            return;
        }

        document.getElementById('end-turn-btn').addEventListener('click', () => this.endTurn());

        this.refreshGameState();
    },

    refreshGameState: async function() {
        try {
            this.gameState = await Api.getGameState(this.sessionId);
            this.render();
        } catch (error) {
            alert('Błąd pobierania stanu gry: ' + error.message);
        }
    },

    render: function() {
        if (!this.gameState) return;

        const user = Auth.getUser();

        // Ustaw HP i inne informacje
        document.getElementById('player-hp').textContent = this.gameState.player1_hp;
        document.getElementById('opponent-hp').textContent = this.gameState.player2_hp;

        // TODO: Renderowanie kart na ręce i na planszy

        // Włącz/wyłącz przycisk tury
        const endTurnBtn = document.getElementById('end-turn-btn');
        if (this.gameState.current_turn == user.id) {
            endTurnBtn.disabled = false;
            endTurnBtn.textContent = 'Zakończ turę';
        } else {
            endTurnBtn.disabled = true;
            endTurnBtn.textContent = 'Tura przeciwnika';
        }
    },

    endTurn: async function() {
        const user = Auth.getUser();
        try {
            const result = await Api.playTurn(this.sessionId, user.id, { action: 'end_turn' });
            if (result.success) {
                this.gameState = result.newState;
                this.render();
            } else {
                alert('Błąd: ' + result.message);
            }
        } catch(error) {
            alert('Błąd zakończenia tury: ' + error.message);
        }
    }
};

// Inicjalizacja, jeśli jesteśmy na stronie gry
if (window.location.pathname.endsWith('game.html')) {
    document.addEventListener('DOMContentLoaded', () => Gameplay.init());
}
