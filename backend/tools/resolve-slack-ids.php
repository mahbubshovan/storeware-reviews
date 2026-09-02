<?php
/**
 * Resolve the agent directory's "D…" DM channel ids into the "U…" member ids
 * that Slack needs for an <@…> mention, and print the directory ready to paste
 * into backend/config/slack.php.
 *
 * Requires this bot scope (add in the Slack app, then reinstall):
 *   users:read   -> lists workspace members and matches each agent by name
 *
 * Note: im:read does NOT work for the stored D… ids. It only covers DM channels
 * the BOT itself belongs to, whereas those ids are a human's own DM channels
 * with each agent — invisible to the bot, so they return channel_not_found.
 *
 * Usage:  php backend/tools/resolve-slack-ids.php
 */

require_once __DIR__ . '/../config/slack.php';

$token = slack_bot_token();
if (!$token) {
    fwrite(STDERR, "No SLACK_BOT_TOKEN configured (backend/config/.env).\n");
    exit(1);
}

function slack_get($method, $params, $token) {
    $url = 'https://slack.com/api/' . $method . '?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
    ]);
    $body = curl_exec($ch);
    curl_close($ch);

    return json_decode($body, true) ?: ['ok' => false, 'error' => 'unreadable_response'];
}

$directory = slack_agent_directory();
$resolved = [];
$missing = [];

// Preferred route: a D… id names exactly one user, so this is unambiguous.
foreach ($directory as $agent => $ids) {
    if (!empty($ids['member_id'])) {
        $resolved[$agent] = $ids['member_id'];
        continue;
    }
    if (empty($ids['dm_id'])) {
        $missing[$agent] = 'no dm_id on record';
        continue;
    }

    $res = slack_get('conversations.info', ['channel' => $ids['dm_id']], $token);
    if (!empty($res['ok']) && !empty($res['channel']['user'])) {
        $resolved[$agent] = $res['channel']['user'];
    } else {
        $missing[$agent] = $res['error'] ?? 'unknown_error';
    }
}

// Fallback: match the remaining agents by name against the member list.
if ($missing) {
    $res = slack_get('users.list', ['limit' => 500], $token);
    if (!empty($res['ok'])) {
        foreach ($res['members'] as $m) {
            if (!empty($m['deleted']) || !empty($m['is_bot'])) {
                continue;
            }
            $names = array_filter([
                $m['name'] ?? '',
                $m['profile']['display_name'] ?? '',
                $m['profile']['real_name'] ?? '',
            ]);
            foreach (array_keys($missing) as $agent) {
                foreach ($names as $n) {
                    if (stripos($n, $agent) !== false) {
                        $resolved[$agent] = $m['id'];
                        unset($missing[$agent]);
                        break 2;
                    }
                }
            }
        }
    }
}

echo "\nPaste into slack_agent_directory() in backend/config/slack.php:\n\n";
foreach ($directory as $agent => $ids) {
    $member = isset($resolved[$agent]) ? "'" . $resolved[$agent] . "'" : 'null';
    $dm = $ids['dm_id'] ? "'" . $ids['dm_id'] . "'" : 'null';
    printf("        '%s' => ['member_id' => %s, 'dm_id' => %s],\n", $agent, $member, $dm);
}

if ($missing) {
    echo "\nStill unresolved:\n";
    foreach ($missing as $agent => $why) {
        printf("  %-8s %s\n", $agent, $why);
    }
    echo "\nIf that says missing_scope, add im:read (or users:read) to the app's\n";
    echo "Bot Token Scopes and reinstall to the workspace, then re-run this.\n";
}
echo "\n";
