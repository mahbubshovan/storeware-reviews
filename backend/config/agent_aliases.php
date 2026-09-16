<?php
/**
 * Support names that turn up in review text, and who each one belongs to.
 *
 * Merchants thank whoever helped them, sometimes by the agent's own name and
 * sometimes by the name they were served under. A review naming any of these is
 * credited to that agent when the scheduled sync announces it in Slack.
 */

function agent_aliases() {
    return [
        'Ashik' => ['Loren', 'Ashik', 'Ashique', 'Ashikur'],
        'Vinz'  => ['Martha', 'Vinz'],
        'Amit'  => ['Florence', 'Amit'],
        'Nadvi' => ['Mitchel', 'Mitchell', 'Michelle', 'Nadvi'],
        'Pial'  => ['Kathryn', 'Pial'],
        'Jen'   => ['Jen', 'Jennesca', 'Roraldo'],
        'Amin'  => ['Santos', 'Amin'],
    ];
}

/**
 * Who a review names, as ['agent' => 'Ashik', 'alias' => 'Loren'], or null when
 * nobody is named.
 *
 * Names match whole and case-insensitively, so "ashik" and "Ashik's" both count
 * while "Aminah" doesn't. When a review names two people — the second usually
 * being a handover or a mention in passing — the one named first wins.
 */
function agent_match_in_review($content) {
    $content = (string) $content;
    if (trim($content) === '') {
        return null;
    }

    $found = null;

    foreach (agent_aliases() as $agent => $aliases) {
        foreach ($aliases as $alias) {
            if (!preg_match('/\b' . preg_quote($alias, '/') . '\b/iu', $content, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $position = $matches[0][1];
            if ($found === null || $position < $found['position']) {
                $found = ['agent' => $agent, 'alias' => $alias, 'position' => $position];
            }
        }
    }

    if (!$found) {
        return null;
    }

    return ['agent' => $found['agent'], 'alias' => $found['alias']];
}
