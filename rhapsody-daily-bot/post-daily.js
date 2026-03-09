/**
 * Rhapsody of Realities Daily Posting Bot
 * Posts one tweet per day with a teaser and link to the full devotional.
 * Uses X API v2 with OAuth 1.0a (free tier – posting only).
 */

import 'dotenv/config';
import { TwitterApi } from 'twitter-api-v2';

const RHAPSODY_LINK = 'https://read.rhapsodyofrealities.org/';
const MAX_TWEET_LENGTH = 280;

function getEnv(name) {
  const value = process.env[name];
  if (!value) throw new Error(`Missing required env: ${name}`);
  return value;
}

/**
 * Build today's tweet text.
 * Optional env: TODAY_TITLE, TODAY_VERSE (or TODAY_TEXT) for custom excerpt.
 * Otherwise uses a generic teaser.
 */
function buildTweetText() {
  const title = process.env.TODAY_TITLE?.trim();
  const verse = process.env.TODAY_VERSE?.trim() || process.env.TODAY_TEXT?.trim();

  let teaser;
  if (title && verse) {
    teaser = `📖 ${title}\n\n"${verse}"`;
  } else if (title) {
    teaser = `📖 ${title}`;
  } else if (verse) {
    teaser = `"${verse}"`;
  } else {
    teaser = "Today's Rhapsody of Realities is ready. Read the full devotional 👇";
  }

  const text = `${teaser}\n\nFull devotional: ${RHAPSODY_LINK}`;

  if (text.length > MAX_TWEET_LENGTH) {
    const maxTeaser = MAX_TWEET_LENGTH - (RHAPSODY_LINK.length + 25); // "Full devotional: " + link + newlines
    return `${teaser.slice(0, maxTeaser - 3)}…\n\nFull devotional: ${RHAPSODY_LINK}`;
  }
  return text;
}

async function main() {
  const appKey = getEnv('X_API_KEY');
  const appSecret = getEnv('X_API_SECRET');
  const accessToken = getEnv('X_ACCESS_TOKEN');
  const accessSecret = getEnv('X_ACCESS_SECRET');

  const client = new TwitterApi({
    appKey,
    appSecret,
    accessToken,
    accessSecret,
  });

  const text = buildTweetText();
  console.log('Posting tweet (%d chars):\n%s', text.length, text);

  const tweet = await client.v2.tweet(text);
  console.log('Posted successfully. Tweet ID:', tweet.data.id);
}

main().catch((err) => {
  console.error('Error:', err.message);
  process.exit(1);
});
