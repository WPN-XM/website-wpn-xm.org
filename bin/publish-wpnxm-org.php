<?php
/**
 * Automatical Website Deployment via a Github Post-Receive Hook Trigger.
 *
 * How to setup a post receive hook on Github:
 *     https://help.github.com/articles/post-receive-hooks
 */

// commands
$commands = array(
    'echo $PWD',
    'whoami',
    'git pull',
    'git status',
    #'git submodule sync',
    #'git submodule update',
    #'git submodule status',
);

// run the commands for output
$output = '';
foreach($commands AS $command){
    // run it
    $tmp = shell_exec($command);
    // prepare output
    $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
    $output .= htmlentities(trim($tmp)) . "\n";
}
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>DEPLOY</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
<?php echo $output; ?>
</pre>
</body>
</html>