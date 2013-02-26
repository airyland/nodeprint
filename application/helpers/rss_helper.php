<?php

!defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * Simple and Elegant Forum Software
 *
 * @package         NodePrint
 * @author          airyland <i@mao.li>
 * @copyright       Copyright (c) 2013, mao.li
 * @license         MIT
 * @link            https://github.com/airyland/nodeprint
 * @version         0.0.5
 */

/**
 * rss generater
 * @param array $data
 * @note the $data should match the format
 */
function generate_rss($data) {
    $rss = '';
    $rss.= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n".'<channel>' . "\n" .
            '<title>' . $data['title'] . '</title>' . "\n" .
            '<link>' . $data['link'] . '</link>' . "\n" .
            '<updated>' . $data['time'] . '</updated>' . "\n" .
            '<description>' . $data['description'] . '</description>' . "\n" .
            '<atom:link href="' . base_url() . 'rss" rel="self" type="application/rss+xml" />'. "\n";
    foreach ($data['post'] as $item) {
        $rss.=
                '<item>' . "\n" .
                '<title>' . xml_convert($item['post_title']) . '</title>' .
                '<link>' . base_url() . 't/' . $item['post_id'] . '</link>' .
                '<description>' . xml_convert($item['post_title']) . '</description>' .
                '<content>' . xml_convert($item['post_content']) . '</content>' .
                '<pubDate>' . date(DATE_RSS, strtotime($item['post_time'])) . '</pubDate>' .
                '<guid>' . base_url() . 't/' . $item['post_id'] . '</guid>' .
                '</item>' . "\n";
    }
    $rss.='</channel></rss>';
    return $rss;
}

?>