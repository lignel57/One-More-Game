// One More Game main application

let allCourts = [];
let latestGames = [];
let mapInstance = null;
let mapMarkers = [];
let editingGameId = null;

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(function (section) {
        section.classList.remove('active');
    });

    const selected = document.getElementById(sectionId);
    if (selected) {
        selected.classList.add('active');
        if (window.location.hash !== '#' + sectionId) {
            history.replaceState(null, '', '#' + sectionId);
        }
    }

    if (sectionId === 'map' && mapInstance) {
        setTimeout(function () { mapInstance.invalidateSize(); }, 0);
    }
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

function setMessage(id, message) {
    const el = document.getElementById(id);
    if (el) el.textContent = message;
}

function formatTime(datetimeStr) {
    if (!datetimeStr) return '';
    const d = new Date(String(datetimeStr).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return String(datetimeStr);
    return d.toLocaleString([], {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
}

// ---------- Courts ----------
async function loadCourts() {
    const courtSelect = document.getElementById('courtSelect');
    const filterCourt = document.getElementById('filterCourt');

    try {
        const response = await fetch('php/courts_list.php', { credentials: 'include' });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Unable to load courts');

        allCourts = data.courts || [];

        if (courtSelect) {
            courtSelect.innerHTML = '<option value="">Select a court</option>';
            allCourts.forEach(function (court) {
                const option = document.createElement('option');
                option.value = court.court_id;
                option.textContent = court.name;
                courtSelect.appendChild(option);
            });
        }

        if (filterCourt) {
            filterCourt.innerHTML = '<option value="">All courts</option>';
            allCourts.forEach(function (court) {
                const option = document.createElement('option');
                option.value = court.court_id;
                option.textContent = court.name;
                filterCourt.appendChild(option);
            });
        }

        renderCourtStatus(allCourts, latestGames);
        renderMapCourts(allCourts, latestGames);
    } catch (error) {
        if (courtSelect) courtSelect.innerHTML = '<option value="">Courts unavailable</option>';
        console.error(error);
    }
}

function setCurrentDateTime() {
    const dateInput = document.getElementById('gameDate');
    const timeInput = document.getElementById('gameTime');
    const now = new Date();

    if (dateInput) {
        const dateValue = now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0');
        dateInput.value = dateValue;
        dateInput.min = dateValue;
    }

    if (timeInput) {
        const next = new Date(now.getTime() + 30 * 60000);
        timeInput.value = String(next.getHours()).padStart(2, '0') + ':' + String(next.getMinutes()).padStart(2, '0');
    }
}

// ---------- Create Game ----------
const gameForm = document.getElementById('gameForm');
if (gameForm) {
    gameForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const courtId = document.getElementById('courtSelect').value;
        const format = document.getElementById('gameType').value;
        const skillLevel = document.getElementById('gameSkill').value;
        const date = document.getElementById('gameDate').value;
        const time = document.getElementById('gameTime').value;
        const maxPlayers = document.getElementById('maxPlayers').value;
        const description = document.getElementById('gameDescription').value;

        if (!courtId || !format || !skillLevel || !date || !time || !maxPlayers) {
            setMessage('gameMessage', 'Please complete all required fields.');
            return;
        }

        try {
            const endpoint = editingGameId ? 'php/update_game.php' : 'php/create_game.php';
            const payload = {
                court_id: courtId,
                format: format,
                start_time: date + ' ' + time + ':00',
                max_players: maxPlayers,
                skill_level: skillLevel,
                description: description
            };
            if (editingGameId) payload.game_id = editingGameId;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                setMessage('gameMessage', data.message || data.error || 'Unable to save the game.');
                return;
            }

            setMessage('gameMessage', editingGameId ? 'Game updated successfully!' : 'Game created successfully!');
            stopEditingGame(false);
            await loadGames();
        } catch (error) {
            setMessage('gameMessage', 'A network error occurred. Please try again.');
            console.error(error);
        }
    });
}

