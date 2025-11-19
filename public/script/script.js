var hero = {
    top: 816,
    left: 240
}

let cameraStart = {
    top: 760,
    left: 120
}

let cameraMoving = false;

let keyboardState = {
    upPressedDown: false,
    downPressedDown: false,
    rightPressedDown: false,
    leftPressedDown: false,
    shiftPressedDown: false
}

document.onkeyup = function (KeyboardEvent) {
    // Stop moving Camera left.
    if (KeyboardEvent.key === 'a') {

        keyboardState.leftPressedDown = false;

        // Stop moving Camera right.
    } else if (KeyboardEvent.key === 'd') {

        keyboardState.rightPressedDown = false;

        // Stop moving Camera up.
    } else if (KeyboardEvent.key === 'w') {

        keyboardState.upPressedDown = false;

        // Stop moving Camera down.
    } else if (KeyboardEvent.key === 's') {

        keyboardState.downPressedDown = false;
    }

    if (KeyboardEvent.key === 'Shift') {
        keyboardState.shiftPressedDown = false;
    }
}

/**
 * There is code here to move a hero unit.
 * I want to set this up so that if only one unit is selected
 *  then
 * That unit acts as a hero unit.
 * @param {*} KeyboardEvent 
 */
document.onkeydown = function(KeyboardEvent) {
    const HERO_SPEED = 2000;
    // Stop moving Camera left.
    if (KeyboardEvent.key === 'a') {

        keyboardState.leftPressedDown = false;

    // Stop moving Camera right.
    } else if (KeyboardEvent.key === 'd') {

        keyboardState.rightPressedDown = false;

    // Stop moving Camera up.
    } else if (KeyboardEvent.key === 'w') {

        keyboardState.upPressedDown = false;

    // Stop moving Camera down.
    } else if (KeyboardEvent.key === 's') {

        keyboardState.downPressedDown = false;
    }

    if (KeyboardEvent.key === 'Shift') {
        keyboardState.shiftPressedDown = false;
    }

    // Move Main Character right.
    if (KeyboardEvent.key === 'j') {
        $("#hero").fadeIn(1000);
        //hero.left -= 16;

        //moveHero(hero.left);
        $( "#hero" ).animate({
            left: "-=16",
        }, HERO_SPEED, function() {
            // Animation complete.
            console.log('left movement completed');
            $("#hero").fadeOut(4000);
        });


    // Move Main Character left.
    } else if (KeyboardEvent.key === 'l') {
        $("#hero").fadeIn(1000);
        // hero.top += 16;
        $( "#hero" ).animate({
            left: "+=16",
        }, HERO_SPEED, function() {
            // Animation complete.
            console.log('left movement completed');
            $("#hero").fadeOut(4000);
        });

        //moveHero(hero.left);
    }

    // Move Main Character up.
    if (KeyboardEvent.key === 'i') {
        $("#hero").fadeIn(1000);

        $( "#hero" ).animate({
            top: "-=16",
        }, HERO_SPEED, function() {
            // Animation complete.
            console.log('top movement completed');
            $("#hero").fadeOut(4000);
        });

        //moveHero(hero.left, hero.top);

    // Move Main Character down.
    } else if (KeyboardEvent.key === 'k') {
        //hero.top += 16;
        $("#hero").fadeIn(1000);

        $( "#hero" ).animate({
            top: "+=16",
        }, HERO_SPEED, function() {
            // Animation complete.
            console.log('top movement completed');
            $("#hero").fadeOut(4000);
        });

        //moveHero(hero.left, hero.top);
    }

    if (KeyboardEvent.key === 'Shift') {
        keyboardState.shiftPressedDown = true;
    }
}

/**
 * The keyboard controls doesn't seem to allow for diagonal movement.
 * @see cameraControls and keyboardState.
 * Created this to work with wasd keys for movement, but it can be reused for other keys in the future
 *
 * @param {*} horizontal
 * @param {*} vertical
 */
function moveCamera(horizontal, vertical) {

    if (((horizontal < 0) && (window.scrollX > 0)) || ((horizontal > 0) && (window.scrollX < (document.body.scrollWidth - window.innerWidth + 15)))) {
        window.scrollX += horizontal;
    }
    if (((vertical < 0) && (window.scrollY > 0)) || ((vertical > 0) && (window.scrollY < (document.body.scrollHeight - window.innerHeight + 15)))) {
        window.scrollY += vertical;
    }

    window.scrollTo(window.scrollX, window.scrollY);
}

function moveCameraVertically(vertical) {
    window.scrollTo(window.scrollX, window.scrollY + vertical);
}

