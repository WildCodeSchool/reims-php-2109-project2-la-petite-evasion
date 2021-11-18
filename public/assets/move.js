function move(direction) {
    fetch('/games?action=' + direction)
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            }
            return response.text();
        })
        .then(html => {
            let fetched = document.createElement("div");
            fetched.innerHTML = html;
            let fetchedGames = fetched.getElementsByClassName("game-container");
            if (fetchedGames.length) {
                let fetchedGame = fetchedGames[0];
                let currentGame = document.getElementsByClassName("game-container")[0];
                currentGame.innerHTML = fetchedGame.innerHTML;
            }
        });
}
document.addEventListener('keydown', (event) => {
    const keyName = event.key;
    const directions = { ArrowUp: 'up', ArrowDown: 'down', ArrowRight: 'right', ArrowLeft: 'left' };

    if (keyName in directions) {
        event.preventDefault();
        move(directions[keyName]);
    }
});