<?php

if (!defined('ABSPATH')) {
    exit;
}

class Quentn_Wp_Learndash
{

    /**
     * Constructor method.
     */
    public function __construct() {

        if ( is_multisite() ) {
            add_action( 'wpmu_activate_user', array( $this, 'quentn_learndash_courses' ) );
            add_action( 'add_user_to_blog', array( $this, 'quentn_learndash_courses' ) );
        } else {
            add_action( 'user_register', array( $this, 'quentn_learndash_courses' ), 100 );
        }

        //add learndash settings
        add_action( 'admin_init', array( $this, 'register_learndash_settings' ) );

        //update user lerandash courses
        add_action( 'profile_update', array( $this, 'quentn_learndash_courses' ), 100 );

        //remove learndash courses if user role is removed
        add_action( 'remove_user_role', array( $this, 'quentn_remove_learndash_courses' ), 10, 2 );

        //add learndash courses if user get new user role
        //set priority higher than 'remove_user_role'
        add_action( 'add_user_role', array( $this, 'quentn_add_learndash_courses' ), 50, 2 );
    }

    /**
     * Get list of all courses from lerndash
     *
     * @access public
     * @return void
     */
    public function get_learndash_courses() {
        $args = array(
            'post_type'   => 'sfwd-courses',
            'posts_per_page'   => 1000,
        );
        $courses_list = get_posts( $args );
        $courses = array();
        foreach ($courses_list as $key => $value) {
            $courses[] = array(
                "id" => $value->ID,
                "title" => $value->post_title,
            );
        }
        return $courses;
    }

    /**
     * Register settings
     *
     * @access public
     * @return void
     */
    public function register_learndash_settings() {

        //set values for register_setting
        $setting = array(
            'option_group' => 'quentn_learndash_options_group',
            'option_name'  => 'quentn_learndash_courses_settings_for_user_roles'
        );

        //add values for settings api, add_settings_section
        $section = array(
            'id' => 'quentn_learndash_option',
            'title' =>  __( 'LearnDash Course Registration Settings', 'quentn-wp'),
            'callback' => '__return_false',
            'page' => 'quentn-learn-dash'
        );

        //add fields
        $fields = array();
        //add tag fields for all wordpress roles
        $wp_roles =  new WP_Roles;

        //set enable/disable add user from wp to quentn
        foreach ( $wp_roles->get_names() as $slug => $name ) {

            //add settings if learndash plugin is active
            if( $slug != 'administrator' ) {
                $fields [] = array(
                    'id'              => 'quentn_learndash_course'.$slug,
                    'title'           => translate_user_role( $name ),
                    'callback'        => array( $this, 'select_learndash_courses' ),
                    'page'            => 'quentn-learn-dash',
                    'section'         => 'quentn_learndash_option',
                    'args'            => array(
                        'label_for'   => __('Please Select Courses', 'quentn-wp'),
                        'role'        => $slug,
                        'courses'     => $this->get_learndash_courses(),
                    )
                );
            }


        }
        // register setting
        register_setting( $setting["option_group"], $setting["option_name"], ( isset( $setting["callback"] ) ? $setting["callback"] : '' ) );

        // add settings section
        add_settings_section( $section["id"], $section["title"], ( isset( $section["callback"] ) ? $section["callback"] : '' ), $section["page"] );

        // add settings field
        foreach ( $fields as $field ) {
            add_settings_field( $field["id"], $field["title"], ( isset( $field["callback"] ) ? $field["callback"] : '' ), $field["page"], $field["section"], ( isset( $field["args"] ) ? $field["args"] : '' ) );
        }
    }

    /**
     * Add learndash courses when user is registered
     *
     * @access public
     * @param int $user_id id of newly created user
     * @return void
     */
    public function quentn_learndash_courses( $user_id ) {

        // Get the user object.
        $user = new \WP_User( $user_id );

        // Get the user roles
        $user_roles = $user->roles;

        foreach ( $user_roles as $user_role ) {
            $this->quentn_add_learndash_courses( $user_id, $user_role );
        }
    }

    /**
     * Add LearnDash courses when user gets new role
     *
     * @access public
     * @param int $user_id id user
     * @param string $role_added role added
     * @return void
     */
    public function quentn_add_learndash_courses( $user_id, $role_added ) {

        //get courses settings for user roles
        $quentn_learndash_courses_selection =  get_option( 'quentn_learndash_courses_settings_for_user_roles' ) ;

        //if courses set for new role
        if( isset( $quentn_learndash_courses_selection[$role_added] ) ) {
            $courses_selected_for_user_role = $quentn_learndash_courses_selection[$role_added];
            $users_current_courses = array();
            //get user courses
            if ( function_exists( 'learndash_get_user_courses_from_meta' ) ) {
                $users_current_courses = learndash_get_user_courses_from_meta( $user_id );
            }
            //combine both current courses and courses set for this user role
            $add_courses = array_merge( $courses_selected_for_user_role, $users_current_courses );
            //set new courses for user
            if ( function_exists( 'learndash_user_set_enrolled_courses' ) ) {
                learndash_user_set_enrolled_courses( $user_id, $add_courses );
            }

        }
    }

    /**
     * Remove learndash user courses when role is removed
     *
     * @access public
     * @param int $user_id id user
     * @param string $role_removed user role removed
     * @return void
     */
    public function quentn_remove_learndash_courses( $user_id, $role_removed ) {

        //get courses settings for user roles
        $quentn_learndash_courses_selection =  get_option( 'quentn_learndash_courses_settings_for_user_roles' ) ;

        //if courses set for removed role
        if( isset( $quentn_learndash_courses_selection[$role_removed] ) ) {
            $courses_selected_for_user_role = $quentn_learndash_courses_selection[$role_removed];

            //get list of current courses of user
            if ( function_exists( 'learndash_get_user_courses_from_meta' ) ) {
                $users_current_courses = learndash_get_user_courses_from_meta( $user_id );
            }

            //remove selected courses for this role from current user courses
            foreach ( $courses_selected_for_user_role as $courses_selected ) {
                if (($key = array_search($courses_selected, $users_current_courses)) !== false) {
                    unset($users_current_courses[$key]);
                }
            }

            //set new courses for user
            if ( function_exists( 'learndash_user_set_enrolled_courses' ) ) {
                learndash_user_set_enrolled_courses( $user_id, $users_current_courses );
            }

            //adjust courses again, in case some courses were removed which is set for remaining user roles
            $this->quentn_learndash_courses( $user_id );
        }
    }

    /**
     * Display learndash courses dropdown
     *
     * @access public
     * @return void
     */
    public function select_learndash_courses( $args ) {
        $existing_courses_selection = array();
        //Get defined settings for tags
        $quentn_learndash_courses_selection =  get_option( 'quentn_learndash_courses_settings_for_user_roles' ) ;
        //Get the selected terms for specific role
        if( isset( $quentn_learndash_courses_selection[$args['role']] ) ) {
            $existing_courses_selection = $quentn_learndash_courses_selection[$args['role']];
        }
        ?>
        <select class="quentn-select-learndash-courses"  style="width: 70%"  name="quentn_learndash_courses_settings_for_user_roles[<?php echo $args['role']?>][]" id="quentn_learndash_courses_settings_for_user_roles<?php echo $args['role']?>"  multiple>
            <?php foreach($args['courses'] as $course) { ?>
`                <option value="<?php echo $course['id']?>" <?php echo ( in_array( $course['id'], $existing_courses_selection ) ) ? 'selected="selected"' : ''; ?>><?php echo $course['title']?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new self;
        }

        return $instance;
    }
}


Quentn_Wp_Learndash::get_instance();