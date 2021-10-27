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
