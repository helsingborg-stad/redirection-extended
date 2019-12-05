<?php

namespace RedirectionExtended;

class App
{
    public function __construct()
    {
        add_action('redirection_monitor_created', array($this, 'recursivelyRedirectChildren'), 10, 3);
        add_action('acf/init', array($this, 'registerOptionsPage'));
    }

    public function registerOptionsPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_sub_page(array(
                'page_title' => 'Redirection Extended',
                'menu_title' => 'Redirection Extended',
                'parent_slug' => 'tools.php',
            ));
        }
    }
    
    public function recursivelyRedirectChildren($newItem, $oldUrl, $postId)
    {
        if (!get_field('redirect_child_pages', 'options')) {
            return;
        }

        $newUrl = $newItem->match->url;
        $children = get_children($postId);

        if (!class_exists('\Red_Item') || empty($children) || empty($newUrl)) {
            return;
        }

        foreach ($children as $child) {
            $newChildUrl = wp_parse_url(get_permalink($child->ID), PHP_URL_PATH);
            $oldChildUrl = str_replace($newUrl, $oldUrl, $newChildUrl);

            // Break if permalink did not change
            if (!apply_filters('redirection_permalink_changed', false, $oldChildUrl, $newChildUrl)) {
                break;
            }

            do_action('redirection_remove_existing', $newChildUrl, $child->ID);

            $data = array(
                'url'         => $oldChildUrl,
                'action_data' => array('url' => $newChildUrl),
                'match_type'  => 'url',
                'action_type' => 'url',
                'action_code' => 301,
                'group_id'    => '1',
            );

            $newChildItem = \Red_Item::create($data);

            if (!is_wp_error($newChildItem)) {
                // Recursively add redirects for deeply nested children
                do_action('redirection_monitor_created', $newChildItem, $oldChildUrl, $child->ID);
            }
        }
    }
}
