<?php
/**
 * The class helps to fetch last updates for the WPN-XM Software Registry Dataset
 * from the "git log". Its a very basic Git Log Parser,
 * which filters "commit messages" and groups them by "date".
 *
 * The commit message for the insert of new component is standardized,
 * e.g. "updated software registry - NodeJS x64 v5.1.1".
 * This allows to scrape only the "component" and "version" part,
 * e.g. "NodeJS x64 v5.1.1". The elements are then grouped by commit date.
 */
class GitLog
{
    public $git_logs = [];
    public $git_history = [];

    public function __construct()
    {
        $this->query();
        $this->parse();
    }

    private function query()
    {
        //chdir(__DIR__ . '/registry/'); // switch to repo folder
        //exec("git log -n 60 --date=short --pretty=format:\"%s#~|~#%ad\"", $this->git_logs);

        // if exec is disabled, write content to text file via cronjob and read file content
        $this->git_logs = file(__DIR__ . '/gitlog.txt');
    }

    private function parse()
    {
        foreach($this->git_logs as $line)
        {
            list($msg, $date) = explode('#~|~#', $line);

            if(strpos($msg, 'updated software registry -') !== false) {
                $msg = str_replace('updated software registry -', '', $msg); // drop prefix
                $msg = trim(explode(',', $msg)[0]); // drop suffix
                if(substr($msg, -1) === 'v') { // skip items with no version number
                    continue;
                }
                $this->git_history[$date][] = $msg;
            }
        }
        unset($this->git_logs);
    }

    public function get()
    {
        return $this->git_history;
    }
}

/**
 * HTML helper class to generate the "Latest Updates" Panel.
 */
class LatestUpdates
{
    public $git_log = [];

    public function __construct(GitLog $gitLog)
    {
        $this->git_log = $gitLog->get();
    }

    public function renderPanel()
    {
        //$html = '<div class="col-md-3">' . "\n";
        $html = '<div class="panel panel-default">' . "\n";
        $html .= '<div class="panel-heading"><strong>Lastest Updates</strong></div>' . "\n";
        $html .= '<div class="panel-body">' . "\n";
        $html .= $this->renderLogItems();
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        //$html .= '</div>' . "\n";
        return $html;
    }

    /**
     * Returns the LogItems as HTML fragment.
     * Makes use of class="newsbox" tags to enable/bind the
     * "newsbox.js" functionality to content elements.
     *
     * @return string HTML fragment LogItems
     */
    public function renderLogItems()
    {
        $html = '<div class="row"><ul class="newsbox">'. "\n";
        foreach($this->git_log as $date => $updated_components)
        {
            $html .= '<li class="news-item">'. "\n";
            $html .= '<dt>' . $date . '</dt>'. "\n";
            foreach($updated_components as $idx => $component) {
                $html .= '<dd>' . $component . '</dd>' . "\n";
            }
            $html .= '</li>'. "\n";
        }
        $html .= '</ul></div>'. "\n";
        return $html;
    }

    public function render()
    {
        return $this->renderPanel() . $this->getScript();
    }

    /**
     * Returns Newsbox Script. Requires jQuery and Bootstrap3.
     *
     * @return string HTML
     */
    public function getScript()
    {
        return '
        <script src="/js/jquery.bootstrap.newsbox.min.js" type="text/javascript">
        <script type="text/javascript">
            function activateLatestUpdateNewsBox() {
                $(".newsbox").bootstrapNews({
                    newsPerPage: 2,
                    autoplay: true,
                    pauseOnHover: true,
                    navigation: true,
                    direction: \'up\',
                    newsTickerInterval: 4000,
                    onToDo: function () {
                        //console.log(this);
                    }
                });
            }
        </script>
        ';
    }
}

function getLatestUpdatesHtml()
{
    $updates = new LatestUpdates(new GitLog);
    return $updates->render();
}

function get_latest_updates()
{
    $cache_file = __DIR__ . '/downloads/latest-updates.html';

    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (3 * 24 * 60 * 60)))) {
        // Use cache file, when not older than 3 days.
        $data = file_get_contents($cache_file);
    } else {
        // The cache is out-of-date. Get data, write cache file.
        $data = getLatestUpdatesHtml();
        file_put_contents($cache_file, $data, LOCK_EX);
    }

    echo $data;
}

get_latest_updates();