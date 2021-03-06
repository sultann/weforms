<?php

/**
 * Ninja Form
 *
 * Import Ninja form forms
 */
class WeForms_Importer_NF extends WeForms_Importer_Abstract {

    function __construct() {
        $this->id        = 'nf';
        $this->title     = 'Ninja Forms';
        $this->shortcode = 'ninja_form';

        parent::__construct();
    }

    /**
     * See if the plugin exists
     *
     * @return boolean
     */
    public function plugin_exists() {
        return class_exists( 'Ninja_Forms' );
    }

    /**
     * Show notice if Ninja From found
     *
     * @return void
     */
    public function ninja_form_field($form) {

        $data = array();
        foreach( Ninja_Forms()->form( $form->get_id() )->get_fields() as $field ){
            $data[$field->get_settings( 'order' )] = array(
                'type'      => $field->get_setting( 'type' ),
                'key'       => $field->get_setting( 'key' ),
                'label'     => $field->get_setting( 'label' ),
                'required'  => $field->get_setting( 'required' ) ? $field->get_setting( 'required' ) : 0
            );

            if (in_array($field->get_setting('type'), array('listselect', 'listradio', 'listcheckbox', 'listmultiselect')) ) {
                foreach ($field->get_setting('options') as $option) {
                    $data[$field->get_settings( 'order' )]['options'][] = array(
                        'label'     => $option['label'],
                        'value'     => $option['value'],
                    );
                }
            }
        }
        return $data;
    }


    /**
     * Get all the forms
     *
     * @return array
     */
    public function get_forms() {
        $items    = Ninja_Forms()->form()->get_forms();

        return $items;
    }

    /**
     * Get form name
     *
     * @param  object $form
     *
     * @return string
     */
    public function get_form_name( $form ) {
        return $form->get_setting( 'title' );
    }

    /**
     * Get the form fields
     *
     * @param  object $form
     *
     * @return array
     */
    public function get_form_fields( $form ) {
        $form_fields = $this->ninja_form_field($form);

        foreach ($form_fields as $menu_order => $field) {

            $field_content = array();

            switch ( $field['type'] ) {
                case 'text':
                case 'email':
                case 'textarea':
                case 'date':
                case 'url':
                case 'firstname':
                case 'lastname':

                    if($field['type'] == 'firstname' || $field['type'] == 'firstname') {
                        $field['type'] = 'text';
                    }

                    $form_fields[] = $this->get_form_field( $field['type'], array(
                        'required' => $field['required'] ? 'yes' : 'no',
                        'label'    => $this->find_label( $field['label'], $field['type'], $field['key'] ),
                        'name'     => $field['key'],
                    ) );
                    break;

                case 'checkbox':


                    $form_fields[] = $this->get_form_field( $field['type'], array(
                        'required' => $field['required'] ? 'yes' : 'no',
                        'label'    => $this->find_label( $field['label'], $field['type'], $field['key'] ),
                        'name'     => $field['key'],
                    ) );
                    break;

                case 'select':
                case 'radio':
                case 'checkbox':
                case 'listcheckbox':
                case 'listmultiselect':
                case 'listradio':
                case 'listselect':


                    $form_fields[] = $this->get_form_field( $field['type'], array(
                        'required' => $field['required'] ? 'yes' : 'no',
                        'label'    => $this->find_label( $field['label'], $field['type'], $field['key'] ),
                        'name'     => $field['key'],
                        'options'  => $this->get_options( $field['options'] ),
                    ) );
                    break;

                case 'range':
                case 'phone':
                case 'number':
                case 'quantity':
                case 'total':
                case 'shipping':
                case 'quantity':

                    $form_fields[] = $this->get_form_field( $field['type'], array(
                        'required'        => $field['required'] ? 'yes' : 'no',
                        'label'           => $this->find_label( $field['label'], $field['type'], $field['key'] ),
                        'name'            => $field['key'],
                    ) );

                    break;

                case 'city':
                case 'quiz':
                case 'address':
                case 'listcountry':
                case 'liststate':
                case 'zip':
                case 'product':
                case 'hr':
                case 'html':
                case 'hidden':
                case 'spam':
                case 'starrating':
                case 'submit':
                    
                    break;

                case 'acceptance':

                    $form_fields[] = $this->get_form_field( 'toc', array(
                        'required'    => $field['required'] ? 'yes' : 'no',
                        'description' => $this->find_label( $field['label'], $field['type'], $field['key'] ),
                        'name'        => $field['key'],
                    ) );
                    break;

                case 'recaptcha':

                    $form_fields[] = $this->get_form_field( $field['type'], array(
                        'required'    => $field['required'] ? 'yes' : 'no',
                        'label' => $this->find_label( $field['label'], $field['type'], $field['key'] ),
                        'name'        => $field['key'],
                    ) );
                    break;
            }
        }

        return $form_fields;
    }

    /**
     * Get form settings
     *
     * @param  object $form
     *
     * @return array
     */
    public function get_form_settings( $form ) {
        $all_settings = get_option( 'nf_form_' . $form->get_id(), true );
        foreach ($all_settings['actions'] as $actions) {
            if('successmessage' == $actions['settings']['type']){
                $message = $actions['settings']['message'];
            }
        }
        $message    = str_replace(' {field:name}', '', $message);
        $default    = $this->get_default_form_settings();
        $settings   = wp_parse_args( array(
            'message' => $message,
            ), $default );

        return $settings;
    }

