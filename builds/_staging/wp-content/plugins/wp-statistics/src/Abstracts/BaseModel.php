<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Utils\Query;


/**
 * Todo object cache, consider historical, hooks, filters, etc
 */
abstract class BaseModel
{
    protected $query = Query::class;

    /**
     * @param $args
     * @param $defaults
     * @return mixed|null
     */
    protected function parseArgs($args, $defaults = [])
    {
        $args = wp_parse_args($args, $defaults);
        $args = $this->parseQueryParamArg($args);

        return apply_filters('wp_statistics_data_{child-method-name}_args', $args);
    }

    /**
     * Parses the query_param argument.
     *
     * @return array The parsed arguments.
     */
    private function parseQueryParamArg($args)
    {
        if (!empty($args['query_param'])) {
            $select = $this->query;
            $uri    = $select::select('uri')
                ->from('pages')
                ->where('page_id', '=', $args['query_param'])
                ->getVar();

            $args['query_param'] = !empty($uri) ? $uri : '';
        }

        return $args;
    }
}