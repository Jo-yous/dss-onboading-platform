# Rhapsody of Realities Daily Posting Bot

## Project Overview

**Purpose:** Automatically post one tweet per day containing a short excerpt/teaser of the current day's Rhapsody of Realities devotional (title, key verse or short text, link to full reading).

**Key constraint:** Uses only X API free tier → no reading/searching tweets, no liking, no retweeting. **Only posting is allowed.**

**Frequency:** 1 post per day (≈30 posts/month → safe within free limit).

**Stack:** JavaScript (Node.js 18+), [twitter-api-v2](https://www.npmjs.com/package/twitter-api-v2).

**Hosting:** Local machine + cron, or free cloud (Railway.app, Render.com, Fly.io, etc.).

**Compliance:** The bot account must clearly disclose automation in its bio (see example below).

---

## Prerequisites

- Node.js 18+ installed
- A dedicated X account for the bot (do not use a personal account)
- X Developer App with free tier access

---

## Step 1 – X Account & Developer Setup

### Create bot X account

- **Username suggestion:** @RhapsodyExcerpts, @DailyRhapsodyBot, etc.
- **Bio (required):**  
  `Unofficial automated daily excerpts from Rhapsody of Realities • Not affiliated with Loveworld • Full devotional: https://read.rhapsodyofrealities.org/`

### Register developer account

- Go to [https://developer.x.com](https://developer.x.com)
- Sign in with the bot account
- Apply / complete profile (e.g. “Automated daily posting of public devotional excerpts”)

### Create Project & App

- **Projects & Apps** → **+ New Project**
- **Project name:** e.g. “Rhapsody Daily Poster”
- **Use case:** Making a bot / Automated posting
- **App name:** unique (e.g. `rhapsody-daily-poster-2026`)
- **App type:** Automated App / Bot
- **Website URL:** https://example.com (placeholder is ok)
- **Permissions:** Read + Write
- **Authentication:** OAuth 1.0a User Context (required for posting)

### Generate & save credentials

From **App** → **Keys and Tokens**:

- API Key
- API Key Secret
- Access Token
- Access Token Secret

Store them securely (never in git). Use a `.env` file (see Step 2).

---

## Step 2 – Install and Run

### Install dependencies

```bash
cd rhapsody-daily-bot
npm install
```

### Configure environment

```bash
cp .env.example .env
```

Edit `.env` and set:

- `X_API_KEY` – API Key  
- `X_API_SECRET` – API Key Secret  
- `X_ACCESS_TOKEN` – Access Token  
- `X_ACCESS_SECRET` – Access Token Secret  

Optional (for a custom daily excerpt):

- `TODAY_TITLE` – e.g. today’s devotional title  
- `TODAY_VERSE` or `TODAY_TEXT` – key verse or short teaser  

If these are not set, the bot posts a generic teaser plus the link.

### Testing with your own Twitter account

The account that actually posts is determined by the **Access Token** and **Access Token Secret** in your `.env`, not by the app itself. So you can test with your personal account first:

1. Sign in to [developer.x.com](https://developer.x.com) with **your own X account** (the one you want to post from).
2. Create a **Project** and **App** (or use an existing one). Set permissions to **Read + Write**, app type **Automated App / Bot**.
3. Under the app, go to **Keys and Tokens** and generate:
   - **API Key** and **API Key Secret**
   - **Access Token** and **Access Token Secret** (these will be for the account you’re signed in as — i.e. your account)
4. Put those four values into `rhapsody-daily-bot/.env` as `X_API_KEY`, `X_API_SECRET`, `X_ACCESS_TOKEN`, `X_ACCESS_SECRET`.
5. Run `npm run post` — the tweet will appear on **your** timeline.

When you’re ready to run the real bot, repeat the process while signed in as the Rhapsody bot account and put the bot’s tokens in `.env` (or in your host’s environment variables).

### Post once (manual test)

```bash
npm run post
```

Or:

```bash
node post-daily.js
```

### Schedule once per day

**Windows (Task Scheduler):** Create a daily task that runs at your chosen time, e.g.:

```powershell
node "C:\path\to\rhapsody-daily-bot\post-daily.js"
```

Set “Start in” to the project directory so `.env` is found.

**Linux/macOS (cron):** Add a line to `crontab -e`, e.g. 8:00 AM daily:

```cron
0 8 * * * cd /path/to/rhapsody-daily-bot && node post-daily.js
```

**Cloud (Railway, Render, Fly.io, etc.):** Use their cron/scheduled job feature to run `node post-daily.js` once per day; ensure the app has access to your env vars.

---

## Tweet format

- Default:  
  `Today's Rhapsody of Realities is ready. Read the full devotional 👇`  
  `Full devotional: https://read.rhapsodyofrealities.org/`

- With `TODAY_TITLE` and `TODAY_VERSE`/`TODAY_TEXT`:  
  Title and verse/excerpt are used as the teaser, then the same link. Text is trimmed to 280 characters if needed.

---

## License

MIT.
