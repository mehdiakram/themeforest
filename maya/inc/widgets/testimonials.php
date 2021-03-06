<?php

class testimonials extends WP_Widget
{
    var $icons;
    var $path_icons = 'images/icons/widget/';

    function __construct()
    {
        $widget_ops = array(
            'classname' => 'testimonial-widget',
            'description' => __('Add a slider testimonial on your widget which link to the single testimonial page.', 'yiw')
        );

        $control_ops = array( 'id_base' => 'testimonial-widget' );

        WP_Widget::__construct( 'testimonial-widget', 'Testimonial Widget', $widget_ops, $control_ops );

        $this->path_icons = dirname(__FILE__) . '/../../' . $this->path_icons;
        foreach ( glob( $this->path_icons . '*.png' ) as $icon ) {
            $icon = explode( '.', basename($icon) );
            $this->icons[] = $icon[0];
        }
    }

    function widget( $args, $instance )
    {
        extract( $args );

        /* User-selected settings. */
        if( !isset( $instance['title'] ) )
            $instance['title'] = '';

        $title = apply_filters('widget_title', $instance['title'] );

        $img_url = '';
        if ( ! empty( $instance['icon_img'] ) )
            $img_url = get_template_directory_uri() . '/images/icons/widget/' . $instance['icon_img'] . '.png';
        else if ( ! empty( $instance['img_url'] ) )
            $img_url = $instance['img_url'];

        $test_fx = isset( $instance['test_fx']) ? $instance['test_fx'] : 'fade';
        $test_timeout_fx = isset( $instance['test_timeout_fx']) ? $instance['test_timeout_fx'] : 5000;
        $test_speed_fx = isset( $instance['test_speed_fx']) ? $instance['test_speed_fx'] : 500;
        $test_n_items = isset( $instance['test_n_items']) ? $instance['test_n_items'] : 5;
        $size = 32;

        global $more, $post;
        $more = 0;

        wp_reset_query();

        $test_posts = new WP_Query("post_type=bl_testimonials&posts_per_page=$test_n_items");

        if( $test_posts->have_posts() )
        {
            echo $before_widget;

            if ( $title ) echo $before_title . '<img src="' . $img_url . '" title="" alt="" />' . $title . $after_title;

            echo '<div class="testimonial-text">';
            echo '<ul>';
            while( $test_posts->have_posts() )
            {
                $test_posts->the_post();

                echo '<li>';

                $show_thumbnail = isset($instance['show_thumbnail']) ? $instance['show_thumbnail'] : 'on';

                if ( 'on' ===  $show_thumbnail ) {
                    the_post_thumbnail();
                }
                echo '<blockquote>';
                the_excerpt( );
                the_title( '<div class="name-testimonial"><a class="name-testimonial" href="' . get_permalink( get_the_ID() ) . '">', ' </a></div>' );

                $label   = get_post_meta( get_the_ID(), '_testimonial_label', true );
                $website = get_post_meta( get_the_ID(), '_testimonial_website', true );
                $website = "<a class='url-testimonial' href=\"" . esc_url( $website ) . "\">". $label  ."</a>";

                if($label) echo $website;

                echo '</blockquote>';
                echo '</li>';
            }
            echo '</ul>';

            //echo '<div class="p-testimonial"></div>';
            echo '</div>';

            $script = "<script type=\"text/javascript\">
                jQuery(document).ready(function($){
                    $('.testimonial-widget ul').cycle({
                        fx:'$test_fx',
                        timeout:$test_timeout_fx,
                        speed:$test_speed_fx,
                        pager:'.p-testimonial',
                        height: true
                    });
                });
            </script>";

            echo $script;
            echo $after_widget;

        }

        wp_reset_query();
    }

