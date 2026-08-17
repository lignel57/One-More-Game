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
