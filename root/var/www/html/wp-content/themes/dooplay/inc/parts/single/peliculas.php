<?php
/*
* -------------------------------------------------------------------------------------
* @author: Doothemes
* @author URI: https://doothemes.com/
* @aopyright: (c) 2021 Doothemes. All rights reserved
* -------------------------------------------------------------------------------------
*
* @since 2.5.0
*
*/

// All Postmeta
$classlinks = new DooLinks;
$postmeta = doo_postmeta_movies($post->ID);
$adsingle = doo_compose_ad('_dooplay_adsingle');
// Movies Meta data
$trailer = doo_isset($postmeta,'youtube_id');
$trailer2 = trim($trailer, ']');
$trailer3 = trim($trailer2, '[');   
$pviews  = doo_isset($postmeta,'dt_views_count');
$player  = doo_isset($postmeta,'players');
$player  = maybe_unserialize($player);
$images  = doo_isset($postmeta,'imagenes');
$tviews  = ($pviews) ? sprintf( __d('%s Views'), $pviews) : __d('0 Views');
//  Image
$dynamicbg  = dbmovies_get_rand_image($images);
$posterposter = dbmovies_get_poster($post->ID,'medium');
$posterposterposter = str_replace('w185', 'original', $posterposter);
// Options
$player_ads = doo_compose_ad('_dooplay_adplayer');
$player_wht = dooplay_get_option('playsize','regular');
// Sidebar
$sidebar = dooplay_get_option('sidebar_position_single','right');
// Dynamic Background
if(dooplay_get_option('dynamicbg') == true) { ?>
<style>
#dt_contenedor {
    background-image: url(<?php echo $dynamicbg; ?>);
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover;
    background-position: 50% 0%;
}
</style>
<?php } ?>
<?php get_template_part('inc/parts/single/report-video'); ?>

<!-- Big Player -->
<?php DooPlayer::viewer_big($player_wht, $player_ads, $dynamicbg); ?>
<!-- Start Single -->
<div id="single" class="dtsingle" itemscope itemtype="http://schema.org/Movie">
    <!-- Edit link response Ajax -->
    <div id="edit_link"></div>
    <!-- Start Post -->
    <?php if(have_posts()) :while (have_posts()) : the_post(); ?>
    
    <div class="content <?php echo $sidebar; ?>">

        <!-- Views Counter -->
        <?php DooPlayViews::Meta($post->ID); ?>
        
        
        
        
        



