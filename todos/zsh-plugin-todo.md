# zsh plugin troubleshooting TODO

Goal: Fix the "[oh-my-zsh] plugin '...' not found" messages when opening the terminal.

Notes:
- Oh My Zsh looks for plugins in `~/.oh-my-zsh/plugins/` and `~/.oh-my-zsh/custom/plugins/`.
- Common missing plugins: `zsh-autosuggestions`, `zsh-syntax-highlighting`, `zsh-256color`.

Checklist (work through top → bottom). Check each box as you complete it.

- [ ] 1) Inspect configured plugins
  - Open `~/.zshrc` and find the `plugins=(...)` line.
  - Copy the exact line somewhere for reference (paste into a message if you want me to review).
  - Example form: `plugins=(git zsh-autosuggestions zsh-syntax-highlighting zsh-256color)`

- [ ] 2) List plugin folders to see what actually exists
  - Run: `ls -la ~/.oh-my-zsh/plugins`
  - Run: `ls -la ~/.oh-my-zsh/custom/plugins`
  - Save the output (or paste it here) to confirm which plugins are missing.

- [ ] 3) Decide per-missing-plugin: install it or remove it from `plugins=(...)`
  - If you want the feature, install the plugin into `~/.oh-my-zsh/custom/plugins/`.
  - If you don't need it, edit `~/.zshrc` and remove the plugin name from `plugins=(...)`.

- [ ] 4) Install common plugins (if you choose to install)
  - Install `zsh-autosuggestions`:
    - `git clone https://github.com/zsh-users/zsh-autosuggestions.git ~/.oh-my-zsh/custom/plugins/zsh-autosuggestions`
  - Install `zsh-syntax-highlighting`:
    - `git clone https://github.com/zsh-users/zsh-syntax-highlighting.git ~/.oh-my-zsh/custom/plugins/zsh-syntax-highlighting`
  - Tip: make sure the clone commands finish without error and that the cloned directories exist.

- [ ] 5) Ensure `zsh-syntax-highlighting` is sourced last
  - `zsh-syntax-highlighting` should be loaded near the end of `~/.zshrc`.
  - Add (near the end of the file): `source ~/.oh-my-zsh/custom/plugins/zsh-syntax-highlighting/zsh-syntax-highlighting.zsh`
  - Do NOT duplicate multiple sources—only one is needed.

- [ ] 6) Handle `zsh-256color`
  - Option A (if you need that plugin): find and install the plugin that provides `zsh-256color` into `~/.oh-my-zsh/custom/plugins/`.
  - Option B (likely simpler): remove `zsh-256color` from `plugins=(...)` and instead ensure your terminal uses 256 colors:
    - Set terminal `$TERM`, for example: `export TERM=xterm-256color` (put in `~/.zshrc` if needed).
    - Enable shell colors: `autoload -U colors && colors`
  - If you know which repo provides `zsh-256color`, clone it into the custom plugins folder as with other plugins.

- [ ] 7) Reload Zsh / test
  - Reload config: `source ~/.zshrc`
  - Or restart the shell: `exec zsh`
  - Open a new terminal and confirm the error messages are gone.

- [ ] 8) If errors persist, collect diagnostics
  - Paste the `plugins=(...)` line from `~/.zshrc`.
  - Paste the outputs of:
    - `ls -la ~/.oh-my-zsh/plugins`
    - `ls -la ~/.oh-my-zsh/custom/plugins`
    - `echo $ZSH` (shows Oh My Zsh root, optional)
    - `echo $TERM`
  - Note any error text you saw when cloning plugin repos.

Troubleshooting tips / gotchas
- Typos in the `plugins=(...)` names will trigger "not found".
- Plugin directory name must match the plugin name referenced in `plugins=(...)`.
- If a plugin repo was cloned but the folder name differs, either rename it to match or update the `plugins=(...)` list.
- If `zsh-syntax-highlighting` doesn't work, ensure it is sourced after other plugin initializations and after `oh-my-zsh` has initialized.
- If `git clone` fails, check network, proxy, or authentication issues.

Optional follow-ups (if you want me to handle more)
- I can produce the exact `git clone` commands for any other plugin you list.
- I can suggest a minimal `plugins=(...)` line for a fast, clean configuration.
- I can generate a small shell script to automatically install the three common plugins into `~/.oh-my-zsh/custom/plugins/`.

Estimated time: 5–15 minutes for basic fixes (inspect, install 1–3 plugins, reload).

Priority: High (clears terminal startup noise).

End.