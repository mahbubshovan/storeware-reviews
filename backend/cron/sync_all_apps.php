<?php
// Scheduled review sync — the Analytics tab's "Sync Reviews", run for every app,
// announcing genuinely new reviews in Slack.
//
// Per app it does exactly what pressing that button does: UniversalLiveScraper
// reads page 1 of the app's Shopify listing, saves reviews it hasn't seen,
// mirrors them into access_reviews and refreshes app_metadata.
//
// A review is announced only when all three hold:
//   * this run is what stored it (its id is above the app's highest id
//     beforehand), so re-reading the same page changes nothing;
//   * it is dated today or yesterday (ANNOUNCE_MAX_AGE_DAYS), so a backfill of
//     older reviews lands in the database quietly instead of flooding a channel;
//   * it isn't already in review_announcements, which remembers every post and
//     holds even if a row is deleted and scraped again under a new id.
//
// The post is the message the Send to Slack button builds, crediting whoever the
// review names (config/agent_aliases.php) and leaving the credit line off when
// nobody is named.
//
// Usage: php backend/cron/sync_all_apps.php
//        php backend/cron/sync_all_apps.php --test-post=907003
//          announces one stored review through the same path, without scraping
//          and without touching the ledger, for checking an environment's Slack
//          wiring.
//
// Cron, every 6 hours:
//   0 */6 * * * /usr/bin/php /path/to/backend/cron/sync_all_apps.php >> /path/to/logs/sync_all_apps.log 2>&1

// Command line only — a full sync runs for minutes and must not be web-triggerable.
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script can only be run from command line.');
}

set_time_limit(1800);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../config/apps.php';
require_once __DIR__ . '/../config/agent_aliases.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/../utils/ReviewLink.php';
require_once __DIR__ . '/../utils/SlackNotifier.php';

// Log timestamps and "how old is this review" follow Bangladesh time, the team's day.
date_default_timezone_set('Asia/Dhaka');

// How old a newly stored review may be and still reach Slack: today or yesterday.
// The sync runs four times a day, so anything genuinely new is hours old. Older
// rows are still stored, they just stay out of the channels.
define('ANNOUNCE_MAX_AGE_DAYS', 1);

$logFile = __DIR__ . '/../../logs/sync_all_apps.log';
$lockFile = __DIR__ . '/../../logs/sync_all_apps.lock';

if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

// Database reads $_SERVER['HTTP_HOST'] for its platform logging, which CLI has no
// reason to set. PHP notices and the scraper's own error_log lines go beside the
// run log rather than into it, so the log reads as a record of what was announced.
$_SERVER['HTTP_HOST'] = 'cli';
ini_set('error_log', dirname($logFile) . '/sync_all_apps.errors.log');

function sync_log($message) {
    global $logFile;

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    echo $line . PHP_EOL;
    file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);
}

/**
 * The ledger of announced reviews, created on first run.
 *
 * Keyed by app, store and review date rather than review id: a store reviews an
 * app once, so that triple identifies the review even if the row is rebuilt.
 */
function ensure_announcement_ledger($conn) {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS review_announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            review_id INT NOT NULL,
            app_name VARCHAR(100) NOT NULL,
            store_name VARCHAR(255) NOT NULL,
            review_date DATE NOT NULL,
            channel VARCHAR(32) NOT NULL,
            announced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_announced_review (app_name, store_name, review_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/**
 * Whether this review has already been posted to Slack by an earlier run.
 */
function already_announced($conn, $review) {
    $stmt = $conn->prepare('SELECT 1 FROM review_announcements
                            WHERE app_name = ? AND store_name = ? AND review_date = ? LIMIT 1');
    $stmt->execute([$review['app_name'], $review['store_name'], $review['review_date']]);

    return (bool) $stmt->fetchColumn();
}

/**
 * Post one stored review to its app's Slack channel, crediting whoever it names.
 * Returns whether Slack accepted it.
 *
 * $remember writes the post to the ledger; --test-post passes false so a manual
 * check can't stop the real announcement later.
 */
function announce_review($conn, $review, $remember = true) {
    $match = agent_match_in_review($review['review_content'] ?? '');

    // A name in the review decides the credit. With none, fall back to whoever
    // the dashboard already credited, which only applies to --test-post.
    $agent = $match['agent'] ?? trim((string) ($review['earned_by'] ?? ''));
    if (strcasecmp($agent, 'Organic') === 0) {
        $agent = '';
    }

    $review['review_url'] = shopify_review_url($conn, $review);
    $channel = slack_channel_for_app($review['app_name']);
    $result = SlackNotifier::notifyReviewAssignment($review, $agent);

    if ($match) {
        $credit = sprintf('credited to %s (review says "%s")', $match['agent'], $match['alias']);
    } elseif ($agent !== '') {
        $credit = sprintf('credited to %s (already assigned)', $agent);
    } else {
        $credit = 'no support name found — posted without a credit line';
    }

    $link = $review['review_url'] ? 'with link' : 'no Shopify link found';

    if (!empty($result['sent'])) {
        if ($remember) {
            $stmt = $conn->prepare('INSERT IGNORE INTO review_announcements
                                        (review_id, app_name, store_name, review_date, channel)
                                    VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$review['id'], $review['app_name'], $review['store_name'],
                            $review['review_date'], $channel]);
        }

        sync_log(sprintf('     -> Slack %s: %s, %s, %s', $channel, $review['store_name'], $credit, $link));
        return true;
    }

    sync_log(sprintf('     -> Slack %s FAILED for %s: %s', $channel, $review['store_name'],
        $result['error'] ?? $result['skipped'] ?? 'unknown error'));

    return false;
}

$conn = (new Database())->getConnection();
ensure_announcement_ledger($conn);

// --test-post=<id>: announce one stored review and stop, for checking Slack.
$testPostId = 0;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--test-post=(\d+)$/', $arg, $matches)) {
        $testPostId = (int) $matches[1];
    }
}

