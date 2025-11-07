// Moduł obsługi autoryzacji

const Auth = {
    handleLogin: async function(event) {
        event.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const result = await Api.login(email, password);
            if (result.user_id) {
                // Zapisz dane użytkownika w localStorage (prosta metoda sesji)
                localStorage.setItem('genesisDuelsUser', JSON.stringify({
                    id: result.user_id,
                    username: result.username,
                }));
                // Przekieruj do panelu głównego
                window.location.href = 'dashboard.html';
            } else {
                alert('Błąd logowania: ' + result.message);
            }
        } catch (error) {
            alert('Błąd logowania: ' + error.message);
        }
    },

    handleRegister: async function(event) {
        event.preventDefault();
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const result = await Api.register(username, email, password);
            alert(result.message);
            if (result.message.includes("successfully registered")) {
                window.location.href = 'login.html';
            }
        } catch (error) {
            alert('Błąd rejestracji: ' + error.message);
        }
    },

    logout: function() {
        localStorage.removeItem('genesisDuelsUser');
        window.location.href = 'login.html';
    },

    getUser: function() {
        const user = localStorage.getItem('genesisDuelsUser');
        return user ? JSON.parse(user) : null;
    },

    isAuthenticated: function() {
        return this.getUser() !== null;
    }
};
