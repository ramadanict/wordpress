<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tablesome_Event_Log_Table_Query extends BerlinDB\Database\Query
{

    /**
     * Name of the database table to query.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $table_name = 'tablesome_event_log';

    /**
     * String used to alias the database table in MySQL statement.
     *
     * Keep this short, but descriptive. I.E. "tr" for term relationships.
     *
     * This is used to avoid collisions with JOINs.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $table_alias = 'ts_event_log';

    /**
     * Name of class used to setup the database schema.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $table_schema = 'Tablesome_Event_Log_Table_Schema';

    /** Item ******************************************************************/

    /**
     * Name for a single item.
     *
     * Use underscores between words. I.E. "term_relationship"
     *
     * This is used to automatically generate action hooks.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $item_name = 'tablesome_event_log';

    /**
     * Plural version for a group of items.
     *
     * Use underscores between words. I.E. "term_relationships"
     *
     * This is used to automatically generate action hooks.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $item_name_plural = 'tablesome_events_log';

    /**
     * Name of class used to turn IDs into first-class objects.
     *
     * This is used when looping through return values to guarantee their shape.
     *
     * @since 1.0.0
     * @var   mixed
     */
    protected $item_shape = 'Tablesome_Event_Log_Table_Row';
}