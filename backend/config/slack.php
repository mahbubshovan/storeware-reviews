<?php
/**
 * Slack Integration Configuration
 *
 * Holds the target channel, the per-agent Slack identities, and the bot token
 * lookup used by SlackNotifier. Kept separate from the notifier itself so the
 * IDs can be edited without touching message-building logic.
 */

/**
 * Channel a review is posted to, chosen by the app it belongs to. Several apps
 * intentionally share a channel. Keys are lowercased app names as stored in
 * `reviews.app_name` (see src/config/appConfig.js).
 */
function slack_channel_map() {
    return [
        'storeseo' => 'G01CJS6GCCC',
        'storefaq' => 'C060FP3AGLA',
        'easyflow' => 'C07TX7Q68FM',
        'trustsync' => 'C067HPDQ04Q',
        'vidify' => 'C08Q75LKXSN',
        'betterdocs faq knowledge base' => 'C02H726MBB2',
        // Alias used by some backend responses for the same app.
        'betterdocs faq' => 'C02H726MBB2',
    ];
}

/**
 * Fallback for an app that isn't in the map — an unrecognised or newly added
 * app still gets announced rather than silently vanishing.
 */
if (!defined('SLACK_DEFAULT_CHANNEL_ID')) {
    define('SLACK_DEFAULT_CHANNEL_ID', 'C05R6J3LK8R');
}

/**
 * Resolve the destination channel for an app name.
 */
function slack_channel_for_app($appName) {
    $map = slack_channel_map();
    $key = strtolower(trim((string) $appName));

    if (isset($map[$key])) {
        return $map[$key];
    }

    error_log("SlackNotifier: no channel mapped for app '{$appName}' — using default channel.");

    return SLACK_DEFAULT_CHANNEL_ID;
}

/**
 * Slack identities per agent, keyed by the lowercased agent name stored in
 * `reviews.earned_by` (the same names listed in src/config/agents.js).
 *
 *   member_id  "U…" Slack member ID. This is the ONLY id that works for an
 *              <@…> mention inside a channel post. Null => we fall back to
 *              posting the agent's plain-text name.
 *   dm_id      "D…" personal DM channel id, belonging to whoever collected it.
 *              It does NOT resolve as a mention, and the bot cannot read it
 *              either: these are a human's DM channels with each agent, not the
 *              bot's own, so conversations.info answers channel_not_found even
 *              when the app holds im:read. Kept only as a record of provenance.
 *
 * Member ids verified against users.info on 2026-09-02. If someone new joins,
 * grab their id from Slack (avatar -> View full profile -> ⋮ More -> "Copy
 * member ID") or re-run backend/tools/resolve-slack-ids.php with users:read.
 * Zeba is the one agent with no ids by choice — see the row below.
 */
function slack_agent_directory() {
    return [
        'ashik' => ['member_id' => 'U04H409MXK5', 'dm_id' => 'D04LEQUC5T5'],
        'jen'   => ['member_id' => 'U07L386AU7J', 'dm_id' => 'D082BDW32P2'],
        'pial'  => ['member_id' => 'U0351UFTD9Q', 'dm_id' => 'D038F0ZFSQH'],
        'amin'  => ['member_id' => 'U04HGNAME4R', 'dm_id' => 'D04J22RT8TW'],
        'vinz'  => ['member_id' => 'U08872FDQ2C', 'dm_id' => 'D08CPCT5FRD'],
        'nadvi' => ['member_id' => 'U02BV7RP820', 'dm_id' => 'D02CCNF904Q'],
        'amit'  => ['member_id' => 'U02DQ9Y1ZFS', 'dm_id' => 'D02CU1EE2EA'],
        'abid'  => ['member_id' => 'U0145JY1JQP', 'dm_id' => 'D014S2CDV1U'],
        // Zeba is deliberately left without ids — reviews credited to her post
        // with her name as plain text rather than a mention.
        'zeba'  => ['member_id' => null, 'dm_id' => null],
    ];
}

/**
 * Look up one agent's Slack identity by display name (case-insensitive).
 * Returns null when the agent isn't in the directory.
 */
function slack_agent_identity($agentName) {
    $key = strtolower(trim((string) $agentName));
    $directory = slack_agent_directory();

    return $directory[$key] ?? null;
}

/**
 * Slack member id ("U…") for an agent, or null when we can't mention them.
 * Deliberately ignores `dm_id` — a "D…" id renders as broken text in a channel
 * post rather than as a mention, so it must never be used here.
 */
function slack_member_id($agentName) {
    $identity = slack_agent_identity($agentName);
    if (!$identity || empty($identity['member_id'])) {
        return null;
    }

    $memberId = trim($identity['member_id']);

    // Guard against a "D…" id being pasted into the member_id slot by mistake.
    return strtoupper(substr($memberId, 0, 1)) === 'U' ? $memberId : null;
}

/**
 * Bot token, read from the environment the same way Database reads its config:
 * system env first, then an optional backend/config/.env file.
 */
function slack_bot_token() {
    foreach (['SLACK_BOT_TOKEN', 'SLACK_TOKEN'] as $name) {
        if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
            return trim($_ENV[$name]);
        }
        if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') {
            return trim($_SERVER[$name]);
        }
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return trim($value);
        }
    }

    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $env = parse_ini_file($envFile);
        foreach (['SLACK_BOT_TOKEN', 'SLACK_TOKEN'] as $name) {
            if (!empty($env[$name])) {
                return trim($env[$name]);
            }
        }
    }

    return null;
}
