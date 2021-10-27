var gameDiv = document.querySelector(".game");

const levelContent = gameDiv.dataset.levelContent.split(',').map(x => x.split(''));
const levelHeight = levelContent.length;
const levelWidth = levelContent[0].length;

const CELL_WALL = "1";
const CELL_FLOOR = "0";

const VIEWPOINT_RADIUS = 2;

let player = {
    x: 0,
    y: 0,
};

for (y = player.y - VIEWPOINT_RADIUS; y <= player.y + VIEWPOINT_RADIUS; ++y) {
    let row = Array(levelWidth).fill(CELL_WALL);
    if (levelContent[y] !== undefined) {
        row = levelContent[y];
    }

    for (x = player.x - VIEWPOINT_RADIUS; x <= player.x + VIEWPOINT_RADIUS; ++x) {
        let cellData = CELL_WALL;
        if (row[x] !== undefined) {
            cellData = row[x];
        }

        var tile = document.createElement("div");
        if (x === player.x && y === player.y) {
            tile.classList.add("player");
        } else if (cellData === CELL_FLOOR) {
            tile.classList.add("floor");
        } else if (cellData === CELL_WALL) {
            tile.classList.add("wall");
        }
        gameDiv.appendChild(tile);
    }
}