if ($testPostId > 0) {
    $stmt = $conn->prepare('SELECT id, app_name, store_name, country_name, rating,
                                   review_content, review_date, earned_by
                            FROM reviews WHERE id = ?');
    $stmt->execute([$testPostId]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        sync_log("Test post: no review with id $testPostId");
        exit(1);
    }

    sync_log(sprintf('Test post: #%d %s — %s (%s)',
        $review['id'], $review['app_name'], $review['store_name'], $review['review_date']));

    exit(announce_review($conn, $review, false) ? 0 : 1);
}

// A sync takes minutes. If the previous run is still going, skip this turn
// rather than scrape the same pages twice over.
$lock = fopen($lockFile, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    sync_log('Previous sync still running — skipping this run.');
    exit(0);
}

$startedAt = microtime(true);
$apps = shopify_apps();
$scraper = new UniversalLiveScraper();
$oldestWorthAnnouncing = date('Y-m-d', strtotime('-' . ANNOUNCE_MAX_AGE_DAYS . ' days'));
$totalNew = 0;
$announced = 0;
$announceFailures = 0;
$skipped = 0;
$failed = [];

sync_log('Sync started for ' . count($apps) . ' apps');

foreach ($apps as $appName => $appSlug) {
    try {
        // Anything stored above this id during the scrape is a review we hadn't seen.
        $stmt = $conn->prepare('SELECT COALESCE(MAX(id), 0) FROM reviews WHERE app_name = ?');
        $stmt->execute([$appName]);
        $highestBefore = (int) $stmt->fetchColumn();

        // The scraper narrates its progress; keep that out of the log's way.
        ob_start();
        $result = $scraper->scrapeFirstPageOnly($appSlug, $appName);
        $chatter = trim(ob_get_clean());

        if (!empty($result['success'])) {
            $new = (int) ($result['new_reviews_count'] ?? 0);
            $totalNew += $new;
            sync_log(sprintf('OK   %s: %d new review(s) of %d on page 1',
                $appName, $new, (int) ($result['total_on_page'] ?? 0)));
        } else {
            $failed[] = $appName;
            sync_log(sprintf('FAIL %s: %s', $appName, $result['message'] ?? 'sync failed'));
        }

        if ($chatter !== '') {
            sync_log('     ' . str_replace("\n", "\n     ", substr($chatter, 0, 500)));
        }

        // Announce what this run stored, oldest review first.
        $stmt = $conn->prepare('SELECT id, app_name, store_name, country_name, rating,
                                       review_content, review_date, earned_by
                                FROM reviews
                                WHERE app_name = ? AND id > ? AND is_active = TRUE
                                ORDER BY review_date ASC, id ASC');
        $stmt->execute([$appName, $highestBefore]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $review) {
            if ($review['review_date'] < $oldestWorthAnnouncing) {
                $skipped++;
                sync_log(sprintf('     -- %s: stored quietly, dated %s and older than %d days',
                    $review['store_name'], $review['review_date'], ANNOUNCE_MAX_AGE_DAYS));
                continue;
            }

            if (already_announced($conn, $review)) {
                $skipped++;
                sync_log(sprintf('     -- %s: announced by an earlier run, not posting again',
                    $review['store_name']));
                continue;
            }

            if (announce_review($conn, $review)) {
                $announced++;
            } else {
                $announceFailures++;
            }

            // Slack is content with about a message a second.
            sleep(1);
        }

    } catch (Throwable $e) {
        // One app failing mustn't stop the others.
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $failed[] = $appName;
        sync_log(sprintf('FAIL %s: %s', $appName, $e->getMessage()));
    }

    // A polite pause between apps, as the rest of the scrapers do.
    sleep(3);
}

sync_log(sprintf('Finished in %ss — %d new review(s), %d announced in Slack%s%s, %d of %d apps synced%s',
    round(microtime(true) - $startedAt, 1),
    $totalNew,
    $announced,
    $skipped ? ", $skipped stored quietly" : '',
    $announceFailures ? " ($announceFailures failed to post)" : '',
    count($apps) - count($failed),
    count($apps),
    $failed ? ' (failed: ' . implode(', ', $failed) . ')' : ''));

flock($lock, LOCK_UN);
fclose($lock);

exit($failed || $announceFailures ? 1 : 0);
