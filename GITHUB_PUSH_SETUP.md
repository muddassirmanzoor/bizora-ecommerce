# GitHub push from this server

Git is connected to your repos. To **push from this server** you need to authenticate once.

## Remotes

| Remote   | Repo |
|----------|------|
| **origin**  | https://github.com/muddassirmanzoor/bizora-ecommerce (personal) |
| **vestbee** | https://github.com/vestbeegit/toolproject (client) |

- Push to **personal**: `git push origin main`
- Push to **client**: `git push vestbee main`

## Option 1: GitHub CLI (easiest)

```bash
# Install (if not installed): https://cli.github.com/
gh auth login
# Follow prompts: HTTPS, login in browser, select scopes (repo)
```

Then `git push origin main` will use your GitHub login.

## Option 2: Personal Access Token (HTTPS)

1. GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)** → **Generate new token**.
2. Name it (e.g. "this server"), enable scope **repo**.
3. Copy the token.
4. From this project folder:
   ```bash
   cd /home/site-commerce/htdocs/commerce.site.com/bagisto-master
   git push origin main
   ```
5. When asked for **Username**: your GitHub username (`muddassirmanzoor`).
6. When asked for **Password**: paste the **token** (not your GitHub password).

Git will remember the credential if credential helper is enabled.

## Option 3: SSH key

1. Generate a key on this server (if you don’t have one):
   ```bash
   ssh-keygen -t ed25519 -C "your_email@example.com" -f ~/.ssh/id_ed25519 -N ""
   ```
2. Add the **public** key to GitHub: **Settings** → **SSH and GPG keys** → **New SSH key** → paste contents of `~/.ssh/id_ed25519.pub`.
3. Switch this repo to SSH and push:
   ```bash
   cd /home/site-commerce/htdocs/commerce.site.com/bagisto-master
   git remote set-url origin git@github.com:muddassirmanzoor/bizora-ecommerce.git
   git push origin main
   ```

Use the same idea for **vestbee** if you push there (replace with `vestbeegit/toolproject` and the URL for that repo).

## Current status

- Branch **main** is tracking **origin/main**.
- You have local changes (modified files, new seeders). When ready:
  ```bash
  git add .
  git commit -m "Your message"
  git push origin main
  ```
- Files under `storage/` and `node_modules/`, `.env` are ignored and won’t be committed.