function moveCameraHorizontally(horizontal) {
    window.scrollTo(window.scrollX + horizontal, window.scrollY);
}

/**
 * 
 * @param {*} path 
 * @param {*} unit 
 */
function moveUnit(path, unit) {
    const pathLength = path.length;
    const peasant = userPeasants.get(unit.getAttribute('id'));

    for (let nodeNumber = 0; nodeNumber < pathLength; nodeNumber++) {
        const coord = path.shift();

        const toTop = (coord.x * 16);
        const toLeft = (coord.y * 16);

        $(unit).animate({
            left: toLeft,
            top: toTop,
        }, {
            duration: 1000,
            easing: "linear",
            step: function( now, fx ) {
                let $unit = $( fx.elem );
                let selectedIndicators = document.getElementsByName(unit.getAttribute('id'));
                for (let index = 0; index < selectedIndicators.length; index++) {
                    if (selectedIndicators[index].style === undefined) {
                        selectedIndicators[index].style = {};
                    }
                    selectedIndicators[index].style.left = $unit.offset().left + 'px';
                    selectedIndicators[index].style.top = $unit.offset().top + 'px';
                }

                if (peasant.animateMoveToDirection(toTop, toLeft) === false) {
                    console.log('Stopping');
                }
            }
        });
    }
}

/**
 * @param {*} path
 * @param {*} unit
 * 
 * I want to redo this correctly with requestAnimationFrame
 * https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame
 */
function moveUnitBroken(path, unit) {
    // function moveUnitToCoord(path, unit, ) {
    //     const coord = path.shift();
    //     const intervalValue = 1000;
    //     let coord = {}
    //     let pathNumber = 0;
    //     const pathLength = path.length;
    //     while (athNumber <= pathLength) {
    //         pathNumber++;
    //         console.log(coord);
    //         let toTop = (coord.x * 16);
    //         let toLeft = (coord.y * 16);
    //         The peasant until they can't move anymore.
    //         if (peasant.moveTo(toTop, toLeft) === false) {
    //             console.log('Stopping');
    //             clearInterval(intervalId);
    //         }
    //     }
    //     The peasant until they can't move anymore.
    //     if (peasant.moveTo(toTop, toLeft) === false) {
    //         console.log('Stopping');
    //         clearInterval(intervalId);
    //     }
    // }
    //intervalId = setInterval();
    // console.log('peasant', peasant); 
    // console.log('unit', unit);
    // console.log('path', path);
}

/**
 * Helper method to do what typescript does.
 *
 * @param {} values 
 * @returns 
 */
function createEnum(values) {
    const enumObject = {};
    for (const val of values) {
        enumObject[val] = val;
    }
    return Object.freeze(enumObject);
}

/**
 * Finds the className unitSelected in the class list and removes it.
 *
 * @param {*} unitId
 *
 * @returns
 */
function unSelectUnit(unitId) {
    var unit = document.getElementById(unitId);
    unit.classList.remove("unitSelected");

    // The selected indicator should have the same name as the unit's selected id.
    let selectedIndicator = document.querySelector("div[name='" + unit.getAttribute('id') + "']");
    if (selectedIndicator === null) {
        return true;
    }

    // Make sure that the indicator is hidden.
    selectedIndicator.style.display = 'none';
    selectedIndicator.remove();
}

/**
 *
 */
function selectUnit(unitId) {
    let currentUnit = document.getElementById(unitId);
    let currentClassName = currentUnit.className;
    let unitLeft = $(currentUnit).offset().left;
    let unitTop = $(currentUnit).offset().top;

    if (currentClassName.length > 0) {
        document.getElementById(unitId).className = [currentClassName, 'unitSelected'].join(' ');
    }
    let selectIndicator = document.createElement('div');
    selectIndicator.setAttribute('name', currentUnit.getAttribute('id'));
    selectIndicator.style.left = unitLeft + 'px';
    selectIndicator.style.top = unitTop + 'px';
    selectIndicator.className = 'unitOutlined';

    document.getElementById('main').appendChild(selectIndicator);
}

/**
 * Right click will work normally if no units are selected.
 *
 * @param {*} clickEvent
 */
function rightClickContextMenu(clickEvent) {

    let selectedIndicator = document.querySelector(".unitSelected ");

    if (selectedIndicator !== null) {
        clickEvent.preventDefault();
        return false;
    }
}

/**
 * Sets the key down states for the camera directions.
 */
