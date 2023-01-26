<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tablesome_Table_Query extends BerlinDB\Database\Query
{
    public function __construct($query)
    {
        $this->schema_columns = $query['schema_columns'];
        $this->table_name = $query['table_name'];

        parent::__construct($query);
    }

    public function set_columns()
    {
        // Bail if no table schema
        if (!class_exists($this->table_schema)) {
            return;
        }

        // Invoke a new table schema class
        $schema = new $this->table_schema($this->schema_columns);

        // Maybe get the column objects
        if (!empty($schema->columns)) {
            $this->columns = $schema->columns;
        }
    }

    /**
     * Name of the database table to query.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $table_name = '';

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
    protected $table_alias = 'ts';

    /**
     * Name of class used to setup the database schema.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $table_schema = 'Tablesome_Table_Schema';

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
    protected $item_name = 'tablesome_table';

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
    protected $item_name_plural = 'tablesome_tables';

    /**
     * Name of class used to turn IDs into first-class objects.
     *
     * This is used when looping through return values to guarantee their shape.
     *
     * @since 1.0.0
     * @var   mixed
     */
    protected $item_shape = 'Tablesome_Table_Record';
}