function stopEditingGame(showMessage = true) {
    editingGameId = null;
    if (gameForm) gameForm.reset();
    setCurrentDateTime();

    const submitButton = document.getElementById('gameSubmitButton');
    if (submitButton) submitButton.textContent = 'Create Game';

    const clearButton = document.getElementById('clearGameButton');
    if (clearButton) clearButton.textContent = 'Clear Form';

    if (showMessage) setMessage('gameMessage', 'Game form cleared.');
}

function cancelGame() {
    stopEditingGame(true);
}

function editGame(gameId) {
    const game = latestGames.find(function (item) {
        return Number(item.game_id) === Number(gameId);
    });
    if (!game || !game.is_host) {
        setMessage('joinMessage', 'Only the person who created this game can edit it.');
        return;
    }

    editingGameId = Number(gameId);
    document.getElementById('courtSelect').value = String(game.court_id);
    document.getElementById('gameType').value = game.format;
    document.getElementById('gameSkill').value = game.skill_level;
    document.getElementById('maxPlayers').value = game.max_players;
    document.getElementById('gameDescription').value = game.description || '';

    const dt = new Date(String(game.start_time).replace(' ', 'T'));
    if (!Number.isNaN(dt.getTime())) {
        document.getElementById('gameDate').value = dt.getFullYear() + '-' +
            String(dt.getMonth() + 1).padStart(2, '0') + '-' +
            String(dt.getDate()).padStart(2, '0');
        document.getElementById('gameTime').value =
            String(dt.getHours()).padStart(2, '0') + ':' + String(dt.getMinutes()).padStart(2, '0');
    }

    const submitButton = document.getElementById('gameSubmitButton');
    if (submitButton) submitButton.textContent = 'Save Changes';
    const clearButton = document.getElementById('clearGameButton');
    if (clearButton) clearButton.textContent = 'Cancel Editing';

    setMessage('gameMessage', 'Editing your game. Make your changes and click Save Changes.');
    showSection('game');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function cancelHostedGame(gameId) {
    if (!window.confirm('Cancel this game? It will be removed from the active game list.')) return;

    try {
        const response = await fetch('php/cancel_game.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ game_id: gameId })
        });
        const data = await response.json();
        setMessage('joinMessage', data.message || data.error || 'Unable to cancel the game.');
        if (response.ok && data.success) {
            if (editingGameId === Number(gameId)) stopEditingGame(false);
            await loadGames();
        }
    } catch (error) {
        setMessage('joinMessage', 'A network error occurred. Please try again.');
        console.error(error);
    }
}

// ---------- Browse / Join ----------
function buildGameQuery() {
    const params = new URLSearchParams();
    const format = document.getElementById('filterFormat');
    const court = document.getElementById('filterCourt');
    const skill = document.getElementById('filterSkill');

    if (format && format.value) params.set('format', format.value);
    if (court && court.value) params.set('court_id', court.value);
    if (skill && skill.value) params.set('skill_level', skill.value);
    return params.toString();
}

async function loadGames() {
    const list = document.getElementById('gameList');
    if (list) list.innerHTML = '<p>Loading games...</p>';

    try {
        const query = buildGameQuery();
        const response = await fetch('php/games_list.php' + (query ? '?' + query : ''), { credentials: 'include' });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Unable to load games');

        const filteredGames = data.games || [];
        renderGames(filteredGames);

        // Court Status and map should reflect all active games, not the Browse filters.
        const statusResponse = query
            ? await fetch('php/games_list.php', { credentials: 'include' })
            : response;
        const statusData = query ? await statusResponse.json() : data;
        if (query && !statusResponse.ok) throw new Error(statusData.error || 'Unable to load court status');

        latestGames = statusData.games || [];
        renderCourtStatus(allCourts, latestGames);
        renderMapCourts(allCourts, latestGames);
    } catch (error) {
        if (list) list.innerHTML = '<p>Games could not be loaded. Check the database connection.</p>';
        console.error(error);
    }
}

