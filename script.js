// Show different sections

function showSection(sectionId) {

    // Get all sections

    const sections =
        document.querySelectorAll(".section");


    // Hide all sections

    sections.forEach(function(section) {

        section.classList.remove("active");

    });


    // Show selected section

    document
        .getElementById(sectionId)
        .classList.add("active");

}



// Create Account

document
    .getElementById("accountForm")
    .addEventListener(
        "submit",
        function(event) {

            event.preventDefault();


            const username =
                document
                    .getElementById("username")
                    .value;


            document
                .getElementById("accountMessage")
                .textContent =
                "Account created successfully! Welcome, "
                + username + "!";


            document
                .getElementById("accountForm")
                .reset();

        }
    );



// Set the default game time
// to the user's current time

function setCurrentTime() {

    const now = new Date();


    const hours =
        String(now.getHours())
        .padStart(2, "0");


    const minutes =
        String(now.getMinutes())
        .padStart(2, "0");


    document
        .getElementById("gameTime")
        .value =
        hours + ":" + minutes;

}


setCurrentTime();



// Create Game

document
    .getElementById("gameForm")
    .addEventListener(
        "submit",
        function(event) {

            event.preventDefault();


            const court =
                document
                    .getElementById("courtSelect")
                    .value;


            const gameType =
                document
                    .getElementById("gameType")
                    .value;


            const skillLevel =
                document
                    .getElementById("gameSkill")
                    .value;


            const time =
                document
                    .getElementById("gameTime")
                    .value;


            // Check if required fields are empty

            if (
                court === "" ||
                gameType === "" ||
                skillLevel === "" ||
                time === ""
            ) {

                document
                    .getElementById("gameMessage")
                    .textContent =
                    "Please complete all required fields.";

                return;

            }


            document
                .getElementById("gameMessage")
                .textContent =
                "Game created successfully! "
                + gameType
                + " at "
                + court
                + " starting at "
                + time
                + ". Skill Level: "
                + skillLevel
                + ".";

        }
    );



// Cancel Game

function cancelGame() {

    document
        .getElementById("gameForm")
        .reset();


    setCurrentTime();


    document
        .getElementById("gameMessage")
        .textContent =
        "Game canceled.";

}



// Join Game

function joinGame(court) {

    document
        .getElementById("joinMessage")
        .textContent =
        "You successfully joined the game at "
        + court
        + "!";

}




// Fetch and render live Court Status from games_list.php
function loadCourtStatus() {
    fetch('php/games_list.php')
        .then(function(response) {
            if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
            return response.json();
        })
        .then(function(data) {
            renderCourtStatus(data.games);
        })
        .catch(function(error) {
            console.error('Failed to load court status:', error);
        });
}

function renderCourtStatus(games) {
    const container = document.querySelector('#home .status-container');
    if (!container) return;

    container.innerHTML = '';

    if (!games || games.length === 0) {
        container.innerHTML = '<p>No active games right now.</p>';
        return;
    }

    games.forEach(function(game) {
        const card = document.createElement('div');
        card.className = 'status-card';

        card.innerHTML =
            '<h3>🏀 ' + game.court_name + '</h3>' +
            '<p class="active-game">Active Game</p>' +
            '<p>' + game.format + '</p>' +
            '<p>Players: ' + game.current_players + ' / ' + game.max_players + '</p>' +
            '<p>Skill: ' + game.skill_level + '</p>';

        container.appendChild(card);
    });
}

loadCourtStatus();

// Optional: refresh every 30 seconds so status stays live
setInterval(loadCourtStatus, 30000);





// Court Map Code (Michael Lignelle)
var center = [39.709974, -75.117575];
//initialize court player count.
var count = 0;



var bounds = L.latLngBounds(
    [center[0] - 0.001, center[1] - 0.0015],  // southwest corner
    [center[0] + 0.001, center[1] + 0.0015]   // northeast corner
);

var map = L.map('leafletMap', {
    maxBounds: bounds,
    maxBoundsViscosity: 1.0,
    minZoom: 18,
    maxZoom: 18
}).setView(center, 18);


//Tile layers from Maptiler (including attribution)

L.tileLayer('https://api.maptiler.com/maps/openstreetmap/{z}/{x}/{y}.jpg?key=tJwk9meS0E9ByXaEfPw7', {
    attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
}).addTo(map);


// Names and coordinates of the two courts

var courts = [
    {
        name: "Walking Trail",
        coordinates: [39.709435, -75.116998]
    },
    {
        name: "Recreation Center",
        coordinates: [39.710367073297384, -75.11807590270632]
    }
];

//Function to get court Status to give the court status colors. 
function getStatus(count){
    if (count >=4) return "#2ecc71"; //green
    if (count >= 1) return "#f1c40f"; //yellow
    return "#e74c3c" //red
}


//places markers of the courts based on court status (not implemented yet)
// Red for empty, Yellow for 1-3. and Green for 4+ 


courts.forEach(function(court) {
    court.marker = L.circleMarker(court.coordinates, {
        radius: 10,
        color: "#333",
        weight: 1,
        fillColor: getStatus(court.playerCount),
        fillOpacity: 0.9
    })
        .addTo(map)
        .bindPopup("<strong>" + court.name + "</strong><br>");   //Used to claude to update the old marker function for the future dynamic markers with the previous getStatus function
});
 

//Map Legend


var legend = L.control({position: 'bottomright'});

// Color coded court status
legend.onAdd = function (map){
    var div = L.DomUtil.create('div', 'info legend');
    div.innerHTML = 
    '<h4>Court Status</h4>' +
    '<div class = "legend-row"><span class = "legend-marker" style ="background:#e74c3c;"></span>Red: Empty</div>' +
        '\n<div class = "legend-row"><span class = "legend-marker" style = "background: #f1c40f;"></span> Yellow: 1-3 Players</div>' +
        '\n<div class = "legend-row"><span class = "legend-marker" style = "background: #2ecc71;"></span> Green: 4+ Players</div>';

        return div;
};

legend.addTo(map)

const mapSection = document.getElementById('map');
const mapObserver = new MutationObserver(function() {
    if (mapSection.classList.contains('active')) {
        map.invalidateSize();
    }
});
mapObserver.observe(mapSection, { attributes: true, attributeFilter: ['class'] });

;