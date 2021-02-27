<?php

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class Recent_Commented_Posts_Util
{
    public static function get_recent_commented_posts($args)
    {

        $number = empty( $args['number'] ) ? false : filter_var( $args['number'], FILTER_VALIDATE_INT );
        if ( !$number ) {
            $number = 5;
        }

        $recent_commented_posts_result = Recent_Commented_Posts_Util::query_posts_with_recent_comments($number, 'post');
        $html_list = Recent_Commented_Posts_Util::format_last_commented_list($recent_commented_posts_result, '');

        return $html_list;
    }


    /**
     * Query for posts sorted by the last approved comment without password protected posts and pingbacks.
     *
     * @param $limit
     * @return object|mixed
     */
    public static function query_posts_with_recent_comments($limit)
    {

        global $wpdb;

        $query = "select
             wp_posts.*,
             coalesce((
             select
                max(comment_date)
             from
                $wpdb->comments wpc
             where
                wpc.comment_post_id = wp_posts.id
                AND comment_approved = 1
                AND post_password = ''
                AND comment_type NOT IN ( 'pingback' )
                AND comment_type NOT IN ( 'trackback' )
            ),
             wp_posts.post_date  ) as mcomment_date
          from
             $wpdb->posts wp_posts
          where
             post_type = 'post'
             and post_status = 'publish'
             and comment_count > '0'
          order by
     mcomment_date desc  limit $limit";

        $query_result = $wpdb->get_results($query);

        return $query_result;
    }

    /**
     * @param $results
     * @return string
     */
    public static function format_last_commented_list($results)
    {
        $html = '<ul id="lastcommented">';
        $icon = '<svg style="height: 0.75em; padding-right: 4px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M10 9.25c-2.27 0-2.73-3.44-2.73-3.44C7 4.02 7.82 2 9.97 2c2.16 0 2.98 2.02 2.71 3.81 0 0-.41 3.44-2.68 3.44zm0 2.57L12.72 10c2.39 0 4.52 2.33 4.52 4.53v2.49s-3.65 1.13-7.24 1.13c-3.65 0-7.24-1.13-7.24-1.13v-2.49c0-2.25 1.94-4.48 4.47-4.48z"/></g></svg>';

        foreach ($results as $result) {

            $html .= '<li class="lastcommented">';

            $comment = Recent_Commented_Posts_Util::get_first_approved_comment($result->ID);

            //$startTime = microtime(true);
            $comment_url = get_comment_link($comment);
            //echo "<!-- Elapsed time is: ". (microtime(true) - $startTime) ." seconds -->";
            $authorname = $comment->comment_author;

            if (strlen($authorname) > 20) {
                $authorname = substr($authorname, 0, 18) . '...';
            }
            
            $comment_user = '<a href="' . $comment_url . '"><span class="comment-author-link">' .  get_the_title($result->ID) . '</span><br>' . $icon . $authorname . '</a>';

            $html .= $comment_user;

            $html .= '</li>';

        }

        $html .= '</ul>';

        return $html;
    }

    /**
     *
     * Returns the first approved comment of a post id.
     *
     * @param $post_id
     * @return comment object
     */

    public static function get_first_approved_comment($post_id)
    {
        $comments = get_comments(
            array('status' => 'approve',
                'post_id' => $post_id,
                'number' => 1)
        );
        $comment = $comments[0];

        return $comment;
    }

}
