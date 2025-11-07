// Główny moduł aplikacji

const Main = {
    init: function() {
        // Sprawdź, czy użytkownik jest na stronie logowania/rejestracji
        if (window.location.pathname.endsWith('login.html') || window.location.pathname.endsWith('register.html')) {
            if (Auth.isAuthenticated()) {
                // Jeśli zalogowany, przekieruj do dashboardu
                window.location.href = 'dashboard.html';
            }
            return;
        }

        // Zabezpieczenie pozostałych stron
        if (!Auth.isAuthenticated()) {
            window.location.href = 'login.html';
            return;
        }

        // Inicjalizacja dla zalogowanego użytkownika
        if (window.location.pathname.endsWith('dashboard.html')) {
            this.initDashboard();
        }
    },

    initDashboard: function() {
        const user = Auth.getUser();
        if (!user) return;

        // Ustaw dane użytkownika w headerze
        document.getElementById('username-display').textContent = user.username;

        // TODO: Pobierz i wyświetl waluty
        // Api.getProfile(user.id).then(...)

        // Nawigacja
        document.getElementById('nav-collection').addEventListener('click', () => this.loadView('collection'));
        document.getElementById('nav-shop').addEventListener('click', () => this.loadView('shop'));
        document.getElementById('nav-play').addEventListener('click', () => this.loadView('play'));
        document.getElementById('logout-btn').addEventListener('click', Auth.logout);
    },

    loadView: function(viewName) {
        const mainContent = document.getElementById('main-content');
        mainContent.innerHTML = ''; // Wyczyść zawartość

        switch (viewName) {
            case 'collection':
                mainContent.innerHTML = '<h2>Kolekcja Kart</h2><div id="collection-container"></div>';
                Collection.render();
                break;
            case 'shop':
                mainContent.innerHTML = '<h2>Sklep</h2><p>Tu będzie można kupić skrzynki.</p>';
                // TODO: Renderowanie sklepu
                break;
            case 'play':
                mainContent.innerHTML = '<h2>Wybierz tryb gry</h2><button id="play-pve">Graj z AI</button>';
                document.getElementById('play-pve').addEventListener('click', () => {
                    // Załóżmy, że użytkownik ma aktywną talię o ID 1
                    Api.findMatch(Auth.getUser().id, 1, 'PvE').then(result => {
                        window.location.href = `game.html?session=${result.session_id}`;
                    });
                });
                break;
            default:
                mainContent.innerHTML = '<h2>Witaj!</h2>';
        }
    }
};

// Inicjalizacja przy ładowaniu strony
document.addEventListener('DOMContentLoaded', () => Main.init());