    function update( $new_instance, $old_instance )
    {
        $instance = $old_instance;

        $instance['title'] = strip_tags( $new_instance['title'] );

        $instance['test_n_items'] = $new_instance['test_n_items'];

        $instance['show_thumbnail'] = $new_instance['show_thumbnail'] ? 'on' : 'off';

        $instance['test_fx'] = $new_instance['test_fx'];

        $instance['test_timeout_fx'] = $new_instance['test_timeout_fx'];

        $instance['test_speed_fx'] = $new_instance['test_speed_fx'];

        $instance['icon_img'] = $new_instance['icon_img'];

        $instance['img_url'] = $new_instance['img_url'];

        return $instance;
    }

    function form( $instance )
    {
        global $icons_name, $yiw_cycle_fxs, $yiw_easings;


        /* Impostazioni di default del widget */
        $defaults = array(
            'title' => 'Testimonials',
            'test_n_items' => 5,
            'test_fx' => 'scrollLeft',
            'test_easing_fx' => FALSE,
            'test_timeout_fx' => 8000,
            'test_speed_fx' => 300,
            'icon_img' => '',
            'img_url' => '',
            'show_thumbnail' => false,
        );

        $yiw_categories = get_categories('hide_empty=1&orderby=name');
        $wp_cats = array();

        foreach ($yiw_categories as $category_list )
        {
            $wp_cats[$category_list->category_nicename] = $category_list->cat_name;
        }

        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:
                <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
            </label>
        </p>

        <p>
            <label><?php _e( 'Icon', 'yiw' ) ?>:
                <select id="<?php echo $this->get_field_id( 'icon_img' ); ?>" name="<?php echo $this->get_field_name( 'icon_img' ); ?>">
                    <option value="0"></option>
                    <?php foreach ( $this->icons as $icon ) echo '<option value="'.$icon.'"'.selected( $icon, $instance['icon_img'], false ).'>'.$icon.'</option>'; ?>
                </select>
                <?php _e( 'or upload your icon:', 'yiw' ) ?>
                <input type="text" id="<?php echo $this->get_field_id( 'img_url' ); ?>" name="<?php echo $this->get_field_name( 'img_url' ); ?>" value="<?php echo $instance['img_url']; ?>" />
                <a href="#" class="upload-image button-secondary">Upload</a>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'test_n_items' ); ?>">Items:
                <select id="<?php echo $this->get_field_id( 'test_n_items' ); ?>" name="<?php echo $this->get_field_name( 'test_n_items' ); ?>">
                    <?php
                    for($i=1;$i<=20;$i++)
                    {
                        $select = '';
                        if($instance['test_n_items'] == $i) $select = ' selected="selected"';
                        echo "<option value=\"$i\"$select>$i</option>\n";
                    }
                    ?>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>">Show Thumbnail:
                <input type="checkbox" id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" <?php checked( $instance['show_thumbnail'], 'on' ); ?>>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'test_fx' ); ?>">Effect Slider:
                <select id="<?php echo $this->get_field_id( 'test_fx' ); ?>" name="<?php echo $this->get_field_name( 'test_fx' ); ?>">
                    <?php
                    foreach($yiw_cycle_fxs as $fx)
                    {
                        $select = '';
                        if($instance['test_fx'] == $fx) $select = ' selected="selected"';
                        echo "<option value=\"$fx\"$select>$fx</option>\n";
                    }
                    ?>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'test_timeout_fx' ); ?>">Timeout effect (ms):
                <input type="text" id="<?php echo $this->get_field_id( 'test_timeout_fx' ); ?>" name="<?php echo $this->get_field_name( 'test_timeout_fx' ); ?>" value="<?php echo $instance['test_timeout_fx']; ?>" size="4" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'test_speed_fx' ); ?>">Speed Animation (ms):
                <input type="text" id="<?php echo $this->get_field_id( 'test_speed_fx' ); ?>" name="<?php echo $this->get_field_name( 'test_speed_fx' ); ?>" value="<?php echo $instance['test_speed_fx']; ?>" size="4" />
            </label>
        </p>
    <?php
    }
}

?>
