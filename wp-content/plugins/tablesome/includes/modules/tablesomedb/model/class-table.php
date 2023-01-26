<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tablesome_Table extends \BerlinDB\Database\Table

{
    public $table_schema = array();

    public function __construct($args)
    {
        $this->name = isset($args['table_name']) ? $args['table_name'] : '';
        $this->table_schema = isset($args['table_schema']) ? $args['table_schema'] : [];

        if (empty($this->name)) {
            return;
        }

        parent::__construct();
        // error_log("Books_Table args: " . print_r($args, true));
    }

    /**
     * Table name, without the global table prefix.
     *
     * @since 1.0.0
     * @var   string
     */
    public $name = '';

    /**
     * Database version key (saved in _options or _sitemeta)
     *
     * @since 1.0.0
     * @var   string
     */
    protected $db_version_key = 'tablesome_table_version';

    /**
     * Optional description.
     *
     * @since 1.0.0
     * @var   string
     */
    public $description = 'Tablesome Tables';

    /**
     * Database version.
     *
     * @since 1.0.0
     * @var   mixed
     */
    protected $version = '1.0.0';

    /**
     * Key => value array of versions => methods.
     *
     * @since 1.0.0
     * @var   array
     */
    protected $upgrades = array();

    /**
     * Setup this database table.
     *
     * @since 1.0.0
     */
    protected function set_schema()
    {
        $this->schema = $this->table_schema;
    }

    public function modify_the_table($table_meta, $table_columns, $requests)
    {
        $columns_deleted = isset($requests['columns_deleted']) ? $requests['columns_deleted'] : [];

        $this->delete_columns($columns_deleted);

        /** Add new columns to DB */
        foreach ($table_columns as $table_column) {
            $result = $this->column_exists($table_column);
            if ($result == false) {
                $this->get_db()->query("ALTER TABLE {$this->table_name} ADD COLUMN {$table_column} text default '';");
            }
        }

        $this->modified_table_meta_columns($table_meta);

        return $this->is_success(true);
    }

    public function delete_columns($columns_deleted)
    {
        if (empty($columns_deleted)) {return;}

        foreach ($columns_deleted as $column_id) {
            $cell_column_name = 'column_' . $column_id;
            $cell_column_meta_name = $cell_column_name . '_meta';

            $column_exists = $this->column_exists($cell_column_name);
            if ($column_exists) {
                $this->get_db()->query("ALTER TABLE {$this->table_name} DROP COLUMN {$cell_column_name};");
            }

            $meta_column_exists = $this->column_exists($cell_column_meta_name);
            if ($meta_column_exists) {
                $this->get_db()->query("ALTER TABLE {$this->table_name} DROP COLUMN {$cell_column_meta_name};");
            }
        }
    }

    public function modified_table_meta_columns($table_meta)
    {
        $meta_columns = isset($table_meta['columns']) ? $table_meta['columns'] : [];

        if (empty($meta_columns)) {return;}

        foreach ($meta_columns as $meta_column) {

            // Now, meta columns need only to the format 'url' and 'button'.
            $need_meta_column = ($meta_column['format'] == 'url' || $meta_column['format'] == 'button' || $meta_column['format'] == 'file');
            $meta_column_name = 'column_' . $meta_column['id'] . '_meta';

            // Check the meta column is exists in DB
            $db_meta_column_exists = $this->column_exists($meta_column_name);
            // Remove meta column if the 'url' or 'button' format columns doesn't use in frontend table
            $remove_meta_column = ($db_meta_column_exists && !$need_meta_column);
            if ($remove_meta_column) {
                $this->get_db()->query("ALTER TABLE {$this->table_name} DROP COLUMN {$meta_column_name};");
            }

            if (!$need_meta_column) {
                continue;
            }

            // Add meta column if the column doesn't exists DB.
            if ($db_meta_column_exists == false) {
                $this->get_db()->query("ALTER TABLE {$this->table_name} ADD COLUMN {$meta_column_name} text default '';");
            }
        }
    }

}
