// Moduł do komunikacji z API backendu

const API_BASE_URL = 'api/'; // Ścieżka do folderu API

const Api = {
    _request: async function(endpoint, method = 'GET', body = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
        };

        if (body) {
            options.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(API_BASE_URL + endpoint, options);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // --- User Endpoints ---
    register: function(username, email, password) {
        return this._request('user.php?action=register', 'POST', { username, email, password });
    },
    login: function(email, password) {
        return this._request('user.php?action=login', 'POST', { email, password });
    },
    getProfile: function(userId) {
        return this._request(`user.php?action=profile&id=${userId}`);
    },

    // --- Collection Endpoints ---
    getCollection: function(userId) {
        return this._request(`collection.php?action=get_collection&user_id=${userId}`);
    },
    createDeck: function(userId, name, cards) {
        return this._request('collection.php?action=create_deck', 'POST', { user_id: userId, name, cards });
    },

    // --- Game Endpoints ---
    findMatch: function(playerId, deckId, gameType = 'PvE') {
        return this._request('game.php?action=find_match', 'POST', { player_id: playerId, deck_id: deckId, game_type: gameType });
    },
    getGameState: function(sessionId) {
        return this._request(`game.php?action=get_state&session_id=${sessionId}`);
    },
    playTurn: function(sessionId, playerId, actionData) {
        return this._request('game.php?action=play_turn', 'POST', { session_id: sessionId, player_id: playerId, action_data: actionData });
    },

    // --- Shop Endpoints ---
    openLootbox: function(userId) {
        return this._request('shop.php?action=open_lootbox', 'POST', { user_id: userId });
    },
    doubleOrNothing: function(userId, creditsRised) {
        return this._request('shop.php?action=double_or_nothing', 'POST', { user_id: userId, credits_risked: creditsRised });
    },
};
