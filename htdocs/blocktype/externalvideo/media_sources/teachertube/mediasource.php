<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_teachertube implements MediaBase {

    private static $base_url = 'http://www.teachertube.com/';

    private static $default_width  = 485;
    private static $default_height = 300;

    private static $embed_sources  = array(
        array(
            'match' => '#^http://(?:www\.)?teachertube\.com/flvideo/([0-9]+)\.flv.*#',
            'url'   => 'http://www.teachertube.com/skin-p/mediaplayer.swf?file=http://www.teachertube.com/flvideo/$1.flv',
        ),
        array(
            'match' => '#^http://(?:www\.)?teachertube\.com/viewVideo\.php\?video_id=(\d+).*#',
            'url'   => 'http://www.teachertube.com/embed/player.swf?file=http://www.teachertube.com/embedFLV.php?pg=video_$1',
        ),
    );

    // <EKAMPUS
    private static $iframe_sources;

    function __construct() {
        self::$iframe_sources = array(
            array(
                // http://www.teachertube.com/video/basic-factoring-384772
                'match' => '#^http://(?:www\.)?teachertube\.com/video/[A-Za-z0-9\-_]+\-(\d+)/?#',
                'url'   => '//www.teachertube.com/embed/video/$1'
            )
        );
    }
    // EKAMPUS>


    public function enabled() {
        return true;
    }

    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;

        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                $result = array(
                    'videoid' => $output,
                    'type'    => 'embed',
                    'width'   => $width,
                    'height'  => $height,
                );
                return $result;
            }
        }

        // <EKAMPUS
        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                $result = array(
                    'videoid' => $output,
                    'type'    => 'iframe',
                    'width'   => $width,
                    'height'  => $height,
                );
                return $result;
            }
        }
        // EKAMPUS>

        return false;
    }

    public function validate_url($input) {
        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }
        // <EKAMPUS
        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }
        // EKAMPUS>
        return false;
    }

    public function get_base_url() {
        return self::$base_url;
    }
}
