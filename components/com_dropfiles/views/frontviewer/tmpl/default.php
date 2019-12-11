<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access.
defined('_JEXEC') || die;

?>
<div id="dropViewer">
    <?php if ($this->mediaType === 'image') { ?>
        <img src="<?php echo $this->downloadLink; ?>" alt="" title=""/>
    <?php } elseif ($this->mediaType === 'video') { ?>
        <video width="100%" height="100%" src="<?php echo $this->downloadLink; ?>" type="<?php echo $this->mineType; ?>"
               class="mejs-player" data-mejsoptions='{"alwaysShowControls": true}'
               id="playerVid" controls="controls" preload="auto" autoplay="true">
            <source type="<?php echo $this->mineType; ?>" src="<?php echo $this->downloadLink; ?>"/>
            Your browser does not support the <code>video</code> element.
        </video>
    <?php } elseif ($this->mediaType === 'audio') { ?>
        <audio src="<?php echo $this->downloadLink; ?>" type="<?php echo $this->mineType; ?>"
               id="playerAud" controls="controls" preload="auto" autoplay="true"></audio>
    <?php } ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var w = $('#dropViewer').width();
        var h = $('#dropViewer').height();
        var vid = document.getElementById("playerVid");
        var aud = document.getElementById("playerAud");
        if (vid !== null) {
            vid.onloadeddata = function () {
                // Browser has loaded the current frame
                var vW = $(vid).width();
                var vH = $(vid).height();

                var newH;
                var newW;
                if (vH > h) {
                    newH = h - 10;
                    newW = newH / vH * vW;
                    $(vid).attr('width', newW).attr('height', newH);
                    $(vid).width(newW);
                    $(vid).height(newH);

                    $(".mejs-video").width(newW);
                    $(".mejs-video").height(newH);

                    var barW = newW - 150;
                    $(".mejs-time-rail").width(barW).css('padding-right', '5px');
                    $(".mejs-time-total").width(barW);
                }

            };

        }

        $('video,audio').mediaelementplayer(/* Options */);

    });
</script>

<style>

    #dropViewer {
        text-align: center;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100%;
    }

    #dropViewer img {
        max-width: 100%;
        height: auto;
        max-height: 100%;
    }

    #dropViewer audio, #dropViewer video {
        display: inline-block;
    }

    #dropViewer .mejs-container {
        margin: 0 auto;
        max-width: 100%;
    }

    #dropViewer video {
        width: 100% !important;
        max-width: 100%;
        height: auto !important;
        max-height: 100% !important;
    }

    #dropViewer .mejs-container.mejs-video {
        margin: 0 auto;
    }

    #dropViewer .mejs-container.mejs-audio {
        top: 50%;
        margin-top: -15px;
    }
</style>    