function cameraControls() {
    let x = 0;
    let y = 0;

    // Camera Controls
    if (keyboardState.leftPressedDown === true) {

        x = x - 2;

    }
    if (keyboardState.rightPressedDown === true) {

        x = x + 2;

    }
    if (keyboardState.upPressedDown === true) {

        y = y - 2;

    }
    if (keyboardState.downPressedDown === true) {

        y = y + 2;

    }

    moveCamera(x, y);
}

/**
 * This is temporary, eventually this will wait for a server to come back.
 */
function waitForCameraStart() {

    if (cameraStart !== undefined) {
        window.scrollTo({
            top: cameraStart.top,
            left: cameraStart.left,
            behavior: 'smooth'
        });
    }
}

function gameLoop() {
    setTimeout(gameLoop, 500);
}

function camera() {
    setTimeout(camera, 5);

    // Only run checks if the right buttons are pressed.
    if (keyboardState.leftPressedDown || keyboardState.rightPressedDown || keyboardState.upPressedDown || keyboardState.downPressedDown) {
        cameraControls();
    }
}

class Peasant {

    /**
     * Read the html that represents the basic unit, and set up this object.
     * Then set up any initial css or javascript we need to run the unit's behavior.
     * @param {*} divElement 
     */
    constructor(divElement) {
        let $unit = $(divElement);

        this.name = divElement.id

        if (divElement.style === undefined) {
            divElement.style = {};
        }
        divElement.style.left = $unit.offset().left + 'px';
        divElement.style.top = $unit.offset().top + 'px';

        this.element = divElement;

        this.Directions = createEnum(['Up', 'Down', 'Left', 'Right']);

        this.States = createEnum(['Nothing', 'Working', 'Moving', 'Stopped']);

        this.direction = this.Directions.Up;

        this.currentState = this.States.Nothing;
    }

    moveRight(pxSpeed) {

        if (pxSpeed === undefined) {
            pxSpeed = 1;
        }

        //this.element.style.left = (parseInt(this.element.style.left) - pxSpeed) + 'px';

        console.log("Moving Left");

        return this;
    };

    moveLeft(pxSpeed) {
        if (pxSpeed === undefined) {
            pxSpeed = 1;
        }

        //this.element.style.left = (parseInt(this.element.style.left) + pxSpeed) + 'px';

        console.log("Moving Right");

        return this;
    };

    moveUp(pxSpeed) {

        if (pxSpeed === undefined) {
            pxSpeed = 1;
        }

        //this.element.style.top = (parseInt(this.element.style.top) - pxSpeed) + 'px';

        console.log("Moving Up");

        return this;
    };

    moveDown(pxSpeed) {

        if (pxSpeed === undefined) {
            pxSpeed = 1;
        }

        ///this.element.style.top = (parseInt(this.element.style.top) + pxSpeed) + 'px';

        console.log("Moving Down");

        return this;
    };

    /**
     * Determine what the direction of the peasant is
     * and updateDate the object's state.
     * Then hopefully animate based off of a sprite sheet
     * 
     * @param {*} top 
     * @param {*} left 
     * @returns 
     */
    animateMoveToDirection(top, left) {

        const myLeft = parseInt(this.element.style.left);
        const myTop = parseInt(this.element.style.top);
        const toTop = parseInt(top);
        const toLeft = parseInt(left);

        console.log("Moving From", myTop, myLeft);
        console.log("Moving To", toTop, toLeft);

        let stillMoving = false;

        if ((parseInt(myLeft) == parseInt(toLeft)) && (parseInt(myTop) == parseInt(toTop))) {
            return false;
        }

        if (myLeft < toLeft) {
            this.moveLeft(1);
            stillMoving = true;
        }

        if (myLeft > toLeft) {
            this.moveRight(1);
            stillMoving = true;
        }

        if (myTop > toTop) {
            this.moveUp(1);
            stillMoving = true;
        }

        if (myTop < toTop) {
            this.moveDown(1);
            stillMoving = true;
        }

        return stillMoving;
    }


}

// Global Peasant array
var userPeasants = new Map();

/**
 * I'm going to use the actual div of the peasant to determine attributes and behaviour
 */
function PeasantInitialize(divPeasant) {

    userPeasants.set(divPeasant.id, new Peasant(divPeasant));
}

gameLoop();
camera();

$(document).ready(function () {

    setTimeout(waitForCameraStart, 1000);

    // Only for the peasants we start with, will need to create new peasants at some point and add onclick event on creation.
    const peasants = document.querySelectorAll('.peasant');
    peasants.forEach(peasant => {

        peasant.onClick = function (event) {
            selectUnit(this.id);
        }
        console.log('initializing', peasant);
        PeasantInitialize(peasant);
    });

});
