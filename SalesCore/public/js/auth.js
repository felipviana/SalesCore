function getAuthToken() {
    return localStorage.getItem('salescore_token');
}

function getAuthHeaders(extraHeaders = {}) {
    const token = getAuthToken();

    return {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
        ...extraHeaders
    };
}

function getAuthJsonHeaders() {
    return getAuthHeaders({
        'Content-Type': 'application/json'
    });
}

function getCurrentUser() {
    const user = localStorage.getItem('salescore_user');

    return user ? JSON.parse(user) : null;
}

function requireAuth() {
    const token = getAuthToken();

    if (!token) {
        window.location.href = '/login.html';
    }
}

async function logout() {
    const token = getAuthToken();

    try {
        if (token) {
            await fetch('/api/logout', {
                method: 'POST',
                headers: getAuthHeaders()
            });
        }
    } catch (error) {
        console.warn('Erro ao encerrar sessão no servidor:', error);
    }

    localStorage.removeItem('salescore_token');
    localStorage.removeItem('salescore_user');

    window.location.href = '/login.html';
}

function handleUnauthorized(response) {
    if (response.status === 401) {
        localStorage.removeItem('salescore_token');
        localStorage.removeItem('salescore_user');
        window.location.href = '/login.html';
        return true;
    }

    return false;
}