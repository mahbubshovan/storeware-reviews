<?php
/**
 * Slack Notifier
 * Posts a celebratory message to the team channel when a review is credited to
 * an agent from the "Review Earned By" picker.
 *
 * Every public method is failure-tolerant by design: a Slack outage, a missing
 * token or a malformed response is logged and reported back in the return
 * value, but never throws. Assignment must succeed whether or not Slack does.
 */

require_once __DIR__ . '/../config/slack.php';

class SlackNotifier {

    /** Slack Web API endpoint for posting a message. */
    const POST_MESSAGE_URL = 'https://slack.com/api/chat.postMessage';

    /**
     * Bounded, but with real headroom: the request runs inline with the
     * assignment response, yet a dual-stack host can spend seconds on a stalled
     * IPv6 attempt before falling back to IPv4. A tighter limit turns ordinary
     * network variance into a reported failure. Typical round trip is under 1s.
     */
    const CONNECT_TIMEOUT_SECONDS = 10;
    const TOTAL_TIMEOUT_SECONDS = 15;

    /**
     * Build and send the assignment notification.
     *
     * @param array  $review    Row from `reviews` (app_name, store_name,
     *                          country_name, rating, review_content, review_date).
     * @param string $agentName Agent the review was credited to.
     * @return array{sent:bool, skipped?:string, error?:string}
     */
    public static function notifyReviewAssignment($review, $agentName) {
        try {
            $agentName = trim((string) $agentName);

            $token = slack_bot_token();
            if (!$token) {
                error_log('SlackNotifier: SLACK_BOT_TOKEN is not configured — skipping notification.');
                return ['sent' => false, 'skipped' => 'missing_token'];
            }

            return self::postMessage(self::buildMessage($review, $agentName), $token);

        } catch (Throwable $e) {
            // Belt and braces: nothing in here may bubble up to the caller.
            error_log('SlackNotifier: unexpected failure — ' . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Assemble the chat.postMessage payload (blocks + plain-text fallback).
     */
    public static function buildMessage($review, $agentName) {
        $rating = self::normalizeRating($review['rating'] ?? 0);
        $appName = self::valueOr($review['app_name'] ?? '', 'Unknown app');
        $store = self::valueOr($review['store_name'] ?? '', 'A store');
        $country = self::valueOr($review['country_name'] ?? '', 'Unknown');
        $date = self::formatDate($review['review_date'] ?? null);
        $text = self::truncate(trim((string) ($review['review_content'] ?? '')), 1500);

        $agentName = trim((string) $agentName);
        $memberId = $agentName !== '' ? slack_member_id($agentName) : null;
        $mention = $memberId ? '<@' . $memberId . '>' : '*' . self::escape($agentName) . '*';

        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => '📝 *' . self::escape($store) . '* has just left a review!',
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    ['type' => 'mrkdwn', 'text' => "*Rating*\n" . self::stars($rating)],
                    ['type' => 'mrkdwn', 'text' => "*Date*\n" . self::escape($date)],
                    ['type' => 'mrkdwn', 'text' => "*App*\n" . self::escape($appName)],
                    ['type' => 'mrkdwn', 'text' => "*Country*\n" . self::escape($country)],
                ],
            ],
        ];

        if ($text !== '') {
            $blocks[] = [
                'type' => 'section',
                'text' => ['type' => 'mrkdwn', 'text' => self::escape($text)],
            ];
        }

        // An unassigned / Organic review can still be shared manually — it just
        // has no one to credit, so the earner line is left off entirely.
        if ($agentName !== '') {
            $blocks[] = ['type' => 'divider'];
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => '🌟 Review earned by ' . $mention,
                ],
            ];
        }

        return [
            'channel' => slack_channel_for_app($appName),
            'text' => self::buildFallbackText($agentName, $appName, $store, $rating),
            'blocks' => $blocks,
        ];
    }

    /**
     * Plain-text summary used for notifications and any client that can't
     * render blocks.
     */
    private static function buildFallbackText($agentName, $appName, $store, $rating) {
        $summary = $store . ' left a ' . $rating . '★ review on ' . $appName;

        return $agentName !== '' ? $summary . ' — earned by ' . $agentName : $summary;
    }

    /**
     * Filled stars up to the rating, hollow markers for the remainder.
     */
    private static function stars($rating) {
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }

    /**
     * Clamp whatever the database hands us into the 0–5 range.
     */
    private static function normalizeRating($rating) {
        $rating = (int) $rating;

        return max(0, min(5, $rating));
    }

    private static function formatDate($date) {
        if (empty($date)) {
            return 'Unknown date';
        }

        $timestamp = strtotime((string) $date);

        return $timestamp ? date('F j, Y', $timestamp) : (string) $date;
    }

    private static function valueOr($value, $fallback) {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    private static function truncate($text, $limit) {
        if (function_exists('mb_strlen') && mb_strlen($text) > $limit) {
            return rtrim(mb_substr($text, 0, $limit)) . '…';
        }
        if (!function_exists('mb_strlen') && strlen($text) > $limit) {
            return rtrim(substr($text, 0, $limit)) . '…';
        }

        return $text;
    }

    /**
     * Escape the three characters Slack treats as markup control characters, so
     * review text can't forge a mention or a link.
     */
    private static function escape($text) {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], (string) $text);
    }

    /**
     * POST the payload to Slack. Returns the outcome; logs anything that fails.
     */
    private static function postMessage($payload, $token) {
        $ch = curl_init(self::POST_MESSAGE_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT_SECONDS,
            CURLOPT_TIMEOUT => self::TOTAL_TIMEOUT_SECONDS,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('SlackNotifier: request failed — ' . $curlError);
            return ['sent' => false, 'error' => $curlError ?: 'request_failed'];
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            error_log('SlackNotifier: unreadable response — ' . substr((string) $response, 0, 200));
            return ['sent' => false, 'error' => 'invalid_response'];
        }

        if (empty($data['ok'])) {
            $slackError = $data['error'] ?? 'unknown_error';
            error_log('Slack post failed: ' . $slackError);
            return ['sent' => false, 'error' => $slackError];
        }

        return ['sent' => true];
    }
}
