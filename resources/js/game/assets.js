// Centralized asset loader for placeholder and real game art.
// Provides graceful fallback: if PNG not found, tries JPEG, then logs warning.

// Asset registration supports images and sprite sheets.
// Worker now expected as a sprite sheet: /images/game/units/worker_sheet.png (horizontal row of frames).
// Provide frameWidth/frameHeight options below.

export function registerPhaserAssets(scene, { workerFrames = 4, frameWidth = 64, frameHeight = 64 } = {}) {
    const assets = [
        { type: 'image', key: 'grass', pathPng: '/images/game/tiles/grass.png', pathJpg: '/images/game/tiles/grass.jpg' },
        { type: 'spritesheet', key: 'worker', pathPng: '/images/game/units/worker_sheet.png', pathJpg: '/images/game/units/worker_sheet.jpg', frameWidth, frameHeight, endFrame: workerFrames - 1 },
    ];

    assets.forEach(asset => {
        if (asset.type === 'image') {
            scene.load.image(asset.key, asset.pathPng);
        } else if (asset.type === 'spritesheet') {
            scene.load.spritesheet(asset.key, asset.pathPng, {
                frameWidth: asset.frameWidth,
                frameHeight: asset.frameHeight,
                endFrame: asset.endFrame,
            });
        }
    });
}

export function postLoadFallback(scene, { frameWidth = 64, frameHeight = 64 } = {}) {
    const fallbacks = [
        { type: 'image', key: 'grass', alt: '/images/game/tiles/grass.jpg' },
        { type: 'spritesheet', key: 'worker', alt: '/images/game/units/worker_sheet.jpg', singleFrameAlt: '/images/game/units/worker.png' },
    ];

    fallbacks.forEach(asset => {
        if (!scene.textures.exists(asset.key)) {
            // Try alternate sheet/image
            if (asset.type === 'image') {
                scene.load.image(asset.key, asset.alt);
            } else if (asset.type === 'spritesheet') {
                scene.load.spritesheet(asset.key, asset.alt, { frameWidth, frameHeight });
            }
            scene.load.once('complete', () => {
                if (!scene.textures.exists(asset.key) && asset.singleFrameAlt) {
                    // Fallback to single-frame image if sheet missing
                    scene.load.image(asset.key, asset.singleFrameAlt);
                    scene.load.once('complete', () => {
                        if (!scene.textures.exists(asset.key)) {
                            console.warn(`Asset '${asset.key}' missing in all attempted formats.`);
                        }
                    });
                    scene.load.start();
                } else if (!scene.textures.exists(asset.key)) {
                    console.warn(`Asset '${asset.key}' missing in both primary and fallback sources.`);
                }
            });
            scene.load.start();
        }
    });
}

export function createWorkerAnimations(scene, { idleFrameRate = 6, workerFrames = 4, rows = { idle: 0, north: 1, south: 2, east: 3, west: 4 } } = {}) {
    if (!scene.textures.exists('worker')) return;
    const existing = scene.anims.get('worker-idle');
    if (!existing) {
        scene.anims.create({
            key: 'worker-idle',
            frames: scene.anims.generateFrameNumbers('worker', { start: rows.idle * workerFrames, end: rows.idle * workerFrames + (workerFrames - 1) }),
            frameRate: idleFrameRate,
            repeat: -1,
        });
    }

    if (!scene.anims.get('worker-walk-north')) {
        scene.anims.create({
            key: 'worker-walk-north',
            frames: scene.anims.generateFrameNumbers('worker', { start: rows.north * workerFrames, end: rows.north * workerFrames + (workerFrames - 1) }),
            frameRate: Math.max(8, idleFrameRate),
            repeat: -1,
        });
    }
    if (!scene.anims.get('worker-walk-south')) {
        scene.anims.create({
            key: 'worker-walk-south',
            frames: scene.anims.generateFrameNumbers('worker', { start: rows.south * workerFrames, end: rows.south * workerFrames + (workerFrames - 1) }),
            frameRate: Math.max(8, idleFrameRate),
            repeat: -1,
        });
    }
    if (rows.east !== undefined && !scene.anims.get('worker-walk-east')) {
        scene.anims.create({
            key: 'worker-walk-east',
            frames: scene.anims.generateFrameNumbers('worker', { start: rows.east * workerFrames, end: rows.east * workerFrames + (workerFrames - 1) }),
            frameRate: Math.max(8, idleFrameRate),
            repeat: -1,
        });
    }
    if (rows.west !== undefined && !scene.anims.get('worker-walk-west')) {
        scene.anims.create({
            key: 'worker-walk-west',
            frames: scene.anims.generateFrameNumbers('worker', { start: rows.west * workerFrames, end: rows.west * workerFrames + (workerFrames - 1) }),
            frameRate: Math.max(8, idleFrameRate),
            repeat: -1,
        });
    }
}
