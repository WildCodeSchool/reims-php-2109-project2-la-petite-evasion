var gameDiv = document.querySelector(".game");

const levelContent = gameDiv.dataset.levelContent.split(',').map(x => x.split(''));
const levelHeight = levelContent.length;
const levelWidth = levelContent[0].length;
