<?php

class VentureEventSystemFilters
{

    public function __construct()
    {
        global $wp_embed;

        // Add support for autoembedding videos in content other than post_content (specifically, here, for event details)
        add_filter('the_content', [$wp_embed, 'autoembed'], 11);

        // Content filters
        add_filter('the_title', [$this, 'filterTitle'], 10, 2);
        add_filter('the_content', [$this, 'eventContent'], 1);

        // Placeholder filter
        add_filter('enter_title_here', [$this, 'titlePlaceholder']);
    }

    public function eventContent($content) {
        global $post;

        if ($post->post_type == 'event') {

            $vem3 = new VentureEventManager3();
            $vem3->setContext('content');

            if (is_single()) {
                $multiple = false;
                
                // Custom layout?
                $titan = TitanFramework::getInstance('venture-event-system-pro');
                $enabled = $titan->getOption('features');
                if (is_array($enabled) && in_array('event-layout', $enabled)) {
                    $titan = TitanFramework::getInstance('venture-event-system');
                    $useCustom = $titan->getOption('use-custom', $post->ID);
                    if ($useCustom) {
                        $pageChunks = $titan->getOption('event-layout-fields', $post->ID);
                        $dateChunks = $titan->getOption('event-layout-occurrence-fields', $post->ID);
                        $vem3->setCustomChunks(true, $pageChunks, $dateChunks);
                    }
                }
            } elseif (is_archive()) {
                $multiple = true;
            }

            if (post_password_required()) {
                $content = get_the_password_form($post->ID);
            } else {
                $content = '<div class="vem-single-event-content">'.$vem3->getSingleEventContent($post, $multiple).'</div>';
            }
            return $content;
        }

        return $content;
    }

    public function filterTitle($title, $id = null)
    {
        global $post;

        if (!is_single() || $post->post_type !== 'event' || get_post_type($id) !== 'event' || !in_the_loop()) {
            return $title;
        }
        $titan = TitanFramework::getInstance('venture-event-system');

        switch ($titan->getOption('single-event-page-title')) {
            case 'none':
                return '';

            case 'category':
                $option = $titan->getOption('single-event-page-title-category-display');

                switch ($option) {
                    case 'top':
                        $cats = wp_get_object_terms($id, 'event_category', array('fields' => 'all'));
                        if (sizeof($cats) > 0) {
                            $label = $cats[0]->name;
                            foreach ($cats as $c) {
                                if ($c->parent == 0) {
                                    $label = $c->name;
                                    break;
                                }
                            }
                        } else {
                            $label = 'Uncategorized';
                        }

                        return $label;

                    case 'list':
                    default:
                        $cats = wp_get_object_terms($id, 'event_category', array('fields' => 'slugs'));
                        return strip_tags(get_the_term_list($id, 'event_category', '', ', ', ''));
                }

            case 'title':
            default:
                return $title;
        }
    }

    public function titlePlaceholder($title){
         $screen = get_current_screen();
         if  ($screen->post_type == 'event') {
              $title = 'Enter Event Name';
         }
         return $title;
    }

}
