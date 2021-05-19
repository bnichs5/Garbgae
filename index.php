<?php
/*
Plugin Name: Split Call To Action
*/
add_action('init', function() {
    add_shortcode('split-plugin',function($atts = [], $content = null){
        $content .= "<script>var atts = " .
        json_encode($atts) .
        "</script>";
        return $content . <<<EOT
    <div class="row">
        <div class="col-md-6 equal">
            <p>I am just a poor boy, From a poor family, Spare me my life, For this one atrocity.</p>
            <div class="btn-wrap">
                <a class="btn btn-primary" href="{$atts['url1']}">Learn more</a>
            </div>
        </div>
        <div class="col-md-6 equal">
            <p>Easy come, easy go, will you let me go?</p>
            <div class="btn-wrap">
                <a class="btn btn-primary" href="{$atts['url2']}">Learn more</a>
            </div>
        </div>
    </div>
EOT;

    });
    wp_register_style('bootstrap', "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");
    wp_enqueue_style('bootstrap');
    wp_register_style('main_stylesheet', 
    plugins_url('main.css', __FILE__));
    wp_enqueue_style('main_stylesheet');
});    
