<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tablesome_Event_Log extends \BerlinDB\Database\Table
{
    /**
     * Table name, without the global table prefix.
     *
     * @since 1.0.0
     * @var   string
     */
    public $name = 'tablesome_event_log';

    /**
     * Database version key (saved in _options or _sitemeta)
     *
     * @since 1.0.0
     * @var   string
     */
    protected $db_version_key = 'tablesome_event_log_version';

    /**
     * Optional description.
     *
     * @since 1.0.0
     * @var   string
     */
    public $description = 'Tablesome Event Logs Table';

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
        $schema = array();
        $schema[] = 'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
        $schema[] = 'user_id BIGINT UNSIGNED DEFAULT "0"';
        $schema[] = 'type VARCHAR(25) NULL';
        $schema[] = 'table_id BIGINT UNSIGNED DEFAULT "0"';
        $schema[] = 'trigger_id INT(11) UNSIGNED DEFAULT "0"';
        $schema[] = 'action_id INT(11) UNSIGNED DEFAULT "0"';
        $schema[] = 'status TINYINT UNSIGNED DEFAULT "0"';
        $schema[] = 'content TEXT DEFAULT NULL';
        $schema[] = 'user_ip VARBINARY(16) NULL';
        $schema[] = 'user_agent TEXT DEFAULT NULL';
        $schema[] = 'created_at DATETIME DEFAULT "0000-00-00 00:00:00" NULL';
        $schema[] = 'PRIMARY KEY (id)';

        $schema_content = implode(",", $schema);

        $this->schema = $schema_content;
    }

}