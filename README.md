**Edit a file, create a new file, and clone from Bitbucket in under 2 minutes**

When you're done, you can delete the content in this README and update the file with details for others getting started with your repository.

*We recommend that you open this README in another tab as you perform the tasks below. You can [watch our video](https://youtu.be/0ocf7u76WSo) for a full demo of all the steps in this tutorial. Open the video in a new tab to avoid leaving Bitbucket.*

---

## Edit a file

You’ll start by editing this README file to learn how to edit a file in Bitbucket.

1. Click **Source** on the left side.
2. Click the README.md link from the list of files.
3. Click the **Edit** button.
4. Delete the following text: *Delete this line to make a change to the README from Bitbucket.*
5. After making your change, click **Commit** and then **Commit** again in the dialog. The commit page will open and you’ll see the change you just made.
6. Go back to the **Source** page.

---

## Create a file

Next, you’ll add a new file to this repository.

1. Click the **New file** button at the top of the **Source** page.
2. Give the file a filename of **contributors.txt**.
3. Enter your name in the empty file space.
4. Click **Commit** and then **Commit** again in the dialog.
5. Go back to the **Source** page.

Before you move on, go ahead and explore the repository. You've already seen the **Source** page, but check out the **Commits**, **Branches**, and **Settings** pages.

---

## Clone a repository

Use these steps to clone from SourceTree, our client for using the repository command-line free. Cloning allows you to work on your files locally. If you don't yet have SourceTree, [download and install first](https://www.sourcetreeapp.com/). If you prefer to clone from the command line, see [Clone a repository](https://confluence.atlassian.com/x/4whODQ).

1. You’ll see the clone button under the **Source** heading. Click that button.
2. Now click **Check out in SourceTree**. You may need to create a SourceTree account or log in.
3. When you see the **Clone New** dialog in SourceTree, update the destination path and name if you’d like to and then click **Clone**.
4. Open the directory you just created to see your repository’s files.

<<<<<<< HEAD
### How to use
1. Ensure the `app/Helpers` directory is present (it was merged into the project).  
2. Run the commands in order to build the map into your DB / map model:  

```bash
php artisan map:1init
php artisan map:2firststep-tiles
php artisan map:3mountain
php artisan map:4water
```

3. Review `resources/views/mapgen/` for a minimal UI to preview progress (optional).

### Notes on integration
- The mapgen uses its own MapDatabase and Cell models under `app/Helpers/MapDatabase/`. If you wish to map these directly to your game's `tiles` and `resource_nodes` tables, I can add an importer that converts generated Cells into Eloquent `Tile` and `ResourceNode` rows. Tell me if you'd like me to implement that importer (recommended).

- The map generator supports deterministic seeds. Pass seed options via the console commands (check command options in `app/Console/Commands/`).

## Co-Op Procedurally generated RTS Colony survival

That is the idea anyways

Includes:

- A* pathfinding
- GameEngine tick-loop integration
- Procedurally Generated Terrain

🗺️ Gameplay Overview 🧑‍🌾 Colonists

Each colonist has:

- Stats

- Mood

- Needs

- Skills

- Current job state

- Pathfinding agent

🔁 Tick Loop

- The entire simulation ticks at 250 ms intervals:

- Update colonists

- Process jobs

- Evaluate states

- Move units

- Harvest/build

- Sync to clients

🌾 Resources

- Trees
- Stone
- Fields (wheat, barley, vegetables)
- Forageables

🏗️ Buildings

- Stockpiles
- Houses
- Workshops
- Farms
- Storage huts
- Walls and defenses

🧭 A* Pathfinding

- Custom binary min-heap (~40% faster than SplPriorityQueue)
- Terrain weights (mud, grass, roads)
- Diagonal movement
- Early exit optimization

🤝 Contributing

- Contributions are welcome!
- Fork the repo
- Create a feature branch
- Submit a PR
- Include test coverage where appropriate

📅 Planned Features (Roadmap) 🌱 Gameplay

- Temperature system & seasons
- Hunting & wildlife
- Illness + medicine
- Bandit raids
- Diplomacy/reputation system

⚙️ Systems

- Save/load multiple worlds
- Deeper colonist AI (psych traits, work priorities RimWorld-style)
- Auto-designated work zones
- Blueprint system for buildings

🌐 Multiplayer Enhancements

- Player factions
- Territory
- Shared trade economy
=======