<!-- <img src='<?php echo $dynamicbg; ?>' width="132%"  onmouseover="this.style.width='0%';" style="opacity:0.4;filter:alpha(opacity=40);" /><h1><img itemprop="image" src="<?php echo dbmovies_get_poster($post->ID,'medium'); ?>" width="25%" alt="<?php the_title(); ?>" style="position:relative; left:50px; top:-520px; border:none;" ><div class="data" style="position:relative; left:50px; top:-520px; border:none;">
        		<h1 style="font-size:20px"><?php the_title(); ?></h1>
        		<div class="extra">
        		<?php
                // Movie Meta Info
                if($d = doo_isset($postmeta,'tagline')) echo "<span class='tagline'>{$d}</span>";
        		if($d = doo_isset($postmeta,'release_date')) echo "<span class='date' itemprop='dateCreated'>".doo_date_compose($d,false)."</span>";
        		if($d = doo_isset($postmeta,'Country')) echo "<span class='country'>{$d}</span>";
        		if($d = doo_isset($postmeta,'runtime')) echo "<span itemprop='duration' class='runtime'>{$d} ".__d('Min.')."</span>";
        		if($d = doo_isset($postmeta,'Rated')) echo "<span itemprop='contentRating' class='C{$d} rated'>{$d}</span>";
                // end..
                ?>
        		</div>
        		
        		<div class="sgeneros">
        		<?php echo get_the_term_list($post->ID, 'genres', '', '', ''); ?>
        		</div>
                  <h2 style="position:relative; left:0px; top:12px; border:none; font-size:18px; font-weight:bold;"><b><?php _d('Summary:'); ?></b></h2>
            
                <h4 style="position:relative; left:0px; top:15px; border:none;"><?php the_content(); ?></h4>  
                    
        	</div></h1><img src='<?php echo $dynamicbg; ?>' width="132%"  />   -->
                    
                    
                    
                    




        <!-- Regular Player and Player Options -->
        <!-- <?php DooPlayer::viewer($post->ID, 'movie', $player, $trailer, $player_wht, $tviews, $player_ads, $dynamicbg); ?> -->
        <!-- Head movie Info -->
        <div class="sheader">
        	<div class="poster">
        		<!-- <img itemprop="image" src="<?php echo dbmovies_get_poster($post->ID,'medium'); ?>" alt="<?php the_title(); ?>"> -->
            	<img itemprop="image" src="<?php echo str_replace('w185', 'w600_and_h900_bestv2', dbmovies_get_poster($post->ID,'medium')); ?>" alt="<?php the_title(); ?>"><!-- if w300_and_h450_bestv2 doesnt work anymore replace it with original -->
        	</div>
        	<div class="data">
        		<h1><?php the_title(); ?></h1>
        		<div class="extra">
        		<?php
                // Movie Meta Info
                if($d = doo_isset($postmeta,'tagline')) echo "<span class='tagline'>{$d}</span>";
        		if($d = doo_isset($postmeta,'release_date')) echo "<span class='date' itemprop='dateCreated'>".doo_date_compose($d,false)."</span>";
        		if($d = doo_isset($postmeta,'Country')) echo "<span class='country'>{$d}</span>";
        		if($d = doo_isset($postmeta,'runtime')) echo "<span itemprop='duration' class='runtime'>{$d} ".__d('Min.')."</span>";
        		if($d = doo_isset($postmeta,'Rated')) echo "<span itemprop='contentRating' class='C{$d} rated'>{$d}</span>";
                // end..
                ?>
        		</div>
        		<!-- <?php echo do_shortcode('[starstruck_shortcode]'); ?> -->
                
                    
        		<div class="sgeneros">
        		<?php echo get_the_term_list($post->ID, 'genres', '', '', ''); ?>
        		</div>
                    
        	</div>
        </div>

                    <!-- Movie Sidebar -->
    <div class="sidebar  <?php echo $sidebar; ?> scrolling" >
    	 <!-- <a href="https://youtube.com/watch?v=8AZ8GqW5iak">Generate Sources</a> -->
         <!-- <?php dynamic_sidebar('sidebar-movies'); ?> -->
         <!-- <button onclick="generateSources()" style="height:4%; width:50%; position:relative; top:30px; border:none;">Generate Sources</button> -->
                        <!-- <li><a href="#info" onclick="generateSources()">Generate Sources</a></li> -->
              
             
             
    </div>
    <!-- End Sidebar -->
                    
        <!-- Movie Tab single -->
        <div class="single_tabs">
            <?php if(is_user_logged_in() && doo_is_true('permits','eusr')){ ?>
        	<div class="user_control">
        		<?php dt_list_button($post->ID); dt_views_button($post->ID); ?>
        	</div>
            <?php } ?>
        	<ul id="section" class="smenu idTabs">
            	<li><a id="main_ali" href="#info"><?php _d('Info'); ?></a></li>
                <li><a href="">Trailer</a></li>    
                <li><a href="#info" onclick="generateSources()">Generate Sources</a></li>
                
                <!-- <li><?php dt_list_button($post->ID); ?></li> -->   
                  
                <!-- <li><?php echo $trailer3; ?>    </li> -->
                <li><a href="https://www.youtube.com/watch?v=<?php echo $trailer3 ?>?fs=1">Trailer</a></li>    
                <li><a href="https://bulkup.org/doo252test/YTDL/Test.php?id=<?php echo $trailer3 ?>">Trailer</a></li>    
                <!-- <li><a href="https://www.youtube.com/embed/<?php echo $trailer3 ?>?autoplay=1">Trailer</a></li>    -->
                <!-- <li><?php echo do_shortcode("[youtube_download_button id='$trailer3' text='Trailer' minutes='15']"); ?></li>    -->
                    
                <li><a class="far fa-heart"></a></li>    
                <li><a href="#cast"><?php _d('Cast'); ?></a></li>
                <li><a href="">Add/Remove from Favorites</a></li>
           		<!--<button onclick="hideFANARTs()" style="height:4%; width:40%">Add/Remove from WatchList</button>
                <button onclick="hideFANARTs()" style="height:4%; width:40%">Go To Favorites</button>
                <button onclick="hideFANARTs()" style="height:4%; width:40%">Go To WatchList</button>  -->  
                <?php if(doo_here_links($post->ID)) echo '<li><a href="#linksx">'.__d('Links').'</a></li>'; ?>
        	</ul>
        </div>

        <!-- Single Post Ad -->
        <?php if($adsingle) echo '<div class="module_single_ads">'.$adsingle.'</div>'; ?>

        <!-- Movie more info -->
        <div id="info" class="sbox">
            <h2><?php _d('Summary'); ?></h2>
            <div itemprop="description" class="wp-content">
                <?php the_content(); ?>
                <?php the_tags('<ul class="wp-tags"><li>','</li><li>','</li></ul>'); ?>
                <!-- <?php dbmovies_get_images($images); ?> -->
            </div>
            <?php if($d = doo_isset($postmeta, 'original_title')) { ?>
            <!-- <div class="custom_fields">
                <b class="variante"><?php _d('Original title'); ?></b>
                <span class="valor"><?php echo $d; ?></span>
            </div> -->
                       
            <?php } if($d = doo_isset($postmeta, 'imdbRating')) { ?>
            <!-- <div class="custom_fields">
        	    <b class="variante"><?php _d('IMDb Rating'); ?></b>
        	    <span class="valor">
        		    <b id="repimdb"><?php echo '<strong>'.$d.'</strong> '; if($votes = doo_isset($postmeta, 'imdbVotes')) echo sprintf( __d('%s votes'), doo_format_number($votes) ); ?></b>
        	        <?php if(current_user_can('administrator')) { ?><a data-id="<?php echo $post->ID; ?>" data-imdb="<?php echo doo_isset($postmeta, 'ids'); ?>" id="update_imdb_rating"><?php _d('Update Rating'); ?></a><?php } ?>
        	    </span>
            </div> -->
            <?php } if($d = doo_isset($postmeta, 'vote_average')) { ?>
            <!-- <div class="custom_fields">
                <b class="variante"><?php _d('TMDb Rating'); ?></b>
                <span class="valor"><?php echo '<strong>'.$d.'</strong> '; if($votes = doo_isset($postmeta, 'vote_count')) echo sprintf( __d('%s votes'), number_format($votes) ); ?></span>
            </div>  -->
            <?php } ?>
        </div>

        <!-- Movie Cast -->
        <div id="cast" class="sbox fixidtab">
            <h2><?php _d('Director'); ?></h2>
            <div class="persons">
            	<?php doo_director(doo_isset($postmeta,'dt_dir'), "img", true); ?>
            </div>
            <h2><?php _d('Cast'); ?></h2>
            <div class="persons">
            	<?php doo_cast(doo_isset($postmeta,'dt_cast'), "img", true); ?>
            </div>
        </div>

        <div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div><div>.</div>            
                    
        <!-- Movie Links -->
        <!-- <?php if(DOO_THEME_DOWNLOAD_MOD) get_template_part('inc/parts/single/links'); ?> -->

        <!-- Movie Social Links -->
        <!-- <?php doo_social_sharelink($post->ID); ?> -->

         
        <!-- Movie Related content -->
        <?php if(DOO_THEME_RELATED) get_template_part('inc/parts/single/relacionados'); ?>

        <!-- Movie comments -->
        <!-- <?php get_template_part('inc/parts/comments'); ?> -->

        <!-- Movie breadcrumb -->
        <!-- <?php doo_breadcrumb( $post->ID, 'movies', __d('Movies'), 'breadcrumb_bottom' ); ?> -->

    </div>
    <!-- End Post-->
    <?php endwhile; endif; ?>

    
        
        
        

</div>
<!-- End Single -->