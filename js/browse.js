// B.1 - fetch and display the list of existing games available to join
async function loadGames() {
  const format = document.getElementById('filterFormat').value;
  const skill = document.getElementById('filterSkill').value;

  const params = new URLSearchParams();
  if (format) params.append('format', format);
  if (skill) params.append('skill_level', skill);

  const listEl = document.getElementById('gameList');
  listEl.innerHTML = '<p>Loading games...</p>';

  try {
    const res = await fetch('php/games_list.php?' + params.toString());
    const data = await res.json();
    renderGames(data.games);
  } catch (err) {
    listEl.innerHTML = '<p>Something went wrong loading games. Try again.</p>';
    console.error(err);
  }
}

// B.1.1 - roster display, B.1.2 - join button, B.1.2.2 - disabled when full
function renderGames(games) {
  const listEl = document.getElementById('gameList');

  if (!games.length) {
    listEl.innerHTML = '<p>No games match right now. Be the first to create one.</p>';
    return;
  }

  listEl.innerHTML = games.map(game => `
    <div class="game-row">
      <div class="game-info">
        <div class="game-format">${game.format}</div>
        <div class="game-text">
          <div class="title">${game.court_name} · ${game.skill_level}</div>
          <div class="sub">
            ${formatTime(game.start_time)} ·
            ${game.current_players} of ${game.max_players} spots filled
            ${game.description ? ' · "' + escapeHtml(game.description) + '"' : ''}
          </div>
          <div class="roster">Players: ${game.roster.join(', ') || 'None yet'}</div>
        </div>
      </div>
      <button
        class="join-btn"
        data-game-id="${game.game_id}"
        ${game.is_full ? 'disabled' : ''}
      >
        ${game.is_full ? 'Full' : 'Join'}
      </button>

      ${game.is_full && game.alternative ? `
        <div class="alt-suggestion">
          Try ${game.alternative.court_name} — ${game.alternative.open_spots} open spot${game.alternative.open_spots === 1 ? '' : 's'}
        </div>
      ` : ''}
    </div>
  `).join('');

  document.querySelectorAll('.join-btn').forEach(btn => {
    btn.addEventListener('click', () => joinGame(btn.dataset.gameId, btn));
  });
}

// B.1.2.1 - add user to roster on join click
async function joinGame(gameId, btnEl) {
  btnEl.disabled = true;
  btnEl.textContent = 'Joining...';

  try {
    const res = await fetch('php/join_game.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ game_id: gameId, user_id: CURRENT_USER_ID })
    });
    const data = await res.json();

    if (res.ok) {
      showToast(data.message || "You're in.");
      loadGames(); // refresh list so roster/full status updates
    } else {
      showToast(data.error || 'Could not join this game.');
      btnEl.disabled = false;
      btnEl.textContent = 'Join';
    }
  } catch (err) {
    showToast('Network error. Try again.');
    btnEl.disabled = false;
    btnEl.textContent = 'Join';
    console.error(err);
  }
}

function formatTime(datetimeStr) {
  const d = new Date(datetimeStr.replace(' ', 'T'));
  return d.toLocaleString([], { weekday: 'short', hour: 'numeric', minute: '2-digit' });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2600);
}

document.getElementById('applyFilters').addEventListener('click', loadGames);
loadGames();

// B.1.3 idea: poll periodically so roster/full status stays current without a manual refresh
setInterval(loadGames, 15000);
