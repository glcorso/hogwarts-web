<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

/**
 * Helpers - funções utilizadas em toda a aplicação
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */

if (!function_exists('oci_bind_array_by_name')) {
    /**
     * Binds a PHP array to an Oracle PL/SQL array parameter
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_bind_array_by_name(
        resource $statement,
        string $name,
        array &$var_array,
        int $max_table_length,
        int $max_item_length = -1,
        int $type = SQLT_AFC
    ) {
        return false;
    }
}

if (!function_exists('oci_bind_by_name')) {
    /**
     * Binds a PHP variable to an Oracle placeholder
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_bind_by_name(
        resource $statement,
        string $bv_name,
        mixed &$variable,
        int $maxlength = -1,
        int $type = SQLT_CHR
    ) {
        return false;
    }
}

if (!function_exists('oci_cancel')) {
    /**
     * Cancels reading from cursor
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_cancel(resource $statement)
    {
        return false;
    }
}

if (!function_exists('oci_client_version')) {
    /**
     * Returns the Oracle client library version
     *
     * @return string  Returns the version number as a string.
     */
    function oci_client_version()
    {
        return '11.2.0.2';
    }
}

if (!function_exists('oci_close')) {
    /**
     * Closes an Oracle connection
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_close(resource $connection)
    {
        return false;
    }
}

if (!function_exists('oci_commit')) {
    /**
     * Commits the outstanding database transaction
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_commit(resource $connection)
    {
        return false;
    }
}

if (!function_exists('oci_connect')) {
    /**
     * Connect to an Oracle database
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_connect(resource $connection)
    {
        return false;
    }
}

if (!function_exists('oci_define_by_name')) {
    /**
     * Associates a PHP variable with a column for query fetches
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_define_by_name(resource $statement, string $column_name, mixed &$variable, int $type = SQLT_CHR)
    {
        return false;
    }
}

if (!function_exists('oci_error')) {
    /**
     * Returns the last error found
     *
     * @return mixed If no error is found, oci_error() returns FALSE. Otherwise, oci_error() returns the error information as an associative array.
     */
    function oci_error(resource $resource)
    {
        return false;
    }
}

if (!function_exists('oci_execute')) {
    /**
     * Executes a statement
     *
     * @return bool Retorna TRUE em caso de sucesso ou FALSE em caso de falha.
     */
    function oci_execute(resource $statement, int $mode = OCI_COMMIT_ON_SUCCESS)
    {
        return false;
    }
}

if (!function_exists('oci_fetch_all')) {
    /**
     * Fetches multiple rows from a query into a two-dimensional array
     *
     * @return mixed  Returns the number of rows in output, which may be 0 or more, ou FALSE em caso de falha.
     */
    function oci_fetch_all(
        resource $statement,
        array &$output,
        int $skip = 0,
        int $maxrows = -1,
        int $flags = OCI_FETCHSTATEMENT_BY_COLUMN + OCI_ASSOC
    ) {
        return 0;
    }
}

if (!function_exists('oci_fetch_array')) {
    /**
     * Returns the next row from a query as an associative or numeric array
     *
     * @return mixed Returns an array with associative and/or numeric indices. If there are no more rows in the statement then FALSE is returned.
     */
    function oci_fetch_array(resource $statement, int $mode)
    {
        return array();
    }
}

// oci_fetch_array — Returns the next row from a query as an associative or numeric array
// oci_fetch_assoc — Returns the next row from a query as an associative array
// oci_fetch_object — Returns the next row from a query as an object
// oci_fetch_row — Returns the next row from a query as a numeric array
// oci_fetch — Fetches the next row from a query into internal buffers
// oci_field_is_null — Checks if a field in the currently fetched row is NULL
// oci_field_name — Returns the name of a field from the statement
// oci_field_precision — Tell the precision of a field
// oci_field_scale — Tell the scale of the field
// oci_field_size — Returns field's size
// oci_field_type_raw — Tell the raw Oracle data type of the field
// oci_field_type — Returns a field's data type name
// oci_free_descriptor — Frees a descriptor
// oci_free_statement — Frees all resources associated with statement or cursor
// oci_get_implicit_resultset — Returns the next child statement resource from a parent statement resource that has Oracle Database 12c Implicit Result Sets
// oci_internal_debug — Enables or disables internal debug output
// oci_lob_copy — Copies large object
// oci_lob_is_equal — Compares two LOB/FILE locators for equality
// oci_new_collection — Allocates new collection object
// oci_new_connect — Connect to the Oracle server using a unique connection
// oci_new_cursor — Allocates and returns a new cursor (statement handle)
// oci_new_descriptor — Initializes a new empty LOB or FILE descriptor
// oci_num_fields — Returns the number of result columns in a statement
// oci_num_rows — Returns number of rows affected during statement execution
// oci_parse — Prepares an Oracle statement for execution
// oci_password_change — Changes password of Oracle's user
// oci_pconnect — Connect to an Oracle database using a persistent connection
// oci_result — Returns field's value from the fetched row
// oci_rollback — Rolls back the outstanding database transaction
// oci_server_version — Returns the Oracle Database version
// oci_set_action — Sets the action name
// oci_set_client_identifier — Sets the client identifier
// oci_set_client_info — Sets the client information
// oci_set_edition — Sets the database edition
// oci_set_module_name — Sets the module name
// oci_set_prefetch — Sets number of rows to be prefetched by queries
// oci_statement_type — Returns the type of a statement