function renderGames(games) {
    const list = document.getElementById('gameList');
    if (!list) return;

    if (!games.length) {
        list.innerHTML = '<p>No games match those filters right now.</p>';
        return;
    }

    list.innerHTML = games.map(function (game) {
        const players = Number(game.current_players || 0);
        const max = Number(game.max_players || 0);
        const full = Boolean(game.is_full);
        const roster = Array.isArray(game.roster) && game.roster.length
            ? game.roster.map(escapeHtml).join(', ')
            : 'None yet';

        return '<div class="game-card">' +
            '<h3>' + escapeHtml(game.court_name) + '</h3>' +
            '<p><strong>' + escapeHtml(game.format) + '</strong> · ' + escapeHtml(game.skill_level) + '</p>' +
            '<p>' + escapeHtml(formatTime(game.start_time)) + '</p>' +
            '<p>Players: ' + players + ' / ' + max + '</p>' +
            '<p>Host: ' + escapeHtml(game.host_name) + (game.is_host ? '<span class="host-badge">Your Game</span>' : '') + '</p>' +
            '<p class="roster-line"><strong>Roster:</strong> ' + roster + '</p>' +
            (game.description ? '<p>' + escapeHtml(game.description) + '</p>' : '') +
            '<div class="game-actions">' +
            (game.is_host
                ? '<button class="edit-game-button" onclick="editGame(' + Number(game.game_id) + ')">Edit Game</button>' +
                  '<button class="cancel-game-button" onclick="cancelHostedGame(' + Number(game.game_id) + ')">Cancel Game</button>'
                : '<button ' + (full ? 'disabled' : '') + ' onclick="joinGame(' + Number(game.game_id) + ')">' +
                  (full ? 'Game Full' : 'Join Game') + '</button>') +
            '</div>' +
            (full && game.alternative
                ? '<p class="alt-suggestion">Try ' + escapeHtml(game.alternative.court_name) +
                  ' — ' + Number(game.alternative.open_spots) + ' open spot' +
                  (Number(game.alternative.open_spots) === 1 ? '' : 's') + '</p>'
                : '') +
            '</div>';
    }).join('');
}

async function joinGame(gameId) {
    try {
        const response = await fetch('php/join_game.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ game_id: gameId })
        });
        const data = await response.json();
        setMessage('joinMessage', data.message || data.error || 'Unable to join the game.');
        if (response.ok) await loadGames();
    } catch (error) {
        setMessage('joinMessage', 'A network error occurred. Please try again.');
        console.error(error);
    }
}

function renderCourtStatus(courts, games) {
    const container = document.querySelector('#home .status-container');
    if (!container) return;

    if (!courts.length) {
        container.innerHTML = '<p>Loading court status...</p>';
        return;
    }

    container.innerHTML = courts.map(function (court) {
        const courtGames = games.filter(function (game) {
            return Number(game.court_id) === Number(court.court_id);
        });

        if (!courtGames.length) {
            return '<div class="status-card">' +
                '<h3>🏀 ' + escapeHtml(court.name) + '</h3>' +
                '<p class="available">Available</p>' +
                '<p>Open for a new game</p>' +
                '</div>';
        }

        const nextGame = courtGames[0];
        return '<div class="status-card">' +
            '<h3>🏀 ' + escapeHtml(court.name) + '</h3>' +
            '<p class="active-game">Active Game</p>' +
            '<p>' + escapeHtml(nextGame.format) + ' · ' + escapeHtml(nextGame.skill_level) + '</p>' +
            '<p>Players: ' + Number(nextGame.current_players || 0) + ' / ' + Number(nextGame.max_players || 0) + '</p>' +
            '<p>' + escapeHtml(formatTime(nextGame.start_time)) + '</p>' +
            (courtGames.length > 1 ? '<p>' + courtGames.length + ' upcoming games</p>' : '') +
            '</div>';
    }).join('');
}

// ---------- Court Map ----------
const courtCoordinates = {
    'Walking Trail': [39.709435, -75.116998],
    'Recreation Center': [39.710367073297384, -75.11807590270632]
};

