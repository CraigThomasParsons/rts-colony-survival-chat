<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * Basic Dusk acceptance test skeleton for the "create new game" -> "start mapgen" flow.
 *
 * Notes:
 * - This is a smoke test / happy-path test designed to demonstrate the UI flow.
 * - It assumes you have a working User factory and that Dusk has been installed.
 * - For CI and repeatable runs, configure the test environment to use an in-memory or disposable DB.
 */
class CreateGameTest extends DuskTestCase
{
    /**
     * Test creating a new game and starting map generation.
     *
     * This test:
     *  - creates a test user via factory
     *  - logs in as that user
     *  - visits the "Create New Game" form
     *  - fills the form and submits it
     *  - verifies we land on the map generation page (or progress)
     *  - starts map generation and ensures progress page is shown
     *
     * @return void
     * @throws \Throwable
     */
    public function testCreateGameAndStartMapgen()
    {
        // Create a test user using factory. Adjust if your project uses different factory APIs.
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/new-game')
                ->assertSee('Create New Game')
                // Fill basic required fields; input names in the form are 'name', 'width', 'height'
                ->type('name', 'Dusk Test Game')
                ->type('width', 64)
                ->type('height', 38)
                // Submit the form (button text is "Create Game")
                ->press('Create Game')
                // Wait for redirect to the map generation form or progress route
                ->pause(800) // small pause to allow redirect; prefer waitForLocation in a robust environment
                ->assertPathBeginsWith('/game/')
                // We expect the map generation page to show a "Map Generation" header
                ->assertSee('Map Generation')
                // If the form is shown, submit the seed form (seed input name is 'seed')
                ->whenAvailable('form', function (Browser $form) {
                    // nothing here; keep the flow simple
                })
                // Attempt to start generation (button text "Start Map Generation" exists on mapgen view)
                ->clickLink('Start Map Generation')
                // Wait for the progress page to load and show the progress header
                ->waitForText('Map Generation Progress', 10)
                ->assertSee('Map Generation Progress');
        });
    }
}
