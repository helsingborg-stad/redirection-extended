<?php

namespace RedirectionExtended;

class App
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        wp_register_style('redirection-extended-css', REDIRECTIONEXTENDED_URL . '/dist/' . \RedirectionExtended\Helper\CacheBust::name('css/redirection-extended.css'));
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        wp_register_script('redirection-extended-js', REDIRECTIONEXTENDED_URL . '/dist/' . \RedirectionExtended\Helper\CacheBust::name('js/redirection-extended.js'));
    }
}
