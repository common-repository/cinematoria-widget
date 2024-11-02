<?php
/*
Plugin Name: Cinematoria Widget
Plugin URI: http://www.cinematoria.com/
Description: Cinematoria Widget displays new, top or coming soon movies.
Author: Cinematoria
Author URI: http://www.cinematoria.com/
*/


class Cinematoria_widget extends WP_Widget {

    function cinematoria_widget() {
        $widget_ops = array( 'classname' => 'cinematoria_widget', 'description' => 'New, top or coming soon movies' ); // Widget Settings
        $control_ops = array( 'id_base' => 'cinematoria_widget' ); // Widget Control Settings
        $this->WP_Widget( 'cinematoria_widget', 'Cinematoria Widget', $widget_ops, $control_ops ); // Create the widget
    }

    function widget($args, $instance) {
        extract( $args );
        extract( $instance );

        //Display widget options form 

        //Fill array of feeds
        $feeds = array(
            'en' => array(
                'new' => 'http://www.cinematoria.com/rss/new_en.xml',
                'up' => 'http://www.cinematoria.com/rss/up_en.xml',
                'top' => 'http://www.cinematoria.com/rss/top_en.xml'
            ),
            'ru' => array(
                'new' => 'http://ru.cinematoria.com/rss/new_ru.xml',
                'top' => 'http://ru.cinematoria.com/rss/top_ru.xml',
                'up' => 'http://ru.cinematoria.com/rss/up_ru.xml'
            )
        );

        /*
		To add a new language, just add new element to array $feeds

		For example let's add a new fr feed. 

         $feeds = array(
            'en' => array(
                'new' => 'http://www.cinematoria.com/rss/new_en.xml',
                'up' => 'http://www.cinematoria.com/rss/up_en.xml',
                'top' => 'http://www.cinematoria.com/rss/top_en.xml'
            ),
            'ru' => array(
                'new' => 'http://ru.cinematoria.com/rss/new_ru.xml',
                'top' => 'http://ru.cinematoria.com/rss/top_ru.xml',
                'up' => 'http://ru.cinematoria.com/rss/up_ru.xml'
            ),
            'fr' => array(
                'new' => 'http://fr.cinematoria.com/rss/new_fr.xml',
                'top' => 'http://fr.cinematoria.com/rss/top_fr.xml',
                'up' => 'http://fr.cinematoria.com/rss/up_fr.xml'
            ),
         );

        Also change the template form for the options, see below
        */


        echo $before_widget;

        if ( $title ) { echo $before_title . $title . $after_title; }


        //Get array of movies
        include_once(ABSPATH . WPINC . '/feed.php');

        $rss = fetch_feed($feeds[$language][$feed]);

        if (!is_wp_error( $rss ) )
        {
            $maxitems = $rss->get_item_quantity($number);
            $rss_items = $rss->get_items(0, $maxitems);
        }
        ?>

    <ul>
        <?php if ($maxitems == 0) echo '<li>No items.</li>';
    else
        foreach ( $rss_items as $item ) { ?>

            <li>
                <a href='<?php echo esc_url( $item->get_permalink() ); ?>'><?php echo esc_html( $item->get_title() ); ?></a>
                <?php if($show_posters) { ?>
                <?php $poster = $item->data['child'][''][$poster_size . '_poster']['0']['data']?>
                <p><a href='<?php echo esc_url( $item->get_permalink() ); ?>'><img src="<?php echo $poster ?>" alt=""/></a></p>
                <?php } ?>

                <?php if($show_description) { ?>
                <?php $description = $item->data['child']['']['description']['0']['data']?>
                <p><?php echo $description ?></p>
                <?php } ?>
            </li>
            <?php } ?>
    </ul>
<?php
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = intval($new_instance['number']);
        $instance['language'] = $new_instance['language'];
        $instance['feed'] = $new_instance['feed'];
        $instance['show_posters'] = $new_instance['show_posters'];
        $instance['show_descriptions'] = $new_instance['show_descriptions'];
        $instance['poster_size'] = $new_instance['poster_size'];
        return $instance;
    }

    //Display widget options

    function form($instance) {

        load_textdomain('cinematoria', dirname( plugin_basename( __FILE__ ) ) . '/languages/');

        //Default options
        $defaults = array( 'title' => 'Movies', 'number' => 5, 'language' => 'en', 'feed' => 'new', 'show_posters' => 'on', 'show_descriptions' => '', 'poster_size' => 'medium');
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo _e('Title:')?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>'" type="text" value="<?php echo $instance['title']; ?>" />
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('language'); ?>"><?php echo _e('Language')?>:</label>
        <select id="<?php echo $this->get_field_id('language'); ?>" name="<?php echo $this->get_field_name('language'); ?>" class="widefat" style="width:100%;">
            <option value="ru" <?php selected('ru', $instance['language']); ?>><?php echo _e('Russian')?></option>
            <option value="en" <?php selected('en', $instance['language']); ?>><?php echo _e('English')?></option>
        </select>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('poster_size'); ?>"><?php echo _e('Poster size:')?></label>
        <select id="<?php echo $this->get_field_id('poster_size'); ?>" name="<?php echo $this->get_field_name('poster_size'); ?>" class="widefat" style="width:100%;">
            <option value="small" <?php selected('small', $instance['poster_size']); ?>><?php echo _e('Small')?></option>
            <option value="medium" <?php selected('medium', $instance['poster_size']); ?>><?php echo _e('Medium')?></option>
            <option value="big" <?php selected('big', $instance['poster_size']); ?>><?php echo _e('Big')?></option>
        </select>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('feed'); ?>"><?php echo _e('Type:'); ?></label>
        <select id="<?php echo $this->get_field_id('feed'); ?>" name="<?php echo $this->get_field_name('feed'); ?>" class="widefat" style="width:100%;">
            <option value="new" <?php selected('new', $instance['feed']); ?>><?php echo _e('New Movies'); ?></option>
            <option value="top" <?php selected('top', $instance['feed']); ?>><?php echo _e('Top Movies'); ?></option>
            <option value="up" <?php selected('up', $instance['feed']); ?>><?php echo _e('Coming Soon Movies'); ?></option>
        </select>
    </p>



    <p>
        <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of movies to display'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $instance['number']; ?>" />
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('show_posters'); ?>"><?php _e('Show Posters?'); ?></label>
        <input type="checkbox" class="checkbox" <?php checked( $instance['show_posters'], 'on' ); ?> id="<?php echo $this->get_field_id('show_posters'); ?>" name="<?php echo $this->get_field_name('show_posters'); ?>" />
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('show_descriptions'); ?>"><?php _e('Show Descriptions?'); ?></label>
        <input type="checkbox" class="checkbox" <?php checked( $instance['show_descriptions'], 'on' ); ?> id="<?php echo $this->get_field_id('show_descriptions'); ?>" name="<?php echo $this->get_field_name('show_descriptions'); ?>" />
    </p>


    <?php }

}

//Register Cinematoria widget

add_action('widgets_init', create_function('', 'return register_widget("Cinematoria_widget");'));
?>