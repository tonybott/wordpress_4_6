<?php
/*===============
    KBE Category Widget
 ===============*/
 
//========= Custom Knowledgebase Category Widget
add_action( 'widgets_init', 'kbe_category_widgets' );
function kbe_category_widgets() {
    register_widget( 'kbe_Cat_Widget' );
}

//========= Custom Knowledgebase Category Widget Body
class kbe_Cat_Widget extends WP_Widget {
	
    function __construct() {
        parent::__construct(
            'kbe_category_widget', // Base ID
            __( 'Knowledgebase Category', 'kbe' ), // Name
            array( 'description' => __('WP Knowledgebase category widget to show categories on the site', 'kbe'), 
                    'classname' => 'kbe' ), // Args
            array( 'width' => 300, 'height' => 350, 'id_base' => 'kbe_category_widget' )
        );
    }
	
     //=======> How to display the widget on the screen.
    function widget($args, $widgetData) {
        extract($args);
		
        //Our variables from the widget settings.
        $kbe_widget_cat_title = $widgetData['txtKbeCatHeading'];
        $kbe_widget_cat_count = $widgetData['txtKbeCatCount'];
		
        //=======> widget body
        echo $before_widget;
        echo '<div class="kbe_widget">';
        
            if ($kbe_widget_cat_title){
                echo '<h2>'.$kbe_widget_cat_title.'</h2>';
            }
			
            $kbe_cat_args = array(
                'number' 	=>  $kbe_widget_cat_count,
                'taxonomy'	=>  'kbe_taxonomy',
                'orderby'   =>  'terms_order',
                'order'     =>  'ASC'
            );
			
            $kbe_cats = get_categories($kbe_cat_args);
            echo "<ul>";
                foreach($kbe_cats as $kbe_taxonomy){
                    echo "<li>"
                            ."<a href=".get_term_link($kbe_taxonomy->slug, 'kbe_taxonomy')." title=".sprintf( __( "View all posts in %s" ), $kbe_taxonomy->name ).">"
                                .$kbe_taxonomy->name.
                             "</a>"
                         ."</li>";
                }
            echo "</ul>";
        
        echo "</div>";
        echo $after_widget;
    }
	
    //Update the widget 
    function update($new_widgetData, $old_widgetData) {
        $widgetData = $old_widgetData;
		
        //Strip tags from title and name to remove HTML 
        $widgetData['txtKbeCatHeading'] = $new_widgetData['txtKbeCatHeading'];
        $widgetData['txtKbeCatCount'] = $new_widgetData['txtKbeCatCount'];
		
        return $widgetData;
    }
    function form($widgetData) {
        //Set up some default widget settings.
        $widgetData = wp_parse_args((array) $widgetData);
?>
        <p>
            <label for="<?php echo $this->get_field_id('txtKbeCatHeading'); ?>"><?php _e('Category Title:','kbe') ?></label>
            <input id="<?php echo $this->get_field_id('txtKbeCatHeading'); ?>" name="<?php echo $this->get_field_name('txtKbeCatHeading'); ?>" value="<?php echo $widgetData['txtKbeCatHeading']; ?>" style="width:275px;" />
        </p>    
        <p>
            <label for="<?php echo $this->get_field_id('txtKbeCatCount'); ?>"><?php _e('Catgory Quantity:','kbe'); ?></label>
            <input id="<?php echo $this->get_field_id('txtKbeCatCount'); ?>" name="<?php echo $this->get_field_name('txtKbeCatCount'); ?>" value="<?php echo $widgetData['txtKbeCatCount']; ?>" style="width:275px;" />
        </p>
<?php
    }
}
?>