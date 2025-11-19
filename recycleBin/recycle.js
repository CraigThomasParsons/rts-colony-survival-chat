// I'm just moving things here that I'm not sure I'll be using again

document.onkeydown = function(KeyboardEvent) {
    const HERO_SPEED = 2000;

    // Move Camera left.
    if (KeyboardEvent.key === 'a') {

        keyboardState.leftPressedDown = true;

    // Move Camera right.
    } else if (KeyboardEvent.key === 'd') {

        keyboardState.rightPressedDown = true;
    }

    // Move Camera up.
    if (KeyboardEvent.key === 'w') {

        keyboardState.upPressedDown = true;

    // Move Camera down.
    } else if (KeyboardEvent.key === 's') {

        keyboardState.downPressedDown = true;
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
 * Move the peasant using the css properties.
 * I was using simple keyboard commands to test this out
 * This isn't necessary, we won't use keyboard controls like this in the future.
 */
function moveHero(left, top) {
    let heroElement = document.getElementById('hero');

    if (left !== null) {
        heroElement.style.left = left + 'px';
    }

    if (top !== null) {
        heroElement.style.top = top + 'px';
    }
}