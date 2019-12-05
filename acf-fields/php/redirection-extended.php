<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5de91ccdd7b7e',
    'title' => __('Redirection Extended', 'redirection-extended'),
    'fields' => array(
        0 => array(
            'key' => 'field_5de91cfb0b312',
            'label' => __('Redirect child pages', 'redirection-extended'),
            'name' => 'redirect_child_pages',
            'type' => 'true_false',
            'instructions' => __('Enable to redirect all child pages to new url when parent page change permalink', 'redirection-extended'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
            'ui_on_text' => __('Enabled', 'redirection-extended'),
            'ui_off_text' => __('Disabled', 'redirection-extended'),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'acf-options-redirection-extended',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}