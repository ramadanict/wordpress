<?php

namespace Tablesome\Includes\Db;

interface CRUD_Interface
{
    /**
     * Get all rows
     *
     * @param integer $table_id | $post_id
     * @param  array $params
     * @return void
     */
    public function get_all_rows($table_id, array $params = array());

    /**
     * Get Selected Records
     * @param integer $table_id || $post_id
     * @param array $record_ids
     * @return array
     */
    public function get_rows($table_id, array $record_ids);

    /**
     * Get Single Record
     *
     * @param integer $table_id || $post_id
     * @param integer $record_id
     * @return mixed
     */
    public function get_row($table_id, $record_id);

    /**
     * Insert Single Record
     * @param integer $table_id
     * @param array $args
     * @return boolean
     */
    public function insert($table_id, array $args);

    /**
     * Inserting Bulk Data
     * @param integer $table_id
     * @param array $args
     * @return boolean
     */
    public function bulk_inserting($table_id, array $args);

    /**
     * Updating the tablesome meta table single row content
     * @see https://developer.wordpress.org/reference/classes/wpdb/#update-rows
     * @see https://developer.wordpress.org/reference/classes/wpdb/update/
     * @param integer $table_id || $post_id
     * @param integer $record_id
     * @param array $content
     * @return true
     *
     */
    public function update($table_id, $record_id, array $content, $rank_order = "");

    /**
     * remove row from wp tablesom_meta table
     * @see https://developer.wordpress.org/reference/classes/wpdb/#delete-rows
     * @param integer $table_id || $post_id
     * @param integer $record_id
     * @return boolean
     *
     */
    public function remove($table_id, $record_id);

    /**
     * remove bulk of records from tablesome custom table
     *
     * @param integer $table_id
     * @param array $record_ids
     * @return boolean
     */
    public function delete_records($table_id, array $record_ids);

    /**
     * Remove the table
     *
     * @param integer $table_id
     * @return boolean
     */
    public function delete_records_by_table_id($table_id);

    /**
     * Get records by limit
     *
     * @param integer $table_id | $post_id
     * @param mixed $args
     * @return void
     */
    public function get_paginated_records($table_id, array $args);

    /**
     * Get records count by table_ids
     *
     * @param integer|array $table_id|($table_ids | $post_ids)
     * @return integer
     */
    public function get_records_count($table_ids);

    /**
     * Copy table records
     * @param integer source $table_id
     * @param integer New $table_id
     */
    public function copy_table_records($table_id, $new_table_id);

    /**
     * Truncate the table
     */
    public function truncate_table();

    /**
     * Drop the table
     */
    public function drop_table();

}