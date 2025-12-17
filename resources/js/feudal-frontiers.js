import Phaser from "phaser";

// Simple Boot scene
class BootScene extends Phaser.Scene {
    constructor() {
        super("BootScene");
    }

    preload() {
        // later: load spritesheets, tiles, UI
        this.load.image("grass", "/images/game/tiles/grass.png");
        this.load.image("worker", "/images/game/units/worker.png");
    }

    create() {
        this.scene.start("MapScene");
    }
}

// Basic map scene for tonight
class MapScene extends Phaser.Scene {
    constructor() {
        super("MapScene");
    }

    create() {
        const { width, height } = this.scale;

        // Simple 10x10 tile field
        const tileSize = 64;
        for (let y = 0; y < 10; y++) {
            for (let x = 0; x < 10; x++) {
                this.add.image(
                    x * tileSize + tileSize / 2,
                    y * tileSize + tileSize / 2,
                    "grass"
                ).setOrigin(0.5);
            }
        }

        // Drop a single worker in the middle
        this.worker = this.add.image(width / 2, height / 2, "worker");

        // Simple click-to-move for tonight
        this.input.on("pointerdown", (pointer) => {
            this.tweens.add({
                targets: this.worker,
                x: pointer.x,
                y: pointer.y,
                duration: 400,
                ease: "Sine.easeInOut",
            });

            // Later: send this intent to backend
            // window.dispatchEvent(new CustomEvent('workerMoveRequested', { detail: { x: pointer.x, y: pointer.y }}))
        });
    }
}

// Boot the game – this will be called from Blade once
export function startFeudalFrontiersGame(containerId = "game-container") {
    const config = {
        type: Phaser.AUTO,
        width: 1280,
        height: 720,
        parent: containerId,
        backgroundColor: "#1b3b26",
        scene: [BootScene, MapScene],
        pixelArt: true,
    };

    return new Phaser.Game(config);
}