    /**
     * Get form notifications
     *
     * @param  object $form
     *
     * @return array
     */
    public function get_form_notifications( $form ) {
        $notifications = array();
        $all_settings = get_option( 'nf_form_' . $form->get_id(), true );
        foreach ($all_settings['actions'] as $actions) {
            if('Email Notification' == $actions['settings']['label']){
                $action_settings = $actions['settings'];
            }
            else if('Email Confirmation' == $actions['settings']['label']){
                $action_settings2 = $actions['settings'];
            }
        }

        $sub    = str_replace('{field:name}', '{site_name}', $action_settings['email_subject']);

        $notifications = array(
            array(
                'active'      => $action_settings['active'] ? 'true' : 'false',
                'name'        => 'Admin Notification',
                'subject'     => str_replace( '[your-subject]', '{field:your-subject}', $sub ),
                'to'          => '{field:your-email}',
                'replyTo'     => '{field:your-email}',
                'message'     => '{all_fields}',
                'fromName'    => '{site_name}',
                'fromAddress' => '{admin_email}',
                'cc'          => '',
                'bcc'         => '',
            ),
        );

        $sender_match = $this->get_notification_sender_match( get_option( 'admin_email' ) );

        if ( !empty( $sender_match['fromName'] ) ) {
            $form_notifications[0]['fromName'] = $sender_match['fromName'];
        }

        if ( isset( $sender_match['fromAddress'] ) ) {
            $form_notifications[0]['fromAddress'] = $sender_match['fromAddress'];
        }

        if ( $action_settings2['active'] ) {
            $notifications[] = array(
                'active'      => $action_settings2['active'] ? 'true' : 'false',
                'name'        => 'Admin Notification',
                'subject'     => str_replace( '[your-subject]', $action_settings2['subject'], $action_settings2['subject'] ),
                'to'          => '{field:your-email}',
                'replyTo'     => '{field:your-email}',
                'message'     => '{all_fields}',
                'fromName'    => '{site_name}',
                'fromAddress' => '{admin_email}',
                'cc'          => '',
                'bcc'         => '',
            );
        }

        $sender_match = $this->get_notification_sender_match( get_option( 'admin_email' ) );

        if ( !empty( $sender_match['fromName'] ) ) {
            $form_notifications[1]['fromName'] = $sender_match['fromName'];
        }

        if ( isset( $sender_match['fromAddress'] ) ) {
            $form_notifications[1]['fromAddress'] = $sender_match['fromAddress'];
        }

        return $notifications;
    }

    /**
     * Match the sender
     *
     * @param  array $mail
     *
     * @return array
     */
    public function get_notification_sender_match( $mail ) {

        if ( !isset( $mail['sender'] ) ) {
            return;
        }
        $sender       = array( 'fromName' => '', 'fromAddress' => '' );
        $sender_match = array();

        preg_match( '/([^<"]*)"?\s*<(\S*)>/', $mail['sender'], $sender_match );

        if ( isset( $sender_match[1] ) ) {
            $sender['fromName'] = $sender_match[1];
        }

        if ( isset( $sender_match[2] ) ) {
            $sender['fromAddress'] = $sender_match[2];
        }

        return $sender;
    }

    /**
     * Try to find out the input label
     *
     * Loop through all the label tags and try to find out
     * if the field is inside that tag. Then strip out the field and find out label
     *
     * @param  string $content
     * @param  string $type
     * @param  string $fieldname
     *
     * @return string
     */
    private function find_label( $content, $type, $fieldname ) {

        // find all enclosing label fields
        $pattern = '/<label>([ \w\S\r\n\t]+?)<\/label>/';
        preg_match_all( $pattern, $content, $matches );

        foreach ($matches[1] as $key => $match) {
            $match = trim( str_replace( "\n", '', $match ) );

            preg_match( '/\[(?:' . preg_quote( $type ) . ') ' . $fieldname . '(?:[ ](.*?))?(?:[\r\n\t ](\/))?\]/', $match, $input_match );

            if ( $input_match ) {
                $label = strip_tags( str_replace( $input_match[0], '', $match ) );
                return trim( $label );
            }
        }

        return $fieldname;
    }

    /**
     * Get file type for upload files
     *
     * @param  string $extension
     *
     * @return boolean|string
     */
    private function get_file_type( $extension ) {
        $allowed_extensions = wpuf_allowed_extensions();

        foreach ($allowed_extensions as $type => $extensions) {
            $_extensions = explode( ',', $extensions['ext'] );

            if ( in_array( $extension, $_extensions ) ) {
                return $type;
            }
        }

        return false;
    }

    /**
     * Translate to wpuf field options array
     *
     * @param  object $field
     *
     * @return array
     */
    private function get_options( $field ) {
        $options = array();

        if ( !is_array( $field ) ) {
            return $options;
        }

        foreach ($field as $value) {
            $options[ $value['value'] ] = $values['label'];
        }

        return $options;
    }
}
