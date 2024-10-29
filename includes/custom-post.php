<?php
/**
 * Advanced Custom Data
 */
defined('ABSPATH') or die();

global $advanced_data;

if(empty($advanced_data)) :

/**
 * The advanced_data class.
 */
class advanced_data {

    public $post_type = 'acd-data';

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action('init',           array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post',      array($this, 'save'));
        add_action('admin_init',     array($this, 'admin_init'));
    }
    
    /**
     * Register `acd-data` post type.
     *
     * @link https://developer.wordpress.org/reference/functions/register_post_type/
     */
    function register_post_type()
    {
        $labels = array(
            'name'               => _x( 'Advanced Data', 'post type general name', 'acd' ),
            'singular_name'      => _x( 'Advanced Data', 'post type singular name', 'acd' ),
            'menu_name'          => _x( 'Advanced Data', 'admin menu', 'acd' ),
            'name_admin_bar'     => _x( 'Advanced Data', 'add new on admin bar', 'acd' ),
            'add_new'            => _x( 'Add New', 'Advanced Data', 'acd' ),
            'add_new_item'       => __( 'Add New Data', 'acd' ),
            'new_item'           => __( 'New Data', 'acd' ),
            'edit_item'          => __( 'Edit Data', 'acd' ),
            'view_item'          => __( 'View Data', 'acd' ),
            'all_items'          => __( 'All Data', 'acd' ),
            'search_items'       => __( 'Search Data', 'acd' ),
            'parent_item_colon'  => __( 'Parent Data:', 'acd' ),
            'not_found'          => __( 'No Data found.', 'acd' ),
            'not_found_in_trash' => __( 'No Data found in Trash.', 'acd' )
        );

        $args = array(
            'labels'             	=> $labels,
            'description'        	=> __( 'Description', 'acd' ),
            'public'             	=> false,
            'publicly_queryable' 	=> false,
            'show_ui'            	=> true,
            'show_in_menu'       	=> true,
            'query_var'          	=> true,
            'rewrite'            	=> false,
            'taxonomies'    	 	=> array(),
            'capability_type'    	=> 'post',
            'has_archive'        	=> false,
            'hierarchical'       	=> true,
            'menu_position'      	=> 99,  // 20: below Pages
            // https://developer.wordpress.org/resource/dashicons/#megaphone
            'menu_icon'    			=> 'dashicons-media-spreadsheet', 
            'supports'           	=> array('title', 'thumbnail')
        );

        register_post_type($this->post_type, $args);
    }
 
    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        if ($post_type == $this->post_type) {
            add_meta_box(
                'acd_meta_box',
                __( 'Advanced Data', 'acd' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
    
    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {
 
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
 
        // Check if our nonce is set.
        if ( ! isset( $_POST['acd_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = sanitize_text_field($_POST['acd_inner_custom_box_nonce']);
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'acd_inner_custom_box' ) ) {
            return $post_id;
        }
        
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        /* OK, it's safe for us to save the data now. */

        // Sanitize the user input.
        $type = sanitize_text_field($_POST['acd_type']);
        $data = sanitize_post_field('post_content', $_POST['acd_data'], $post_id, 'edit');

        // Update the meta field.
        update_post_meta($post_id, '_acd_data', $data);
        update_post_meta($post_id, '_acd_type', $type);
    }
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {
 
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'acd_inner_custom_box', 'acd_inner_custom_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $type = sanitize_text_field(get_post_meta($post->ID, '_acd_type', true));
        $data = sanitize_post_field('post_content', get_post_meta($post->ID, '_acd_data', true), $post->ID, 'edit');
        
        // Display the form, using the current value.
        
        $type_options = array(
            ''          => 'Text',
            'br'        => 'Automatically add br',
            'list'      => 'List',
            'thumbnail' => 'Thumbnail',
            'script'    => 'Style - Script'
        );
        
        ?>
        <p>
            <textarea name="acd_data" style="width: 100%; height: 100px;"><?php echo $data; ?></textarea>
        </p>
        <p>
            <label for="acd_type">
                <?php _e( 'Type', 'acd' ); ?>
            </label>
            <select id="acd_type" name="acd_type">
                <?php foreach( $type_options as $key => $value ) : ?>
                <option value="<?php echo esc_attr($key);?>" <?php 
                    echo esc_attr($type == $key ? 'selected' : '');
                ?>><?php _e( $value, 'acd' ); ?></option>
                <?php endforeach;?>
            </select>
        </p>
        <hr/>
        <p>
            <strong>
                <?php _e( 'Show text in Content Post', 'acd' ); ?>
            </strong>
        </p>
        <p>
            <input value="[acd-data id=<?php echo $post->ID; ?>]" />
        </p>
        <hr/>
        <p>
            <strong>
                <?php _e( 'Show list in Contact Form 7', 'acd' ); ?>:
            </strong>
            [select your-list acd-data:<?php echo $post->ID; ?>], 
            [checkbox your-check acd-data:<?php echo $post->ID; ?>], 
            [radio your-radio acd-data:<?php echo $post->ID; ?>]
        </p>
        <p>
            <input value="acd-data:<?php echo $post->ID; ?>" />
        </p>
        <hr/>
        <p>
            <strong>
                <?php _e( 'Show list in Advanced Custom Fields', 'acd' ); ?>:
            </strong>
            (Select, Checkbox, Radio Button)
        </p>
        <p>
            <strong>
                <?php _e('Choices', 'acd'); ?>:
            </strong>
            <input value="acd-data:<?php echo $post->ID; ?>" />
        </p>
        <hr/>
        <p>
            <a href="https://docs.photoboxone.com/advanced-custom-data.html" target="_blank">
                <strong>
                    <?php _e('Documentation', 'acd'); ?>
                </strong>
            </a>
            |
            <a href="<?php echo acd_pbone_url('contact')?>" target="_blank">
                <strong>
                    <?php _e('Support', 'acd'); ?>
                </strong>
            </a>
        </p>
        <?php
    }

    function admin_init() 
    {
        add_filter("manage_posts_columns", array($this, "add_post_column"), 10, 2);
        add_action("manage_{$this->post_type}_posts_custom_column", array($this, "post_column"), 10, 2);    
    }

    /*
     * Since 1.0.0
     * 
     * Display column title;
     */
    function add_post_column($columns = array(), $post_type = '')
    {
        if($post_type == $this->post_type) {
            $list = array();

            foreach ($columns as $key => $value) {
                $list[$key] = $value;

                if ($key == 'title') {
                    // $list['type']   = __( 'Type', 'acd' );
                    $list['choices']   = 'Contact Form 7 | Advanced Custom Fields';
                    $list['shortcode']   = __( 'Shortcode', 'acd' );
                }
            }

            $columns = $list;
        }

        return $columns;
    }

    /*
     * Since 1.0.0
     * 
     * Display column value;
     */
    function post_column($column = '', $post_id = 0) 
    {
        $p = get_post($post_id);
        
        if (is_object($p) && $p->post_type == $this->post_type)
        {
            if($column == 'type') {
                esc_attr_e(get_post_meta($post_id, '_acd_type', true));
            } else if($column == 'shortcode' || $column == 'choices') {
                echo $this->column_input($column, $post_id);
            }
        }
    }

    /*
     * Since 1.0.0
     * 
     * Display column value;
     */
    function column_input($column = '', $post_id = 0)
    {
        if($column == 'shortcode') {
            $value = '[acd-data id=&quot;'.$post_id.'&quot;]';
        } else {
            $value = 'acd-data:' . $post_id;
        }

        return '<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value="'.$value.'" class="large-text code"></span>';
    }

    /*
     * Since 1.0.21
     * 
     * @params args Array;
     * 
     * @return list Array;
     */
    function get_posts($args = [])
    {
        $args['post_type']  = $this->post_type;

        return get_posts($args);
    }

    /*
     * Since 1.0.21
     * 
     * @return list Array;
     */
    function get_data_list()
    {
        $args = [
            'meta_key'   => '_acd_type',
            'meta_value' => 'list',
        ];

        return $this->get_posts($args);
    }
}

$advanced_data = new advanced_data();

endif;