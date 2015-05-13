<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

$blogId    = isset($blogId) ? $blogId : '1748567850225926498';
$login     = isset($login) ? $login : 'joomla-jumi';
$cacheTime = isset($cacheTime) ? (int)$cacheTime : 86400;

$myBlog = new blog($blogId, $login, $cacheTime);
$myBlog->printAllPosts();

echo '<style type="text/css">
.post {margin:0 0 1.5em;font-family:Verdana,sans-serif;color:#000000;}
.post div {margin:0 0 .75em;line-height:1.3em;}
.post img {padding:4px;border:1px solid #cccccc;}
.post blockquote {margin:1em 20px;}
.post blockquote p {margin:.75em 0;}
.date-header {margin:1.5em 0 0;font-weight:normal;color:#999999;font-size:100%;}
.post-title {margin:0;padding:0;font-size:110%;font-weight:bold;line-height:1.1em;}
.post-title a, .post-title a:visited, .post-title strong {text-decoration:none;color:#333333;font-weight:bold;}
.post-footer {color:#333333;font-size:87%;}
.post-footer .span {margin-right:.3em;}
</style>';

class blog {
    var $id;
    var $login;
    var $posts;
    var $cacheTime;

    function __construct($id, $login, $cacheTime) {
        $this->id = $id;
        $this->login = $login;
        $this->cacheTime = $cacheTime;
        $postsURL = 'http://www.blogger.com/feeds/'.$id.'/posts/default';
        $fileName = 'cache/'.md5($postsURL);
        if(file_exists($fileName) and time() - filemtime($fileName) < $this->cacheTime) {
            $this->posts = simplexml_load_string(file_get_contents($fileName));
        } else {
            $feed = file_get_contents($postsURL);
            if(strlen($feed) > 1000) {
                file_put_contents($fileName, $feed);
                $this->posts = simplexml_load_string($feed);
            } else {
                $this->posts = simplexml_load_string(file_get_contents($fileName));
            }
        }
    }

    function blog($id, $login, $cacheTime)  {
        $this->__construct($id, $login, $cacheTime);
    }

    function printAllPosts() {
        echo '<div class="blog-posts">';
        $prev_date = '';
        foreach ($this->posts->entry as $entry) {
            for ($i = 0; $i < 5; $i++)
                $entry->link[$i] = $entry->link[$i]->attributes();
            if($prev_date != date('l, F j, Y', strtotime($entry->published))) {
                echo '<h2 class="date-header">'.date('l, F j, Y', strtotime($entry->published)).'</h2>';
                $prev_date = date('l, F j, Y', strtotime($entry->published));
            }
            echo '<div class="post">';
            echo '<h3 class="post-title"><a href="'.$entry->link[0]['href'].'">'.$entry->title.'</a></h3>';
            echo '<div class="post-header-line-1"></div>';
            echo '<div class="post-body">'.$entry->content.'</div>';
            echo '<div class="post-footer">';
            echo '<div class="post-footer-line-1">';
            echo '<span class="post-author">Posted by '.$entry->author->name.'</span> ';
            echo '<span class="post-timestamp">at <a href="'.$entry->link[0]['href'].'">'.date('H:i', strtotime($entry->published)).'</a></span> ';
            echo '<span class="post-comment-link"><a title="View or Add Comments" onclick="javascript:window.open(this.href,\'bloggerPopup\',\'toolbar=0,location=0,statusbar=1,menubar=0,scrollbars=yes,width=400,height=450\');return false;" href="'.str_replace('&', '&amp;', $entry->link[1]['href']).'" class="comment-link">'.$entry->link[1]['title'].'</a></span> ';
            echo '</div>';
            $labels = '';
            if(isset($entry->category)) {
                $labels = 'Labels: ';
                for ($i = 0; isset($entry->category[$i]); $i++) {
                    $entry->category[$i] = $entry->category[$i]->attributes();
                    $labels .= '<a href="http://'.$this->login.'.blogspot.com/search/label/'.$entry->category[$i]['term'].'">'.$entry->category[$i]['term'].'</a>';
                    if (isset($entry->category[$i+1]))
                        $labels .= ', ';
                }
            }
            echo '<div class="post-footer-line-2"><span class="post-labels">'.$labels.'</span></div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<small>Last updated: '.date('j M, Y H:i', filemtime('cache/'.md5('http://www.blogger.com/feeds/'.$this->id.'/posts/default'))).'</small>';
    }

    function printComments($postId) { echo ''; }

    function getPostId($id) { return substr($id, -19); }
}