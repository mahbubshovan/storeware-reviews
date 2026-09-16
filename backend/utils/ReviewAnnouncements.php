<?php
/**
 * Ledger of reviews already announced in Slack.
 *
 * Shared by the scheduled sync and the Send to Slack button, so a review reaches
 * a channel once whichever of the two sent it. Keyed by app, store and review
 * date rather than review id: a store reviews an app only once, so that triple
 * identifies the review even if its row is rebuilt under a new id.
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
 * Remember that this review went to Slack. Never throws: failing to write the
 * ledger mustn't look like the post itself failed.
 */
function record_announcement($conn, $review, $channel) {
    try {
        ensure_announcement_ledger($conn);

        $stmt = $conn->prepare('INSERT IGNORE INTO review_announcements
                                    (review_id, app_name, store_name, review_date, channel)
                                VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$review['id'], $review['app_name'], $review['store_name'],
                        $review['review_date'], $channel]);

    } catch (Throwable $e) {
        error_log('Review announcement not recorded — ' . $e->getMessage());
    }
}