function initMap() {
    if (typeof L === 'undefined' || !document.getElementById('leafletMap')) return;

    const center = [39.709974, -75.117575];
    const bounds = L.latLngBounds(
        [center[0] - 0.0012, center[1] - 0.0017],
        [center[0] + 0.0012, center[1] + 0.0017]
    );

    mapInstance = L.map('leafletMap', {
        maxBounds: bounds,
        maxBoundsViscosity: 1.0,
        minZoom: 18,
        maxZoom: 18
    }).setView(center, 18);

    L.tileLayer('https://api.maptiler.com/maps/openstreetmap/{z}/{x}/{y}.jpg?key=tJwk9meS0E9ByXaEfPw7', {
        attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'
    }).addTo(mapInstance);

    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'info legend');
        div.innerHTML =
            '<h4>Court Status</h4>' +
            '<div class="legend-row"><span class="legend-marker" style="background:#e74c3c"></span> Red: Empty</div>' +
            '<div class="legend-row"><span class="legend-marker" style="background:#f1c40f"></span> Yellow: 1-3 Players</div>' +
            '<div class="legend-row"><span class="legend-marker" style="background:#2ecc71"></span> Green: 4+ Players</div>';
        return div;
    };
    legend.addTo(mapInstance);

    renderMapCourts(allCourts, latestGames);
}

function getCourtStatusColor(playerCount) {
    if (playerCount >= 4) return '#2ecc71'; // green: 4+ players
    if (playerCount >= 1) return '#f1c40f'; // yellow: 1-3 players
    return '#e74c3c'; // red: empty
}

function renderMapCourts(courts, games) {
    if (!mapInstance || !courts.length) return;

    mapMarkers.forEach(function (marker) { marker.remove(); });
    mapMarkers = [];

    courts.forEach(function (court) {
        const coordinates = courtCoordinates[court.name];
        if (!coordinates) return;

        const activeGames = games.filter(function (game) {
            return Number(game.court_id) === Number(court.court_id);
        });
        const nextGame = activeGames.length ? activeGames[0] : null;
        const playerCount = nextGame ? Number(nextGame.current_players || 0) : 0;
        const statusText = playerCount >= 4 ? '4+ Players' : (playerCount >= 1 ? '1-3 Players' : 'Empty');
        const detail = nextGame
            ? '<br>' + escapeHtml(nextGame.format) + ' · ' + playerCount + '/' + Number(nextGame.max_players || 0) + ' players'
            : '<br>Open for a new game';
        const fillColor = getCourtStatusColor(playerCount);

        const marker = L.circleMarker(coordinates, {
            radius: 10,
            color: '#333',
            weight: 1,
            fillColor: fillColor,
            fillOpacity: 0.9
        }).addTo(mapInstance)
          .bindPopup('<strong>' + escapeHtml(court.name) + '</strong><br>' + statusText + detail);

        mapMarkers.push(marker);
    });
}

const applyFilters = document.getElementById('applyFilters');
if (applyFilters) applyFilters.addEventListener('click', loadGames);

const clearFilters = document.getElementById('clearFilters');
if (clearFilters) {
    clearFilters.addEventListener('click', function () {
        ['filterFormat', 'filterCourt', 'filterSkill'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        loadGames();
    });
}


const gameTypeSelect = document.getElementById('gameType');
if (gameTypeSelect) {
    gameTypeSelect.addEventListener('change', function () {
        const suggestedMax = { '5v5': 10, '4v4': 8, '3v3': 6, '2v2': 4 }[gameTypeSelect.value];
        const maxInput = document.getElementById('maxPlayers');
        if (suggestedMax && maxInput) maxInput.value = suggestedMax;
    });
}

initMap();
loadCourts();
setCurrentDateTime();
loadGames();

const initialSection = window.location.hash.replace('#', '');
if (['home', 'map', 'browse', 'game'].includes(initialSection)) {
    showSection(initialSection);
}

setInterval(loadGames, 30000);
