<?php
namespace Deployer;

before('deploy:update_code', 'backlog:changelog:prepare');
after('success', 'backlog:changelog:slack');

// Get the previous commit hash
task('backlog:changelog:prepare', function () {
    if (has('previous_release')) {
        cd("{{previous_release}}");
        $hash = run("git rev-parse HEAD");
        set('backlog.changelog.previous_hash', $hash);
    } else {
        set('backlog.changelog.previous_hash', 'HEAD');
    }
});

task('backlog:changelog:slack', function () {
    if (get('backlog.changelog.previous_hash') === 'HEAD') {
        // Stop if the code has not been updated
        return;
    }

    cd("{{release_path}}");
    $changelog = run("git log --oneline --no-abbrev-commit --decorate=no --no-merges {{backlog.changelog.previous_hash}}..HEAD");

    $slackText = "Deploy to *{{target}}* successful";
    foreach (explode("\n", $changelog) as $log) {
        $log = trim($log);
        if (strlen($log) === 0) {
            continue;
        }
        $hash = trim(substr($log, 0, 40));
        $hashShort = substr($hash, 0, 7);
        $comment = trim(substr($log, 41));

        if (!preg_match(get('backlog.regex.ticket_id'), $comment, $matches)) {
            continue;
        }

        $ticketUrl = sprintf(get('backlog.url.ticket'), $matches[1]);
        $ticketLink = sprintf("<%s|%s>", $ticketUrl, $matches[1]);

        $commitUrl = sprintf(get('backlog.url.commit'), $hash);
        $commitLink = sprintf("<%s|%s>", $commitUrl, $hashShort);

        $slackText .= sprintf("\n%s %s %s", $comment, $ticketLink, $commitLink);
    }
    set('slack_text', $slackText);
    writeln($slackText);
    invoke('slack:notify');
});
