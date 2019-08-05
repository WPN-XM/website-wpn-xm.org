<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * The script renders the "support page".
 */

echo render_header();
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<div class="row">
    <div class="col-lg-12 centered">
        <div class="row">
            <div class="col-lg-12 centered">
                <h2>Support</h2>
                <h3>We provide comprehensive help and support.</h3>
                <h4>Feel free to contact us through one of the following channels:</h4>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-lg-6">
                <div class="col-lg-4" style="padding: 15px 0px 15px 0px;">
                    <img src="images/so-logo-150px.png" width="150" alt="Stack Overflow" title="Stack Overflow">
                </div>
                <div class="col-lg-8">
                    <h3>Questions and Answers</h3>
                    <p>
                        Feel free to ask on StackOverflow.
                        Please tag your question with: <span class="label label-default">wpn-xm</span> and <span class="label label-default">php</span>.
                    </p>
                    <p>
                        <a href="https://stackoverflow.com/questions/ask/?tags=wpn-xm+php" class="btn btn-default button-link" target="_blank" role="button">
                            <i class="fa fa-stack-overflow"></i>&nbsp;&nbsp;Ask Question on StackOverflow</a>
                    </p>
                    <p>
                        You may also find a good collection of WPN-XM related questions.
                    </p>
                    <p>
                        <a href="https://stackoverflow.com/questions/tagged/wpn-xm" class="btn btn-default button-link" target="_blank" role="button">
                            <i class="fa fa-stack-overflow"></i>&nbsp;&nbsp;Find Questions &amp; Answers</a>
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="col-lg-4" style="padding: 15px 0px 15px 0px;">
                    <img src="images/octocat-logo-150px.png" width="150" alt="GitHub" title="GitHub">
                </div>
                <div class="col-lg-8">
                    <h3>Issues</h3>
                    <p>
                        Please report bugs or issues using our central GitHub issue tracker.
                    </p>
                    <p>
                        <a href="https://github.com/WPN-XM/WPN-XM/issues" class="btn btn-default button-link" target="_blank" role="button">
                            <i class="fa fa-github-alt"></i>&nbsp;Report Issue</a>
                    </p>
                </div>
            </div>

        </div>

        <br>

        <div class="row">
            <div class="col-lg-6">
                <div class="col-lg-4" style="padding: 15px 0px 15px 0px;">
                    <img src="images/forum-icon-150px.png" width="150" alt="Stack Overflow" title="Stack Overflow">
                </div>
                <div class="col-lg-8">
                    <h3>Community Forums</h3>
                    <p>
                        You might also discuss WPN-XM or PHP related issues in our community forums.
                    </p>
                    <p>                        
                        <a href="https://www.youtube.com/watch?v=W8_Kfjo3VjU" class="btn btn-default button-link" target="_blank" role="button">
                            <i class="fa fa-comments"></i>&nbsp;&nbsp;Community Forums (or maybe not =)</a>
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="col-lg-4" style="padding: 15px 0px 15px 0px;">
                    <img src="images/mailinglist-icon-150px.png" width="150" alt="GitHub" title="GitHub">
                </div>
                <div class="col-lg-8">
                    <h3>Mailing List</h3>
                    <p>
                        You may also keep it simple and send a mail to our mailing list.
                    </p>
                    <p>
                        <a href="https://groups.google.com/forum/#!forum/wpn-xm" class="btn btn-default button-link" target="_blank" role="button">
                            <i class="fa fa-envelope"></i>&nbsp;Mailing list</a>
                    </p>
                </div>
            </div>

        </div>

        <br>
        <br>

    </div>
</div>
<?php
echo render_footer_scripts();
?>
</div></div></div></div>
</body>
</html>

<?php
function render_header()
{
    require __DIR__ . '/view/header.php';
    define('RENDER_WPNXM_HEADER_LOGO', false);
    require __DIR__ . '/view/topnav.php';
}

function render_footer_scripts()
{
    require __DIR__ . '/view/footer_scripts.php';
}
