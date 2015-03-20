<?php

/**
 * Navigation plugin to generate a better, more configurable navigation with (theoretically endless) subpages.
 *
 * @author Ahmet Topal
 * @author Niko Strijbol <strijbol.niko@gmail.com>
 * @author Oliver Lorenz
 *
 * @link http://ahmet-topal.com
 *
 * @license http://opensource.org/licenses/MIT
 */
class AT_Navigation
{
    /**
     * Holds the settings.
     */
    private $settings = array();

    /**
     * Holds the actual navigation.
     */
    private $navigation = array();

    /**
     * Add the meta tag Order. With this tag, it is possible to manually specify the order in which the menu pages
     * are displayed in the navigation. Each child menu (a folder in the file system) starts to count again. The index
     * file will be used to determine the order of the dropdown menu.
     *
     * For example:
     *      start.md - Order: 1
     *      folder/index.md - Order: 2
     *      folder/subpage.md - Order: 1
     *      test.md - Order: 3
     * will output this menu:
     *      start
     *      index
     *      |- subppage
     *      test
     *
     * @see sort_pages for the exact logic.
     *
     * @author Niko Strijbol
     */
    public function before_read_file_meta( &$headers )
    {
        $headers['order'] = "Order";
    }

    public function get_page_data( &$data, $page_meta )
    {
        //Loads all the meta values, including meta values from headers into page values
        foreach ($page_meta as $key => $value) {
            $data[$key] = $value;
        }
    }

    public function get_pages( &$pages, &$current_page, &$prev_page, &$next_page )
    {
        global $config;
        $navigation = array();

        foreach ($pages as $page) {
            if ( ! $this->at_exclude( $page )) {
                $_split     = explode( '/', substr( $page['url'], strlen( $this->settings['base_url'] ) + 1 ) );
                $navigation = array_merge_recursive( $navigation,
                    $this->at_recursive( $_split, $page, $current_page ) );
            }
        }

        array_multisort( $navigation );

        $this->navigation = $navigation;
    }

    public function config_loaded( &$settings )
    {
        $this->settings = $settings;

        // default id
        if ( ! isset( $this->settings['at_navigation']['id'] )) {
            $this->settings['at_navigation']['id'] = 'at-navigation';
        }

        // default classes
        if ( ! isset( $this->settings['at_navigation']['class'] )) {
            $this->settings['at_navigation']['class'] = 'at-navigation';
        }
        if ( ! isset( $this->settings['at_navigation']['class_li'] )) {
            $this->settings['at_navigation']['class_li'] = 'li-item';
        }
        if ( ! isset( $this->settings['at_navigation']['class_a'] )) {
            $this->settings['at_navigation']['class_a'] = 'a-item';
        }

        // default excludes
        $this->settings['at_navigation']['exclude'] = array_merge_recursive(
            array( 'single' => array(), 'folder' => array(), 'regex' => array() ),
            isset( $this->settings['at_navigation']['exclude'] ) ? $this->settings['at_navigation']['exclude'] : array()
        );
    }

    public function before_render( &$twig_vars, &$twig )
    {
        $twig_vars['at_navigation']['navigation'] = $this->at_build_navigation( $this->navigation, true );
    }

    private function at_build_navigation( $navigation = array(), $start = false )
    {
        $id       = $start ? $this->settings['at_navigation']['id'] : '';
        $class    = $start ? $this->settings['at_navigation']['class'] : '';
        $class_li = $this->settings['at_navigation']['class_li'];
        $class_a  = $this->settings['at_navigation']['class_a'];
        $child    = '';
        $ul       = $start ? '<ul id="%s" class="%s">%s</ul>' : '<ul>%s</ul>';

        if (isset( $navigation['_child'] )) {
            $_child = $navigation['_child'];

            //Sort the pages.
            uasort( $_child, array( $this, "sort_pages" ) );

            foreach ($_child as $c) {
                $child .= $this->at_build_navigation( $c );
            }

            $child = $start ? sprintf( $ul, $id, $class, $child ) : sprintf( $ul, $child );
        }

        $li = isset( $navigation['title'] )
            ? sprintf(
                '<li class="%1$s %5$s"><a href="%2$s" class="%1$s %6$s" title="%3$s">%3$s</a>%4$s</li>',
                $navigation['class'],
                $navigation['url'],
                $navigation['title'],
                $child,
                $class_li,
                $class_a
            )
            : $child;

        return $li;
    }

    private function at_exclude( $page )
    {
        $exclude = $this->settings['at_navigation']['exclude'];
        $url     = substr( $page['url'], strlen( $this->settings['base_url'] ) + 1 );
        $url     = ( substr( $url, - 1 ) == '/' ) ? $url : $url . '/';

        foreach ($exclude['single'] as $s) {
            $s = ( substr( $s, - 1 * strlen( 'index' ) ) == 'index' ) ? substr( $s, 0, - 1 * strlen( 'index' ) ) : $s;
            $s = ( substr( $s, - 1 ) == '/' ) ? $s : $s . '/';

            if ($url == $s) {
                return true;
            }
        }

        foreach ($exclude['folder'] as $f) {
            $f        = ( substr( $f, - 1 ) == '/' ) ? $f : $f . '/';
            $is_index = ( $f == '' || $f == '/' ) ? true : false;

            if (substr( $url, 0, strlen( $f ) ) == $f || $is_index) {
                return true;
            }
        }

        foreach ($exclude['regex'] as $r) {
            if (preg_match( $r, $url )) {
                return true;
            }
        }

        return false;
    }

    private function at_recursive( $split = array(), $page = array(), $current_page = array() )
    {
        $activeClass = ( isset( $this->settings['at_navigation']['activeClass'] ) ) ? $this->settings['at_navigation']['activeClass'] : 'is-active';
        if (count( $split ) == 1) {
            $ret = array(
                'title' => $page['title'],
                'url'   => $page['url'],
                'order' => $page['order'],
                'class' => ( $page['url'] == $current_page['url'] ) ? $activeClass : ''
            );

            $split0 = ( $split[0] == '' ) ? '_index' : $split[0];
            return array( '_child' => array( $split0 => $ret ) );
        } else {
            if ($split[1] == '') {
                array_pop( $split );
                return $this->at_recursive( $split, $page, $current_page );
            }

            $first = array_shift( $split );
            return array( '_child' => array( $first => $this->at_recursive( $split, $page, $current_page ) ) );
        }
    }

    /**
     * Compare two pages.
     *
     * If the meta tag Order is not set, the pages are sorted according to their name.
     * If both pages have the same Order, they are not treated as equal.
     * If one of the pages don't have Order set, the page that does will be sorted before the page that doesn't.
     *
     * @author Niko Strijbol
     *
     * @param $a array The first page.
     * @param $b array The second page.
     *
     * @return int The value to sort.
     */
    private function sort_pages( $a, $b )
    {
        if (empty( $a['order'] ) && empty( $b['order'] )) {
            return strcmp( basename( $a['url'] ), basename( $b['url'] ) );
        } else {
            if ($a['order'] == $b['order']) {
                return 0;
            } elseif (empty( $a['order'] )) {
                return 1;
            } elseif (empty( $b['order'] )) {
                return - 1;
            } else {
                return ( $a['order'] < $b['order'] ) ? - 1 : 1;
            }
        }
    }
}

?>